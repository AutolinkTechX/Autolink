<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Accord;
use App\Entity\MaterielRecyclable;
use App\Enum\StatutEnum;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Service\EmailService;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\AccordRepository;
use App\Repository\MaterielRecyclableRepository;




final class AccordController extends AbstractController
{
    #[Route('/accord', name: 'app_accord')]
    public function index(): Response
    {
        return $this->render('accord/index.html.twig', [
            'controller_name' => 'AccordController',
        ]);
    }




    // src/Controller/AccordController.php

#[Route('/accords/acceptes/search', name: 'accord_search')]
public function search(Request $request, AccordRepository $accordRepository, MaterielRecyclableRepository $materielRecyclableRepository): Response
{
    $nomMateriel = $request->query->get('nom_materiel');

    if ($nomMateriel) {
        // Trouver les matériaux recyclables correspondant au nom
        $materiaux = $materielRecyclableRepository->searchByName($nomMateriel);

        // Si des matériaux sont trouvés, récupérer les accords associés
        if (!empty($materiaux)) {
            $accords = $accordRepository->createQueryBuilder('a')
                ->join('a.materielRecyclable', 'm')
                ->where('m IN (:materiaux)')
                ->setParameter('materiaux', $materiaux)
                ->getQuery()
                ->getResult();
        } else {
            $accords = [];
        }
    } else {
        // Si aucun terme de recherche, récupérer tous les accords
        $accords = $accordRepository->findAll();
    }

    return $this->render('accord/accords_acceptes.html.twig', [
        'accords' => $accords,
      
    ]);
}





#[Route('/entreprise/accords', name: 'entreprise_accords')]
public function listeAccords(EntityManagerInterface $entityManager): Response
{
    // Vérifier que l'utilisateur est connecté
    $user = $this->getUser();
    if (!$user) {
        throw $this->createAccessDeniedException('Vous devez être connecté pour voir cette page.');
    }

    // Vérifier si l'utilisateur a le rôle ENTREPRISE
    if (!in_array('ROLE_ENTREPRISE', $user->getRoles())) {
        throw $this->createAccessDeniedException('Seules les entreprises peuvent voir cette page.');
    }

    // Récupérer les matériaux recyclables appartenant à l'entreprise et qui sont en attente
    $materiaux = $entityManager->getRepository(MaterielRecyclable::class)->findBy([
        'entreprise' => $user,
        'statut' => StatutEnum::EN_ATTENTE // 🔥 Filtre basé sur l'ENUM du matériel recyclable
    ]);

    // Récupérer les accords liés aux matériaux trouvés
    $accords = $entityManager->getRepository(Accord::class)->findBy([
        'materielRecyclable' => $materiaux
    ]);

    return $this->render('accord/accord.html.twig', [
        'accords' => $accords,
    ]);
}

/*#[Route('/accord/refuser/{id}', name: 'accord_refuser', methods: ['GET'])]
public function refuser(Accord $accord, EntityManagerInterface $entityManager, EmailService $emailService): Response
{
    console.loh('La méthode refuser() est exécutée');// ✅ Vérifie si la fonction est bien appelée

    $materiel = $accord->getMaterielRecyclable();
    $materiel->setStatut(StatutEnum::REFUSE);

    $utilisateur = $materiel->getUser();

    $entityManager->persist($materiel);
    $entityManager->remove($accord);
    $entityManager->flush();

    // Récupérer l'email de l'utilisateur et envoyer l'email
   

    if ($utilisateur && !empty($utilisateur->getEmail())) {
        dump('Utilisateur trouvé: ' . $utilisateur->getEmail()); // ✅ Vérifie si l'utilisateur a un email

        try {
            $emailService->envoyerRefusEmail($utilisateur->getEmail());
            dump('Email envoyé avec succès'); // ✅ Vérifie si l'email a été envoyé
        } catch (\Exception $e) {
            dump('Erreur envoi email: ' . $e->getMessage()); die;
        }
    } else {
        dump('Utilisateur sans email'); die;
    }

    return $this->redirectToRoute('entreprise_accords');
}*/


/*private function envoyerRefusEmail(Accord $accord, MailerInterface $mailer): void
{
    $utilisateur = $accord->getMaterielRecyclable()->getUser(); // 🔥 Récupérer l'utilisateur

    if (!$utilisateur) {
        throw new \Exception('Utilisateur non trouvé pour cet accord.');
    }

    $email = (new Email())
        ->from('farahbaklouti007@gmail.com')
        ->to($utilisateur->getEmail()) // 🔹 Email du destinataire
        ->subject('Accord refusé')
        ->text('Votre demande d’accord a été refusée.');
        dump($utilisateur->getEmail()); die();


    $mailer->send($email);
}*/







