<?php

declare(strict_types=1);

namespace App\Controller\Public;

use App\Entity\Demande;
use App\Enum\TypeDemande;
use App\Form\DemandeType;
use App\Repository\FermetureExceptionnelleRepository;
use App\Repository\PlageHoraireRepository;
use App\Repository\PrestationRepository;
use App\Repository\VehiculeRepository;
use App\Service\Antiabus\TurnstileValidator;
use App\Service\Contact\DemandeHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
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
        private readonly RateLimiterFactoryInterface $demandeParIpLimiter,
        private readonly PrestationRepository $prestationRepository,
        private readonly VehiculeRepository $vehiculeRepository,
        private readonly PlageHoraireRepository $plageHoraireRepository,
        private readonly FermetureExceptionnelleRepository $fermetureExceptionnelleRepository,
    ) {
    }

    #[Route('/rendez-vous', name: 'rendez_vous')]
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

        $prestationSlug = $request->query->getString('prestation');
        if ('' !== $prestationSlug) {
            $prestation = $this->prestationRepository->findOneBy(['slug' => $prestationSlug, 'actif' => true]);
            if (null !== $prestation) {
                $demande->setType(TypeDemande::DEVIS)->setPrestation($prestation);
            }
        }

        $vehiculeReference = $request->query->getString('vehicule');
        if ('' !== $vehiculeReference) {
            $vehicule = $this->vehiculeRepository->findOneBy(['reference' => $vehiculeReference]);
            if (null !== $vehicule && \App\Enum\StatutVehicule::PUBLIE === $vehicule->getStatut()) {
                $demande->setType(TypeDemande::VEHICULE)->setVehicule($vehicule);
            }
        }

        $form = $this->createForm(DemandeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Honeypot : un champ rempli signale un robot, on ignore silencieusement.
            if ($form->get('siteWeb')->getData()) {
                return $this->redirectToRoute('rendez_vous_confirmation');
            }

            $limite = $this->demandeParIpLimiter->create($request->getClientIp())->consume(1);
            if (!$limite->isAccepted()) {
                $form->addError(new FormError(
                    'Trop de demandes envoyées depuis cette adresse. Merci de réessayer plus tard.',
                ));

                return $this->renderDemande($request, $form);
            }

            $jetonTurnstile = $form->get('cfTurnstileResponse')->getData();
            if (!$this->turnstileValidator->estValide($jetonTurnstile, $request->getClientIp())) {
                $form->addError(new FormError(
                    'La vérification anti-robot a échoué. Merci de réessayer.',
                ));

                return $this->renderDemande($request, $form);
            }

            $this->demandeHandler->traiter($demande);

            // POST-Redirect-GET : empêche le double envoi (back.md section 5.3).
            return $this->redirectToRoute('rendez_vous_confirmation');
        }

        return $this->renderDemande($request, $form);
    }

    /**
     * @param FormInterface<Demande> $form
     */
    private function renderDemande(Request $request, FormInterface $form): Response
    {
        if ('contact' !== $request->attributes->get('_route')) {
            return $this->render('public/contact/formulaire.html.twig', ['form' => $form]);
        }

        return $this->render('public/contact/index.html.twig', [
            'form' => $form,
            'horaires' => $this->plageHoraireRepository->findToutesIndexeesParJour(),
            'fermetures' => $this->fermetureExceptionnelleRepository->trouverAvenir(new \DateTimeImmutable('today')),
        ]);
    }

    #[Route('/rendez-vous/confirmation', name: 'rendez_vous_confirmation')]
    public function confirmation(): Response
    {
        return $this->render('public/contact/confirmation.html.twig');
    }
}
