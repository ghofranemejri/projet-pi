<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface; // Import nécessaire
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;



class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request, ValidatorInterface $validator): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        if ($request->isMethod('POST')) {
            $recaptchaToken = $request->request->get('g-recaptcha-response');
            if (!$recaptchaToken) {
                $this->addFlash('error', 'Erreur reCAPTCHA : Token manquant.');
                return $this->redirectToRoute('app_login');
            }

            $recaptchaSecretKey = $_ENV['GOOGLE_RECAPTCHA_SECRET_KEY'];
            $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptchaSecretKey . '&response=' . $recaptchaToken);
            $responseKeys = json_decode($response, true);

            if (intval($responseKeys["success"]) !== 1) {
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
        throw new \LogicException('Cette méthode est interceptée par le firewall de Symfony.');
    }

    #[Route('/test-email', name: 'app_test_email', methods: ['GET'])]
    public function testEmail(MailerInterface $mailer, EntityManagerInterface $entityManager): Response
    {
        $email = (new Email())
            ->from('dadou.2001@icloud.com')
            ->to('hammamiazza84@gmail.com')
            ->subject('Test depuis Symfony')
            ->text('Mail envoyé')
            ->subject('Réinitialisation de votre mot de passe')
            ->html('<p>Bonjour,</p>
            <p>Vous avez demandé une réinitialisation de votre mot de passe.</p>
            <p>Votre nouveau mot de passe : 123456</p>
            <p>Cordialement,</p>
            <p>L\'équipe Support</p>');
            $mailer->send($email);
        try {
            $mailer->send($email);

            // Enregistrer le message dans la base de données
            $message = new Message();
            $message->setSubject('Test depuis Symfony')
                    ->setBody('Ça marche !')
                    ->setSender('dadou.2001@icloud.com')
                    ->setRecipient('hammamiazza84@gmail.com')
                    ->setSentAt(new \DateTime());
            $entityManager->persist($message);
            $entityManager->flush();

            $this->addFlash('success', 'Email envoyé avec succès ! Vérifiez Mailtrap.');
        } catch (TransportExceptionInterface $e) {
            echo "Erreur lors de l'envoi de l'email : " . $e->getMessage();
        }
         catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
        }
        

        return $this->redirectToRoute('app_inbox');
    
    }
    #[Route('/forgot-password', name: 'app_forgot_password')]
public function forgotPassword(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager): Response
{
        $email ="dadou.2001@icloud.com"; // $request->request->get('email');
        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            $this->addFlash('error', 'Aucun utilisateur trouvé avec cet email.');
            return $this->redirectToRoute('app_forgot_password');
        }

        // Générer un token unique
        $resetToken = bin2hex(random_bytes(32));
        $user->setResetToken($resetToken);
        $entityManager->flush();

        // Construire l'email de réinitialisation
        $email = (new Email())
            ->from('dadou.2001@icloud.com')
            ->to($user->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->html('<p>Cliquez ici pour réinitialiser votre mot de passe : 
                   <a href="' . $this->generateUrl('app_reset_password', ['token' => $resetToken], UrlGeneratorInterface::ABSOLUTE_URL) . '">Réinitialiser</a></p>');

        $mailer->send($email);
        $this->addFlash('success', 'Un email de réinitialisation a été envoyé.');

        return $this->redirectToRoute('app_login');

}
#[Route('/reset-password/{token}', name: 'app_reset_password')]
public function resetPassword(string $token, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
{
    $user = $entityManager->getRepository(User::class)->findOneBy(['resetToken' => $token]);

    if (!$user) {
        $this->addFlash('error', 'Lien invalide ou expiré.');
        return $this->redirectToRoute('app_forgot_password');
    }

    if ($request->isMethod('POST')) {
        $newPassword = $request->request->get('password');
        $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);
        $user->setResetToken(null); // Supprimer le token après utilisation
        $entityManager->flush();

        $this->addFlash('success', 'Mot de passe mis à jour.');
        return $this->redirectToRoute('app_login');
    }

    return $this->render('security/reset_password.html.twig', ['token' => $token]);
}


    

    
    #[Route('/inbox', name: 'app_inbox', methods: ['GET'])]
    public function inbox(EntityManagerInterface $entityManager): Response
    {
        $messages = $entityManager->getRepository(Message::class)->findAll();
        
        $totalSent = count($messages);
        $lastMessage = $totalSent > 0 ? $messages[$totalSent - 1] : null;
        $maxSize = 50; // Limite arbitraire

        return $this->render('security/inbox.html.twig', [
            'inboxes' => 1, // Par simplicité, on suppose 1 boîte de réception
            'totalSent' => $totalSent,
            'messages' => $messages,
            'maxSize' => $maxSize,
            'lastMessage' => $lastMessage,
        ]);
    }
}