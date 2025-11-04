<?php

namespace App\Controller\Api;

use App\Entity\AppelOffres;
use App\Entity\AppelOffresType;
use App\Entity\MoyenLivraison;
use App\Entity\Pays;
use App\Entity\Devises;
use App\Entity\OrganismeDemandeur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppelOffresController extends AbstractController
{
    #[Route('/api/getAll/appelOffres', name: 'api_appel_offres_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): JsonResponse
    {
        try {
            $appels = $em->getRepository(AppelOffres::class)->findAll();
            $data = [];

            foreach ($appels as $appel) {
                $data[] = $this->serializeAppelOffres($appel);
            }

            return new JsonResponse($data, Response::HTTP_OK);
        } catch (\Exception $e) {
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

            // Validation minimale
            if (!isset($data['appelOffresObjet'])) {
                return new JsonResponse(['error' => 'appelOffresObjet est requis'], Response::HTTP_BAD_REQUEST);
            }

            $appel = new AppelOffres();
            $connection = $em->getConnection();

            // ✅ AUTO-CALCUL DU DEVIS (max+1 de l'année en cours)
            $annee = $data['appelOffreAnnee'] ?? (int)date('Y');
            
            $sql = 'SELECT MAX(CAST(appelOffreDevis AS UNSIGNED)) as maxDevis 
                    FROM appel_offres 
                    WHERE appelOffreAnnee = :annee 
                    AND appelOffreDevis IS NOT NULL 
                    AND appelOffreDevis != ""';
            
            $stmt = $connection->prepare($sql);
            $result = $stmt->executeQuery(['annee' => $annee]);
            $maxDevis = $result->fetchOne();
            
            $nouveauDevis = ($maxDevis ? (int)$maxDevis : 0) + 1;
            $appel->setAppelOffreDevis((string)$nouveauDevis);
            $appel->setAppelOffreAnnee($annee);

            // ✅ AUTO-CALCUL DU DEVIS PARTICIPATION (si participation = Oui)
            $participation = isset($data['appelOffresParticipation']) ? (int)$data['appelOffresParticipation'] : 0;

            if ($participation === 1 && !empty($data['appelOffresDateParticipation'])) {
                // Extraire l'année de la date de participation
                $dateParticipation = new \DateTime($data['appelOffresDateParticipation']);
                $anneeParticipation = (int)$dateParticipation->format('Y');
                
                // Requête SQL pour trouver le max des devis participation de cette année
                $sqlParticipation = 'SELECT MAX(CAST(appelOffresNumeroDevisParticipation AS UNSIGNED)) as maxDevisParticipation 
                                     FROM appel_offres 
                                     WHERE YEAR(appelOffresDateParticipation) = :anneeParticipation 
                                     AND appelOffresNumeroDevisParticipation IS NOT NULL 
                                     AND appelOffresNumeroDevisParticipation != ""';
                
                $stmtParticipation = $connection->prepare($sqlParticipation);
                $resultParticipation = $stmtParticipation->executeQuery(['anneeParticipation' => $anneeParticipation]);
                $maxDevisParticipation = $resultParticipation->fetchOne();
                
                $nouveauDevisParticipation = ($maxDevisParticipation ? (int)$maxDevisParticipation : 0) + 1;
                $appel->setAppelOffresNumeroDevisParticipation((string)$nouveauDevisParticipation);
            } else {
                // Si participation = Non, le devis participation reste NULL
                $appel->setAppelOffresNumeroDevisParticipation(null);
            }

            // Champs simples
            $appel->setAppelOffresObjet($data['appelOffresObjet'] ?? null);
            $appel->setAppelOffresCCRetire(isset($data['appelOffresCCRetire']) ? (int)$data['appelOffresCCRetire'] : null);
            $appel->setAppelOffresLienAnnonce($data['appelOffresLienAnnonce'] ?? null);
            $appel->setAppelOffresCautionBancaire(isset($data['appelOffresCautionBancaire']) ? (int)$data['appelOffresCautionBancaire'] : null);
            $appel->setAppelOffresTypeParticipationId($data['appelOffresTypeParticipationId'] ?? null);
            $appel->setAppelOffresRemarque($data['appelOffresRemarque'] ?? null);
            $appel->setAppelOffresParticipation($participation);
            $appel->setAppelOffresEtat($data['appelOffresEtat'] ?? null);
            $appel->setAppelOffresResultatRang(isset($data['appelOffresResultatRang']) ? (int)$data['appelOffresResultatRang'] : null);
            $appel->setAppelOffresResultatRangTotal(isset($data['appelOffresResultatRangTotal']) ? (int)$data['appelOffresResultatRangTotal'] : null);

            // Dates/Heures
            $appel->setAppelOffreDateRemise(!empty($data['appelOffreDateRemise']) ? new \DateTime($data['appelOffreDateRemise']) : null);
            $appel->setAppelOffresDateLimiteRemise(!empty($data['appelOffresDateLimiteRemise']) ? new \DateTime($data['appelOffresDateLimiteRemise']) : null);
            $appel->setAppelOffresHeureLimiteRemise(!empty($data['appelOffresHeureLimiteRemise']) ? new \DateTime($data['appelOffresHeureLimiteRemise']) : null);
            $appel->setAppelOffresDateParticipation(!empty($data['appelOffresDateParticipation']) ? new \DateTime($data['appelOffresDateParticipation']) : null);

            // Relations
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

            $em->persist($appel);
            $em->flush();

            return new JsonResponse([
                'message' => 'AppelOffres créé avec succès',
                'id' => $appel->getAppelOffresId(),
                'devis' => $appel->getAppelOffreDevis(),
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

            // Champs simples
            if (array_key_exists('appelOffresObjet', $data)) $appel->setAppelOffresObjet($data['appelOffresObjet']);
            if (array_key_exists('appelOffreDevis', $data)) $appel->setAppelOffreDevis($data['appelOffreDevis']);
            if (array_key_exists('appelOffreAnnee', $data)) $appel->setAppelOffreAnnee($data['appelOffreAnnee']);
            if (array_key_exists('appelOffresCCRetire', $data)) $appel->setAppelOffresCCRetire((int)$data['appelOffresCCRetire']);
            if (array_key_exists('appelOffresLienAnnonce', $data)) $appel->setAppelOffresLienAnnonce($data['appelOffresLienAnnonce']);
            if (array_key_exists('appelOffresCautionBancaire', $data)) $appel->setAppelOffresCautionBancaire((int)$data['appelOffresCautionBancaire']);
            if (array_key_exists('appelOffresTypeParticipationId', $data)) $appel->setAppelOffresTypeParticipationId($data['appelOffresTypeParticipationId']);
            if (array_key_exists('appelOffresRemarque', $data)) $appel->setAppelOffresRemarque($data['appelOffresRemarque']);
            if (array_key_exists('appelOffresParticipation', $data)) $appel->setAppelOffresParticipation((int)$data['appelOffresParticipation']);
            if (array_key_exists('appelOffresEtat', $data)) $appel->setAppelOffresEtat($data['appelOffresEtat']);
            if (array_key_exists('appelOffresResultatRang', $data)) $appel->setAppelOffresResultatRang((int)$data['appelOffresResultatRang']);
            if (array_key_exists('appelOffresResultatRangTotal', $data)) $appel->setAppelOffresResultatRangTotal((int)$data['appelOffresResultatRangTotal']);
            if (array_key_exists('appelOffresNumeroDevisParticipation', $data)) $appel->setAppelOffresNumeroDevisParticipation($data['appelOffresNumeroDevisParticipation']);

            // Dates/Heures
            if (array_key_exists('appelOffreDateRemise', $data)) {
                $appel->setAppelOffreDateRemise(!empty($data['appelOffreDateRemise']) ? new \DateTime($data['appelOffreDateRemise']) : null);
            }
            if (array_key_exists('appelOffresDateLimiteRemise', $data)) {
                $appel->setAppelOffresDateLimiteRemise(!empty($data['appelOffresDateLimiteRemise']) ? new \DateTime($data['appelOffresDateLimiteRemise']) : null);
            }
            if (array_key_exists('appelOffresHeureLimiteRemise', $data)) {
                $appel->setAppelOffresHeureLimiteRemise(!empty($data['appelOffresHeureLimiteRemise']) ? new \DateTime($data['appelOffresHeureLimiteRemise']) : null);
            }
            if (array_key_exists('appelOffresDateParticipation', $data)) {
                $appel->setAppelOffresDateParticipation(!empty($data['appelOffresDateParticipation']) ? new \DateTime($data['appelOffresDateParticipation']) : null);
            }

            // Relations
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
        $partenaires = [];
        foreach ($appel->getAppelOffresPartenaires() as $aop) {
            $p = $aop->getPartenaire();
            if ($p) {
                $partenaires[] = [
                    'id' => $aop->getId(),
                    'partenaireId' => $p->getPartenaireId(),
                    'partenaireLibelle' => $p->getPartenaireLibelle(),
                    'partenaireAcronyme' => $p->getPartenaireAcronyme(),
                    'role' => $aop->getRole(),
                ];
            }
        }

        // ✅ Extraire les entités relations pour accéder aux libellés
        $type = $appel->getAppelOffresTypeId();
        $moyen = $appel->getAppelOffresMoyenLivraisonId();
        $pays = $appel->getAppelOffresPaysId();
        $organisme = $appel->getAppelOffresOrganismeDemandeurId();
        $devise = $appel->getAppelOffresDevisesId();

        return [
            'appelOffresId' => $appel->getAppelOffresId(),
            'appelOffreDevis' => $appel->getAppelOffreDevis(),
            'appelOffresObjet' => $appel->getAppelOffresObjet(),
            'appelOffreDateRemise' => $appel->getAppelOffreDateRemise()?->format('Y-m-d'),
            'appelOffresDateLimiteRemise' => $appel->getAppelOffresDateLimiteRemise()?->format('Y-m-d'),
            'appelOffresHeureLimiteRemise' => $appel->getAppelOffresHeureLimiteRemise()?->format('H:i:s'),
            'appelOffresCCRetire' => $appel->getAppelOffresCCRetire(),
            'appelOffresLienAnnonce' => $appel->getAppelOffresLienAnnonce(),
            'appelOffresCautionBancaire' => $appel->getAppelOffresCautionBancaire(),
            'appelOffresTypeParticipationId' => $appel->getAppelOffresTypeParticipationId(),
            'appelOffresRemarque' => $appel->getAppelOffresRemarque(),
            'appelOffresParticipation' => $appel->getAppelOffresParticipation(),
            'appelOffresDateParticipation' => $appel->getAppelOffresDateParticipation()?->format('Y-m-d'),
            'appelOffresEtat' => $appel->getAppelOffresEtat(),
            'appelOffresResultatRang' => $appel->getAppelOffresResultatRang(),
            'appelOffresResultatRangTotal' => $appel->getAppelOffresResultatRangTotal(),
            'appelOffresNumeroDevisParticipation' => $appel->getAppelOffresNumeroDevisParticipation(),
            'appelOffreAnnee' => $appel->getAppelOffreAnnee(),

            // Relations - IDs
            'appelOffresTypeId' => $type?->getAppelOffresTypeId(),
            'appelOffresMoyenLivraisonId' => $moyen?->getMoyenLivraisonId(),
            'appelOffresPaysId' => $pays?->getPaysId(),
            'appelOffresOrganismeDemandeurId' => $organisme?->getId(),
            'appelOffresDevisesId' => $devise?->getDevisesId(),

            // ✅ LIBELLÉS ENRICHIS (pour affichage dans le frontend)
            'appelOffreTypeLibelle' => $type?->getAppelOffresTypeLibelle(),
            'appelOffreTypeShort' => $type?->getAppelOffresTypeShort(),
            'moyenLivraisonLibelle' => $moyen?->getMoyenLivraisonLibelle(),
            'moyenLivraisonShort' => $moyen?->getMoyenLivraisonShort(),
            'paysLibelle' => $pays?->getPaysLibelle(),
            'organismeDemandeurLibelle' => $organisme?->getOrganismeDemandeurLibelle(),
            'devisesLibelle' => $devise?->getDevisesLibelle(),
            'devisesAcronyme' => $devise?->getDevisesAcronyme(),

            'partenaires' => $partenaires,
        ];
    }
}