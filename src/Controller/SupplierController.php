<?php

namespace App\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Supplier;
use App\Form\SupplierType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;


final class SupplierController extends AbstractController
{
   
    #[Route('/article', name: 'app_article')]
    public function index(): Response
    {
        return $this->render('base.html.twig', [
            'controller_name' => 'ArticleController',
        ]);
    }


    #[Route('/supplier/new', name: 'supplier_new')]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $supplier = new Supplier();
    $form = $this->createForm(SupplierType::class, $supplier);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($supplier);
        $entityManager->flush();

        return $this->redirectToRoute('supplier_list'); // Vérifie que cette route existe !
    }

    return $this->render('supplier/new.html.twig', [
        'form' => $form->createView(),
    ]);
}

#[Route('/supplier', name: 'supplier_list')]
public function listSuppliers(EntityManagerInterface $entityManager): Response
{
    $suppliers = $entityManager->getRepository(Supplier::class)->findAll();

    if (empty($suppliers)) {
        dd("Aucun fournisseur trouvé !");
    }

    return $this->render('supplier/list.html.twig', [
        'suppliers' => $suppliers,
    ]);
}



    #[Route('/supplier/edit/{id}', name: 'supplier_edit')]
    public function editSupplier(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer le fournisseur par ID
        $supplier = $entityManager->getRepository(Supplier::class)->find($id);

        if (!$supplier) {
            throw $this->createNotFoundException('Fournisseur non trouvé.');
        }

        // Créer le formulaire
        $form = $this->createForm(SupplierType::class, $supplier);
        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Le fournisseur a été modifié avec succès.');
            return $this->redirectToRoute('supplier_list');
        }

        // Afficher le formulaire d'édition
        return $this->render('supplier/edit.html.twig', [
            'form' => $form->createView(),
            'supplier' => $supplier,
        ]);
    }


    #[Route('/supplier/delete/{id}', name: 'supplier_delete', methods: ['POST', 'GET'])]
public function deleteSupplier(int $id, EntityManagerInterface $entityManager): Response
{
    // Récupérer le fournisseur par ID
    $supplier = $entityManager->getRepository(Supplier::class)->find($id);

    if (!$supplier) {
        throw $this->createNotFoundException('Fournisseur non trouvé.');
    }

    // Supprimer l'entité
    $entityManager->remove($supplier);
    $entityManager->flush();

    $this->addFlash('success', 'Le fournisseur a été supprimé avec succès.');
    return $this->redirectToRoute('supplier_list');
}

}
