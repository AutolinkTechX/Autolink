<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ListArticleController extends AbstractController
{
    #[Route('/list/article', name: 'app_listarticle')]
    public function index(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findAll();

        return $this->render('list_article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/list/article/search', name: 'article_index')]
    public function search(Request $request, ArticleRepository $articleRepository): Response
    {
        $nomArticle = $request->query->get('nom_article');

        if ($nomArticle) {
            $articles = $articleRepository->findByNom($nomArticle);
        } else {
            $articles = $articleRepository->findAll();
        }

        return $this->render('list_article/index.html.twig', [
            'articles' => $articles,
        ]);
    }

    #[Route('/list/article/category/{category}', name: 'list_article_by_categorie')]
    public function filterByCategory(string $category, ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findBy(['category' => $category]);

        return $this->render('list_article/index.html.twig', [
            'articles' => $articles,
        ]);
    }
}
