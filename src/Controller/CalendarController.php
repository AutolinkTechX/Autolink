<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\AccordRepository;
use Symfony\Component\HttpFoundation\JsonResponse;


final class CalendarController extends AbstractController{
    #[Route('/calendar', name: 'app_calendar')]
    public function index(): Response
    {
        return $this->render('calendar/index1.html.twig', [
            'controller_name' => 'CalendarController',
        ]);
    }




    #[Route('/calendar/events', name: 'app_calendar_events')]
public function getEvents(AccordRepository $accordRepository): JsonResponse
{
    $accords = $accordRepository->findAll();

    $events = [];
    foreach ($accords as $accord) {
        $nomProduit = $accord->getMaterielRecyclable()->getName(); // Récupère le nom du produit
    
        $events[] = [
            'title' => $nomProduit ?? ('Accord ID ' . $accord->getId()), // Affiche le nom du produit s'il existe
            'start' => $accord->getDateCreation()->format('Y-m-d'),
            'end' => $accord->getDateReception() ? $accord->getDateReception()->format('Y-m-d') : null,
            'color' => $accord->getDateReception() ? 'green' : 'red',
            'date_creation' => $accord->getDateCreation()->format('Y-m-d'), // Ajout de la date de création
            'date_acceptation' => $accord->getDateReception() ? $accord->getDateReception()->format('Y-m-d') : null, // Ajout de la date d'acceptation (si elle existe)
        ];
    }
    
    return new JsonResponse($events);
}

}
