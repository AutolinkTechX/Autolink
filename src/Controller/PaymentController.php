<?php
namespace App\Controller;

use App\Repository\ListArticleRepository;
use App\Repository\UserRepository;
use App\Repository\CommandeRepository;
use App\Entity\Commande;
use App\Entity\Article;
use App\Entity\Facture;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Stripe\Stripe;
use Stripe\Charge;

final class PaymentController extends AbstractController
{
    private $listarticleRepository;
    private $userRepository;
    private $commandeRepository;
    private $entityManager;
    private $stripeSecretKey;
    private $stripePublicKey;

    public function __construct(
        ListArticleRepository $listarticleRepository,
        UserRepository $userRepository,
        CommandeRepository $commandeRepository,
        EntityManagerInterface $entityManager,
        string $stripeSecretKey,
        string $stripePublicKey
    ) {
        $this->listarticleRepository = $listarticleRepository;
        $this->userRepository = $userRepository;
        $this->commandeRepository = $commandeRepository;
        $this->entityManager = $entityManager;
        $this->stripeSecretKey = $stripeSecretKey;
        $this->stripePublicKey = $stripePublicKey;
    }

    #[Route('/payment', name: 'app_payment')]
    public function index(Request $request, Security $security): Response
    {
        // Vérifier si l'utilisateur est connecté
        $user = $security->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour ajouter un article au panier.');
            return $this->redirectToRoute('login'); // Redirection vers la page de connexion
        }
    

        // Dummy data (replace with actual cart data)
        $paniers = $this->listarticleRepository->findAll(); 
        $totalHT = 0;
        foreach ($paniers as $panier) {
            $totalHT += $panier->getQuantite() * $panier->getPrixUnitaire();
        }

        $tva = $totalHT * 0.20;
        $totalTTC = $totalHT + $tva;

        // Detect which modal should be shown
        $showCardModal = filter_var($request->query->get('showCardModal', false), FILTER_VALIDATE_BOOLEAN);
        $showCashModal = filter_var($request->query->get('showCashModal', false), FILTER_VALIDATE_BOOLEAN);

        if ($request->isMethod('POST')) {
            $paymentMethod = $request->get('payment_method');

            if ($paymentMethod === 'card') {
                return $this->handleCardPayment($request, $totalTTC , $paniers , $user);
            } elseif ($paymentMethod === 'especes') {
                return $this->handleCashPayment($request, $totalTTC , $paniers );
            } else {
                $this->addFlash('error', 'Mode de paiement invalide.');
                return $this->redirectToRoute('app_payment');
            }
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

    private function handleCardPayment(Request $request, float $totalTTC , array $paniers, User $user): Response
    {
        $token = $request->get('stripeToken'); // Get the token sent from the frontend

        if (!$token) {
            // Token is missing
            $this->addFlash('error', 'Erreur de paiement : le token est manquant.');
            return $this->redirectToRoute('app_payment');
        }

        try {
            // ✅ Use the SECRET API Key for backend
            \Stripe\Stripe::setApiKey($this->stripeSecretKey);

            // Perform the charge using the token
            \Stripe\Charge::create([
                'amount' => $totalTTC * 100, // Convert to cents
                'currency' => 'eur',
                'source' => $token, // Use the token from frontend
                'description' => 'Paiement Commande'
            ]);


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

            // Persister la commande
           // Essayer de persister et flush juste la commande
            try {
                $this->entityManager->persist($commande);
                $this->entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la persistance de la commande: ' . $e->getMessage());
                return $this->redirectToRoute('app_payment');
            }

           // Vérification de l'existence de $paniers
           if (!isset($paniers)) {
                $this->addFlash('error', 'Le panier est introuvable.');
                return $this->redirectToRoute('app_payment');
            }

            // Récupérer les identifiants des articles dans le panier
           $articleIds = [];
           foreach ($paniers as $panier) {
                $articleIds[] = $panier->getArticle()->getId();
            }

            $commande->setArticleIds($articleIds);
            $this->entityManager->flush(); // Sauvegarde de la commande

            // Enregistrer dans la table facture
            $facture = new Facture();
            $facture->setCommande($commande);
            $facture->setMontant($totalTTC);
            $facture->setDatetime($commande->getDateCommande());
            $facture->setClient($user);

            $this->entityManager->persist($facture);
            $this->entityManager->flush();

            // Vider le panier et mettre à jour le stock
            foreach ($paniers as $panier) {
                 $article = $panier->getArticle();
                $quantiteStock = $article->getQuantiteStock();

                if ($quantiteStock >= $panier->getQuantite()) {
                    $article->setQuantiteStock($quantiteStock - $panier->getQuantite());
                    $this->entityManager->persist($article);
                } else {
                    $this->addFlash('error', 'Pas assez de stock pour l\'article ' . $article->getNom());
                    return $this->redirectToRoute('app_payment');
                }

                $this->entityManager->remove($panier);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Paiement par carte réussi.');
            return $this->redirectToRoute('app_listarticle');

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur de paiement : ' . $e->getMessage());
            return $this->redirectToRoute('app_payment');
        }

    
    }

    private function handleCashPayment(Request $request, float $totalTTC , array $paniers): Response
    {
        
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

            // Persister la commande
            $this->entityManager->persist($commande);

           // Vérification de l'existence de $paniers
           if (!isset($paniers)) {
                $this->addFlash('error', 'Le panier est introuvable.');
                return $this->redirectToRoute('app_payment');
            }

            // Récupérer les identifiants des articles dans le panier
            $articleIds = [];
            foreach ($paniers as $panier) {
                $articleIds[] = $panier->getArticle()->getId();
            }

            $commande->setArticleIds($articleIds);
            $this->entityManager->flush(); // Sauvegarde de la commande

            // Enregistrer dans la table facture
           $facture = new Facture();
           $facture->setCommande($commande);
           $facture->setMontant($totalTTC);
           $facture->setDatetime($commande->getDateCommande());
           $facture->setClient($user);

           $this->entityManager->persist($facture);
           $this->entityManager->flush();

          // Vider le panier et mettre à jour le stock
           foreach ($paniers as $panier) {
                $article = $panier->getArticle();
                $quantiteStock = $article->getQuantiteStock();

                if ($quantiteStock >= $panier->getQuantite()) {
                    $article->setQuantiteStock($quantiteStock - $panier->getQuantite());
                    $this->entityManager->persist($article);
                } else {
                    $this->addFlash('error', 'Pas assez de stock pour l\'article ' . $article->getNom());
                    return $this->redirectToRoute('app_payment');
                }

                $this->entityManager->remove($panier);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Paiement effectué avec succès et votre commande a été enregistrée.');
        }

        return $this->redirectToRoute('app_listarticle');
    }


}