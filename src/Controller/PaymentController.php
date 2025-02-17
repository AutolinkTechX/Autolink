<?php
namespace App\Controller;

use App\Repository\ListArticleRepository;
use App\Repository\UserRepository;
use App\Repository\CommandeRepository;
use App\Entity\Commande;
use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class PaymentController extends AbstractController
{
    private $listarticleRepository;
    private $userRepository;
    private $commandeRepository;
    private $entityManager;

    public function __construct(
        ListArticleRepository $listarticleRepository,
        UserRepository $userRepository,
        CommandeRepository $commandeRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->listarticleRepository = $listarticleRepository;
        $this->userRepository = $userRepository;
        $this->commandeRepository = $commandeRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/payment', name: 'app_payment')]
    public function index(Request $request): Response
    {
        // Récupérer tous les articles du panier
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
    
        // Si le formulaire de paiement est soumis
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
                // L'utilisateur n'a pas été trouvé
                $this->addFlash('error', 'Vérifiez vos données. Aucune correspondance trouvée.');
                return $this->redirectToRoute('app_payment');
            }
    
            // Récupérer le mode de paiement
            $modePaiement = $request->get('payment_method');
            
            // Vérifier le mode de paiement
            if (!$modePaiement || !in_array($modePaiement, ['especes', 'card'])) {
                $this->addFlash('error', 'Mode de paiement invalide.');
                return $this->redirectToRoute('app_payment');
            }
    
            // Création de la commande
            $commande = new Commande();
            $commande->setTotal($totalTTC);
            $commande->setModePaiement($modePaiement);
            $commande->setClient($user);
            $commande->setDateCommande(new \DateTime());
    
            // Persister la commande (cela génère un ID pour la commande)
            $this->entityManager->persist($commande);
    
            // Récupérer les identifiants des articles dans le panier et les associer à la commande
            $articleIds = [];
            foreach ($paniers as $panier) {
                $articleIds[] = $panier->getArticle()->getId(); // Ajouter l'ID de l'article à la commande
            }
    
            // Associer les identifiants des articles à la commande
            $commande->setArticleIds($articleIds); // On utilise la méthode setArticleIds
    
            // Sauvegarder la commande avec les articles payés
            $this->entityManager->flush(); // Sauvegarder en base de données
    
            // Vider le panier après un paiement réussi et réduire le stock de chaque article
            foreach ($paniers as $panier) {
                $article = $panier->getArticle(); // Récupérer l'article
                // Récupérer la quantité en stock
                $quantiteStock = $article->getQuantiteStock(); // Utilisation de quantiteStock

                // Vérifier si l'article a suffisamment de stock
                if ($quantiteStock >= $panier->getQuantite()) {
                    // Diminuer le stock de l'article
                    $article->setQuantiteStock($quantiteStock - $panier->getQuantite());
                    $this->entityManager->persist($article); // Persister la modification du stock
                } else {
                    $this->addFlash('error', 'Pas assez de stock pour l\'article ' . $article->getNom());
                    return $this->redirectToRoute('app_payment');
                }

                // Supprimer l'article du panier
                $this->entityManager->remove($panier);
            }

            $this->entityManager->flush(); // Sauvegarder les modifications
    
            // Message de succès
            $this->addFlash('success', 'Paiement effectué avec succès et votre Commande enregistrée.');
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