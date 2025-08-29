<?php

namespace App\Repository;

use App\Entity\EmployeLangueNiveau;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmployeLangueNiveau>
 *
 * @method EmployeLangueNiveau|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmployeLangueNiveau|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmployeLangueNiveau[]    findAll()
 * @method EmployeLangueNiveau[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmployeLangueNiveauRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmployeLangueNiveau::class);
    }

//    /**
//     * @return EmployeLangueNiveau[] Returns an array of EmployeLangueNiveau objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?EmployeLangueNiveau
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
