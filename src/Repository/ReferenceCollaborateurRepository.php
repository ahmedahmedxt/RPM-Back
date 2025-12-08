<?php

namespace App\Repository;

use App\Entity\ReferenceCollaborateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReferenceCollaborateur>
 *
 * @method ReferenceCollaborateur|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReferenceCollaborateur|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReferenceCollaborateur[]    findAll()
 * @method ReferenceCollaborateur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReferenceCollaborateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReferenceCollaborateur::class);
    }

//    /**
//     * @return ReferenceCollaborateur[] Returns an array of ReferenceCollaborateur objects
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

//    public function findOneBySomeField($value): ?ReferenceCollaborateur
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
