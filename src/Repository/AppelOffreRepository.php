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

    /**
     * Compte le nombre total d'appels d'offre
     */
    public function countAllAppelOffres(): int
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Génère la prochaine référence automatiquement (01, 02, 03, etc.)
     * ✅ CORRIGÉ: Format numérique simple sans préfixe "AO"
     */
    public function generateNextAppelOffreRef(): string
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = "
            SELECT MAX(CAST(appelOffreDevis AS UNSIGNED)) as max_num
            FROM appel_offre
            WHERE appelOffreDevis REGEXP '^[0-9]+$'
        ";
        
        $result = $conn->executeQuery($sql)->fetchAssociative();
        $maxNumber = $result['max_num'] ?? 0;
        
        $nextNumber = $maxNumber + 1;
        return str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Génère le numéro de devis avec numérotation globale
     * Format: YYYY-MM-DD-XX où XX est un numéro global indépendant de la date
     * ✅ CORRIGÉ: Numérotation globale pour tous les appels d'offre avec participation = Oui
     */
    public function generateNumeroDevis(string $dateParticipation): string
    {
        $date = new \DateTime($dateParticipation);
        $dateStr = $date->format('Y-m-d');
        
        // Récupérer TOUS les numéros de devis existants (toutes dates confondues)
        $results = $this->createQueryBuilder('a')
            ->select('a.numeroDevisParticipation')
            ->where('a.appelOffreParticipation = 1')
            ->andWhere('a.numeroDevisParticipation IS NOT NULL')
            ->getQuery()
            ->getResult();
        
        // Extraire tous les numéros et trouver le maximum global
        $maxNumber = 0;
        foreach ($results as $result) {
            $numero = $result['numeroDevisParticipation'];
            if ($numero) {
                // Extraire le dernier chiffre après le dernier '-'
                // Format attendu: YYYY-MM-DD-XX
                $parts = explode('-', $numero);
                if (count($parts) >= 4) {
                    $lastPart = end($parts);
                    $number = (int)$lastPart;
                    if ($number > $maxNumber) {
                        $maxNumber = $number;
                    }
                }
            }
        }
        
        // Générer le prochain numéro global
        $nextNumber = $maxNumber + 1;
        return $dateStr . '-' . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Vérifie si une référence existe déjà
     */
    public function appelOffreRefExists(string $reference): bool
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.appelOffreDevis = :ref')
            ->setParameter('ref', $reference)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }
}