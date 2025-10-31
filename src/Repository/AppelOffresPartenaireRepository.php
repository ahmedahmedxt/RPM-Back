<?php

namespace App\Repository;

use App\Entity\AppelOffresPartenaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AppelOffresPartenaire>
 */
class AppelOffresPartenaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppelOffresPartenaire::class);
    }

    public function add(AppelOffresPartenaire $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AppelOffresPartenaire $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Trouve tous les partenaires d'un appel d'offres.
     */
    public function findByAppelOffres(int $appelOffresId): array
    {
        return $this->createQueryBuilder('aop')
            ->andWhere('aop.appelOffres = :id')
            ->setParameter('id', $appelOffresId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve tous les appels d'offres d'un partenaire.
     */
    public function findByPartenaire(int $partenaireId): array
    {
        return $this->createQueryBuilder('aop')
            ->andWhere('aop.partenaire = :id')
            ->setParameter('id', $partenaireId)
            ->getQuery()
            ->getResult();
    }
}