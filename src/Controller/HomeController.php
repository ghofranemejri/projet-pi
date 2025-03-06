<?php

namespace App\Controller;
use App\Repository\UserRepository;

use App\Entity\Product;
use App\Repository\OrdersRepository;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\RatingService;
    use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
    use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{

    
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager,OrdersRepository $ordersRepository): Response
    {
        $featuredProducts = $entityManager->getRepository(Product::class)
            ->findBy([], ['id' => 'DESC'], 6);
            $bestSeller = $ordersRepository->findBestSeller();
              // Pour chaque produit, vous pouvez récupérer l'image du code-barres
  
        return $this->render('product/front/home.html.twig', [
            'featuredProducts' => $featuredProducts,
            'bestSeller' => $bestSeller,
            


        ]);
    }

        #[Route('/api/stock/alertes', name: 'api_stock_alertes', methods: ['GET'])]
    public function stockAlertes(ProductRepository $productRepository): JsonResponse
    {
        $produits = $productRepository->findByQuantiteInf(5);

        $alertes = [];
        foreach ($produits as $produit) {
            $alertes[] = [
                'nom' => $produit->getId(),
                'quantite' => $produit->getQuantite()
            ];
        }

        return $this->json($alertes);
    }
   

    }

