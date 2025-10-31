<?php

namespace App\Repository;

use App\Entity\AppelOffres;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AppelOffres>
 *
 * @method AppelOffres|null find($id, $lockMode = null, $lockVersion = null)
 * @method AppelOffres|null findOneBy(array $criteria, array $orderBy = null)
 * @method AppelOffres[]    findAll()
 * @method AppelOffres[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppelOffresRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppelOffres::class);
    }

    public function add(AppelOffres $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(AppelOffres $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * Récupère les appels d'offres par pays (tri par date de remise).
     */
    public function findByPays(int $paysId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.appelOffresPaysId = :paysId')
            ->setParameter('paysId', $paysId)
            ->orderBy('a.appelOffreDateRemise', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre total d'appels d'offres.
     */
    public function countAllAppelOffres(): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.appelOffresId)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Génère la prochaine référence numérique d'appel d'offres (01, 02, 03, ...).
     * Utilise la colonne "appelOffreDevis" existante.
     */
    public function generateNextAppelOffreRef(): string
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = "
            SELECT MAX(CAST(appelOffreDevis AS UNSIGNED)) AS max_num
            FROM appel_offres
            WHERE appelOffreDevis REGEXP '^[0-9]+$'
        ";

        $result = $conn->executeQuery($sql)->fetchAssociative();
        $maxNumber = $result['max_num'] ?? 0;

        $nextNumber = (int)$maxNumber + 1;
        return str_pad((string)$nextNumber, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Génère le numéro de devis avec numérotation globale (YYYY-MM-DD-XX).
     * XX est un compteur global des participations.
     */
    public function generateNumeroDevis(string $dateParticipation): string
    {
        $date = new \DateTime($dateParticipation);
        $dateStr = $date->format('Y-m-d');

        $results = $this->createQueryBuilder('a')
            ->select('a.appelOffresNumeroDevisParticipation AS num')
            ->where('a.appelOffresParticipation = 1')
            ->andWhere('a.appelOffresNumeroDevisParticipation IS NOT NULL')
            ->getQuery()
            ->getScalarResult();

        $maxNumber = 0;
        foreach ($results as $row) {
            $numero = $row['num'] ?? null;
            if ($numero) {
                $parts = explode('-', $numero); // attendu: YYYY-MM-DD-XX
                if (count($parts) >= 4) {
                    $last = (int) end($parts);
                    if ($last > $maxNumber) {
                        $maxNumber = $last;
                    }
                }
            }
        }

        $next = $maxNumber + 1;
        return $dateStr . '-' . str_pad((string)$next, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Vérifie si une référence "appelOffreDevis" existe déjà.
     */
    public function appelOffreRefExists(string $reference): bool
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.appelOffresId)')
            ->where('a.appelOffreDevis = :ref')
            ->setParameter('ref', $reference)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }
}