<?php

namespace App\Repository;

use App\Entity\CvLangueNiveau;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CvLangueNiveau>
 *
 * @method CvLangueNiveau|null find($id, $lockMode = null, $lockVersion = null)
 * @method CvLangueNiveau|null findOneBy(array $criteria, array $orderBy = null)
 * @method CvLangueNiveau[]    findAll()
 * @method CvLangueNiveau[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CvLangueNiveauRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CvLangueNiveau::class);
    }

//    /**
//     * @return CvLangueNiveau[] Returns an array of CvLangueNiveau objects
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

//    public function findOneBySomeField($value): ?CvLangueNiveau
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
