<?php

namespace App\Controller;

use App\Entity\Favorie;
use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use App\Repository\FavorieRepository;
use Symfony\Component\HttpFoundation\Response;  // Import correct pour la classe Response
use Symfony\Component\HttpFoundation\Request;


final class FavorieController extends AbstractController
{
    #[Route('/favorie', name: 'app_favorie')]
    public function index(FavorieRepository $favorieRepository): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        if (!$user) {
            // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
            return $this->redirectToRoute('app_login');
        }

        // Récupérer les favoris de l'utilisateur connecté
        $favories = $favorieRepository->findBy(['user' => $user]);
        // Récupérer les favoris non expirés de l'utilisateur connecté
        $favories = $favorieRepository->findNonExpiredFavoriesByUser($user);


        return $this->render('favorie/index.html.twig', [
            'favories' => $favories,
        ]);
    }


    #[Route('/add-to-favorites/{articleId}', name: 'add_to_favorites')]
    public function addToFavorites(
        int $articleId,
        EntityManagerInterface $em,
        ArticleRepository $articleRepository,
        Security $security
    ): RedirectResponse {
        // Récupérer l'article
        $article = $articleRepository->find($articleId);
    
        if (!$article) {
            $this->addFlash('error', 'Article non trouvé.');
            return $this->redirectToRoute('app_listarticle');
        }
    
        // Vérifier si l'article est déjà dans les favoris
        $existingFavorite = $em->getRepository(Favorie::class)->findOneBy([
            'article' => $article,
            'user' => $security->getUser()  // Vérification si l'utilisateur a déjà ajouté cet article aux favoris
        ]);
    
        if ($existingFavorite) {
            $this->addFlash('notice', 'Article déjà ajouté aux favoris.');
            return $this->redirectToRoute('app_listarticle');
        }
    
        // Ajouter l'article aux favoris
        $favorie = new Favorie();
        $favorie->setArticle($article);
        $favorie->setUser($security->getUser());  // Associer l'utilisateur connecté
        $favorie->setDateCreation(new \DateTime());
        $favorie->setDateExpiration((new \DateTime())->modify('+1 day'));

        $em->persist($favorie);
        $em->flush();
    
        $this->addFlash('success', 'Article ajouté aux favoris.');
        return $this->redirectToRoute('app_listarticle');
    }

    #[Route('/favorites', name: 'list_favorites')]
    public function listFavorites(FavorieRepository $favorieRepository): Response
    {
        $favories = $favorieRepository->findAll();

        return $this->render('favorie/list.html.twig', [
            'favories' => $favories,
        ]);
    }
    
    #[Route('/favorie/search', name: 'favorie_index')]
    public function search(Request $request, ArticleRepository $articleRepository, FavorieRepository $favorieRepository): Response
    {
        $nomArticle = $request->query->get('nom_article'); // Récupérer le terme de recherche

        // Si un nom d'article est fourni
        if ($nomArticle) {
            // Trouver les articles par nom
            $articles = $articleRepository->findByNom($nomArticle);

            // Trouver les favoris liés aux articles trouvés
            $favories = $favorieRepository->findByArticles($articles); // Assurez-vous d'avoir cette méthode dans le repository
        } else {
            // Si aucun terme de recherche n'est donné, afficher tous les favoris
            $favories = $favorieRepository->findAll();
        }

        return $this->render('favorie/index.html.twig', [
            'favories' => $favories,
        ]);
    }

     // Route pour supprimer un favori
     #[Route('/supprimer/{id}', name: 'supprimer')]
     public function supprimer(FavorieRepository $favorieRepository, $id, EntityManagerInterface $entityManager): RedirectResponse
     {
         // Récupérer l'article favori par ID
         $favorie = $favorieRepository->find($id);
     
         if ($favorie) {
             // Supprimer l'article des favoris
             $entityManager->remove($favorie);
             $entityManager->flush();
     
             // Ajoutez un message flash pour informer l'utilisateur
             $this->addFlash('success', 'Article supprimé des favoris.');
         }
     
         return $this->redirectToRoute('app_favorie'); // Redirige vers la liste des favoris
    }
     
    #[Route('/clean-expired-favorites', name: 'clean_expired_favorites')]
    public function cleanExpiredFavorites(FavorieRepository $favorieRepository, EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les favoris expirés
        $expiredFavories = $favorieRepository->findExpiredFavories(new \DateTime());

        foreach ($expiredFavories as $favorie) {
            // Supprimer le favori expiré
            $entityManager->remove($favorie);
        }

        // Enregistrer les modifications dans la base de données
        $entityManager->flush();

        return new Response('Expired favorites cleaned successfully.');
    }




    
}