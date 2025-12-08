<?php

namespace App\Repository;

use App\Entity\CollaborateurEducation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CollaborateurEducation>
 *
 * @method CollaborateurEducation|null find($id, $lockMode = null, $lockVersion = null)
 * @method CollaborateurEducation|null findOneBy(array $criteria, array $orderBy = null)
 * @method CollaborateurEducation[]    findAll()
 * @method CollaborateurEducation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CollaborateurEducationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CollaborateurEducation::class);
    }
}