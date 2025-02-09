<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ArticleRepository;

final class DetailsController extends AbstractController
{
    #[Route('/details/{id}', name: 'app_details')]
    public function index(int $id, ArticleRepository $articleRepository): Response
    {
        $article = $articleRepository->find($id);
    
        if (!$article) {
            throw $this->createNotFoundException('Article non trouvé.');
        }
    
        return $this->render('details/index.html.twig', [
            'article' => $article,
        ]);
    }
    
}
