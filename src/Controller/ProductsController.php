<?php

namespace App\Controller;
use Picqer\Barcode\BarcodeGeneratorPNG;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/products')]
class ProductsController extends AbstractController
{
    #[Route('/', name: 'app_products_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $products = $entityManager->getRepository(Product::class)->findAll();
        return $this->render('product/products/index.html.twig', ['products' => $products]);
    }

    #[Route('/new', name: 'app_products', methods: ['GET', 'POST'])]
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
        
        return $this->render('product/products/new.html.twig');
    }

    #[Route('/{id}', name: 'app_products_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/products/showw.html.twig', ['product' => $product]);
    }

    #[Route('/{id}/edit', name: 'app_products_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $product->setPrix((float)$request->request->get('prix'));
            $product->setQuantite((int)$request->request->get('quantite'));
            
            $imageFile = $request->files->get('image');
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('kernel.project_dir').'/public/uploads/products',
                    $newFilename
                );
                $product->setImage('/uploads/products/'.$newFilename);
            }
            
            $entityManager->flush();
            return $this->redirectToRoute('app_products_index');
        }
        
        return $this->render('product/products/edit.html.twig', ['product' => $product]);
    }

    #[Route('/{id}', name: 'app_products_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_products_index');
    }
}
