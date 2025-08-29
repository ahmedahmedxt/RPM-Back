<?php

namespace App\Repository;

use App\Entity\Methodologie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Methodologie>
 *
 * @method Methodologie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Methodologie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Methodologie[]    findAll()
 * @method Methodologie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MethodologieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Methodologie::class);
    }

//    /**
//     * @return Methodologie[] Returns an array of Methodologie objects
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

//    public function findOneBySomeField($value): ?Methodologie
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
