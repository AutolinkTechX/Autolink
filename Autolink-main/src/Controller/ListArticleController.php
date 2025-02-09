<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArticleRepository;

final class ListArticleController extends AbstractController{
    #[Route('/list/article', name: 'app_listarticle')]
    public function index(ArticleRepository $articleRepository): Response
    {
        // Utiliser le repository pour récupérer tous les articles
        $articles = $articleRepository->findAll();
        
        // Retourner la vue avec les articles
        return $this->render('list_article/index.html.twig', [
            'articles' => $articles,
        ]);
    }


    /**
     * @Route("/category/{category}", name="product_category", methods={"GET"})
     */
    public function filterByCategory(string $category, ArticleRepository $articleRepository): Response
    {
        // Filtrer les produits par catégorie
        $articles = $articleRepository->findByCategory($category);

        return $this->render('product/index.html.twig', [
            'articles' => $articles,
        ]);
    }

}
