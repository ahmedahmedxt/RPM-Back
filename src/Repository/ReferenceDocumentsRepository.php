<?php

namespace App\Repository;

use App\Entity\ReferenceDocuments;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReferenceDocuments>
 *
 * @method ReferenceDocuments|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReferenceDocuments|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReferenceDocuments[]    findAll()
 * @method ReferenceDocuments[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReferenceDocumentsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReferenceDocuments::class);
    }

//    /**
//     * @return ReferenceDocuments[] Returns an array of ReferenceDocuments objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ReferenceDocuments
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
