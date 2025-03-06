<?php

namespace App\Controller;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Product;
use App\Entity\Orders;
use App\Repository\UserRepository;
use App\Repository\OrdersRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\QrCode;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
#[Route('/cart')]
class CartController extends AbstractController
{
    #[Route('/', name: 'app_cart')]
    public function index(EntityManagerInterface $em): Response
    {
        // Récupérer l'utilisateur avec l'id = 1
        $user = $this->getUser();
    
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }
    
        // Récupérer les commandes en attente pour l'utilisateur
        $orders = $em->getRepository(Orders::class)
            ->createQueryBuilder('o')
            ->where('o.statut = :statut')
            ->andWhere('o.user = :user')  // Filtrer par l'utilisateur spécifique
            ->setParameter('statut', 'en attente')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    
        // Passer les données à la vue
        return $this->render('product/cart/index.html.twig', [
            'orders' => $orders,
        ]);
    }
    
// Suppression d'une commande
#[Route('/order/delete/{id}', name: 'app_order_delete', methods: ['POST'])]
public function delete(int $id, EntityManagerInterface $em, Request $request): Response
{
    $order = $em->getRepository(Orders::class)->find($id);

    if (!$order) {
        throw $this->createNotFoundException('Order not found');
    }

    // Vérifier le token CSRF pour éviter les attaques
    if ($this->isCsrfTokenValid('delete' . $order->getId(), $request->request->get('_token'))) {
        $em->remove($order);
        $em->flush();
        $this->addFlash('success', 'Order deleted successfully!');
    }

    return $this->redirectToRoute('app_cart'); // Rediriger après suppression
}

#[Route('/order/checkout', name: 'app_order_checkout', methods: ['POST'])]
public function checkout(OrdersRepository $ordersRepo, EntityManagerInterface $em, MailerInterface $mailer): Response
{
    $user = $this->getUser();

    // Récupérer toutes les commandes en attente
    $orders = $ordersRepo->findBy(['statut' => 'en attente']);
    $orderDetails = '';
    $orderList = '';  // Variable pour stocker la liste des commandes

//HTML PDF
$html = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Order Summary</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                color: #333;
            }
            h1 {
                color: #4CAF50;
                text-align: center;
                margin-bottom: 20px;
            }
            .order-details {
                border: 1px solid #ddd;
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 5px;
                background-color: #f9f9f9;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
            .order-details h2 {
                color: #333;
                margin-top: 0;
            }
            .order-details p {
                margin: 5px 0;
            }
            .footer {
                text-align: center;
                margin-top: 30px;
                color: #777;
                font-size: 14px;
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
            }
            .header img {
                width: 100px;
                height: auto;
            }
            .header h2 {
                color: #4CAF50;
                margin: 10px 0 0;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            table th, table td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            table th {
                background-color: #f2f2f2;
                color: #333;
            }
        </style>
    </head>
    <body>
        <div class='header'>
            <h2>Shop</h2>
        </div>
        <h1>Order Summary</h1>
        <p>Thank you for your purchase! Below are the details of your orders:</p>

        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
";

foreach ($orders as $order) {
    $html .= "
                <tr>
                    <td>{$order->getId()}</td>
                    <td>{$order->getProduct()->getId()}</td>
                    <td>{$order->getQuantite()}</td>
                    <td>\${$order->getPrixTotal()}</td>
                    <td>Completed</td>
                </tr>
    ";
}

$html .= "
            </tbody>
        </table>

        <div class='footer'>
            <p>Thank you for shopping with us!</p>
            <p>If you have any questions, contact us at support@example.com</p>
        </div>
    </body>
    </html>
";




    
    foreach ($orders as $order) {
    
    $product = $order->getProduct(); // Récupérer le produit associé
    $orderedQuantity = $order->getQuantite(); // Quantité commandée

    // Vérifier si le produit a assez de stock avant décrémentation
    if ($product->getQuantite() >= $orderedQuantity) {
        $product->setQuantite($product->getQuantite() - $orderedQuantity); // Décrémenter le stock
    } else {
        // Gérer le cas où la quantité demandée dépasse le stock disponible
        throw new \Exception("Stock insuffisant pour le produit ID {$product->getId()}.");
    }
        // Ajouter les détails de la commande dans une liste pour l'email
        $orderList .= "
            <li>
                <strong>Order ID:</strong> {$order->getId()}<br>
                <strong>Product:</strong> {$order->getProduct()->getId()}<br>  <!-- Utiliser getName() au lieu de getId() -->
                <strong>Quantity:</strong> {$order->getQuantite()}<br>
                <strong>Total Price:</strong> \${$order->getPrixTotal()}<br>
                <strong>Status:</strong> Completed<br>
            </li>
        ";

        // Mettre à jour le statut de la commande
        $order->setStatut('traite');
        $em->persist($order);
    }
    $em->flush();



    // Initialiser Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');  // Définir le format et l'orientation du PDF
    $dompdf->render();

    // Chemin pour enregistrer le fichier PDF
    $pdfDirectory = $this->getParameter('kernel.project_dir') . '/public/commande';
    if (!file_exists($pdfDirectory)) {
        mkdir($pdfDirectory, 0777, true);  // Créer le dossier s'il n'existe pas
    }

    $pdfFileName = 'order_summary_' . time() . '.pdf';  // Nom unique pour le fichier PDF
    $pdfFilePath = $pdfDirectory . '/' . $pdfFileName;

    // Enregistrer le fichier PDF
    file_put_contents($pdfFilePath, $dompdf->output());

    // Créer l'email
    $email = (new Email())
        ->from("amaltr249@gmail.com")
        ->to($user->getEmail()) // Remplacer par l'email du client
        ->subject('Your Order Summary')
        ->html("
            <p>Thank you for your order! Below is the list of your orders:</p>
            <ul>
                {$orderList}  <!-- Liste des commandes -->
            </ul>
            <p>Please find the attached PDF with your order details.</p>
        ")
        ->attachFromPath($pdfFilePath, 'order_summary.pdf', 'application/pdf');  // Attacher le fichier PDF depuis le chemin

    // Envoyer l'email
    $mailer->send($email);

    // Message de succès
    $this->addFlash('success', 'Your orders have been successfully sent via email.');

    return $this->redirectToRoute('app_cart');
}
    #[Route('/cart/add/{id}', name: 'app_cart_add')]
    public function addToCart(Request $request, int $id, EntityManagerInterface $em): Response
    {
        // Récupérer la quantité depuis la requête
        $quantity = $request->query->getInt('quantity', 1);
    
       // Exemple de code pour tester une commande
$product = $em->getRepository(Product::class)->find($id); // Assurez-vous que le produit existe
$user = $this->getUser();

$order = new Orders();
$order->setUser($user);
$order->setProduct($product);
$order->setQuantite($quantity);
$order->setPrixTotal($product->getPrix() * $quantity);
$order->setDateCommande(new \DateTime());
$order->setStatut('en attente');

// Enregistrer la commande
$em->persist($order);
$em->flush();

    
        $this->addFlash('success', 'Produit ajouté au panier avec succès!');
        return $this->redirectToRoute('app_products_show', ['id' => $product->getId()]);
    }
    
    #[Route('/remove/{id}', name: 'app_cart_remove')]
    public function remove(Products $product, CartService $cartService): Response
    {
        $cartService->removeFromCart($product);
        
        $this->addFlash('success', 'Product removed from cart!');
        return $this->redirectToRoute('app_cart');
    }
}