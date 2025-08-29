<?php

namespace App\Repository;

use App\Entity\EmployePoste;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmployePoste>
 *
 * @method EmployePoste|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmployePoste|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmployePoste[]    findAll()
 * @method EmployePoste[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmployePosteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmployePoste::class);
    }

//    /**
//     * @return EmployePoste[] Returns an array of EmployePoste objects
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

//    public function findOneBySomeField($value): ?EmployePoste
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
