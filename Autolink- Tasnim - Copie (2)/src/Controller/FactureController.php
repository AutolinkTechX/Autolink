<?php
namespace App\Controller;

use Twig\Environment;
use App\Entity\Facture;
use Dompdf\Dompdf;
use Dompdf\Options;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\FactureRepository;
use Symfony\Component\HttpFoundation\Request;

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
}
