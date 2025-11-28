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
            ->orderBy('a.appelOffresNumero', 'ASC')
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
            SELECT MAX(CAST(appelOffresNumero AS UNSIGNED)) AS max_num
            FROM appel_offres
            WHERE appelOffresNumero REGEXP '^[0-9]+$'
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
            ->where('a.appelOffresNumero = :ref')
            ->setParameter('ref', $reference)
            ->getQuery()
            ->getSingleScalarResult() > 0;
    }

    // ========================================
    // ✨ MÉTHODES STATISTIQUES
    // ========================================

    /**
     * 🏆 Compte les AO perdus (tous sauf GAGNE et EN_ATTENTE)
     */
    public function countPerdus(): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.appelOffresId)')
            ->where('a.appelOffresParticipation = 1')
            ->andWhere('a.appelOffresResultatEtat NOT IN (:etats)')
            ->setParameter('etats', ['GAGNE', 'EN_ATTENTE', null])
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * 📊 Statistiques par état (Gagné, Perdu, En attente, etc.)
     */
    public function getStatistiquesParEtat(): array
    {
        $results = $this->createQueryBuilder('a')
            ->select('a.appelOffresResultatEtat as etat, COUNT(a.appelOffresId) as total')
            ->where('a.appelOffresResultatEtat IS NOT NULL')
            ->groupBy('a.appelOffresResultatEtat')
            ->getQuery()
            ->getResult();

        $formatted = [];
        foreach ($results as $row) {
            $formatted[] = [
                'label' => $this->formatEtatLabel($row['etat']),
                'value' => (int) $row['total'],
                'etat' => $row['etat']
            ];
        }

        return $formatted;
    }

    /**
     * 🌍 Statistiques par pays (Top 10)
     */
    public function getStatistiquesParPays(): array
    {
        $results = $this->createQueryBuilder('a')
            ->select('p.paysLibelle as pays, COUNT(a.appelOffresId) as total')
            ->leftJoin('a.appelOffresPaysId', 'p')
            ->where('p.paysLibelle IS NOT NULL')
            ->groupBy('p.paysId')
            ->orderBy('total', 'DESC')
            ->setMaxResults(10) // Top 10 pays
            ->getQuery()
            ->getResult();

        $formatted = [];
        foreach ($results as $row) {
            $formatted[] = [
                'label' => $row['pays'],
                'value' => (int) $row['total']
            ];
        }

        return $formatted;
    }

    /**
     * 📅 Statistiques par mois pour une année donnée
     */
    public function getStatistiquesParMois(int $annee): array
    {
        $results = $this->createQueryBuilder('a')
            ->select('MONTH(a.appelOffresDateLimiteRemise) as mois, COUNT(a.appelOffresId) as total')
            ->where('YEAR(a.appelOffresDateLimiteRemise) = :annee')
            ->setParameter('annee', $annee)
            ->groupBy('mois')
            ->orderBy('mois', 'ASC')
            ->getQuery()
            ->getResult();

        // Créer un tableau avec tous les mois (1-12) initialisés à 0
        $moisNoms = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];

        $formatted = [];
        for ($i = 1; $i <= 12; $i++) {
            $total = 0;
            foreach ($results as $row) {
                if ((int) $row['mois'] === $i) {
                    $total = (int) $row['total'];
                    break;
                }
            }
            $formatted[] = [
                'label' => $moisNoms[$i],
                'value' => $total,
                'mois' => $i
            ];
        }

        return $formatted;
    }

    /**
     * 👥 Statistiques par participation (Participé / Non participé)
     */
    public function getStatistiquesParParticipation(): array
    {
        $participes = $this->count(['appelOffresParticipation' => 1]);
        $nonParticipes = $this->count(['appelOffresParticipation' => 0]);

        return [
            ['label' => 'Participé', 'value' => $participes],
            ['label' => 'Non Participé', 'value' => $nonParticipes]
        ];
    }

    /**
     * 📈 Statistiques par année
     */
    public function getStatistiquesParAnnee(): array
    {
        $results = $this->createQueryBuilder('a')
            ->select('a.appelOffresAnnee as annee, COUNT(a.appelOffresId) as total')
            ->where('a.appelOffresAnnee IS NOT NULL')
            ->groupBy('a.appelOffresAnnee')
            ->orderBy('annee', 'DESC')
            ->getQuery()
            ->getResult();

        $formatted = [];
        foreach ($results as $row) {
            $formatted[] = [
                'label' => (string) $row['annee'],
                'value' => (int) $row['total']
            ];
        }

        return $formatted;
    }

    /**
     * 🎯 Calcul du taux de succès (gagné / participé)
     */
    public function getTauxSucces(): array
    {
        $participes = $this->count(['appelOffresParticipation' => 1]);
        $gagnes = $this->count(['appelOffresResultatEtat' => 'GAGNE']);
        
        $tauxSucces = $participes > 0 ? round(($gagnes / $participes) * 100, 2) : 0;

        return [
            'participes' => $participes,
            'gagnes' => $gagnes,
            'perdus' => $this->countPerdus(),
            'tauxSucces' => $tauxSucces
        ];
    }

    /**
     * 🏢 Statistiques par organisme demandeur (Top 10)
     */
    public function getStatistiquesParOrganisme(): array
    {
        $results = $this->createQueryBuilder('a')
            ->select('o.organismeDemandeurLibelle as organisme, COUNT(a.appelOffresId) as total')
            ->leftJoin('a.appelOffresOrganismeDemandeurId', 'o')
            ->where('o.organismeDemandeurLibelle IS NOT NULL')
            ->groupBy('o.organismeDemandeurId')
            ->orderBy('total', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $formatted = [];
        foreach ($results as $row) {
            $formatted[] = [
                'label' => $row['organisme'],
                'value' => (int) $row['total']
            ];
        }

        return $formatted;
    }

    /**
     * 📋 Statistiques par type d'appel d'offres
     */
    public function getStatistiquesParType(): array
    {
        $results = $this->createQueryBuilder('a')
            ->select('t.appelOffresTypeLibelle as type, COUNT(a.appelOffresId) as total')
            ->leftJoin('a.appelOffresTypeId', 't')
            ->where('t.appelOffresTypeLibelle IS NOT NULL')
            ->groupBy('t.appelOffresTypeId')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getResult();

        $formatted = [];
        foreach ($results as $row) {
            $formatted[] = [
                'label' => $row['type'],
                'value' => (int) $row['total']
            ];
        }

        return $formatted;
    }

    /**
     * 🔧 Formater le label d'état pour l'affichage
     */
    private function formatEtatLabel(?string $etat): string
    {
        if (!$etat) {
            return 'Non défini';
        }

        return match($etat) {
            'EN_ATTENTE' => 'En Attente',
            'GAGNE' => 'Gagné',
            'ANNULE' => 'Annulé',
            'REPORTE' => 'Reporté',
            default => ucfirst(strtolower($etat))
        };
    }
}