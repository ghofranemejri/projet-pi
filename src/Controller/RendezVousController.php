<?php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Form\RendezVousType;
use App\Repository\RendezVousRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\PdfGenerator;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;


#[Route('/rendez')]
final class RendezVousController extends AbstractController
{
    #[Route('/back', name: 'app_rendez_vous_index_back', methods: ['GET'])]
    public function backindex(Request $request, RendezVousRepository $rendezVousRepository): Response
    {
        $query = $request->query->get('q', '');
        $rendezVouses = $query
            ? $rendezVousRepository->searchRendezVous($query)
            : $rendezVousRepository->findAll();

        return $this->render('rendez_vous_back/index.html.twig', [
            'rendez_vouses' => $rendezVouses,
            'query' => $query,
        ]);
    }

    #[Route('/back/new', name: 'app_rendez_vous_new_back', methods: ['GET', 'POST'])]
    public function backnew(Request $request, EntityManagerInterface $entityManager): Response
    {
        $rendezVou = new RendezVous();
        $form = $this->createForm(RendezVousType::class, $rendezVou);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            dump($form->getData());
            dump($rendezVou->getPatient());
            dump($rendezVou->getMedecin());
        
            if ($form->isValid()) {
                $entityManager->persist($rendezVou);
                $entityManager->flush();
        
                return $this->redirectToRoute('app_rendez_vous_index_back');
            }
        
            // Dumping form errors
            foreach ($form->getErrors(true) as $error) {
                dump($error->getMessage());
            }
        
            die('Form submission failed.');
        }
        

