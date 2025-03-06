<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request, ValidatorInterface $validator): Response
    {
        // Récupérer l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        // Dernier email saisi par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        // Vérifier la présence du reCAPTCHA uniquement si le formulaire est soumis
        if ($request->isMethod('POST')) {
            $recaptchaToken = $request->request->get('recaptcha_token');

            if (!$recaptchaToken) {
                $this->addFlash('error', 'Erreur reCAPTCHA : Token manquant.');
                return $this->redirectToRoute('app_login');
            }

            // Créer une instance de la contrainte Recaptcha3
            $constraint = new Recaptcha3();
            $violations = $validator->validate($recaptchaToken, $constraint);

            if ($violations->count() > 0) {
                // Ajouter un message d'erreur si la validation échoue
                $this->addFlash('error', 'Échec du reCAPTCHA. Veuillez réessayer.');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'google_recaptcha_site_key' => $_ENV['GOOGLE_RECAPTCHA_SITE_KEY'],

        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        // Cette méthode sera interceptée par le firewall de Symfony.
        throw new \LogicException('Cette méthode est interceptée par le firewall de Symfony.');
    }
}
