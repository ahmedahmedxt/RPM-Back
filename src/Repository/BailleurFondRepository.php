<?php

namespace App\Repository;

use App\Entity\BailleurFond;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BailleurFond>
 *
 * @method BailleurFond|null find($id, $lockMode = null, $lockVersion = null)
 * @method BailleurFond|null findOneBy(array $criteria, array $orderBy = null)
 * @method BailleurFond[]    findAll()
 * @method BailleurFond[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BailleurFondRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BailleurFond::class);
    }

//    /**
//     * @return BailleurFond[] Returns an array of BailleurFond objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?BailleurFond
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
