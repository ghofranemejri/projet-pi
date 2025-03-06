<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductsType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Picqer\Barcode\BarcodeGeneratorPNG;
#[Route('/admin/products')]
class ProductController extends AbstractController
{
    #[Route('/new', name: 'app_products_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductsType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('products_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Handle exception
                }

                $product->setImage('uploads/products/'.$newFilename);
            }

            $entityManager->persist($product);
            $entityManager->flush();
            $generator = new BarcodeGeneratorPNG();
            $barcodeContent = 'Product Id='.$product->getId() . '- Quantity =' . $product->getQuantite(); // Concaténer ID et quantité
            $barcodeData = $generator->getBarcode($barcodeContent, $generator::TYPE_CODE_128);
    
            // Enregistrer le nouveau code-barres
            $barcodeFilename = 'barcode_' . $product->getId() . '.png';
            $barcodePath = $this->getParameter('kernel.project_dir') . '/public/barcodes/' . $barcodeFilename;
            file_put_contents($barcodePath, $barcodeData);
    

        // Associer le chemin du code-barres au produit
            $this->addFlash('success', 'Product created successfully');
            return $this->redirectToRoute('app_products_index');
        }

        return $this->render('product/admin/products/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
