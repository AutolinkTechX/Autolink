<?php

namespace App\Controller;

use App\Repository\ListArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class PaymentController extends AbstractController
{
    private $listarticleRepository;

    public function __construct(ListArticleRepository $listarticleRepository)
    {
        $this->listarticleRepository = $listarticleRepository;
    }

    #[Route('/payment', name: 'app_payment')]
    public function index(Request $request): Response
    {
        $paniers = $this->listarticleRepository->findAll();

        $totalHT = 0;
        foreach ($paniers as $panier) {
            $totalHT += $panier->getQuantite() * $panier->getPrixUnitaire();
        }

        $tva = $totalHT * 0.20;
        $totalTTC = $totalHT + $tva;

        $showCardModal = filter_var($request->query->get('showCardModal', false), FILTER_VALIDATE_BOOLEAN);
        $showCashModal = filter_var($request->query->get('showCashModal', false), FILTER_VALIDATE_BOOLEAN);

        return $this->render('payment/index.html.twig', [
            'paniers' => $paniers,
            'totalHT' => $totalHT,
            'tva' => $tva,
            'totalTTC' => $totalTTC,
            'showCardModal' => $showCardModal,
            'showCashModal' => $showCashModal,
        ]);
    }
}


