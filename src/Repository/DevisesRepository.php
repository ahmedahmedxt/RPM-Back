<?php

namespace App\Repository;

use App\Entity\Devises;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Devises>
 *
 * @method Devises|null find($id, $lockMode = null, $lockVersion = null)
 * @method Devises|null findOneBy(array $criteria, array $orderBy = null)
 * @method Devises[]    findAll()
 * @method Devises[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DevisesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Devises::class);
    }

    public function add(Devises $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Devises $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}