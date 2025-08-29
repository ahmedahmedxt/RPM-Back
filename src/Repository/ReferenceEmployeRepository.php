<?php

namespace App\Repository;

use App\Entity\ReferenceEmploye;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReferenceEmploye>
 *
 * @method ReferenceEmploye|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReferenceEmploye|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReferenceEmploye[]    findAll()
 * @method ReferenceEmploye[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReferenceEmployeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReferenceEmploye::class);
    }

//    /**
//     * @return ReferenceEmploye[] Returns an array of ReferenceEmploye objects
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

//    public function findOneBySomeField($value): ?ReferenceEmploye
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
