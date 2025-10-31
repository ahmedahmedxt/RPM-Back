<?php

namespace App\Repository;

use App\Entity\OrganismeDemandeur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrganismeDemandeur>
 *
 * @method OrganismeDemandeur|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrganismeDemandeur|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrganismeDemandeur[]    findAll()
 * @method OrganismeDemandeur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrganismeDemandeurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrganismeDemandeur::class);
    }

    public function add(OrganismeDemandeur $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OrganismeDemandeur $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}