<?php

namespace App\Repository;

use App\Entity\AppelOffrePartenaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AppelOffrePartenaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppelOffrePartenaire::class);
    }

    /**
     * Trouver tous les partenaires d'un appel d'offre
     */
    public function findByAppelOffre(int $appelOffreId): array
    {
        return $this->createQueryBuilder('aop')
            ->andWhere('aop.appelOffre = :appelOffreId')
            ->setParameter('appelOffreId', $appelOffreId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouver tous les appels d'offre d'un partenaire
     */
    public function findByPartenaire(int $partenaireId): array
    {
        return $this->createQueryBuilder('aop')
            ->andWhere('aop.partenaire = :partenaireId')
            ->setParameter('partenaireId', $partenaireId)
            ->getQuery()
            ->getResult();
    }
}