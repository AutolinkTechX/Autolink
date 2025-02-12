<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Favorie; // Assurez-vous que cette classe est importée
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\FavorieRepository;

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
        }*/
    
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
    


}
