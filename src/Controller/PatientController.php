<?php

namespace App\Controller;

use App\Repository\PatientRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PatientController extends AbstractController
{
    /**
     * @Route("/patients", name="app_patients")
     */
    public function list(Request $request, PatientRepository $patientRepository, PaginatorInterface $paginator): Response
    {
        $query = $patientRepository->createQueryBuilder('p')->getQuery();
        $patients = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('user/patients.html.twig', [
            'patients' => $patients,
        ]);
    }

    /**
     * @Route("/patients/sorted", name="app_patients_sorted")
     */
    public function sorted(Request $request, PatientRepository $patientRepository, PaginatorInterface $paginator): Response
    {
        $sortBy = $request->query->get('sortBy', 'nom');
        $order = $request->query->get('order', 'ASC');

        $allowedSortBy = ['nom', 'prenom', 'email'];
        $allowedOrder = ['ASC', 'DESC'];

        if (!in_array($sortBy, $allowedSortBy)) {
            $sortBy = 'nom';
        }
        if (!in_array($order, $allowedOrder)) {
            $order = 'ASC';
        }

        $query = $patientRepository->createQueryBuilder('p')
            ->orderBy('p.' . $sortBy, $order)
            ->getQuery();

        $patients = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('user/patients.html.twig', [
            'patients' => $patients,
            'sortBy' => $sortBy,
            'order' => $order,
        ]);
    }
}