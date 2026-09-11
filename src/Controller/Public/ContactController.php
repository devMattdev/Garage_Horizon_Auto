<?php

declare(strict_types=1);

namespace App\Controller\Public;

use App\Entity\Demande;
use App\Enum\TypeDemande;
use App\Form\DemandeType;
use App\Service\Antiabus\TurnstileValidator;
use App\Service\Contact\DemandeHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Point d'entrée public unique en écriture (back.md section 7.1) : soumission du
 * formulaire de demande, protégée par CSRF + honeypot + Turnstile + limitation de débit.
 */
class ContactController extends AbstractController
{
    public function __construct(
        private readonly TurnstileValidator $turnstileValidator,
        private readonly DemandeHandler $demandeHandler,
        private readonly RateLimiterFactory $demandeParIpLimiter,
    ) {
    }

    #[Route('/contact', name: 'contact')]
    public function __invoke(Request $request): Response
    {
        $demande = new Demande();

        if ($request->query->has('type')) {
            $type = TypeDemande::tryFrom((string) $request->query->get('type'));
            if (null !== $type) {
                $demande->setType($type);
            }
        }

        $form = $this->createForm(DemandeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Honeypot : un champ rempli signale un robot, on ignore silencieusement.
            if ($form->get('siteWeb')->getData()) {
                return $this->redirectToRoute('contact_confirmation');
            }

            $limite = $this->demandeParIpLimiter->create($request->getClientIp())->consume(1);
            if (!$limite->isAccepted()) {
                $form->addError(new FormError(
                    'Trop de demandes envoyées depuis cette adresse. Merci de réessayer plus tard.',
                ));

                return $this->render('public/contact/formulaire.html.twig', ['form' => $form]);
            }

            $jetonTurnstile = $form->get('cfTurnstileResponse')->getData();
            if (!$this->turnstileValidator->estValide($jetonTurnstile, $request->getClientIp())) {
                $form->addError(new FormError(
                    'La vérification anti-robot a échoué. Merci de réessayer.',
                ));

                return $this->render('public/contact/formulaire.html.twig', ['form' => $form]);
            }

            $this->demandeHandler->traiter($demande);

            // POST-Redirect-GET : empêche le double envoi (back.md section 5.3).
            return $this->redirectToRoute('contact_confirmation');
        }

        return $this->render('public/contact/formulaire.html.twig', ['form' => $form]);
    }

    #[Route('/contact/confirmation', name: 'contact_confirmation')]
    public function confirmation(): Response
    {
        return $this->render('public/contact/confirmation.html.twig');
    }
}
