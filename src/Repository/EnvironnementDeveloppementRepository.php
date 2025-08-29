<?php

namespace App\Repository;

use App\Entity\EnvironnementDeveloppement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EnvironnementDeveloppement>
 *
 * @method EnvironnementDeveloppement|null find($id, $lockMode = null, $lockVersion = null)
 * @method EnvironnementDeveloppement|null findOneBy(array $criteria, array $orderBy = null)
 * @method EnvironnementDeveloppement[]    findAll()
 * @method EnvironnementDeveloppement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EnvironnementDeveloppementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EnvironnementDeveloppement::class);
    }

//    /**
//     * @return EnvironnementDeveloppement[] Returns an array of EnvironnementDeveloppement objects
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

//    public function findOneBySomeField($value): ?EnvironnementDeveloppement
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
