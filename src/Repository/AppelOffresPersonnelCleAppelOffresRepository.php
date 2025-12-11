<?php

namespace App\Repository;

use App\Entity\AppelOffresPersonnelCleAppelOffres;
use App\Entity\Collaborateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AppelOffresPersonnelCleAppelOffresRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppelOffresPersonnelCleAppelOffres::class);
    }

    public function findByAppelOffresOrdered(int $appelOffresId)
    {
        return $this->createQueryBuilder('l')
            ->where('l.appelOffres = :appelOffresId')
            ->setParameter('appelOffresId', $appelOffresId)
            ->orderBy('l.ordreAffichage', 'ASC')
            ->addOrderBy('l.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countCollaborateurs(int $appelOffresId, int $appelOffresPersonnelCleId): int
    {
        $em = $this->getEntityManager();
        
        return (int) $em->createQueryBuilder()
            ->select('COUNT(c.collaborateurId)')
            ->from(Collaborateur::class, 'c')
            ->where('c.appelOffresPersonnelCle = :pcId')
            ->setParameter('pcId', $appelOffresPersonnelCleId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}