<?php

namespace App\Repository;

use App\Entity\NiveauEtude;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NiveauEtude>
 *
 * @method NiveauEtude|null find($id, $lockMode = null, $lockVersion = null)
 * @method NiveauEtude|null findOneBy(array $criteria, array $orderBy = null)
 * @method NiveauEtude[]    findAll()
 * @method NiveauEtude[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NiveauEtudeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NiveauEtude::class);
    }
}