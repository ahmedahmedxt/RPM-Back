<?php

namespace App\Repository;

use App\Entity\ReferenceCaracteristiqueSpeciale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ReferenceCaracteristiqueSpecialeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReferenceCaracteristiqueSpeciale::class);
    }
}