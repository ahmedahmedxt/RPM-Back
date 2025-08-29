<?php

namespace App\Repository;

use App\Entity\AppelOffre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AppelOffre>
 *
 * @method AppelOffre|null find($id, $lockMode = null, $lockVersion = null)
 * @method AppelOffre|null findOneBy(array $criteria, array $orderBy = null)
 * @method AppelOffre[]    findAll()
 * @method AppelOffre[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppelOffreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppelOffre::class);
    }

    public function add(AppelOffre $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(AppelOffre $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Exemple de méthode personnalisée :
     * Récupère tous les appels d'offre actifs par pays
     */
    public function findByPaysActive(int $paysId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.pays = :paysId')
            ->andWhere('a.appelOffreEtat = 1')
            ->setParameter('paysId', $paysId)
            ->orderBy('a.appelOffreDateRemise', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