        return $this->render('rendez_vous_back/new.html.twig', [
            'rendez_vou' => $rendezVou,
            'form' => $form,
        ]);
    }

    
    #[Route('', name: 'app_rendez_vous_index', methods: ['GET'])]
    public function index(Request $request, RendezVousRepository $rendezVousRepository): Response
    {
        $query = $request->query->get('q', '');
        $rendezVouses = $query
            ? $rendezVousRepository->searchRendezVous($query)
            : $rendezVousRepository->findAll();

        return $this->render('rendez_vous/index.html.twig', [
            'rendez_vouses' => $rendezVouses,
            'query' => $query,
        ]);
    }

    #[Route('/new', name: 'app_rendez_vous_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $rendezVou = new RendezVous();
        $form = $this->createForm(RendezVousType::class, $rendezVou);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($rendezVou);
            $entityManager->flush();

            $this->addFlash('success', 'Votre rendez-vous a été enregistré avec succès.');
            $this->envoyerEmailConfirmation($rendezVou, $mailer);

            return $this->redirectToRoute('app_front');
        }

        return $this->render('rendez_vous/new.html.twig', [
            'rendez_vou' => $rendezVou,
            'form' => $form->createView(),
        ]);
    }

    private function envoyerEmailConfirmation(RendezVous $rendezVou, MailerInterface $mailer)
    {
        $patient = $rendezVou->getPatient();
        $email = (new Email())
            ->from('votre-email@domain.com')
            ->to($patient->getEmail())
            ->subject('Confirmation de votre rendez-vous')
            ->html('<p>Bonjour </p>
                    <p>Votre rendez-vous a été confirmé pour le ' . $rendezVou->getDate()->format('d-m-Y H:i:s') . '.</p>');

        $mailer->send($email);
    }

    
    #[Route('/back/rendez-vous/recherche', name: 'app_rendez_vous_search', methods: ['GET'])]
    public function recherche(Request $request, RendezVousRepository $repository): Response
    {
        $query = $request->query->get('q', '');
        $rendezVouses = $repository->searchRendezVous($query);

        if ($request->isXmlHttpRequest()) {
            return $this->render('rendez_vous_back/search.html.twig', [
                'rendez_vouses' => $rendezVouses,
                'query' => $query,
            ]);
        }

        return $this->render('rendez_vous_back/index.html.twig', [
            'rendez_vouses' => $rendezVouses,
            'query' => $query,
        ]);
    }

    #[Route('/back/rendez_vous/pdf', name: 'app_rendez_vous_pdf', methods: ['GET'])]
    public function generatePdf(RendezVousRepository $rendezVousRepository, PdfGenerator $pdfGenerator): Response
    {
        $rendezVouses = $rendezVousRepository->findAll();
        $htmlContent = $this->renderView('rendez_vous_back/pdf.html.twig', [
            'rendez_vouses' => $rendezVouses,
        ]);

        $pdf = $pdfGenerator->generatePdf($htmlContent);

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="rendezvous_list.pdf"',
        ]);
    }







    #[Route('/back/{id}/changer-statut', name: 'app_rendez_vous_changer_statut', methods: ['POST'])]
    public function changerStatut(RendezVous $rendezVou, Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $nouveauStatut = $request->get('statut');
        $rendezVou->setStatut($nouveauStatut);

        $entityManager->flush();
        $this->envoyerEmailStatut($rendezVou, $mailer);

        return $this->redirectToRoute('app_rendez_vous_index_back');
    }

    private function envoyerEmailStatut(RendezVous $rendezVou, MailerInterface $mailer)
    {
        $patient = $rendezVou->getPatient();
        $email = (new Email())
            ->from('rouaatouil3@gmail.com')
            ->to($patient->getEmail())
            ->subject('Statut de votre Rendez-vous');

        switch ($rendezVou->getStatut()) {
            case 'confirmé':
                $email->html('<p>Bonjour</p>
                              <p>Votre rendez-vous a été confirmé pour le ' . $rendezVou->getDate()->format('d-m-Y H:i:s') . '.</p>');
                break;

            case 'en attente':
                $email->html('<p>Bonjour </p>
                              <p>Votre rendez-vous est actuellement en attente. Nous vous tiendrons informé dès que possible.</p>');
                break;

            case 'refusé':
                $email->html('<p>Bonjour </p>
                              <p>Nous sommes désolés, votre rendez-vous a été refusé.</p>');
                break;
        }

        $mailer->send($email);
    }







    #[Route('/back/{id}/show', name: 'app_rendez_vous_show_back', methods: ['GET'])]
    public function backshow(RendezVous $rendezVou): Response
    {
        return $this->render('rendez_vous_back/show.html.twig', [
            'rendez_vou' => $rendezVou,
        ]);
    }

    #[Route('/back/{id}/edit', name: 'app_rendez_vous_edit_back', methods: ['GET', 'POST'])]
    public function backedit(Request $request, RendezVous $rendezVou, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RendezVousType::class, $rendezVou);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_rendez_vous_index_back');
        }

        return $this->render('rendez_vous_back/edit.html.twig', [
            'rendez_vou' => $rendezVou,
            'form' => $form,
        ]);
    }

    #[Route('/back/{id}/delete', name: 'app_rendez_vous_delete_back', methods: ['POST'])]
    public function backdelete(Request $request, RendezVous $rendezVou, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$rendezVou->getId(), $request->request->get('_token'))) {
            $entityManager->remove($rendezVou);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_rendez_vous_index_back');
    }

    #[Route('/{id}/show', name: 'app_rendez_vous_show', methods: ['GET'])]
    public function show(RendezVous $rendezVou): Response
    {
        return $this->render('rendez_vous/show.html.twig', [
            'rendez_vou' => $rendezVou,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_rendez_vous_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, RendezVous $rendezVou, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RendezVousType::class, $rendezVou);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_rendez_vous_index');
        }

        return $this->render('rendez_vous/edit.html.twig', [
            'rendez_vou' => $rendezVou,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_rendez_vous_delete', methods: ['POST'])]
    public function delete(Request $request, RendezVous $rendezVou, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$rendezVou->getId(), $request->request->get('_token'))) {
            $entityManager->remove($rendezVou);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_rendez_vous_index');
    }

    #[Route('/back/{id}/send-email', name: 'app_rendez_vous_send_email', methods: ['POST'])]
public function sendEmail(RendezVous $rendezVou, MailerInterface $mailer): Response
{
    // Get the patient's email from the rendezVous entity
    $patient = $rendezVou->getPatient();
    
    // Prepare the email content
    $email = (new Email())
        ->from('rouaatouil3@gmail.com') // Your email address
        ->to($patient->getEmail()) // Patient's email address
        ->subject('Notification de Rendez-vous')
        ->html('<p>Bonjour </p>
                <p>Nous vous rappelons que vous avez un rendez-vous prévu pour le ' . $rendezVou->getDate()->format('d-m-Y H:i:s') . '.</p>');

    // Send the email
    $mailer->send($email);

    // Add a flash message to inform the user that the email was sent
    $this->addFlash('success', 'L\'email a été envoyé avec succès.');

    // Redirect back to the list of rendez-vous
    return $this->redirectToRoute('app_rendez_vous_index_back');
}


}
