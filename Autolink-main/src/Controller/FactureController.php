<?php

namespace App\Controller;

use App\Entity\Facture;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\FactureRepository;

final class FactureController extends AbstractController{
    #[Route('/facture', name: 'app_facture')]
    public function index(FactureRepository $factureRepository): Response
    {
        // Utiliser le repository pour récupérer tous les articles
        $factures = $factureRepository->findAll();
        
        // Retourner la vue avec les articles
        return $this->render('facture/index.html.twig', [
            'factures' => $factures,
        ]);
    }
}
