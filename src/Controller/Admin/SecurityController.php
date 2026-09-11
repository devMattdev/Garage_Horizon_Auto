<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/admin/connexion', name: 'admin_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('@EasyAdmin/page/login.html.twig', [
            'csrf_token_intention' => 'authenticate',
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'username_label' => 'Adresse e-mail',
            'password_label' => 'Mot de passe',
            'sign_in_label' => 'Connexion',
            'page_title' => 'Horizon Auto',
        ]);
    }

    #[Route(path: '/admin/deconnexion', name: 'admin_logout')]
    public function logout(): never
    {
        throw new \LogicException('Cette méthode ne devrait jamais être appelée : elle est interceptée par le firewall de déconnexion.');
    }
}
