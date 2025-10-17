<?php

namespace App\Repository\Template;

use App\Entity\Template\ProjectTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProjectTemplateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProjectTemplate::class);
    }

    public function findSystemTemplates(): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.isSystem = :isSystem')
            ->setParameter('isSystem', true)
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findUserTemplates(): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.isSystem = :isSystem')
            ->setParameter('isSystem', false)
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByName(string $name): ?ProjectTemplate
    {
        return $this->createQueryBuilder('t')
            ->where('t.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActiveTemplates(): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}