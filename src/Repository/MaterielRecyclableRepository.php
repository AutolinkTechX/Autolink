<?php

namespace App\Repository;

use App\Entity\MaterielRecyclable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Enum\StatutEnum;

/**
 * @extends ServiceEntityRepository<MaterielRecyclable>
 */
class MaterielRecyclableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MaterielRecyclable::class);
    }

    //    /**
    //     * @return MaterielRecyclable[] Returns an array of MaterielRecyclable objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?MaterielRecyclable
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }








    /**
     * Récupère les matériaux recyclables par statut.
     */
   

     /*public function filterByStatut(?StatutEnum $statut = null): array
     {
         $qb = $this->createQueryBuilder('m');
 
         if ($statut !== null) {
             $qb->andWhere('m.statut = :statut')
                ->setParameter('statut', $statut);
         }
 
         return $qb->getQuery()->getResult();
     }*/




     public function findByStatut(string $statut): array
{
    return $this->createQueryBuilder('m')
        ->where('m.statut = :statut')
        ->setParameter('statut', $statut)
        ->groupBy('m.id')
        ->getQuery();
       // ->getQuery()
       // ->getResult();
}
    


/*public function countByDate()
{
    return $this->getEntityManager()
        ->createQuery("
            SELECT SUBSTRING(m.datecreation, 1, 10) as date, COUNT(m.id) as count
            FROM App\Entity\MaterielRecyclable m
            GROUP BY date
        ")
        ->getResult();*/

/*public function countByStatut(): array
{
    return $this->createQueryBuilder('m')
        ->select('m.statut as statut, COUNT(m.id) as count')
        ->groupBy('m.statut')
        ->getQuery()
        ->getResult();
}*/

     public function findAllSortedByDate(string $sort = 'desc')
     {
         return $this->createQueryBuilder('m')
             ->orderBy('m.datecreation', $sort === 'asc' ? 'ASC' : 'DESC')
             ->getQuery()
             ->getResult();
     }


     public function findByUser(User $user)
{
    return $this->createQueryBuilder('m')
        ->where('m.user = :user')
        ->setParameter('user', $user)
        ->getQuery()
        ->getResult();
}




public function searchByName(string $name): array
{
    return $this->createQueryBuilder('m')
        ->where('m.name LIKE :name')
        ->setParameter('name', '%' . $name . '%')
        ->getQuery()
        ->getResult();
}





public function countByDate($entreprise)
{
    return $this->createQueryBuilder('m')
        ->select("SUBSTRING(m.datecreation, 1, 10) as date, COUNT(m.id) as count") // ✅ Extraction de la date sans l'heure
        ->where('m.entreprise = :entreprise')
        ->setParameter('entreprise', $entreprise)
        ->groupBy('date')
        ->orderBy('date', 'ASC')
        ->getQuery()
        ->getResult();
}

public function countByStatut($entreprise)
{
    return $this->createQueryBuilder('m')
        ->select('m.statut as statut, COUNT(m.id) as count')
        ->where('m.entreprise = :entreprise')
        ->setParameter('entreprise', $entreprise)
        ->groupBy('m.statut')
        ->getQuery()
        ->getResult();
}



public function countByTypeMateriel($entreprise)
{
    return $this->createQueryBuilder('m')
        ->select('m.type_materiel, COUNT(m.id) as nombre')
        ->where('m.entreprise = :entreprise')
        ->setParameter('entreprise', $entreprise)
        ->groupBy('m.type_materiel')
        ->getQuery()
        ->getResult();
}


}






     

