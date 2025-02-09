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
    public function addToFavorie(int $id, EntityManagerInterface $em): JsonResponse
    {
        // Récupérer l'utilisateur connecté
        

        // Récupérer l'article
        $article = $em->getRepository(Article::class)->find($id);
        if (!$article) {
            return new JsonResponse(['status' => 'error', 'message' => 'Article not found'], 404);
        }

        // Vérifier si l'article est déjà dans les favoris de l'utilisateur
        $existingFavorie = $em->getRepository(Favorie::class)->findOneBy([
            'article' => $article,
        ]);

        if ($existingFavorie) {
            return new JsonResponse(['status' => 'exist', 'message' => 'Article already in favorites']);
        }

        // Ajouter aux favoris
        $favorie = new Favorie();
        $favorie->setArticle($article);
        $favorie->setClient($user);

        $em->persist($favorie);
        $em->flush();

        return new JsonResponse(['status' => 'success', 'message' => 'Article added to favorites']);
    }

}
