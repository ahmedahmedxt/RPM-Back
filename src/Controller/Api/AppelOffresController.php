<?php

namespace App\Controller\Api;

use App\Entity\AppelOffres;
use App\Entity\AppelOffresType;
use App\Entity\MoyenLivraison;
use App\Entity\Pays;
use App\Entity\Devises;
use App\Entity\OrganismeDemandeur;
use App\Entity\ParticipationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppelOffresController extends AbstractController
{
    #[Route('/api/getAll/appelOffres', name: 'api_appel_offres_index', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $em): JsonResponse
    {
        try {
            $page = max(1, (int)$request->query->get('page', 1));
            $limit = max(1, min(100, (int)$request->query->get('limit', 10)));
            $sortField = $request->query->get('sortField', 'appelOffresAnnee');
            $sortDir = strtoupper($request->query->get('sortDir', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
            $search = $request->query->get('search', '');
            $etatFilter = $request->query->get('etat', '');

            $allowedSortFields = [
                'appelOffresId' => 'a.appelOffresId',
                'appelOffresNumero' => 'a.appelOffresNumero',
                'appelOffresObjet' => 'a.appelOffresObjet',
                'appelOffresDateLimiteRemise' => 'a.appelOffresDateLimiteRemise',
                'appelOffresAnnee' => 'a.appelOffresAnnee',
                'appelOffresResultatEtat' => 'a.appelOffresResultatEtat',
            ];

            if (!array_key_exists($sortField, $allowedSortFields)) {
                $sortField = 'appelOffresAnnee';
            }

            $qb = $em->getRepository(AppelOffres::class)->createQueryBuilder('a');

            if (!empty($search)) {
                $qb->andWhere('a.appelOffresObjet LIKE :search OR a.appelOffresNumero LIKE :search')
                   ->setParameter('search', '%' . $search . '%');
            }

            if (!empty($etatFilter)) {
                $qb->andWhere('a.appelOffresResultatEtat = :etat')
                   ->setParameter('etat', $etatFilter);
            }

            $qbCount = clone $qb;
            $total = (int)$qbCount->select('COUNT(a.appelOffresId)')->getQuery()->getSingleScalarResult();

            if ($sortField === 'appelOffresAnnee') {
                $qb->orderBy('a.appelOffresAnnee', 'DESC')
                   ->addOrderBy('a.appelOffresNumero', 'DESC');
            } else {
                $qb->orderBy($allowedSortFields[$sortField], $sortDir);
            }

            $qb->setFirstResult(($page - 1) * $limit)
               ->setMaxResults($limit);

            $appels = $qb->getQuery()->getResult();
            $data = [];

            if ($sortField === 'appelOffresAnnee') {
                usort($appels, function($a, $b) {
                    $anneeA = $a->getAppelOffresAnnee() ?? 0;
                    $anneeB = $b->getAppelOffresAnnee() ?? 0;

                    if ($anneeA !== $anneeB) {
                        return $anneeB <=> $anneeA;
                    }

                    $numeroA = (int)($a->getAppelOffresNumero() ?? 0);
                    $numeroB = (int)($b->getAppelOffresNumero() ?? 0);

                    return $numeroB <=> $numeroA;
                });

                $appels = array_slice($appels, ($page - 1) * $limit, $limit);
            }

            foreach ($appels as $appel) {
                $data[] = $this->serializeAppelOffres($appel);
            }

            return new JsonResponse([
                'data' => $data,
                'total' => $total,
                'page' => $page,
                'limit' => $limit
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            error_log('Erreur dans index: ' . $e->getMessage() . ' - Trace: ' . $e->getTraceAsString());
            return new JsonResponse([
                'error' => 'Erreur lors de la récupération des appels d\'offres',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/get/appelOffres/{id}', name: 'api_appel_offres_show', methods: ['GET'])]
    public function show(AppelOffres $appelOffres): JsonResponse
    {
        try {
            return new JsonResponse($this->serializeAppelOffres($appelOffres), Response::HTTP_OK);
        } catch (\Exception $e) {
            error_log('Erreur dans show: ' . $e->getMessage());
            return new JsonResponse([
                'error' => 'Erreur lors de la récupération de l\'appel d\'offres',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/create/appelOffres', name: 'api_appel_offres_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true) ?? [];
    
            if (!isset($data['appelOffresObjet'])) {
                return new JsonResponse(['error' => 'appelOffresObjet est requis'], Response::HTTP_BAD_REQUEST);
            }
    
            $appel = new AppelOffres();
            $connection = $em->getConnection();
    
            $annee = (int)date('Y');
    
            $sql = 'SELECT MAX(CAST(appelOffresNumero AS UNSIGNED)) as maxNumero 
                    FROM appel_offres 
                    WHERE appelOffresAnnee = :annee 
                    AND appelOffresNumero IS NOT NULL 
                    AND appelOffresNumero != ""';
    
            $stmt = $connection->prepare($sql);
            $result = $stmt->executeQuery(['annee' => $annee]);
            $maxNumero = $result->fetchOne();
    
            $nouveauNumero = ($maxNumero ? (int)$maxNumero : 0) + 1;
            $nouveauNumeroFormate = str_pad((string)$nouveauNumero, 3, '0', STR_PAD_LEFT);
    
            $sqlCheck = 'SELECT COUNT(*) as count 
                         FROM appel_offres 
                         WHERE appelOffresAnnee = :annee 
                         AND appelOffresNumero = :numero';
            $stmtCheck = $connection->prepare($sqlCheck);
            $resultCheck = $stmtCheck->executeQuery(['annee' => $annee, 'numero' => $nouveauNumeroFormate]);
            $count = $resultCheck->fetchOne();
    
            while ($count > 0) {
                $nouveauNumero++;
                $nouveauNumeroFormate = str_pad((string)$nouveauNumero, 3, '0', STR_PAD_LEFT);
                $resultCheck = $stmtCheck->executeQuery(['annee' => $annee, 'numero' => $nouveauNumeroFormate]);
                $count = $resultCheck->fetchOne();
            }
    
            $appel->setAppelOffresNumero($nouveauNumeroFormate);
            $appel->setAppelOffresAnnee($annee);
    
            $appel->setAppelOffresObjet($data['appelOffresObjet'] ?? null);
            $appel->setAppelOffresCCRetire(isset($data['appelOffresCCRetire']) ? (int)$data['appelOffresCCRetire'] : null);
            $appel->setAppelOffresLienAnnonce($data['appelOffresLienAnnonce'] ?? null);
            $appel->setAppelOffresCautionBancaire(isset($data['appelOffresCautionBancaire']) ? (int)$data['appelOffresCautionBancaire'] : null);
    
            $appel->setAppelOffresRemarque($data['appelOffresRemarque'] ?? null);
    
         
            $appel->setAppelOffresDateLimiteRemise(!empty($data['appelOffresDateLimiteRemise']) ? new \DateTime($data['appelOffresDateLimiteRemise']) : null);
            $appel->setAppelOffresHeureLimiteRemise(!empty($data['appelOffresHeureLimiteRemise']) ? new \DateTime($data['appelOffresHeureLimiteRemise']) : null);
    
            if (!empty($data['appelOffresTypeId'])) {
                $type = $em->getRepository(AppelOffresType::class)->find($data['appelOffresTypeId']);
                if ($type) { $appel->setAppelOffresTypeId($type); }
            }
            if (!empty($data['appelOffresMoyenLivraisonId'])) {
                $moyen = $em->getRepository(MoyenLivraison::class)->find($data['appelOffresMoyenLivraisonId']);
                if ($moyen) { $appel->setAppelOffresMoyenLivraisonId($moyen); }
            }
            if (!empty($data['appelOffresPaysId'])) {
                $pays = $em->getRepository(Pays::class)->find($data['appelOffresPaysId']);
                if ($pays) { $appel->setAppelOffresPaysId($pays); }
            }
            if (!empty($data['appelOffresOrganismeDemandeurId'])) {
                $org = $em->getRepository(OrganismeDemandeur::class)->find($data['appelOffresOrganismeDemandeurId']);
                if ($org) { $appel->setAppelOffresOrganismeDemandeurId($org); }
            }
            if (!empty($data['appelOffresDevisesId'])) {
                $dev = $em->getRepository(Devises::class)->find($data['appelOffresDevisesId']);
                if ($dev) { $appel->setAppelOffresDevisesId($dev); }
            }
            if (!empty($data['appelOffresCautionBancaireDeviseId'])) {
                $cautionDev = $em->getRepository(Devises::class)->find($data['appelOffresCautionBancaireDeviseId']);
                if ($cautionDev) { $appel->setAppelOffresCautionBancaireDeviseId($cautionDev); }
            }
    
            $em->persist($appel);
            $em->flush();
    
            return new JsonResponse([
                'message' => 'AppelOffres créé avec succès',
                'id' => $appel->getAppelOffresId(),
                'numero' => $appel->getAppelOffresNumero(),
                'devisParticipation' => $appel->getAppelOffresNumeroDevisParticipation(),
                'data' => $this->serializeAppelOffres($appel),
            ], Response::HTTP_CREATED);
    
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la création',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    } 



   #[Route('/api/update/appelOffres/{id}', name: 'api_appel_offres_update', methods: ['PUT'])]
public function update(Request $request, AppelOffres $appel, EntityManagerInterface $em): JsonResponse
{
    try {
        $data = json_decode($request->getContent(), true) ?? [];

        if (array_key_exists('appelOffresObjet', $data)) $appel->setAppelOffresObjet($data['appelOffresObjet']);

        if (array_key_exists('appelOffresAnnee', $data)) {
            $nouvelleAnnee = $data['appelOffresAnnee'];
            $appel->setAppelOffresAnnee($nouvelleAnnee);

            if (array_key_exists('appelOffresNumero', $data) && !empty($data['appelOffresNumero'])) {
                $numeroPropose = is_numeric($data['appelOffresNumero']) ? (int)$data['appelOffresNumero'] : (int)trim($data['appelOffresNumero'], '0');
                $numeroProposeFormate = str_pad((string)$numeroPropose, 3, '0', STR_PAD_LEFT);

                $connection = $em->getConnection();
                $sqlCheck = 'SELECT COUNT(*) as count 
                             FROM appel_offres 
                             WHERE appelOffresAnnee = :annee 
                             AND appelOffresNumero = :numero
                             AND appelOffresId != :currentId';
                $stmtCheck = $connection->prepare($sqlCheck);
                $resultCheck = $stmtCheck->executeQuery([
                    'annee' => $nouvelleAnnee,
                    'numero' => $numeroProposeFormate,
                    'currentId' => $appel->getAppelOffresId()
                ]);
                $count = $resultCheck->fetchOne();

                if ($count > 0) {
                    $sql = 'SELECT MAX(CAST(appelOffresNumero AS UNSIGNED)) as maxNumero 
                            FROM appel_offres 
                            WHERE appelOffresAnnee = :annee 
                            AND appelOffresNumero IS NOT NULL 
                            AND appelOffresNumero != ""
                            AND appelOffresId != :currentId';
                    $stmt = $connection->prepare($sql);
                    $result = $stmt->executeQuery([
                        'annee' => $nouvelleAnnee,
                        'currentId' => $appel->getAppelOffresId()
                    ]);
                    $maxNumero = $result->fetchOne();
                    $nouveauNumero = ($maxNumero ? (int)$maxNumero : 0) + 1;
                    $nouveauNumeroFormate = str_pad((string)$nouveauNumero, 3, '0', STR_PAD_LEFT);

                    $resultCheck = $stmtCheck->executeQuery([
                        'annee' => $nouvelleAnnee,
                        'numero' => $nouveauNumeroFormate,
                        'currentId' => $appel->getAppelOffresId()
                    ]);
                    $count = $resultCheck->fetchOne();

                    while ($count > 0) {
                        $nouveauNumero++;
                        $nouveauNumeroFormate = str_pad((string)$nouveauNumero, 3, '0', STR_PAD_LEFT);
                        $resultCheck = $stmtCheck->executeQuery([
                            'annee' => $nouvelleAnnee,
                            'numero' => $nouveauNumeroFormate,
                            'currentId' => $appel->getAppelOffresId()
                        ]);
                        $count = $resultCheck->fetchOne();
                    }

                    $appel->setAppelOffresNumero($nouveauNumeroFormate);
                } else {
                    $appel->setAppelOffresNumero($numeroProposeFormate);
                }
            } else {
                $connection = $em->getConnection();
                $sql = 'SELECT MAX(CAST(appelOffresNumero AS UNSIGNED)) as maxNumero 
                        FROM appel_offres 
                        WHERE appelOffresAnnee = :annee 
                        AND appelOffresNumero IS NOT NULL 
                        AND appelOffresNumero != ""
                        AND appelOffresId != :currentId';
                $stmt = $connection->prepare($sql);
                $result = $stmt->executeQuery([
                    'annee' => $nouvelleAnnee,
                    'currentId' => $appel->getAppelOffresId()
                ]);
                $maxNumero = $result->fetchOne();
                $nouveauNumero = ($maxNumero ? (int)$maxNumero : 0) + 1;
                $nouveauNumeroFormate = str_pad((string)$nouveauNumero, 3, '0', STR_PAD_LEFT);

                $sqlCheck = 'SELECT COUNT(*) as count 
                             FROM appel_offres 
                             WHERE appelOffresAnnee = :annee 
                             AND appelOffresNumero = :numero
                             AND appelOffresId != :currentId';
                $stmtCheck = $connection->prepare($sqlCheck);
                $resultCheck = $stmtCheck->executeQuery([
                    'annee' => $nouvelleAnnee,
                    'numero' => $nouveauNumeroFormate,
                    'currentId' => $appel->getAppelOffresId()
                ]);
                $count = $resultCheck->fetchOne();

                while ($count > 0) {
                    $nouveauNumero++;
                    $nouveauNumeroFormate = str_pad((string)$nouveauNumero, 3, '0', STR_PAD_LEFT);
                    $resultCheck = $stmtCheck->executeQuery([
                        'annee' => $nouvelleAnnee,
                        'numero' => $nouveauNumeroFormate,
                        'currentId' => $appel->getAppelOffresId()
                    ]);
                    $count = $resultCheck->fetchOne();
                }

                $appel->setAppelOffresNumero($nouveauNumeroFormate);
            }
        } else {
            if (array_key_exists('appelOffresNumero', $data)) {
                $numeroValue = is_numeric($data['appelOffresNumero']) ? (int)$data['appelOffresNumero'] : (int)trim($data['appelOffresNumero'], '0');
                $numeroFormate = str_pad((string)$numeroValue, 3, '0', STR_PAD_LEFT);
                $appel->setAppelOffresNumero($numeroFormate);
            }
        }

        if (array_key_exists('appelOffresCCRetire', $data)) $appel->setAppelOffresCCRetire((int)$data['appelOffresCCRetire']);
        if (array_key_exists('appelOffresLienAnnonce', $data)) $appel->setAppelOffresLienAnnonce($data['appelOffresLienAnnonce']);
        if (array_key_exists('appelOffresCautionBancaire', $data)) $appel->setAppelOffresCautionBancaire((int)$data['appelOffresCautionBancaire']);
 
        if (array_key_exists('appelOffresRemarque', $data)) $appel->setAppelOffresRemarque($data['appelOffresRemarque']);

        if (array_key_exists('appelOffresDateLimiteRemise', $data)) {
            $appel->setAppelOffresDateLimiteRemise(!empty($data['appelOffresDateLimiteRemise']) ? new \DateTime($data['appelOffresDateLimiteRemise']) : null);
        }
        if (array_key_exists('appelOffresHeureLimiteRemise', $data)) {
            $appel->setAppelOffresHeureLimiteRemise(!empty($data['appelOffresHeureLimiteRemise']) ? new \DateTime($data['appelOffresHeureLimiteRemise']) : null);
        }

        if (array_key_exists('appelOffresTypeId', $data)) {
            $type = !empty($data['appelOffresTypeId']) ? $em->getRepository(AppelOffresType::class)->find($data['appelOffresTypeId']) : null;
            $appel->setAppelOffresTypeId($type);
        }
        if (array_key_exists('appelOffresMoyenLivraisonId', $data)) {
            $m = !empty($data['appelOffresMoyenLivraisonId']) ? $em->getRepository(MoyenLivraison::class)->find($data['appelOffresMoyenLivraisonId']) : null;
            $appel->setAppelOffresMoyenLivraisonId($m);
        }
        if (array_key_exists('appelOffresPaysId', $data)) {
            $p = !empty($data['appelOffresPaysId']) ? $em->getRepository(Pays::class)->find($data['appelOffresPaysId']) : null;
            $appel->setAppelOffresPaysId($p);
        }
        if (array_key_exists('appelOffresOrganismeDemandeurId', $data)) {
            $o = !empty($data['appelOffresOrganismeDemandeurId']) ? $em->getRepository(OrganismeDemandeur::class)->find($data['appelOffresOrganismeDemandeurId']) : null;
            $appel->setAppelOffresOrganismeDemandeurId($o);
        }
        if (array_key_exists('appelOffresDevisesId', $data)) {
            $d = !empty($data['appelOffresDevisesId']) ? $em->getRepository(Devises::class)->find($data['appelOffresDevisesId']) : null;
            $appel->setAppelOffresDevisesId($d);
        }
        if (array_key_exists('appelOffresCautionBancaireDeviseId', $data)) {
            $cautionDev = !empty($data['appelOffresCautionBancaireDeviseId']) ? $em->getRepository(Devises::class)->find($data['appelOffresCautionBancaireDeviseId']) : null;
            $appel->setAppelOffresCautionBancaireDeviseId($cautionDev);
        }

        $em->flush();

        return new JsonResponse([
            'message' => 'AppelOffres modifié avec succès',
            'data' => $this->serializeAppelOffres($appel)
        ], Response::HTTP_OK);

    } catch (\Exception $e) {
        return new JsonResponse([
            'error' => 'Erreur lors de la mise à jour',
            'message' => $e->getMessage()
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

    #[Route('/api/update/appelOffres/{id}/participation', name: 'api_appel_offres_update_participation', methods: ['PUT'])]
    public function updateParticipation(Request $request, AppelOffres $appel, EntityManagerInterface $em): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true) ?? [];

            $participation = isset($data['appelOffresParticipation']) ? (int)$data['appelOffresParticipation'] : 0;
            $appel->setAppelOffresParticipation($participation);

            if (array_key_exists('appelOffresTypeParticipationId', $data)) {
                $participationType = !empty($data['appelOffresTypeParticipationId'])
                    ? $em->getRepository(ParticipationType::class)->find($data['appelOffresTypeParticipationId'])
                    : null;
                $appel->setAppelOffresTypeParticipationId($participationType);
            }

            if (array_key_exists('appelOffresDateParticipation', $data)) {
                $dateParticipation = !empty($data['appelOffresDateParticipation'])
                    ? new \DateTime($data['appelOffresDateParticipation'])
                    : null;
                $appel->setAppelOffresDateParticipation($dateParticipation);
            } else {
                $dateParticipation = $appel->getAppelOffresDateParticipation();
            }

            if ($participation === 1 && $dateParticipation) {
                $anneeParticipation = (int)$dateParticipation->format('Y');
                $connection = $em->getConnection();

                if (empty($appel->getAppelOffresNumeroDevisParticipation())) {
                    $sqlParticipation = 'SELECT MAX(appelOffresNumeroDevisParticipation) as maxDevisParticipation 
                                         FROM appel_offres 
                                         WHERE YEAR(appelOffresDateParticipation) = :anneeParticipation 
                                         AND appelOffresNumeroDevisParticipation IS NOT NULL
                                         AND appelOffresId != :currentId';

                    $stmtParticipation = $connection->prepare($sqlParticipation);
                    $resultParticipation = $stmtParticipation->executeQuery([
                        'anneeParticipation' => $anneeParticipation,
                        'currentId' => $appel->getAppelOffresId()
                    ]);
                    $maxDevisParticipation = $resultParticipation->fetchOne();

                    $nouveauDevisParticipation = ($maxDevisParticipation ? (int)$maxDevisParticipation : 0) + 1;

                    $sqlCheckParticipation = 'SELECT COUNT(*) as count 
                                              FROM appel_offres 
                                              WHERE YEAR(appelOffresDateParticipation) = :anneeParticipation 
                                              AND appelOffresNumeroDevisParticipation = :devisParticipation
                                              AND appelOffresId != :currentId';
                    $stmtCheckParticipation = $connection->prepare($sqlCheckParticipation);
                    $resultCheckParticipation = $stmtCheckParticipation->executeQuery([
                        'anneeParticipation' => $anneeParticipation,
                        'devisParticipation' => $nouveauDevisParticipation,
                        'currentId' => $appel->getAppelOffresId()
                    ]);
                    $countParticipation = $resultCheckParticipation->fetchOne();

                    while ($countParticipation > 0) {
                        $nouveauDevisParticipation++;
                        $resultCheckParticipation = $stmtCheckParticipation->executeQuery([
                            'anneeParticipation' => $anneeParticipation,
                            'devisParticipation' => $nouveauDevisParticipation,
                            'currentId' => $appel->getAppelOffresId()
                        ]);
                        $countParticipation = $resultCheckParticipation->fetchOne();
                    }

                    $appel->setAppelOffresNumeroDevisParticipation($nouveauDevisParticipation);
                }
            } else {
                $appel->setAppelOffresNumeroDevisParticipation(null);
            }

            $em->flush();

            return new JsonResponse([
                'message' => 'Participation mise à jour avec succès',
                'data' => $this->serializeAppelOffres($appel)
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la mise à jour de la participation',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/update/appelOffres/{id}/resultat', name: 'api_appel_offres_update_resultat', methods: ['PUT'])]
    public function updateResultat(Request $request, AppelOffres $appel, EntityManagerInterface $em): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true) ?? [];

            if (array_key_exists('appelOffresResultatEtat', $data)) {
                $appel->setAppelOffresResultatEtat($data['appelOffresResultatEtat']);
            }
            if (array_key_exists('appelOffresResultatRang', $data)) {
                $appel->setAppelOffresResultatRang((int)$data['appelOffresResultatRang']);
            }
            if (array_key_exists('appelOffresResultatRangTotal', $data)) {
                $appel->setAppelOffresResultatRangTotal((int)$data['appelOffresResultatRangTotal']);
            }

            $em->flush();

            return new JsonResponse([
                'message' => 'Résultat mis à jour avec succès',
                'data' => $this->serializeAppelOffres($appel)
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la mise à jour du résultat',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/delete/appelOffres/{id}', name: 'api_appel_offres_delete', methods: ['DELETE'])]
    public function delete(AppelOffres $appel, EntityManagerInterface $em): JsonResponse
    {
        try {
            $em->remove($appel);
            $em->flush();

            return new JsonResponse(['message' => 'AppelOffres supprimé avec succès'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la suppression',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function serializeAppelOffres(AppelOffres $appel): array
    {
        try {
            $partenaires = [];
            try {
                if (method_exists($appel, 'getAppelOffresPartenaires')) {
                    $partenairesCollection = $appel->getAppelOffresPartenaires();
                    if ($partenairesCollection) {
                        foreach ($partenairesCollection as $aop) {
                            try {
                                $p = $aop->getPartenaire();
                                if ($p) {
                                    $partenaires[] = [
                                        'id' => $aop->getId(),
                                        'partenaireId' => $p->getPartenaireId(),
                                        'partenaireRaisonSociale' => $p->getPartenaireRaisonSociale() ?? '',
                                        'partenaireRaisonSocialeShort' => $p->getPartenaireRaisonSocialeShort() ?? '',
                                        'role' => $aop->getRole() ?? '',
                                    ];
                                }
                            } catch (\Exception $e) {
                                continue;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                error_log('Erreur lors de la récupération des partenaires: ' . $e->getMessage());
            }

            $type = null;
            $moyen = null;
            $pays = null;
            $organisme = null;
            $devise = null;
            $cautionDevise = null;
            $participationType = null;

            try {
                $type = $appel->getAppelOffresTypeId();
            } catch (\Exception $e) {
                error_log('Erreur getAppelOffresTypeId: ' . $e->getMessage());
            }

            try {
                $moyen = $appel->getAppelOffresMoyenLivraisonId();
            } catch (\Exception $e) {
                error_log('Erreur getAppelOffresMoyenLivraisonId: ' . $e->getMessage());
            }

            try {
                $pays = $appel->getAppelOffresPaysId();
            } catch (\Exception $e) {
                error_log('Erreur getAppelOffresPaysId: ' . $e->getMessage());
            }

            try {
                $organisme = $appel->getAppelOffresOrganismeDemandeurId();
            } catch (\Exception $e) {
                error_log('Erreur getAppelOffresOrganismeDemandeurId: ' . $e->getMessage());
            }

            try {
                $devise = $appel->getAppelOffresDevisesId();
            } catch (\Exception $e) {
                error_log('Erreur getAppelOffresDevisesId: ' . $e->getMessage());
            }

            try {
                $cautionDevise = $appel->getAppelOffresCautionBancaireDeviseId();
            } catch (\Exception $e) {
                error_log('Erreur getAppelOffresCautionBancaireDeviseId: ' . $e->getMessage());
            }

            try {
                $participationType = $appel->getAppelOffresTypeParticipationId();
            } catch (\Exception $e) {
                error_log('Erreur getAppelOffresTypeParticipationId: ' . $e->getMessage());
            }

            $organismeLibelle = null;
            $organismeAcronyme = null;
            if ($organisme) {
                try {
                    $organismeLibelle = $organisme->getOrganismeDemandeurRaisonSociale();
                } catch (\Exception $e) {
                    error_log('Erreur getOrganismeDemandeurRaisonSociale: ' . $e->getMessage());
                    try {
                        $organismeLibelle = $organisme->getOrganismeDemandeurRaisonSocialeShort();
                    } catch (\Exception $e2) {
                        error_log('Erreur getOrganismeDemandeurRaisonSocialeShort: ' . $e2->getMessage());
                    }
                }
                try {
                    $organismeAcronyme = $organisme->getOrganismeDemandeurRaisonSocialeShort();
                } catch (\Exception $e) {
                    error_log('Erreur getOrganismeDemandeurRaisonSocialeShort: ' . $e->getMessage());
                }
            }

            return [
                'appelOffresId' => $appel->getAppelOffresId(),
                'appelOffresNumero' => $appel->getAppelOffresNumero(),
                'appelOffresObjet' => $appel->getAppelOffresObjet(),
                'appelOffresDateLimiteRemise' => $appel->getAppelOffresDateLimiteRemise()?->format('Y-m-d'),
                'appelOffresHeureLimiteRemise' => $appel->getAppelOffresHeureLimiteRemise()?->format('H:i:s'),
                'appelOffresCCRetire' => $appel->getAppelOffresCCRetire(),
                'appelOffresLienAnnonce' => $appel->getAppelOffresLienAnnonce(),
                'appelOffresCautionBancaire' => $appel->getAppelOffresCautionBancaire(),
                'appelOffresTypeParticipationId' => $participationType?->getParticipationTypeId(),
                'appelOffresTypeParticipationLibelle' => $participationType?->getParticipationTypeLibelle(),
                'appelOffresRemarque' => $appel->getAppelOffresRemarque(),
                'appelOffresParticipation' => $appel->getAppelOffresParticipation(),
                'appelOffresDateParticipation' => $appel->getAppelOffresDateParticipation()?->format('Y-m-d'),
                'appelOffresResultatEtat' => $appel->getAppelOffresResultatEtat(),
                'appelOffresResultatRang' => $appel->getAppelOffresResultatRang(),
                'appelOffresResultatRangTotal' => $appel->getAppelOffresResultatRangTotal(),
                'appelOffresNumeroDevisParticipation' => $appel->getAppelOffresNumeroDevisParticipation(),
                'appelOffresAnnee' => $appel->getAppelOffresAnnee(),

                'appelOffresTypeId' => $type?->getAppelOffresTypeId(),
                'appelOffresMoyenLivraisonId' => $moyen?->getMoyenLivraisonId(),
                'appelOffresPaysId' => $pays?->getPaysId(),
                'appelOffresOrganismeDemandeurId' => $organisme?->getOrganismeDemandeurId(),
                'appelOffresDevisesId' => $devise?->getDevisesId(),
                'appelOffresCautionBancaireDeviseId' => $cautionDevise?->getDevisesId(),

                'appelOffreTypeLibelle' => $type?->getAppelOffresTypeLibelle(),
                'appelOffreTypeShort' => $type?->getAppelOffresTypeShort(),
                'moyenLivraisonLibelle' => $moyen?->getMoyenLivraisonLibelle(),
                'moyenLivraisonShort' => $moyen?->getMoyenLivraisonShort(),
                'paysLibelle' => $pays?->getPaysLibelle(),
                'organismeDemandeurLibelle' => $organismeLibelle,
                'organismeDemandeurAcronyme' => $organismeAcronyme,
                'devisesLibelle' => $devise?->getDevisesLibelle(),
                'devisesAcronyme' => $devise?->getDevisesAcronyme(),
                'partenaires' => $partenaires,
            ];
        } catch (\Exception $e) {
            error_log('Erreur dans serializeAppelOffres: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }
}