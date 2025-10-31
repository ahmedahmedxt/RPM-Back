<?php

namespace App\Repository;

use App\Entity\AppelOffresType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AppelOffresType>
 *
 * @method AppelOffresType|null find($id, $lockMode = null, $lockVersion = null)
 * @method AppelOffresType|null findOneBy(array $criteria, array $orderBy = null)
 * @method AppelOffresType[]    findAll()
 * @method AppelOffresType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppelOffresTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppelOffresType::class);
    }

    public function add(AppelOffresType $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AppelOffresType $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}