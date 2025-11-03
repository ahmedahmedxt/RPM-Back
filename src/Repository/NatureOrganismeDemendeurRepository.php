<?php

namespace App\Repository;

use App\Entity\NatureOrganismeDemendeur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NatureOrganismeDemendeur>
 *
 * You can add custom query methods here.
 */
class NatureOrganismeDemendeurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NatureOrganismeDemendeur::class);
    }

    // public function findByLibelle(string $libelle): ?NatureOrganismeDemendeur
    // {
    //     return $this->findOneBy(['natureOrganismeDemendeurLibelle' => $libelle]);
    // }
}
