<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\MaterielRecyclableRepository;
use App\Repository\AccordRepository;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Accord;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

final class DashboardController extends AbstractController
{
    
#[Route('/dashboard', name: 'dashboard_supplier')]
public function index(EntityManagerInterface $entityManager, MaterielRecyclableRepository $repo, AccordRepository $accordRepository): Response
{
    // Vérifier que l'utilisateur est connecté
    $user = $this->getUser();
    if (!$user) {
        throw $this->createAccessDeniedException('Vous devez être connecté pour voir cette page.');
    }

    // Vérifier si l'utilisateur a le rôle ENTREPRISE
    if (!in_array('ROLE_ENTREPRISE', $user->getRoles())) {
        throw $this->createAccessDeniedException('Seules les entreprises peuvent voir cette page.');
    }

    // 🔥 Récupérer uniquement les matériaux recyclables de l'entreprise connectée
    $materiaux = $repo->findBy(['entreprise' => $user]);

    // 🔥 Récupérer les statistiques filtrées par entreprise
    $statsByDate = $repo->countByDate($user);
    $countByStatut = $repo->countByStatut($user);
    $countByTypeMateriel = $repo->countByTypeMateriel($user);
    // 🔥 Ajouter le nombre total de demandes
    $totalDemandes = $accordRepository->countDemandesByEntreprise($user);
    $demandesParClient = $accordRepository->countDemandesByClient($user);
   

    // 🔥 Récupérer le délai moyen par jour
   // $delaiMoyenParJour = $accordRepository->getDelaiMoyenParJour($user);



    // 🔥 Convertir les objets `StatutEnum` en chaînes de caractères
    foreach ($countByStatut as &$row) {
        if ($row['statut'] instanceof StatutEnum) {
            $row['statut'] = $row['statut']->value; // ou ->name selon ton enum
        }
    }

    //  Récupérer les accords liés aux matériaux de l'entreprise
   // $accords = $entityManager->getRepository(Accord::class)->findBy([
       // 'materielRecyclable' => $materiaux,
    //]);

    return $this->render('dashboard/test.html.twig', [
        'statsByDate' => $statsByDate,
        'countByStatut' => $countByStatut,
        'countByTypeMateriel' => $countByTypeMateriel,
        'totalDemandes' => $totalDemandes,
        'demandesParClient' => $demandesParClient,
        
        /*'accords' => $accords,*/
    ]);}




    public function dashboard(AccordRepository $accordRepository): Response
    {
        $totalDemandes = $accordRepository->getTotalDemandes();
    
        return $this->render('dashboard/index.html.twig', [
            'totalDemandes' => $totalDemandes,
        ]);
    }
    




  /*  #[Route('/dashboard', name: 'dashboard_supplier')]
    public function index(): Response
    {
        // Vérifier que l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir cette page.');
        }
    
        if (!in_array('ROLE_ENTREPRISE', $user->getRoles())) {
            throw $this->createAccessDeniedException('Seules les entreprises peuvent voir cette page.');
        }
    
        return $this->render('dashboard/test.html.twig');
    }*/
    
    #[Route('/stats/statut', name: 'stats_statut')]
    public function statsByStatut(MaterielRecyclableRepository $repo): JsonResponse
    {
        $user = $this->getUser();
        $countByStatut = $repo->countByStatut($user);
    
        foreach ($countByStatut as &$row) {
            if ($row['statut'] instanceof StatutEnum) {
                $row['statut'] = $row['statut']->value;
            }
        }
    
        return $this->json($countByStatut);
    }
    
    #[Route('/stats/type-materiel', name: 'stats_type_materiel')]
    public function statsByTypeMateriel(MaterielRecyclableRepository $repo): JsonResponse
    {
        $user = $this->getUser();
        return $this->json($repo->countByTypeMateriel($user));
    }
    /*#[Route('/stats/delai-moyen', name: 'stats_delai_moyen')]
    public function statsByDelai(AccordRepository $accordRepository): JsonResponse
    {
        $user = $this->getUser();
        return $this->json($accordRepository->getDelaiMoyenParJour($user));
    }*/
    





















   /* #[Route('/dashboard', name: 'dashboard_supplier')]
    public function index(
        EntityManagerInterface $entityManager,
        MaterielRecyclableRepository $repo,
        ChartBuilderInterface $chartBuilder
    ): Response {
        // 🔹 Vérifier que l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir cette page.');
        }

        // 🔹 Vérifier si l'utilisateur a le rôle ENTREPRISE
        if (!in_array('ROLE_ENTREPRISE', $user->getRoles())) {
            throw $this->createAccessDeniedException('Seules les entreprises peuvent voir cette page.');
        }

        // 🔹 Récupérer uniquement les matériaux recyclables de l'entreprise connectée
        $materiaux = $repo->findBy(['entreprise' => $user]);

        // 🔹 Récupérer les statistiques filtrées par entreprise
        $statsByDate = $repo->countByDate($user);
        $countByStatut = $repo->countByStatut($user);

        // 🔹 Convertir les objets `StatutEnum` en chaînes de caractères
        foreach ($countByStatut as &$row) {
            if ($row['statut'] instanceof StatutEnum) {
                $row['statut'] = $row['statut']->value; // ou ->name selon ton enum
            }
        }

        // 🔹 Préparer les données pour le Pie Chart
        $labels = [];
        $data = [];
        $colors = ['rgba(255, 99, 132, 0.5)', 'rgba(54, 162, 235, 0.5)', 'rgba(255, 206, 86, 0.5)'];

        foreach ($countByStatut as $index => $row) {
            $labels[] = $row['statut'];
            $data[] = $row['count'];
        }

        // 🔹 Créer un Pie Chart avec Chart.js
        $chart = $chartBuilder->createChart(Chart::TYPE_PIE);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Répartition des Matériaux par Statut',
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
        ]);

        return $this->render('dashboard/test.html.twig', [
            'statsByDate' => $statsByDate,
            'countByStatut' => $countByStatut,
            'chart' => $chart, // On passe le graphique au template
        ]);
    }*/
}




