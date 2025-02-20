<?php

namespace App\Controller;

use App\Entity\Facture;
use App\Repository\FactureRepository;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;

class FactureController extends AbstractController
{
    private FactureRepository $factureRepository;

    public function __construct(FactureRepository $factureRepository)
    {
        $this->factureRepository = $factureRepository;
    }

    /*#[Route('/factures', name: 'factures_index', methods: ['GET'])]
    public function index(Request $request, Security $security): Response
    {
        $user = $security->getUser();
        $factures = [];
    
        if ($user) {
            $searchDate = $request->query->get('date_facture');
    
            $qb = $this->factureRepository->createQueryBuilder('f')
                ->where('f.client = :client')
                ->setParameter('client', $user);
    
            if ($searchDate) {
                $qb->andWhere('DATE(f.dateFacture) = :date')
                   ->setParameter('date', new \DateTime($searchDate));
            }
    
            $factures = $qb->getQuery()->getResult();
        }
    
        return $this->render('facture/index.html.twig', [
            'factures' => $factures,
        ]);
    }*/

    #[Route('/factures', name: 'factures_index', methods: ['GET'])]
public function index(Request $request, Security $security): Response
{
    $user = $security->getUser();
    $factures = [];
    
    if ($user) {
        $searchDate = $request->query->get('date_facture');
    
        $qb = $this->factureRepository->createQueryBuilder('f')
            ->where('f.client = :client')
            ->setParameter('client', $user);
    
    
        if ($searchDate) {
            // Filtrer en fonction de la date, en s'assurant que la comparaison se fait sur le champ 'datetime'
            $qb->andWhere('f.datetime = :date')
               ->setParameter('date', new \DateTime($searchDate));
        }
    
        $factures = $qb->getQuery()->getResult();
    }
    
    return $this->render('facture/index.html.twig', [
        'factures' => $factures,
    ]);
}

    
    
    #[Route('/factures/details/{id}', name: 'facture_details', methods: ['GET'])]
public function show(int $id, FactureRepository $factureRepository, ArticleRepository $articleRepository): Response
{
    // Récupérer la facture
    $facture = $factureRepository->find($id);

    if (!$facture) {
        throw $this->createNotFoundException('Facture introuvable.');
    }

    // Récupérer la commande associée
    $commande = $facture->getCommande();

    // Récupérer les articles associés à partir des identifiants
    $articles = [];
    if ($commande && $commande->getArticleIds()) {
        $articles = $articleRepository->findBy(['id' => $commande->getArticleIds()]);
    }

    return $this->render('details/details.html.twig', [
        'facture' => $facture,
        'commande' => $commande,
        'articles' => $articles,
    ]);
}

    #[Route('/factures/{id}', name: 'factures_delete', methods: ['POST'])]
    public function delete(Request $request, Facture $facture, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $facture->getId(), $request->request->get('_token'))) {
            $entityManager->remove($facture);
            $entityManager->flush();
        }

        return $this->redirectToRoute('factures_index');
    }
}