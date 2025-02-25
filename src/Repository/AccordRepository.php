<?php

namespace App\Repository;

use App\Entity\Accord;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Accord>
 */
class AccordRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Accord::class);
    }

    //    /**
    //     * @return Accord[] Returns an array of Accord objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Accord
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }



    public function getMoyenneDelaiTraitement(Entreprise $entreprise): ?float
{
    return $this->createQueryBuilder('a')
        ->select('AVG(DATEDIFF(a.date_reception, a.date_creation)) as moyenneDelai')
        ->join('a.materielRecyclable', 'm') // Jointure avec le matériel recyclable
        ->where('m.entreprise = :entreprise')
        ->setParameter('entreprise', $entreprise)
        ->getQuery()
        ->getSingleScalarResult();
}



public function getDelaiMoyenParJour(Entreprise $entreprise): array
{
    return $this->createQueryBuilder('a')
        ->select('DATE(a.date_creation) as jour, AVG(DATEDIFF(a.date_reception, a.date_creation)) as moyenneDelai')
        ->join('a.materielRecyclable', 'm')
        ->where('m.entreprise = :entreprise')
        ->setParameter('entreprise', $entreprise)
        ->groupBy('jour')
        ->orderBy('jour', 'ASC')
        ->getQuery()
        ->getResult();



}


/*public function countTotalDemandes(): int
{
    return (int) $this->createQueryBuilder('a')
        ->select('COUNT(a.id)')
        ->getQuery()
        ->getSingleScalarResult();
}*/


public function countDemandesByEntreprise(\App\Entity\Entreprise $entreprise): int
{
    return (int) $this->createQueryBuilder('a')
        ->select('COUNT(a.id) AS nombreDemandes')
        ->join('a.materielRecyclable', 'm')  // Jointure avec MaterielRecyclable
        ->join('m.entreprise', 'e') // Jointure avec Entreprise
        ->where('e = :entreprise') // Filtrer par l'entreprise connectée
        ->setParameter('entreprise', $entreprise)
        ->getQuery()
        ->getSingleScalarResult();
}






public function countDemandesByClient($entreprise)
{
    return $this->createQueryBuilder('a')
        ->select("CONCAT(c.name, ' ', c.lastName) AS client", 'COUNT(a.id) AS nombreDemandes')
        ->join('a.materielRecyclable', 'm') // Jointure avec MaterielRecyclable
        ->join('m.user', 'c') // Jointure avec Client
        ->where('m.entreprise = :entreprise')
        ->setParameter('entreprise', $entreprise)
        ->groupBy('c.id') // Grouper par client
        ->orderBy('nombreDemandes', 'DESC') // Trier par nombre décroissant
        ->getQuery()
        ->getResult();
}


}
