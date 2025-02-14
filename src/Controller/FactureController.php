<?php
namespace App\Controller;

use Twig\Environment;
use App\Entity\Facture;
use App\Entity\ListArticle;
use App\Repository\ListArticleRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\FactureRepository;
use Symfony\Component\HttpFoundation\Request;
use TCPDF;

final class FactureController extends AbstractController
{
    #[Route('/facture', name: 'app_facture')]
    public function index(FactureRepository $factureRepository): Response
    {
        $factures = $factureRepository->findAll();
        return $this->render('facture/index.html.twig', [
            'factures' => $factures,
        ]);
    }

    #[Route('/factures', name: 'facture_index')]
    public function show(Request $request, FactureRepository $factureRepository): Response
    {
        $idFacture = $request->query->get('id_facture');

        if ($idFacture) {
            $factures = $factureRepository->findBy(['id' => $idFacture]);
        } else {
            $factures = $factureRepository->findAll();
        }

        return $this->render('facture/index.html.twig', [
            'factures' => $factures,
        ]);
    }
/*
    #[Route('/facture/download/{id}', name: 'facture_download')]
    public function downloadInvoice(int $id, FactureRepository $factureRepository, Environment $twig): Response
    {
        // Récupérer la facture
        $facture = $factureRepository->find($id);

        if (!$facture) {
            throw $this->createNotFoundException("Facture non trouvée !");
        }

        // Générer le HTML
        $html = $twig->render('facture/invoice_pdf.html.twig', [
            'facture' => $facture,
            'paniers' => $facture->getPaniers(),
            'totalHT' => $facture->getTotalHT(),
            'tva' => $facture->getTVA(),
            'totalTTC' => $facture->getTotalTTC(),
        ]);

        // Configurer Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($pdfOptions);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Retourner le fichier PDF
        return new Response($dompdf->stream("facture_{$id}.pdf", ["Attachment" => true]));
    }
*/
/*
      #[Route('/invoice/{factureId}/download', name: 'download_invoice')]
    public function downloadInvoice($factureId, PanierRepository $panierRepository): Response
    {
        // Récupérer les paniers depuis la base de données
        $paniers = $panierRepository->findByFacture($factureId);

        // Calculer les totaux
        $totalHT = 0;
        $totalTTC = 0;
        foreach ($paniers as $panier) {
            $totalHT += $panier->getPrixUnitaire() * $panier->getQuantite();
        }
        $tva = $totalHT * 0.2;  // TVA à 20%
        $totalTTC = $totalHT + $tva;

        // Créer le PDF avec TCPDF
        $pdf = new TCPDF();
        $pdf->AddPage();

        // Titre de la facture
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Facture', 0, 1, 'C');

        // Entêtes de colonnes
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(40, 10, 'Produit', 1);
        $pdf->Cell(40, 10, 'Prix Unitaire', 1);
        $pdf->Cell(40, 10, 'Quantité', 1);
        $pdf->Cell(40, 10, 'Total', 1);
        $pdf->Ln();

        // Ajouter les lignes des produits
        foreach ($paniers as $panier) {
            $pdf->Cell(40, 10, $panier->getArticle()->getNom(), 1);
            $pdf->Cell(40, 10, '$' . number_format($panier->getPrixUnitaire(), 2), 1);
            $pdf->Cell(40, 10, $panier->getQuantite(), 1);
            $pdf->Cell(40, 10, '$' . number_format($panier->getPrixUnitaire() * $panier->getQuantite(), 2), 1);
            $pdf->Ln();
        }

        // Ajouter le total
        $pdf->Cell(120, 10, 'Total HT: $' . number_format($totalHT, 2), 0, 0, 'L');
        $pdf->Ln();
        $pdf->Cell(120, 10, 'TVA (20%): $' . number_format($tva, 2), 0, 0, 'L');
        $pdf->Ln();
        $pdf->Cell(120, 10, 'Total TTC: $' . number_format($totalTTC, 2), 0, 0, 'L');

        // Retourner le PDF en téléchargement
        $pdf->Output('facture.pdf', 'D');

        return new Response();
    }
    */
}
