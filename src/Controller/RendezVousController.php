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
use Symfony\Component\Mailer\MailerInterface; // Assurez-vous d'importer cette interface
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

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($rendezVou);
            $entityManager->flush();

            return $this->redirectToRoute('app_rendez_vous_index_back');
        }

        return $this->render('rendez_vous_back/new.html.twig', [
            'rendez_vou' => $rendezVou,
            'form' => $form,
        ]);
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
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $rendezVou = new RendezVous();
    $form = $this->createForm(RendezVousType::class, $rendezVou);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Enregistrement du rendez-vous
        $entityManager->persist($rendezVou);
        $entityManager->flush();

        // Ajouter le message flash
        $this->addFlash('success', 'Votre rendez-vous a été enregistré avec succès.');

        // Rediriger vers la page front
        return $this->redirectToRoute('app_front');
    }

    return $this->render('rendez_vous/new.html.twig', [
        'rendez_vou' => $rendezVou,
        'form' => $form->createView(), // Assurez-vous de passer form->createView()
    ]);
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

    #[Route('/back/rendez-vous/recherche', name: 'app_rendez_vous_search', methods: ['GET'])]
    public function recherche(Request $request, RendezVousRepository $repository): Response
    {
        $query = $request->query->get('q', '');
        $rendez_vouses = $repository->searchRendezVous($query); // Recherche des rendez-vous selon le nom ou critère

        if ($request->isXmlHttpRequest()) { // Vérifie si la requête est AJAX
            return $this->render('rendez_vous_back/search.html.twig', [
                'rendez_vouses' => $rendez_vouses,
                'query' => $query
            ]);
        }

        return $this->render('rendez_vous_back/index.html.twig', [
            'rendez_vouses' => $rendez_vouses,
            'query' => $query,
        ]);
    }

    // Correction du PDF : La méthode GET est plus appropriée pour générer un PDF
    #[Route('/back/rendez_vous/pdf', name: 'app_rendez_vous_pdf', methods: ['GET'])]
    public function generatePdf(RendezVousRepository $rendezVousRepository, PdfGenerator $pdfGenerator): Response
    {
        // Récupérer tous les rendez-vous
        $rendezVouses = $rendezVousRepository->findAll();

        // Générer le contenu HTML pour le PDF
        $htmlContent = $this->renderView('rendez_vous_back/pdf.html.twig', [
            'rendez_vouses' => $rendezVouses,
        ]);

        // Utiliser le service PdfGenerator pour générer le PDF
        $pdf = $pdfGenerator->generatePdf($htmlContent);

        // Retourner le PDF comme une réponse HTTP
        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="rendezvous_list.pdf"',
        ]);
    }
    private function envoyerEmailStatut(RendezVous $rendezVou, MailerInterface $mailer)
    {
        $patient = $rendezVou->getPatient(); // On récupère l'objet patient
        $email = (new Email())
            ->from('no-reply@votresite.com')
            ->to($patient->getEmail()) // L'email du patient
            ->subject('Statut de votre Rendez-vous');

        // Construction du corps de l'email en fonction du statut
        switch ($rendezVou->getStatut()) {
            case 'confirmé':
                $email->html('<p>Bonjour ' . $patient->getNom() . ',</p>
                              <p>Votre rendez-vous a été confirmé pour le ' . $rendezVou->getDate()->format('d-m-Y H:i:s') . '.</p>');
                break;

            case 'en attente':
                $email->html('<p>Bonjour ' . $patient->getNom() . ',</p>
                              <p>Votre rendez-vous est actuellement en attente. Nous vous tiendrons informé dès que possible.</p>');
                break;

            case 'refusé':
                $email->html('<p>Bonjour ' . $patient->getNom() . ',</p>
                              <p>Nous sommes désolés, votre rendez-vous a été refusé.</p>');
                break;
        }

        // Envoi de l'email
        $mailer->send($email);
    }

    // Exemple de route pour changer le statut d'un rendez-vous et envoyer l'email
    #[Route('/back/{id}/changer-statut', name: 'app_rendez_vous_changer_statut', methods: ['POST'])]
    public function changerStatut(RendezVous $rendezVou, Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        // Mise à jour du statut à partir du formulaire
        $nouveauStatut = $request->get('statut'); // Récupérer le statut envoyé dans la requête
        $rendezVou->setStatut($nouveauStatut);

        // Sauvegarde des modifications dans la base de données
        $entityManager->flush();

        // Envoi de l'email
        $this->envoyerEmailStatut($rendezVou, $mailer);

        // Rediriger vers la page des rendez-vous
        return $this->redirectToRoute('app_rendez_vous_index_back');
    }
}