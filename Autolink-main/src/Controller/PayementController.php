<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PayementController extends AbstractController
{
    #[Route('/payement', name: 'app_payement')]
    public function index(): Response
    {
        return $this->render('payement/index.html.twig', [
            'controller_name' => 'PayementController',
        ]);
    }

    /**
     * @Route("/payment/confirm-card", name="confirm_payment_card", methods={"POST"})
     */
    public function confirmPaymentCard(): Response
    {
        // Logique de traitement du paiement par carte
        return $this->redirectToRoute('app_payement');
    }

    /**
     * @Route("/payment/confirm-cash", name="confirm_payment_cash", methods={"POST"})
     */
    public function confirmPaymentCash(): Response
    {
        // Logique de traitement du paiement en espèces
        return $this->redirectToRoute('app_payement');
    }
}
