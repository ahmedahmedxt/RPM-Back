<?php

namespace App\Repository;

use App\Entity\AppelOffresPersonnelCleAppelOffres;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AppelOffresPersonnelCleAppelOffresRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppelOffresPersonnelCleAppelOffres::class);
    }
}