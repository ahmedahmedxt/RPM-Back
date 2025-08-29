<?php

namespace App\Repository;

use App\Entity\EmployeDocuments;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmployeDocuments>
 *
 * @method EmployeDocuments|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmployeDocuments|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmployeDocuments[]    findAll()
 * @method EmployeDocuments[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmployeDocumentsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmployeDocuments::class);
    }

//    /**
//     * @return EmployeDocuments[] Returns an array of EmployeDocuments objects
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

//    public function findOneBySomeField($value): ?EmployeDocuments
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
