<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\MaterielRecyclable;
use App\Form\MaterielRecyclableType;
use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Enum\StatutEnum;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Validator\Constraints as Assert;


class MaterielRecyclableController extends AbstractController
{
    #[Route('/materiel/recyclable', name: 'app_materiel_recyclable')]
    public function index(): Response
    {
        return $this->render('materiel_recyclable/index.html.twig', [
            'controller_name' => 'MaterielRecyclableController',
        ]);
    }

    // 2. Modification du chemin d'upload (doit pointer vers le dossier public)
    private string $uploadsDirectory;

    public function __construct(string $projectDir)
    {
        // 3. Construction du chemin absolu correct
        $this->uploadsDirectory = $projectDir . '/public/materielimage';
    }

    #[Route('/ajouter', name: 'ajouter_materiel_recyclable')]
    public function ajouter(Request $request, EntityManagerInterface $entityManager): Response
    {
        $materiel = new MaterielRecyclable();
        $materiel->setStatut(StatutEnum::EN_ATTENTE);

        $form = $this->createForm(MaterielRecyclableType::class, $materiel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                // 4. Génération d'un nom de fichier plus sécurisé
                $fileName = uniqid('img_', true) . '.' . $imageFile->guessExtension();

                try {
                    // 5. Déplacement vers le bon répertoire
                    $imageFile->move($this->uploadsDirectory, $fileName);
                    $materiel->setImage($fileName);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image : ' . $e->getMessage());
                    return $this->redirectToRoute('ajouter_materiel_recyclable');
                }
            }

            $entityManager->persist($materiel);
            $entityManager->flush();

            $this->addFlash('success', 'Matériel ajouté avec succès !');
            return $this->redirectToRoute('app_materiaux_liste');
        }

        return $this->render('materiel_recyclable/ajouter.html.twig', [
            'form' => $form->createView(),
            'materielRecyclable' => $materiel // Nécessaire pour l'affichage de l'image
        ]);
    }





   


    #[Route('/materiaux', name: 'app_materiaux_liste')]
    public function liste(EntityManagerInterface $entityManager): Response
    {
        $materiaux = $entityManager->getRepository(MaterielRecyclable::class)->findAll();

        return $this->render('materiel_recyclable/list.html.twig', [
            'materiaux' => $materiaux,
        ]);
    }

    #[Route('/materiel/edit/{id}', name: 'materiel_edit')]
    public function edit(Request $request, MaterielRecyclable $materiel, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MaterielRecyclableType::class, $materiel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Matériau modifié avec succès !');
            return $this->redirectToRoute('app_materiaux_liste');
        }

        return $this->render('materiel_recyclable/edit.html.twig', [
            'materiel' => $materiel,
            'form' => $form->createView(),
        ]);
    }










    #[Route('/materiel/delete/{id}', name: 'materiel_delete')]
public function delete($id, EntityManagerInterface $entityManager): RedirectResponse
{
    $materiel = $entityManager->getRepository(MaterielRecyclable::class)->find($id);

    if ($materiel) {
        // Suppression de l'image associée si elle existe
        if ($materiel->getImage()) {
            $imagePath = $this->getParameter('materiel_image_directory') . '/' . $materiel->getImage();
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $entityManager->remove($materiel);
        $entityManager->flush();
        $this->addFlash('success', 'Matériel recyclable supprimé avec succès.');
    } else {
        $this->addFlash('error', 'Matériel recyclable non trouvé.');
    }

    return $this->redirectToRoute('app_materiaux_liste'); // Redirection vers la liste des matériaux recyclables
}

}