 #[Route('/accord/accepter/{id}', name: 'accord_accepter', methods: ['GET'])]
public function accepterAccord(int $id, EntityManagerInterface $entityManager): Response
{
    $accord = $entityManager->getRepository(Accord::class)->find($id);

    if (!$accord) {
        throw $this->createNotFoundException('Accord non trouvé.');
    }

    // ✅ Récupérer le matériel recyclable lié à l'accord
    $materiel = $accord->getMaterielRecyclable();

    if (!$materiel) {
        throw $this->createNotFoundException('Matériel recyclable non trouvé.');
    }

    // ✅ Mettre à jour le statut du matériel recyclable
    $materiel->setStatut(StatutEnum::VALIDE); // Assure-toi que StatutEnum::VALIDE est bien défini

    // ✅ Mettre à jour la date de réception de l'accord
    $accord->setDateReception(new \DateTimeImmutable());


    $entityManager->persist($materiel);
    $entityManager->persist($accord);
    $entityManager->flush();

    // ✅ Redirection vers la liste des accords acceptés
    return $this->redirectToRoute('accords_acceptes');
}





/*#[Route('/accord/accepter/{id}', name: 'accord_accepter', methods: ['GET'])]
public function accepterAccord(
    int $id, 
    EntityManagerInterface $entityManager,
    EmailService $emailService // Injection du service EmailService
): Response {
    // 🔍 Recherche de l'accord en base de données
    $accord = $entityManager->getRepository(Accord::class)->find($id);

    // ❌ Vérification si l'accord existe
    if (!$accord) {
        throw $this->createNotFoundException('Accord non trouvé.');
    }

    // 🔍 Récupération du matériel recyclable lié à cet accord
    $materiel = $accord->getMaterielRecyclable();

    // ❌ Vérification si le matériel existe
    if (!$materiel) {
        throw $this->createNotFoundException('Matériel recyclable non trouvé.');
    }

    // 🔍 Récupération de l'utilisateur depuis le matériel
    $user = $materiel->getUser();

    // ❌ Vérification si l'utilisateur existe
    if (!$user) {
        throw $this->createNotFoundException('Utilisateur non trouvé pour ce matériel.');
    }

    // ✅ Mise à jour du statut du matériel
    $materiel->setStatut(StatutEnum::VALIDE);
    
    // ✅ Mise à jour de la date de réception de l'accord
    $accord->setDateReception(new \DateTimeImmutable());

    // 💾 Sauvegarde des modifications
    $entityManager->persist($materiel);
    $entityManager->persist($accord);
    $entityManager->flush();

    // 📧 Envoi de l'email à l'utilisateur
    $emailService->envoyerRefusEmail($user->getEmail()); 

    // 🔄 Redirection vers la liste des accords acceptés
    return $this->redirectToRoute('accords_acceptes');
}
*/


#[Route('/accord/refuser/{id}', name: 'accord_refuser', methods: ['GET'])]
public function refuserAccord(
    int $id, 
    EntityManagerInterface $entityManager, 
    EmailService $emailService // Injection du service EmailService
): Response {
    // 🔍 Recherche de l'accord en base de données
    $accord = $entityManager->getRepository(Accord::class)->find($id);

    // ❌ Vérification si l'accord existe
    if (!$accord) {
        throw $this->createNotFoundException('Accord non trouvé.');
    }

    // 🔍 Récupération du matériel recyclable lié à cet accord
    $materiel = $accord->getMaterielRecyclable();

    // ❌ Vérification si le matériel existe
    if (!$materiel) {
        throw $this->createNotFoundException('Matériel recyclable non trouvé.');
    }

    // 🔍 Récupération de l'utilisateur depuis le matériel
    $user = $materiel->getUser();

    // ❌ Vérification si l'utilisateur existe
    if (!$user) {
        throw $this->createNotFoundException('Utilisateur non trouvé pour ce matériel.');
    }

    // ✅ Mise à jour du statut du matériel en REFUSÉ
    $materiel->setStatut(StatutEnum::REFUSE); // Assure-toi que StatutEnum::REFUSE est bien défini

    // ✅ Mise à jour de la date de refus de l'accord
    $accord->setDateReception(new \DateTimeImmutable());

    // 💾 Sauvegarde des modifications
    $entityManager->persist($materiel);
    $entityManager->persist($accord);
    $entityManager->flush();

    // 📧 Envoi de l'email à l'utilisateur
    $emailService->envoyerRefusEmail($user->getEmail()); 

    // 🔄 Redirection vers la liste des accords refusés
    return $this->redirectToRoute('accords_refuses');
}


#[Route('/accords/refuses', name: 'accords_refuses')]
public function accordsRefuses(EntityManagerInterface $entityManager): Response
{
    // Vérifier que l'utilisateur est connecté
    $user = $this->getUser();
    if (!$user) {
        throw $this->createAccessDeniedException('Vous devez être connecté pour voir cette page.');
    }

    // Vérifier si l'utilisateur a le rôle ENTREPRISE
    if (!in_array('ROLE_ENTREPRISE', $user->getRoles())) {
        throw $this->createAccessDeniedException('Seules les entreprises peuvent voir cette page.');
    }

    // Récupérer les matériaux recyclables refusés
    $materiels = $entityManager->getRepository(MaterielRecyclable::class)->findBy([
        'entreprise' => $user,
        'statut' => StatutEnum::REFUSE // ❌ Filtrer uniquement les matériaux refusés
    ]);

    return $this->render('accord/accords_refuses.html.twig', [
        'materiels' => $materiels,
    ]);
}
    




#[Route('/accords/acceptes', name: 'accords_acceptes')]
public function accordsAcceptes(EntityManagerInterface $entityManager): Response
{
    // Vérifier que l'utilisateur est connecté
    $user = $this->getUser();
    if (!$user) {
        throw $this->createAccessDeniedException('Vous devez être connecté pour voir cette page.');
    }

    // Vérifier si l'utilisateur a le rôle ENTREPRISE
    if (!in_array('ROLE_ENTREPRISE', $user->getRoles())) {
        throw $this->createAccessDeniedException('Seules les entreprises peuvent voir cette page.');
    }

    // Récupérer les matériaux recyclables acceptés
    $materiels = $entityManager->getRepository(MaterielRecyclable::class)->findBy([
        'entreprise' => $user,
        'statut' => StatutEnum::VALIDE // ✅ Filtrer uniquement les matériaux acceptés
    ]);

    return $this->render('accord/accords_acceptes.html.twig', [
        'materiels' => $materiels,
    ]);
}




}
