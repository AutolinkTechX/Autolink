<?php
namespace App\Controller;

use App\Entity\Facture;
use App\Repository\FactureRepository;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityNotFoundException;

class FactureController extends AbstractController
{
    private FactureRepository $factureRepository;

    public function __construct(FactureRepository $factureRepository)
    {
        $this->factureRepository = $factureRepository;
    }

    #[Route('/factures', name: 'factures_index', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')] // Ensure the user is fully authenticated
    public function index(Request $request, Security $security): Response
    {
        $user = $security->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder aux factures.');
            return $this->redirectToRoute('login'); // Redirect to the login page
        }

        $factures = [];

        if ($user) {
            $searchDate = $request->query->get('date_facture');
            $qb = $this->factureRepository->createQueryBuilder('f')
                ->where('f.client = :user')
                ->setParameter('user', $user);

            if ($searchDate) {
                $date = new \DateTime($searchDate);
                $startOfDay = clone $date;
                $startOfDay->setTime(0, 0, 0); // Start of the day
                $endOfDay = clone $date;
                $endOfDay->setTime(23, 59, 59); // End of the day

                $qb->andWhere('f.datetime >= :startOfDay AND f.datetime <= :endOfDay')
                    ->setParameter('startOfDay', $startOfDay)
                    ->setParameter('endOfDay', $endOfDay);
            }

            $factures = $qb->getQuery()->getResult();
        }

        return $this->render('facture/index.html.twig', [
            'factures' => $factures,
        ]);
    }

    #[Route('/factures/details/{id}', name: 'facture_details', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')] // Ensure the user is fully authenticated
    public function show(int $id, FactureRepository $factureRepository, ArticleRepository $articleRepository): Response
    {
        // Retrieve the facture
        $facture = $factureRepository->find($id);
        if (!$facture) {
            throw new EntityNotFoundException('Facture introuvable.');
        }

        // Retrieve the associated command
        $commande = $facture->getCommande();

        // Retrieve the articles associated with the command
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
    #[IsGranted('IS_AUTHENTICATED_FULLY')] // Ensure the user is fully authenticated
    public function delete(Request $request, Facture $facture, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $facture->getId(), $request->request->get('_token'))) {
            $entityManager->remove($facture);
            $entityManager->flush();
        }

        return $this->redirectToRoute('factures_index');
    }
}