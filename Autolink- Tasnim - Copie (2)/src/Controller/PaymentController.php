<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Article;
use App\Entity\ListArticle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\ListArticleRepository;
use Symfony\Component\HttpFoundation\Request;

final class PaymentController extends AbstractController
{
    private $listarticleRepository;

    // Injecter le repository ListArticleRepository dans le contrôleur
    public function __construct(ListArticleRepository $listarticleRepository)
    {
        $this->listarticleRepository = $listarticleRepository;
    }

    #[Route('/payment', name: 'app_payment')]
    public function index(): Response
    {
        // Utiliser le repository pour récupérer tous les articles
        $paniers = $this->listarticleRepository->findAll();

        // Calculer les totaux du panier
        $totalHT = 0;
        foreach ($paniers as $panier) {
            $totalHT += $panier->getQuantite() * $panier->getPrixUnitaire();
        }

        // Calculer la TVA
        $tva = $totalHT * 0.20;
        $totalTTC = $totalHT + $tva;

        // Renvoyer les données au template
        return $this->render('payment/index.html.twig', [
            'paniers' => $paniers,
            'totalHT' => $totalHT,
            'tva' => $tva,
            'totalTTC' => $totalTTC,
        ]);
    }
}
