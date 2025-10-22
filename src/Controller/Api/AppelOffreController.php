<?php

namespace App\Controller\Api;

use App\Entity\AppelOffre;
use App\Entity\AppelOffreType;
use App\Entity\MoyenLivraison;
use App\Entity\Pays;
use App\Entity\Devises;
use App\Entity\OrganismeDemandeur;
use App\Entity\Partenaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppelOffreController extends AbstractController
{
    #[Route('/api/getAll/appelsOffre', name: 'api_appel_offre_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): JsonResponse
    {
        $appels = $em->getRepository(AppelOffre::class)->findAll();
        $data = [];

        foreach ($appels as $appel) {
            $data[] = $this->serializeAppelOffre($appel);
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/get/appelOffre/{id}', name: 'api_appel_offre_show', methods: ['GET'])]
    public function show(AppelOffre $appelOffre): JsonResponse
    {
        return new JsonResponse($this->serializeAppelOffre($appelOffre), Response::HTTP_OK);
    }

    #[Route('/api/create/appelOffre', name: 'api_appel_offre_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Validation des données requises
            if (!isset($data['appelOffreObjet']) || !isset($data['appelOffreDateRemise'])) {
                return new JsonResponse(['error' => 'Données manquantes: objet et date de remise requis'], Response::HTTP_BAD_REQUEST);
            }

            $appel = new AppelOffre();
            
            // AUTO-GÉNÉRATION de la référence si pas fournie
            if (empty($data['appelOffreDevis'])) {
                $reference = $em->getRepository(AppelOffre::class)->generateNextAppelOffreRef();
                $appel->setAppelOffreDevis($reference);
            } else {
                if ($em->getRepository(AppelOffre::class)->appelOffreRefExists($data['appelOffreDevis'])) {
                    return new JsonResponse(['error' => 'Cette référence existe déjà'], Response::HTTP_CONFLICT);
                }
                $appel->setAppelOffreDevis($data['appelOffreDevis']);
            }
            
            // AUTO-GÉNÉRATION du numéro de devis si participation = oui
            if (isset($data['appelOffreParticipation']) && $data['appelOffreParticipation'] == 1) {
                if (!empty($data['dateParticipation'])) {
                    $numeroDevis = $em->getRepository(AppelOffre::class)->generateNumeroDevis($data['dateParticipation']);
                    $appel->setNumeroDevisParticipation($numeroDevis);
                }
            }
            
            // Assignation des champs
            $appel->setAppelOffreObjet($data['appelOffreObjet']);
            $appel->setAppelOffreDateRemise(new \DateTime($data['appelOffreDateRemise']));
            $appel->setAppelOffreRetire($data['appelOffreRetire'] ?? 0);
            $appel->setAppelOffreParticipation($data['appelOffreParticipation'] ?? 0);
            $appel->setAppelOffreEtat($data['appelOffreEtat'] ?? 'EN_ATTENTE');
            $appel->setRemarque($data['remarque'] ?? null);
            $appel->setHeureRemis(isset($data['heureRemis']) ? new \DateTime($data['heureRemis']) : null);
            $appel->setDateParticipation(isset($data['dateParticipation']) ? new \DateTime($data['dateParticipation']) : null);
            
            if (!isset($data['appelOffreParticipation']) || $data['appelOffreParticipation'] != 1) {
                $appel->setNumeroDevisParticipation($data['numeroDevisParticipation'] ?? null);
            }
            
            $appel->setTypeParticipation($data['typeParticipation'] ?? null);
            $appel->setAppelOffreAnnee($data['appelOffreAnnee'] ?? null);
            $appel->setAppelOffreCautionBancaire($data['appelOffreCautionBancaire'] ?? null);
            $appel->setDateLimiteRemise(isset($data['dateLimiteRemise']) ? new \DateTime($data['dateLimiteRemise']) : null);
            $appel->setLienAnnonce($data['lienAnnonce'] ?? null);
            $appel->setResultatRang($data['resultatRang'] ?? null);
            $appel->setResultatRangTotal($data['resultatRangTotal'] ?? null);
            // Relations avec validation stricte
            if (isset($data['appelOffreTypeId'])) {
                $type = $em->getRepository(AppelOffreType::class)->find($data['appelOffreTypeId']);
                if (!$type) {
                    return new JsonResponse(['error' => 'Type d\'appel d\'offre invalide'], Response::HTTP_BAD_REQUEST);
                }
                $appel->setAppelOffreType($type);
            }

            if (isset($data['moyenLivraisonId'])) {
                $moyen = $em->getRepository(MoyenLivraison::class)->find($data['moyenLivraisonId']);
                if (!$moyen) {
                    return new JsonResponse(['error' => 'Moyen de livraison invalide'], Response::HTTP_BAD_REQUEST);
                }
                $appel->setMoyenLivraison($moyen);
            }

            if (isset($data['devisesId'])) {
                $devises = $em->getRepository(Devises::class)->find($data['devisesId']);
                if (!$devises) {
                    return new JsonResponse(['error' => 'Devise invalide'], Response::HTTP_BAD_REQUEST);
                }
                $appel->setDevises($devises);
            }

            if (isset($data['paysId'])) {
                $pays = $em->getRepository(Pays::class)->find($data['paysId']);
                if (!$pays) {
                    return new JsonResponse(['error' => 'Pays invalide'], Response::HTTP_BAD_REQUEST);
                }
                $appel->setPays($pays);
            }

            if (isset($data['organismeDemandeurId'])) {
                $organisme = $em->getRepository(OrganismeDemandeur::class)->find($data['organismeDemandeurId']);
                if (!$organisme) {
                    return new JsonResponse(['error' => 'Organisme demandeur invalide'], Response::HTTP_BAD_REQUEST);
                }
                $appel->setOrganismeDemandeur($organisme);
            }

            // ✅ GESTION DES PARTENAIRES (accepte "Groupement" OU "Avec Partenaires")
            if (isset($data['partenaireIds']) && is_array($data['partenaireIds']) && 
                isset($data['typeParticipation']) && 
                (strtolower($data['typeParticipation']) === 'groupement' || 
                 strtolower($data['typeParticipation']) === 'avec partenaires')) {
                
                $partenaireRepo = $em->getRepository(Partenaire::class);
                
                foreach ($data['partenaireIds'] as $partenaireId) {
                    $partenaire = $partenaireRepo->find($partenaireId);
                    if ($partenaire) {
                        $appel->addPartenaire($partenaire);
                    } else {
                        return new JsonResponse([
                            'error' => "Partenaire avec l'ID $partenaireId introuvable"
                        ], Response::HTTP_BAD_REQUEST);
                    }
                }
            }

            $em->persist($appel);
            $em->flush();

            return new JsonResponse([
                'message' => 'Appel d\'offre créé avec succès',
                'appelOffreDevis' => $appel->getAppelOffreDevis(),
                'numeroDevisParticipation' => $appel->getNumeroDevisParticipation(),
                'data' => $this->serializeAppelOffre($appel)
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la création: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/get/next-appel-offre-ref', name: 'api_appel_offre_next_ref', methods: ['GET'])]
    public function getNextAppelOffreRef(EntityManagerInterface $em): JsonResponse
    {
        try {
            $nextRef = $em->getRepository(AppelOffre::class)->generateNextAppelOffreRef();
            return new JsonResponse(['nextAppelOffreRef' => $nextRef], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la génération de la référence: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/generate/numero-devis', name: 'api_generate_numero_devis', methods: ['POST'])]
    public function generateNumeroDevis(Request $request, EntityManagerInterface $em): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (empty($data['dateParticipation'])) {
                return new JsonResponse(['error' => 'Date de participation requise'], Response::HTTP_BAD_REQUEST);
            }
            
            $numeroDevis = $em->getRepository(AppelOffre::class)->generateNumeroDevis($data['dateParticipation']);
            
            return new JsonResponse(['numeroDevis' => $numeroDevis], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la génération du numéro de devis: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/update/appelOffre/{id}', name: 'api_appel_offre_update', methods: ['PUT'])]
    public function update(Request $request, AppelOffre $appelOffre, EntityManagerInterface $em): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Validation
            if (!isset($data['appelOffreObjet']) || !isset($data['appelOffreDateRemise'])) {
                return new JsonResponse(['error' => 'Données manquantes'], Response::HTTP_BAD_REQUEST);
            }

            $appelOffre->setAppelOffreObjet($data['appelOffreObjet']);
            $appelOffre->setAppelOffreDateRemise(new \DateTime($data['appelOffreDateRemise']));
            $appelOffre->setAppelOffreRetire($data['appelOffreRetire'] ?? 0);
            $appelOffre->setAppelOffreParticipation($data['appelOffreParticipation'] ?? 0);
            $appelOffre->setAppelOffreEtat($data['appelOffreEtat'] ?? 'EN_ATTENTE');
            $appelOffre->setRemarque($data['remarque'] ?? null);
            $appelOffre->setHeureRemis(isset($data['heureRemis']) ? new \DateTime($data['heureRemis']) : null);
            $appelOffre->setDateParticipation(isset($data['dateParticipation']) ? new \DateTime($data['dateParticipation']) : null);
            $appelOffre->setNumeroDevisParticipation($data['numeroDevisParticipation'] ?? null);
            $appelOffre->setTypeParticipation($data['typeParticipation'] ?? null);
            $appelOffre->setAppelOffreAnnee($data['appelOffreAnnee'] ?? null);
            $appelOffre->setResultatRang($data['resultatRang'] ?? null);
$appelOffre->setResultatRangTotal($data['resultatRangTotal'] ?? null);
            $appelOffre->setAppelOffreCautionBancaire($data['appelOffreCautionBancaire'] ?? null);
            $appelOffre->setDateLimiteRemise(isset($data['dateLimiteRemise']) ? new \DateTime($data['dateLimiteRemise']) : null);
            $appelOffre->setLienAnnonce($data['lienAnnonce'] ?? null);

            // Relations
            if (isset($data['appelOffreTypeId'])) {
                $type = $em->getRepository(AppelOffreType::class)->find($data['appelOffreTypeId']);
                if ($type) $appelOffre->setAppelOffreType($type);
            }

            if (isset($data['moyenLivraisonId'])) {
                $moyen = $em->getRepository(MoyenLivraison::class)->find($data['moyenLivraisonId']);
                if ($moyen) $appelOffre->setMoyenLivraison($moyen);
            }

            if (isset($data['paysId'])) {
                $pays = $em->getRepository(Pays::class)->find($data['paysId']);
                if ($pays) $appelOffre->setPays($pays);
            }

            if (isset($data['devisesId'])) {
                $devises = $em->getRepository(Devises::class)->find($data['devisesId']);
                if ($devises) $appelOffre->setDevises($devises);
            }

            if (isset($data['organismeDemandeurId'])) {
                $organisme = $em->getRepository(OrganismeDemandeur::class)->find($data['organismeDemandeurId']);
                if ($organisme) $appelOffre->setOrganismeDemandeur($organisme);
            }

            // ✅ Mise à jour des Partenaires (accepte "Groupement" OU "Avec Partenaires")
            if (isset($data['partenaireIds']) && is_array($data['partenaireIds']) && 
                isset($data['typeParticipation']) && 
                (strtolower($data['typeParticipation']) === 'groupement' || 
                 strtolower($data['typeParticipation']) === 'avec partenaires')) {
                
                // Supprimer tous les partenaires existants
                $appelOffre->clearPartenaires();
                
                // Ajouter les nouveaux partenaires
                $partenaireRepo = $em->getRepository(Partenaire::class);
                foreach ($data['partenaireIds'] as $partenaireId) {
                    $partenaire = $partenaireRepo->find($partenaireId);
                    if ($partenaire) {
                        $appelOffre->addPartenaire($partenaire);
                    }
                }
            } elseif (isset($data['typeParticipation']) && strtolower($data['typeParticipation']) === 'seul') {
                // Si le type change à "Seul", supprimer tous les partenaires
                $appelOffre->clearPartenaires();
            }

            $em->flush();

            return new JsonResponse($this->serializeAppelOffre($appelOffre), Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/delete/appelOffre/{id}', name: 'api_appel_offre_delete', methods: ['DELETE'])]
    public function delete(AppelOffre $appelOffre, EntityManagerInterface $em): JsonResponse
    {
        try {
            $em->remove($appelOffre);
            $em->flush();

            return new JsonResponse(['message' => 'Appel d\'offre supprimé avec succès'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function serializeAppelOffre(AppelOffre $appel): array
    {
        // Sérialiser les partenaires
        $partenaires = [];
        foreach ($appel->getPartenaires() as $partenaire) {
            $partenaires[] = [
                'partenaireId' => $partenaire->getPartenaireId(),
                'partenaireLibelle' => $partenaire->getPartenaireLibelle(),
                'partenaireAcronyme' => $partenaire->getPartenaireAcronyme(),
                'partenaireRole' => $partenaire->getPartenaireRole(),
            ];
        }

        return [
            'id' => $appel->getId(),
            'appelOffreDevis' => $appel->getAppelOffreDevis(),
            'appelOffreObjet' => $appel->getAppelOffreObjet(),
            'appelOffreDateRemise' => $appel->getAppelOffreDateRemise()?->format('Y-m-d'),
            'appelOffreRetire' => $appel->getAppelOffreRetire(),
            'appelOffreParticipation' => $appel->getAppelOffreParticipation(),
            'appelOffreEtat' => $appel->getAppelOffreEtat(),
            'remarque' => $appel->getRemarque(),
            'heureRemis' => $appel->getHeureRemis()?->format('H:i:s'),
            'resultatRang' => $appel->getResultatRang(),
            'resultatRangTotal' => $appel->getResultatRangTotal(),
            'dateParticipation' => $appel->getDateParticipation()?->format('Y-m-d'),
            'numeroDevisParticipation' => $appel->getNumeroDevisParticipation(),
            'typeParticipation' => $appel->getTypeParticipation(),
            'appelOffreAnnee' => $appel->getAppelOffreAnnee(),
            'appelOffreCautionBancaire' => $appel->getAppelOffreCautionBancaire(),
            'dateLimiteRemise' => $appel->getDateLimiteRemise()?->format('Y-m-d'),
            'lienAnnonce' => $appel->getLienAnnonce(),

            // Relations
            'appelOffreTypeId' => $appel->getAppelOffreType()?->getId(),
            'appelOffreTypeLibelle' => $appel->getAppelOffreType()?->getAppelOffreType(),

            'moyenLivraisonId' => $appel->getMoyenLivraison()?->getId(),
            'moyenLivraisonLibelle' => $appel->getMoyenLivraison()?->getMoyenLivraison(),

            'paysId' => $appel->getPays()?->getId(),
            'paysLibelle' => $appel->getPays()?->getPaysLibelle(),

            'devisesId' => $appel->getDevises()?->getDevisesId(),
            'devisesLibelle' => $appel->getDevises()?->getDevisesLibelle(),

            'organismeDemandeurId' => $appel->getOrganismeDemandeur()?->getId(),
            'organismeDemandeurLibelle' => $appel->getOrganismeDemandeur()?->getOrganismeDemandeurLibelle(),

            // ✅ Partenaires
            'partenaires' => $partenaires,
        ];
    }
}