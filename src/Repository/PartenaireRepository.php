<?php

namespace App\Repository;

use App\Entity\Partenaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

    public function findByRaisonSociale(string $search): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.partenaireRaisonSociale LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('p.partenaireRaisonSociale', 'ASC')
            ->getQuery()
            ->getResult();
    }
}