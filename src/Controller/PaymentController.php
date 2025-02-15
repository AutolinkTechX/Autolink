<?php

namespace App\Controller;

use App\Repository\ListArticleRepository;
use App\Repository\UserRepository;
use App\Repository\CommandeRepository;
use App\Entity\Commande;
use Doctrine\ORM\EntityManagerInterface; // Import de l'EntityManager
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class PaymentController extends AbstractController
{
    private $listarticleRepository;
    private $userRepository;
    private $commandeRepository;
    private $entityManager; // Ajout de l'EntityManager

    public function __construct(
        ListArticleRepository $listarticleRepository, 
        UserRepository $userRepository, 
        CommandeRepository $commandeRepository,
        EntityManagerInterface $entityManager // Injection de l'EntityManager
    )
    {
        $this->listarticleRepository = $listarticleRepository;
        $this->userRepository = $userRepository;
        $this->commandeRepository = $commandeRepository;
        $this->entityManager = $entityManager; // Initialisation de l'EntityManager
    }

    #[Route('/payment', name: 'app_payment')]
    public function index(Request $request): Response
    {
        $paniers = $this->listarticleRepository->findAll();

        // Calcul du total HT
        $totalHT = 0;
        foreach ($paniers as $panier) {
            $totalHT += $panier->getQuantite() * $panier->getPrixUnitaire();
        }

        // Calcul de la TVA et du total TTC
        $tva = $totalHT * 0.20;
        $totalTTC = $totalHT + $tva;

        // Récupération des paramètres pour afficher les modals
        $showCardModal = filter_var($request->query->get('showCardModal', false), FILTER_VALIDATE_BOOLEAN);
        $showCashModal = filter_var($request->query->get('showCashModal', false), FILTER_VALIDATE_BOOLEAN);

        // Si le formulaire de paiement en espèces est soumis
        if ($request->isMethod('POST') && $request->get('name') && $request->get('last_name') && $request->get('phone')) {
            $name = $request->get('name');
            $lastName = $request->get('last_name');
            $phone = $request->get('phone');

            // Validation simple des informations
            if (empty($name) || empty($lastName) || empty($phone)) {
                $this->addFlash('error', 'Tous les champs doivent être remplis');
                return $this->redirectToRoute('app_payment');
            }

            // Vérification des informations de l'utilisateur dans la base de données
            $user = $this->userRepository->findOneBy([
                'name' => $name,
                'lastName' => $lastName,
                'phone' => $phone
            ]);

            if (!$user) {
                // L'utilisateur n'a pas été trouvé dans la base de données
                $this->addFlash('error', 'Vérifiez vos données. Aucune correspondance trouvée.');
                return $this->redirectToRoute('app_payment');
            }

            // Récupérer le mode de paiement à partir de la requête
            $modePaiement = $request->get('payment_method');
            
            // Vérifier que le mode de paiement est valide (par exemple 'cash' ou 'card')
            if (!$modePaiement || !in_array($modePaiement, ['especes', 'card'])) {
                $this->addFlash('error', 'Mode de paiement invalide.');
                return $this->redirectToRoute('app_payment');
            }

            // Si l'utilisateur est trouvé, procéder au paiement
            // Création de la commande
            $commande = new Commande();
            $commande->setTotal($totalTTC);
            $commande->setModePaiement($modePaiement); // Par exemple : 'cash' ou 'card'
            $commande->setClient($user); // Associer l'utilisateur à la commande
            $commande->setDateCommande(new \DateTime());

            // Enregistrer la commande avec l'EntityManager
            $this->entityManager->persist($commande); // Prépare l'entité pour la sauvegarde
            $this->entityManager->flush(); // Exécute la sauvegarde en base de données

            // Message de succès
            $this->addFlash('success', 'Paiement effectué avec succès. Commande enregistrée.');
        }

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