<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Article;
use App\Entity\User;
use App\Entity\ListArticle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\ListArticleRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

final class CommandeController extends AbstractController
{
    #[Route('/add-to-cart/{id}', name: 'add_to_cart', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')] // Assure que l'utilisateur est connecté
    public function addToCart(int $id, EntityManagerInterface $em, Request $request, Security $security): Response
    {
        // Vérifier si l'utilisateur est connecté
        $user = $security->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour ajouter un article au panier.');
            return $this->redirectToRoute('app_login'); // Redirection vers la page de connexion
        }
    
        // Récupérer l'article à partir de l'ID
        $article = $em->getRepository(Article::class)->find($id);
    
        if (!$article) {
            $this->addFlash('error', 'Article non trouvé.');
            return $this->redirect($request->headers->get('referer'));
        }
    
        // Vérifier la quantité de stock
        if ($article->getQuantiteStock() == 0) {
            $this->addFlash('error', 'Cet article est épuisé.');
            return $this->redirect($request->headers->get('referer')); // Retour à la page précédente
        }
    
        // Vérifier si l'article est déjà dans le panier pour cet utilisateur
        $existingCartItem = $em->getRepository(ListArticle::class)->findOneBy([
            'article' => $article,
            'user' => $user // Assurez-vous que la relation User est bien définie dans ListArticle
        ]);
    
        if ($existingCartItem) {
            $existingCartItem->setQuantite($existingCartItem->getQuantite() + 1);
        } else {
            $cartItem = new ListArticle();
            $cartItem->setArticle($article);
            $cartItem->setPrixUnitaire($article->getPrix());
            $cartItem->setQuantite(1);
            $cartItem->setUser($user); // Associer l'article ajouté à l'utilisateur
            $em->persist($cartItem);
        }
    
        $em->flush();
    
        $this->addFlash('success', 'Article ajouté au panier.');
    
        return $this->redirect($request->headers->get('referer'));
    }
    


    #[Route('/commande', name: 'app_commande')]
    public function index(ListArticleRepository $listarticleRepository): Response
    {
        // Utiliser le repository pour récupérer tous les articles
        $paniers = $listarticleRepository->findAll();

        // Calculer les totaux du panier
        $totalHT = 0;
        foreach ($paniers as $panier) {
            $totalHT += $panier->getQuantite() * $panier->getPrixUnitaire();
        }

        // Calculer la TVA
        $tva = $totalHT * 0.20;
        $totalTTC = $totalHT + $tva;

        return $this->render('commande/index.html.twig', [
            'paniers' => $paniers,
            'totalHT' => $totalHT,
            'tva' => $tva,
            'totalTTC' => $totalTTC,
        ]);
    }

    #[Route('/decrease-quantity/{id}', name: 'decrease_quantity', methods: ['POST'])]
    public function decreaseQuantity(int $id, EntityManagerInterface $em, Request $request): Response
    {
        // Récupérer l'article du panier
        $cartItem = $em->getRepository(ListArticle::class)->findOneBy(['article' => $id]);

        if ($cartItem && $cartItem->getQuantite() > 1) {
            // Diminuer la quantité
            $cartItem->setQuantite($cartItem->getQuantite() - 1);
            $this->addFlash('success', 'Quantité diminuée.');
        } else {
            // Supprimer l'article si la quantité est 1
            $em->remove($cartItem);
            $this->addFlash('success', 'Article supprimé du panier.');
        }

        // Enregistrer les modifications
        $em->flush();

        // Rediriger vers la même page pour actualiser le panier
        return $this->redirect($request->headers->get('referer'));
    }

    
}