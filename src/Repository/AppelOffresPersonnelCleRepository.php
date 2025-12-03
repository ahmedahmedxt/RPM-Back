<?php

namespace App\Repository;

use App\Entity\AppelOffresPersonnelCle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AppelOffresPersonnelCleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppelOffresPersonnelCle::class);
    }
}