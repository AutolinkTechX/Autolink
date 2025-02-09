<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\ListArticle; // Assurez-vous que cette classe est importée
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\ListArticleRepository;

final class CommandeController extends AbstractController
{
    #[Route('/add-to-cart/{id}', name: 'add_to_cart')]
    public function addToCart(int $id, EntityManagerInterface $em): JsonResponse
    {
        // Récupérer l'article à partir de l'ID
        $article = $em->getRepository(Article::class)->find($id);

        if (!$article) {
            return new JsonResponse(['status' => 'error', 'message' => 'Article not found'], 404);
        }

        
        //return new JsonResponse(['status' => 'error', 'message' => $article->getPrix()], 404);

        // Récupérer le panier de l'utilisateur ou en créer un nouveau (logique à définir)
        // Exemple : $cart = $user->getCart();
        // À remplacer par la logique de votre propre gestion de panier

        // Vérifier si l'article est déjà dans le panier de l'utilisateur
        $existingCartItem = $em->getRepository(ListArticle::class)->findOneBy([
            'article' => $article,
        ]);

        

        if ($existingCartItem) {
            // Si l'article existe déjà, augmenter la quantité
            $existingCartItem->setQuantite($existingCartItem->getQuantite() + 1);
        } else {
            // Sinon, créer un nouvel élément de panier
            $cartItem = new ListArticle(); 
            $cartItem->setArticle($article);
            $cartItem->setPrixUnitaire($article->getPrix()); // Assurez-vous que cette méthode existe
            $cartItem->setQuantite(1);
            $em->persist($cartItem);
        }

        // Enregistrer les changements dans la base de données
        $em->flush();

        // Retourner une réponse JSON
        return new JsonResponse(['status' => 'success', 'message' => 'Article added to panier']);
    }



    #[Route('/commande', name: 'app_commande')]
    public function index(ListArticleRepository $listarticleRepository): Response
    {
        // Utiliser le repository pour récupérer tous les articles
        $paniers = $listarticleRepository->findAll();
        
        // Retourner la vue avec les articles
        return $this->render('commande/index.html.twig', [
            'paniers' => $paniers,
        ]);
    }




    
}
