<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commande>
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    public function countSalesByProduct(): array
    {
        $conn = $this->getEntityManager()->getConnection();
    
        // Récupérer tous les article_ids depuis la table commande
        $sql = "SELECT article_ids FROM commande";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        $commandes = $resultSet->fetchAllAssociative();
    
        // Récupérer tous les articles depuis la table article
        $sqlArticles = "SELECT id, nom FROM article";
        $stmtArticles = $conn->prepare($sqlArticles);
        $resultSetArticles = $stmtArticles->executeQuery();
        $articles = $resultSetArticles->fetchAllAssociative();
    
        // Créer un tableau associatif pour mapper les IDs des articles à leurs noms
        $articleMap = [];
        foreach ($articles as $article) {
            $articleMap[$article['id']] = $article['nom'];
        }
    
        $productSales = [];
    
        // Parcourir les commandes et compter les ventes par produit
        foreach ($commandes as $commande) {
            $articleIds = json_decode($commande['article_ids'], true);
    
            if (is_array($articleIds)) {
                foreach ($articleIds as $productId) {
                    if (!isset($productSales[$productId])) {
                        $productSales[$productId] = [
                            'nom' => $articleMap[$productId] ?? 'Unknown Product', // Utiliser le nom de l'article
                            'sales' => 0
                        ];
                    }
                    $productSales[$productId]['sales']++;
                }
            }
        }
    
        // Convertir en format SQL-friendly
        $result = [];
        foreach ($productSales as $productId => $data) {
            $result[] = [
                'product_id' => $productId,
                'nom' => $data['nom'],
                'sales' => $data['sales']
            ];
        }
    
        return $result;
    }

    // Dans CommandeRepository
    public function countPaymentsByMode(): array
    {
        return $this->createQueryBuilder('c')
            ->select('
                SUM(CASE WHEN c.modePaiement = :card THEN 1 ELSE 0 END) AS card,
                SUM(CASE WHEN c.modePaiement = :especes THEN 1 ELSE 0 END) AS especes
            ')
            ->setParameter('card', 'card')
            ->setParameter('especes', 'especes')
            ->getQuery()
            ->getSingleResult();
    }


    //    /**
    //     * @return Commande[] Returns an array of Commande objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Commande
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}