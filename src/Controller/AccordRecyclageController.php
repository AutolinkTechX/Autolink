<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\AccordRecyclageRepository; // 🔹 Vérifie que cette ligne est bien présente

final class AccordRecyclageController extends AbstractController
{
    #[Route('/demandes', name: 'demandes_recyclage')]
    public function listeDemandes(AccordRecyclageRepository $accordRecyclageRepository): Response
    {
        // Récupérer toutes les demandes de recyclage via le repository injecté
        $materiaux = $accordRecyclageRepository->findAll();

        return $this->render('Accord_recyclage/listdemande.html.twig', [
            'materiaux' => $materiaux,
        ]);
    }
}
