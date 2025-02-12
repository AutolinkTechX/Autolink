<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Article;
use App\Entity\ListArticle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\ListArticleRepository;
use Symfony\Component\HttpFoundation\Request;

final class CommandeController extends AbstractController
{
    #[Route('/add-to-cart/{id}', name: 'add_to_cart', methods: ['GET', 'POST'])]
    public function addToCart(int $id, EntityManagerInterface $em, Request $request): Response
    {
        // Récupérer l'article à partir de l'ID
        $article = $em->getRepository(Article::class)->find($id);

        if (!$article) {
            $this->addFlash('error', 'Article non trouvé.');
            return $this->redirect($request->headers->get('referer')); // Redirige vers la page précédente
        }

        // Vérifier si l'article est déjà dans le panier
        $existingCartItem = $em->getRepository(ListArticle::class)->findOneBy([
            'article' => $article,
        ]);

        if ($existingCartItem) {
            // Augmenter la quantité si l'article existe déjà
            $existingCartItem->setQuantite($existingCartItem->getQuantite() + 1);
        } else {
            // Ajouter un nouvel article au panier
            $cartItem = new ListArticle();
            $cartItem->setArticle($article);
            $cartItem->setPrixUnitaire($article->getPrix());
            $cartItem->setQuantite(1);
            $em->persist($cartItem);
        }

        // Sauvegarder dans la base de données
        $em->flush();

        // Ajouter un message de succès
        $this->addFlash('success', 'Article ajouté au panier.');

        // Rediriger vers la page précédente pour actualiser l'affichage
        return $this->redirect($request->headers->get('referer'));
    }


    #[Route('/commande', name: 'app_commande')]
    public function index(ListArticleRepository $listarticleRepository): Response
    {
        // Utiliser le repository pour récupérer tous les articles
        $paniers = $listarticleRepository->findAll();

        // Calculer les totaux du panier
        $totalHT = 0;
        foreach ($paniers as $panier) {
            $totalHT += $panier->getQuantite() * $panier->getPrixUnitaire();
        }

        // Calculer la TVA
        $tva = $totalHT * 0.20;
        $totalTTC = $totalHT + $tva;

        return $this->render('commande/index.html.twig', [
            'paniers' => $paniers,
            'totalHT' => $totalHT,
            'tva' => $tva,
            'totalTTC' => $totalTTC,
        ]);
    }

    #[Route('/decrease-quantity/{id}', name: 'decrease_quantity', methods: ['POST'])]
    public function decreaseQuantity(int $id, EntityManagerInterface $em, Request $request): Response
    {
        // Récupérer l'article du panier
        $cartItem = $em->getRepository(ListArticle::class)->findOneBy(['article' => $id]);

        if ($cartItem && $cartItem->getQuantite() > 1) {
            // Diminuer la quantité
            $cartItem->setQuantite($cartItem->getQuantite() - 1);
            $this->addFlash('success', 'Quantité diminuée.');
        } else {
            // Supprimer l'article si la quantité est 1
            $em->remove($cartItem);
            $this->addFlash('success', 'Article supprimé du panier.');
        }

        // Enregistrer les modifications
        $em->flush();

        // Rediriger vers la même page pour actualiser le panier
        return $this->redirect($request->headers->get('referer'));
    }

    /*
    #[Route('/facture/download', name: 'facture_download')]
    public function downloadInvoice(): Response
    {
        // Créer la facture en PDF à l'aide de Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $dompdf = new Dompdf($options);

        $html = $this->renderView('facture/invoice_pdf.html.twig');
        $dompdf->loadHtml($html);
        $dompdf->render();

        // Générer le PDF et le télécharger
        $pdfOutput = $dompdf->output();
        return new Response($pdfOutput, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="facture.pdf"'
        ]);
    }
*/

    #[Route('/facture/download/{id}', name: 'facture_download')]
    public function downloadInvoice(int $id, Environment $twig): Response
    {
        // Récupérer la facture et les produits associés depuis la base de données
        $facture = $this->getDoctrine()->getRepository(Facture::class)->find($id);

        /*
        if (!$facture) {
            throw $this->createNotFoundException("Facture non trouvée !");
        }*/

        // Générer le HTML de la facture
        $html = $twig->render('facture/facture_pdf.html.twig', [
            'facture' => $facture,
            'paniers' => $facture->getPaniers(),
            'totalHT' => $facture->getTotalHT(),
            'tva' => $facture->getTVA(),
            'totalTTC' => $facture->getTotalTTC(),
        ]);

        // Initialiser Dompdf avec des options
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($pdfOptions);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Générer la réponse avec le fichier PDF
        return new Response($dompdf->stream("facture_{$id}.pdf", [
            "Attachment" => true
        ]));
    }

}
