<?php

namespace App\Repository;

use App\Entity\MoyenLivraison;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MoyenLivraison>
 *
 * @method MoyenLivraison|null find($id, $lockMode = null, $lockVersion = null)
 * @method MoyenLivraison|null findOneBy(array $criteria, array $orderBy = null)
 * @method MoyenLivraison[]    findAll()
 * @method MoyenLivraison[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MoyenLivraisonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MoyenLivraison::class);
    }

    public function add(MoyenLivraison $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MoyenLivraison $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}