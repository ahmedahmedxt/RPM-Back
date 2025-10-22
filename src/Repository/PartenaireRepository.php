<?php

namespace App\Repository;

use App\Entity\Partenaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Partenaire>
 */
class PartenaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Partenaire::class);
    }

    public function save(Partenaire $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Partenaire $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Rechercher des partenaires par libellé
     */
    public function findByLibelle(string $search): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.partenaireLibelle LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('p.partenaireLibelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Rechercher par rôle
     */
    public function findByRole(string $role): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.partenaireRole = :role')
            ->setParameter('role', $role)
            ->orderBy('p.partenaireLibelle', 'ASC')
            ->getQuery()
            ->getResult();
    }
}