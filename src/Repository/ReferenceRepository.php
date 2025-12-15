<?php

namespace App\Repository;

use App\Entity\Categorie;
use App\Entity\Reference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\LockMode;

class ReferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reference::class);
    }

    public function countAllReferences(): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.referenceID)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countByCategorie(Categorie $categorie): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.referenceID)')
            ->andWhere('r.categorie = :cat')
            ->setParameter('cat', $categorie)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getMaxOrdreForCategorie(Categorie $categorie): int
    {
        $max = $this->createQueryBuilder('r')
            ->select('COALESCE(MAX(r.referenceOrdre), 0)')
            ->andWhere('r.categorie = :cat')
            ->setParameter('cat', $categorie)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $max;
    }

    public function getNextOrdreForCategorie(Categorie $categorie): int
    {
        return $this->getMaxOrdreForCategorie($categorie) + 1;
    }

    /**
     * @return Reference[]
     */
    public function findByCategorieOrdered(Categorie $categorie): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.categorie = :cat')
            ->setParameter('cat', $categorie)
            ->orderBy('r.referenceOrdre', 'ASC')
            ->addOrderBy('r.referenceID', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function referenceRefExists(string $referenceRef, ?int $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.referenceID)')
            ->andWhere('r.referenceRef = :ref')
            ->setParameter('ref', $referenceRef);

        if ($excludeId) {
            $qb->andWhere('r.referenceID != :id')->setParameter('id', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function findOneByCategorieAndOrdre(Categorie $categorie, int $ordre): ?Reference
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.categorie = :cat')
            ->andWhere('r.referenceOrdre = :ordre')
            ->setParameter('cat', $categorie)
            ->setParameter('ordre', $ordre)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function swapOrdreSameCategorie(int $refIdA, int $refIdB): void
    {
        $em = $this->getEntityManager();

        /** @var Reference|null $a */
        $a = $em->find(Reference::class, $refIdA, LockMode::PESSIMISTIC_WRITE);
        /** @var Reference|null $b */
        $b = $em->find(Reference::class, $refIdB, LockMode::PESSIMISTIC_WRITE);

        if (!$a || !$b) {
            throw new \RuntimeException('Reference not found for swap.');
        }

        if (!$a->getCategorie() || !$b->getCategorie()) {
            throw new \RuntimeException('Category missing on reference.');
        }

        if ($a->getCategorie()->getCategorieId() !== $b->getCategorie()->getCategorieId()) {
            throw new \RuntimeException('Swap allowed only inside same category.');
        }

        $ordreA = (int) $a->getReferenceOrdre();
        $ordreB = (int) $b->getReferenceOrdre();

        $a->setReferenceOrdre($ordreB);
        $b->setReferenceOrdre($ordreA);

        $short = $a->getCategorie()->getCategorieShort();
        $a->rebuildReferenceRefFromCategorieShort($short);
        $b->rebuildReferenceRefFromCategorieShort($short);

        $em->persist($a);
        $em->persist($b);
    }

    public function moveOrdreSameCategorie(int $referenceId, int $newOrdre): void
    {
        $em = $this->getEntityManager();

        /** @var Reference|null $ref */
        $ref = $em->find(Reference::class, $referenceId, LockMode::PESSIMISTIC_WRITE);
        if (!$ref) {
            throw new \RuntimeException('Reference not found.');
        }

        $cat = $ref->getCategorie();
        if (!$cat) {
            throw new \RuntimeException('Category missing on reference.');
        }

        $currentOrdre = (int) $ref->getReferenceOrdre();
        if ($newOrdre < 1) {
            $newOrdre = 1;
        }

        $max = $this->getMaxOrdreForCategorie($cat);
        if ($newOrdre > $max) {
            $newOrdre = $max;
        }

        if ($newOrdre === $currentOrdre) {
            return;
        }

        $refs = $this->createQueryBuilder('r')
            ->andWhere('r.categorie = :cat')
            ->setParameter('cat', $cat)
            ->orderBy('r.referenceOrdre', 'ASC')
            ->getQuery()
            ->getResult();

        foreach ($refs as $r) {
            $em->lock($r, LockMode::PESSIMISTIC_WRITE);
        }

        if ($newOrdre < $currentOrdre) {
            foreach ($refs as $r) {
                if ($r->getReferenceID() === $ref->getReferenceID()) continue;
                $o = (int) $r->getReferenceOrdre();
                if ($o >= $newOrdre && $o < $currentOrdre) {
                    $r->setReferenceOrdre($o + 1);
                }
            }
        } else {
            foreach ($refs as $r) {
                if ($r->getReferenceID() === $ref->getReferenceID()) continue;
                $o = (int) $r->getReferenceOrdre();
                if ($o <= $newOrdre && $o > $currentOrdre) {
                    $r->setReferenceOrdre($o - 1);
                }
            }
        }

        $ref->setReferenceOrdre($newOrdre);

        $short = $cat->getCategorieShort();
        foreach ($refs as $r) {
            $r->rebuildReferenceRefFromCategorieShort($short);
            $em->persist($r);
        }

        $em->persist($ref);
    }
}
