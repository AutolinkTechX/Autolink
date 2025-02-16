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
    public function index(FavorieRepository $favorieRepository ): Response
    {
        $favories = $favorieRepository->findAll();

        return $this->render('favorie/index.html.twig', [
            'favories' => $favories,
        ]);
    }

/*
    #[Route('/add-to-favorie/{id}', name: 'add_to_favorie')]
    public function addToFavorie(int $id, EntityManagerInterface $em): Response
    {
        // Récupérer l'article
        $article = $em->getRepository(Article::class)->find($id);
        /*
        if (!$article) {
            // Si l'article n'est pas trouvé, redirigez vers une page d'erreur ou une autre action
            return $this->redirectToRoute('app_home'); // Modifier selon la page d'accueil de votre application
        }
    */
        // Récupérer l'utilisateur connecté
       /* $user = $this->getUser();
        if (!$user) {
            // Si l'utilisateur n'est pas authentifié, redirigez vers la page de connexion
            return $this->redirectToRoute('app_login'); // Assurez-vous d'avoir la bonne route pour la connexion
        }
    
        // Vérifier si l'article est déjà dans les favoris de l'utilisateur
        $existingFavorie = $em->getRepository(Favorie::class)->findOneBy([
            'article' => $article,
            //'client' => $user,  // Assurez-vous d'utiliser la relation correcte avec l'utilisateur (client)
        ]);
    
        if ($existingFavorie) {
            // Si l'article est déjà dans les favoris, redirigez vers la page des favoris avec un message
            $this->addFlash('warning', 'Cet article est déjà dans vos favoris.');
            return $this->redirectToRoute('app_favorie'); // Redirection vers la page des favoris
        }
    
        // Ajouter aux favoris
        $favorie = new Favorie();
        $favorie->setArticle($article); // Doctrine gère la relation via l'objet Article
       // $favorie->setClient($user); // Assurez-vous que la relation client est correctement gérée
    
        // Enregistrer dans la base de données
        $em->persist($favorie);
        $em->flush();
    
        // Ajouter un message flash de succès et rediriger vers la page des favoris
        $this->addFlash('success', 'L\'article a été ajouté à vos favoris.');
        return $this->redirectToRoute('app_favorie'); // Redirection vers la page des favoris
    }
    */
   #[Route('/add-to-favorites/{articleId}', name: 'add_to_favorites')]
    public function addToFavorites(
        int $articleId,
        EntityManagerInterface $em,
        ArticleRepository $articleRepository
    ): RedirectResponse {
        $article = $articleRepository->find($articleId);

        if (!$article) {
            $this->addFlash('error', 'Article non trouvé.');
            return $this->redirectToRoute('app_listarticle');
        }

        // Vérifier si l'article est déjà dans les favoris
        $existingFavorite = $em->getRepository(Favorie::class)->findOneBy([
            'article' => $article
        ]);

        if ($existingFavorite) {
            $this->addFlash('notice', 'Article déjà ajouté aux favoris.');
            return $this->redirectToRoute('app_listarticle');
        }

        // Ajouter l'article aux favoris
        $favorie = new Favorie();
        $favorie->setArticle($article);
        $favorie->setDateCreation(new \DateTime());
        $favorie->setDateExpiration((new \DateTime())->modify('+1 year'));

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
    
    #[Route('/favoris', name: 'favorie_list')]
public function listFavoris(FavorieRepository $favorieRepository): Response
{
    $favories = $favorieRepository->findAll(); // Récupérer tous les favoris avec leurs articles

    return $this->render('favorie/list.html.twig', [
        'favories' => $favories,
    ]);
}

    #[Route('/favorie/search', name: 'favorie_index')]
    public function search(Request $request, ArticleRepository $articleRepository): Response
    {
         $nomArticle = $request->query->get('nom_article');

         // Recherche par nom d'article
         if ($nomArticle) {
            // Filtrer les articles par le nom
             $articles = $articleRepository->findByNom($nomArticle); // Assurez-vous que cette méthode est correcte dans votre repository
        } else {
        // Si aucun nom n'est fourni, afficher tous les articles
             $articles = $articleRepository->findAll();
        }

        return $this->render('favorie/index.html.twig', [
        'articles' => $articles,
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
     




    
}
