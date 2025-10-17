<?php

namespace App\Repository;

use App\Entity\Reference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reference>
 *
 * @method Reference|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reference|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reference[]    findAll()
 * @method Reference[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reference::class);
    }

//    /**
//     * @return Reference[] Returns an array of Reference objects
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

//    public function findOneBySomeField($value): ?Reference
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


/**
 * Compte le nombre total de références
 */
public function countAllReferences(): int
{
    return $this->createQueryBuilder('r')
        ->select('COUNT(r.referenceID)')
        ->getQuery()
        ->getSingleScalarResult();
}

/**
 * Génère la prochaine référence automatiquement (REF01, REF02, etc.)
 */
public function generateNextReferenceRef(): string
{
    $count = $this->countAllReferences();
    $nextNumber = $count + 1;
    return 'REF' . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
}

/**
 * Vérifie si une référence existe déjà
 */
public function referenceRefExists(string $referenceRef): bool
{
    return $this->createQueryBuilder('r')
        ->select('COUNT(r.referenceID)')
        ->where('r.referenceRef = :ref')
        ->setParameter('ref', $referenceRef)
        ->getQuery()
        ->getSingleScalarResult() > 0;
}
}
