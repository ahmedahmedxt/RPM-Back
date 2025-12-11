<?php

namespace App\Controller\Api;

use App\Entity\AppelOffresPersonnelCle;
use App\Entity\AppelOffres;
use App\Entity\AppelOffresPersonnelCleAppelOffres;
use App\Entity\Collaborateur;
use App\Entity\NiveauEtude;
use App\Repository\AppelOffresPersonnelCleRepository;
use App\Repository\AppelOffresPersonnelCleAppelOffresRepository;
use App\Repository\AppelOffresRepository;
use App\Repository\CollaborateurRepository;
use App\Repository\NiveauEtudeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/appelOffresPersonnelCle', name: 'api_appel_offres_personnel_cle_')]
class AppelOffresPersonnelCleController extends AbstractController
{
    private $entityManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(
        AppelOffresPersonnelCleRepository $repository,
        Request $request,
        AppelOffresRepository $appelOffresRepository,
        AppelOffresPersonnelCleAppelOffresRepository $liaisonRepository,
        TokenStorageInterface $tokenStorage
    ): JsonResponse {
        try {
            $appelOffresId = $request->query->get('appelOffresId');
            $appelOffresPersonnelCles = $repository->findAll();

            $data = [];
            foreach ($appelOffresPersonnelCles as $appelOffresPersonnelCle) {
                $niveauEtude = $appelOffresPersonnelCle->getNiveauEtude();
                $collaborateurs = $appelOffresPersonnelCle->getCollaborateurs();

                $couleurStatus = null;
                $ordreAffichage = null;
                $appelOffres = null;
                $assignes = 0;

                if ($appelOffresId) {
                    $appelOffresIdInt = (int) $appelOffresId;
                    $appelOffres = $appelOffresRepository->find($appelOffresIdInt);

                    if ($appelOffres) {
                        $liaison = $liaisonRepository->findOneBy([
                            'appelOffres' => $appelOffres,
                            'appelOffresPersonnelCle' => $appelOffresPersonnelCle
                        ]);

                        if ($liaison) {
                            $couleurStatus = $liaison->getCouleurStatus();
                            $ordreAffichage = $liaison->getOrdreAffichage();
                        }

                        // Compter les collaborateurs affectés à ce personnel clé pour cet appel d'offre
                        $assignes = $liaisonRepository->countCollaborateurs($appelOffresIdInt, $appelOffresPersonnelCle->getAppelOffresPersonnelCleId());
                    }
                }

                $collaborateursData = [];
                foreach ($collaborateurs as $collaborateur) {
                    $collaborateursData[] = [
                        'collaborateurId' => $collaborateur->getCollaborateurId(),
                        'collaborateurNom' => $collaborateur->getCollaborateurNom(),
                        'collaborateurPrenom' => $collaborateur->getCollaborateurPrenom(),
                        'collaborateurEmail1' => $collaborateur->getCollaborateurEmail1(),
                        'collaborateurTelephone1' => $collaborateur->getCollaborateurTelephone1()
                    ];
                }

                $data[] = [
                    'appelOffresPersonnelCleId' => $appelOffresPersonnelCle->getAppelOffresPersonnelCleId(),
                    'appelOffresPersonnelCleIntitule' => $appelOffresPersonnelCle->getAppelOffresPersonnelCleIntitule() ?? '',
                    'appelOffresPersonnelCleDescription' => $appelOffresPersonnelCle->getAppelOffresPersonnelCleDescription() ?? '',
                    'appelOffresPersonnelCleNiveauEtudeMin' => $appelOffresPersonnelCle->getAppelOffresPersonnelCleNiveauEtudeMin() ?? '',
                    'appelOffresPersonnelCleNbrAnneeExperience' => $appelOffresPersonnelCle->getAppelOffresPersonnelCleNbrAnneeExperience(),
                    'appelOffresPersonnelCleCouleurStatus' => $couleurStatus,
                    'ordreAffichage' => $ordreAffichage,
                    'appelOffresId' => $appelOffres ? $appelOffres->getAppelOffresId() : null,
                    'appelOffresObjet' => $appelOffres ? $appelOffres->getAppelOffresObjet() : null,
                    'niveauEtudeId' => $niveauEtude ? $niveauEtude->getNiveauEtudeId() : null,
                    'niveauEtudeLibelle' => $niveauEtude ? $niveauEtude->getNiveauEtudeLibelle() : null,
                    'collaborateurs' => $collaborateursData,
                    'collaborateursCount' => $appelOffresPersonnelCle->getAppelOffresPersonnelCleCollaborateursCount() ?? 0, // quota
                    'collaborateursAssignes' => $assignes // compteur affectés
                ];
            }

            return new JsonResponse($data, Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'data' => []
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/byAppelOffres/{appelOffresId}', name: 'get_by_appel_offres', methods: ['GET'])]
    public function getByAppelOffres(
        int $appelOffresId,
        AppelOffresPersonnelCleRepository $repository,
        AppelOffresRepository $appelOffresRepository,
        AppelOffresPersonnelCleAppelOffresRepository $liaisonRepository,
        TokenStorageInterface $tokenStorage
    ): JsonResponse {
        try {
            $appelOffres = $appelOffresRepository->find($appelOffresId);

            if (!$appelOffres) {
                return new JsonResponse(['message' => 'AppelOffres not found'], Response::HTTP_NOT_FOUND);
            }

            // Trier par ordreAffichage asc, puis id asc (si ordreAffichage est nul)
            $liaisons = $liaisonRepository->findBy(
                ['appelOffres' => $appelOffres],
                ['ordreAffichage' => 'ASC', 'id' => 'ASC']
            );

            $data = [];
            foreach ($liaisons as $liaison) {
                $appelOffresPersonnelCle = $liaison->getAppelOffresPersonnelCle();
                if (!$appelOffresPersonnelCle) {
                    continue;
                }

                // Recharger l'entité depuis le repository au lieu d'utiliser refresh()
                // Cela garantit que toutes les colonnes sont chargées correctement
                $appelOffresPersonnelCle = $repository->find($appelOffresPersonnelCle->getAppelOffresPersonnelCleId());
                if (!$appelOffresPersonnelCle) {
                    continue;
                }

                $niveauEtude = $appelOffresPersonnelCle->getNiveauEtude();
                $collaborateurs = $appelOffresPersonnelCle->getCollaborateurs();

                $couleurStatus = $liaison->getCouleurStatus();
                $ordreAffichage = $liaison->getOrdreAffichage();

                $collaborateursData = [];
                foreach ($collaborateurs as $collaborateur) {
                    $collaborateursData[] = [
                        'collaborateurId' => $collaborateur->getCollaborateurId(),
                        'collaborateurNom' => $collaborateur->getCollaborateurNom(),
                        'collaborateurPrenom' => $collaborateur->getCollaborateurPrenom(),
                        'collaborateurEmail1' => $collaborateur->getCollaborateurEmail1(),
                        'collaborateurTelephone1' => $collaborateur->getCollaborateurTelephone1()
                    ];
                }

                // compter les collaborateurs affectés à ce PC pour cet AO
                $assignes = $liaisonRepository->countCollaborateurs($appelOffresId, $appelOffresPersonnelCle->getAppelOffresPersonnelCleId());

                // Récupérer collaborateursCount directement depuis l'entité (sans refresh)
                $collaborateursCount = $appelOffresPersonnelCle->getAppelOffresPersonnelCleCollaborateursCount();
                if ($collaborateursCount === null) {
                    $collaborateursCount = 0;
                }

                $data[] = [
                    'appelOffresPersonnelCleId' => $appelOffresPersonnelCle->getAppelOffresPersonnelCleId(),
                    'appelOffresPersonnelCleIntitule' => $appelOffresPersonnelCle->getAppelOffresPersonnelCleIntitule() ?? '',
                    'appelOffresPersonnelCleDescription' => $appelOffresPersonnelCle->getAppelOffresPersonnelCleDescription() ?? '',
                    'appelOffresPersonnelCleNiveauEtudeMin' => $appelOffresPersonnelCle->getAppelOffresPersonnelCleNiveauEtudeMin() ?? '',
                    'appelOffresPersonnelCleNbrAnneeExperience' => $appelOffresPersonnelCle->getAppelOffresPersonnelCleNbrAnneeExperience(),
                    'appelOffresPersonnelCleCouleurStatus' => $couleurStatus,
                    'ordreAffichage' => $ordreAffichage,
                    'appelOffresId' => $appelOffres->getAppelOffresId(),
                    'appelOffresObjet' => $appelOffres->getAppelOffresObjet(),
                    'niveauEtudeId' => $niveauEtude ? $niveauEtude->getNiveauEtudeId() : null,
                    'niveauEtudeLibelle' => $niveauEtude ? $niveauEtude->getNiveauEtudeLibelle() : null,
                    'collaborateurs' => $collaborateursData,
                    'collaborateursCount' => $collaborateursCount,
                    'collaborateursAssignes' => $assignes
                ];
            }

            return new JsonResponse($data, Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(), // Ajouter la trace pour debug
                'data' => []
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'get_by_id', methods: ['GET'])]
    public function getById(int $id, AppelOffresPersonnelCleRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $appelOffresPersonnelCle = $repository->find($id);

            if (!$appelOffresPersonnelCle) {
                return new JsonResponse(['message' => 'AppelOffresPersonnelCle not found'], Response::HTTP_NOT_FOUND);
            }

            $niveauEtude = $appelOffresPersonnelCle->getNiveauEtude();
            $collaborateurs = $appelOffresPersonnelCle->getCollaborateurs();

            $collaborateursData = [];
            foreach ($collaborateurs as $collaborateur) {
                $collaborateursData[] = [
                    'collaborateurId' => $collaborateur->getCollaborateurId(),
                    'collaborateurNom' => $collaborateur->getCollaborateurNom(),
                    'collaborateurPrenom' => $collaborateur->getCollaborateurPrenom(),
                    'collaborateurEmail1' => $collaborateur->getCollaborateurEmail1(),
                    'collaborateurTelephone1' => $collaborateur->getCollaborateurTelephone1(),
                    'pays' => $collaborateur->getPays() ? $collaborateur->getPays()->getPaysLibelle() : null,
                    'nationalite' => $collaborateur->getNationalite() ? $collaborateur->getNationalite()->getNationaliteLibelle() : null
                ];
            }

            $data = [
                'appelOffresPersonnelCleId' => $appelOffresPersonnelCle->getAppelOffresPersonnelCleId(),
                'appelOffresPersonnelCleIntitule' => $appelOffresPersonnelCle->getAppelOffresPersonnelCleIntitule() ?? '',
                'appelOffresPersonnelCleDescription' => $appelOffresPersonnelCle->getAppelOffresPersonnelCleDescription() ?? '',
                'appelOffresPersonnelCleNiveauEtudeMin' => $appelOffresPersonnelCle->getAppelOffresPersonnelCleNiveauEtudeMin() ?? '',
                'appelOffresPersonnelCleNbrAnneeExperience' => $appelOffresPersonnelCle->getAppelOffresPersonnelCleNbrAnneeExperience(),
                'niveauEtudeId' => $niveauEtude ? $niveauEtude->getNiveauEtudeId() : null,
                'niveauEtudeLibelle' => $niveauEtude ? $niveauEtude->getNiveauEtudeLibelle() : null,
                'collaborateurs' => $collaborateursData,
                'collaborateursCount' => $appelOffresPersonnelCle->getAppelOffresPersonnelCleCollaborateursCount() ?? 0
            ];

            return new JsonResponse($data, Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de la récupération de l\'appel d\'offres personnel clé'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        AppelOffresRepository $appelOffresRepository,
        NiveauEtudeRepository $niveauEtudeRepository,
        CollaborateurRepository $collaborateurRepository,
        AppelOffresPersonnelCleAppelOffresRepository $liaisonRepository,
        TokenStorageInterface $tokenStorage
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            $appelOffresPersonnelCle = new AppelOffresPersonnelCle();
            $appelOffresPersonnelCle->setAppelOffresPersonnelCleIntitule($data['appelOffresPersonnelCleIntitule'] ?? null);
            $appelOffresPersonnelCle->setAppelOffresPersonnelCleDescription($data['appelOffresPersonnelCleDescription'] ?? null);
            $appelOffresPersonnelCle->setAppelOffresPersonnelCleNiveauEtudeMin($data['appelOffresPersonnelCleNiveauEtudeMin'] ?? null);
            $appelOffresPersonnelCle->setAppelOffresPersonnelCleNbrAnneeExperience($data['appelOffresPersonnelCleNbrAnneeExperience'] ?? null);
            
            // S'assurer que collaborateursCount est toujours défini (même si 0)
            $collaborateursCount = isset($data['collaborateursCount']) ? (int)$data['collaborateursCount'] : 0;
            $appelOffresPersonnelCle->setAppelOffresPersonnelCleCollaborateursCount($collaborateursCount);

            if (isset($data['niveauEtudeId']) && $data['niveauEtudeId'] !== null) {
                $niveauEtude = $niveauEtudeRepository->find($data['niveauEtudeId']);
                if ($niveauEtude) {
                    $appelOffresPersonnelCle->setNiveauEtude($niveauEtude);
                }
            }

            $this->entityManager->persist($appelOffresPersonnelCle);
            $this->entityManager->flush();

            if (isset($data['appelOffresId']) && $data['appelOffresId'] !== null) {
                $appelOffres = $appelOffresRepository->find($data['appelOffresId']);
                if ($appelOffres) {
                    $liaison = new AppelOffresPersonnelCleAppelOffres();
                    $liaison->setAppelOffres($appelOffres);
                    $liaison->setAppelOffresPersonnelCle($appelOffresPersonnelCle);
                    $liaison->setCouleurStatus($data['appelOffresPersonnelCleCouleurStatus'] ?? null);
                    if (isset($data['ordreAffichage'])) {
                        $liaison->setOrdreAffichage($data['ordreAffichage']);
                    }
                    $this->entityManager->persist($liaison);
                    $this->entityManager->flush();
                }
            }

            if (isset($data['collaborateurId']) && $data['collaborateurId'] !== null) {
                $collaborateur = $collaborateurRepository->find($data['collaborateurId']);
                if ($collaborateur) {
                    $appelOffresPersonnelCle->addCollaborateur($collaborateur);
                    $this->entityManager->flush();
                }
            }

            return new JsonResponse(['message' => 'AppelOffresPersonnelCle created'], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de la création de l\'appel d\'offres personnel clé'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
public function update(
    int $id,
    Request $request,
    AppelOffresPersonnelCleRepository $repository,
    AppelOffresRepository $appelOffresRepository,
    NiveauEtudeRepository $niveauEtudeRepository,
    AppelOffresPersonnelCleAppelOffresRepository $liaisonRepository,
    TokenStorageInterface $tokenStorage
): JsonResponse {
    try {
        $appelOffresPersonnelCle = $repository->find($id);

        if (!$appelOffresPersonnelCle) {
            return new JsonResponse(['message' => 'AppelOffresPersonnelCle not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        // Update only the basic fields
        $appelOffresPersonnelCle->setAppelOffresPersonnelCleIntitule($data['appelOffresPersonnelCleIntitule'] ?? $appelOffresPersonnelCle->getAppelOffresPersonnelCleIntitule());
        $appelOffresPersonnelCle->setAppelOffresPersonnelCleDescription($data['appelOffresPersonnelCleDescription'] ?? $appelOffresPersonnelCle->getAppelOffresPersonnelCleDescription());
        $appelOffresPersonnelCle->setAppelOffresPersonnelCleNiveauEtudeMin($data['appelOffresPersonnelCleNiveauEtudeMin'] ?? $appelOffresPersonnelCle->getAppelOffresPersonnelCleNiveauEtudeMin());
        $appelOffresPersonnelCle->setAppelOffresPersonnelCleNbrAnneeExperience($data['appelOffresPersonnelCleNbrAnneeExperience'] ?? $appelOffresPersonnelCle->getAppelOffresPersonnelCleNbrAnneeExperience());
        
        if (isset($data['collaborateursCount'])) {
            $appelOffresPersonnelCle->setAppelOffresPersonnelCleCollaborateursCount($data['collaborateursCount']);
        }

        // Update niveau d'étude if provided
        if (isset($data['niveauEtudeId'])) {
            if ($data['niveauEtudeId'] === null) {
                $appelOffresPersonnelCle->setNiveauEtude(null);
            } else {
                $niveauEtude = $niveauEtudeRepository->find($data['niveauEtudeId']);
                if ($niveauEtude) {
                    $appelOffresPersonnelCle->setNiveauEtude($niveauEtude);
                }
            }
        }

        // Mettre à jour l'ordre / couleur si appelOffresId et liaison visée
        if (isset($data['appelOffresId'])) {
            $appelOffres = $appelOffresRepository->find($data['appelOffresId']);
            if ($appelOffres) {
                $liaison = $liaisonRepository->findOneBy([
                    'appelOffres' => $appelOffres,
                    'appelOffresPersonnelCle' => $appelOffresPersonnelCle
                ]);

                if ($liaison && isset($data['ordreAffichage'])) {
                    $liaison->setOrdreAffichage($data['ordreAffichage']);
                }
                if ($liaison && isset($data['appelOffresPersonnelCleCouleurStatus'])) {
                    $liaison->setCouleurStatus($data['appelOffresPersonnelCleCouleurStatus']);
                }
            }
        }

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'AppelOffresPersonnelCle updated'], Response::HTTP_OK);

    } catch (\Exception $e) {
        return new JsonResponse([
            'error' => $e->getMessage(),
            'message' => 'Erreur lors de la mise à jour de l\'appel d\'offres personnel clé'
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
} 


    #[Route('/{id}/couleur', name: 'update_couleur', methods: ['PUT'])]
    public function updateCouleur(
        int $id,
        Request $request,
        AppelOffresPersonnelCleRepository $repository,
        AppelOffresRepository $appelOffresRepository,
        AppelOffresPersonnelCleAppelOffresRepository $liaisonRepository,
        TokenStorageInterface $tokenStorage
    ): JsonResponse {
        try {
            $appelOffresPersonnelCle = $repository->find($id);

            if (!$appelOffresPersonnelCle) {
                return new JsonResponse(['message' => 'AppelOffresPersonnelCle not found'], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);
            $couleurStatus = $data['appelOffresPersonnelCleCouleurStatus'] ?? null;
            $appelOffresId = $data['appelOffresId'] ?? null;

            if (!$appelOffresId) {
                return new JsonResponse(['message' => 'appelOffresId is required'], Response::HTTP_BAD_REQUEST);
            }

            $appelOffres = $appelOffresRepository->find($appelOffresId);
            if (!$appelOffres) {
                return new JsonResponse(['message' => 'AppelOffres not found'], Response::HTTP_NOT_FOUND);
            }

            $liaison = $liaisonRepository->findOneBy([
                'appelOffres' => $appelOffres,
                'appelOffresPersonnelCle' => $appelOffresPersonnelCle
            ]);

            if (!$liaison) {
                $liaison = new AppelOffresPersonnelCleAppelOffres();
                $liaison->setAppelOffres($appelOffres);
                $liaison->setAppelOffresPersonnelCle($appelOffresPersonnelCle);
                $this->entityManager->persist($liaison);
            }

            $liaison->setCouleurStatus($couleurStatus);
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'Couleur updated'], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de la mise à jour de la couleur'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/collaborateurs', name: 'add_collaborateurs', methods: ['POST'])]
    public function addCollaborateurs(
        int $id,
        Request $request,
        AppelOffresPersonnelCleRepository $repository,
        CollaborateurRepository $collaborateurRepository,
        AppelOffresRepository $appelOffresRepository,
        AppelOffresPersonnelCleAppelOffresRepository $liaisonRepository,
        TokenStorageInterface $tokenStorage
    ): JsonResponse {
        try {
            $appelOffresPersonnelCle = $repository->find($id);

            if (!$appelOffresPersonnelCle) {
                return new JsonResponse(['message' => 'AppelOffresPersonnelCle not found'], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            // Quota check : si appelOffresId fourni
            $appelOffresId = $data['appelOffresId'] ?? null;
            $assignes = 0;
            $quota = 0;
            
            if ($appelOffresId) {
                $assignes = $liaisonRepository->countCollaborateurs((int)$appelOffresId, $appelOffresPersonnelCle->getAppelOffresPersonnelCleId());
                $quota = $appelOffresPersonnelCle->getAppelOffresPersonnelCleCollaborateursCount() ?? 0;
            }

            // Gérer plusieurs collaborateurs (tableau) ou un seul (rétrocompatibilité)
            $collaborateurIds = [];
            
            if (isset($data['collaborateurIds']) && is_array($data['collaborateurIds'])) {
                // Nouveau format : tableau d'IDs
                $collaborateurIds = array_map('intval', $data['collaborateurIds']);
            } elseif (isset($data['collaborateurId'])) {
                // Ancien format : un seul ID (rétrocompatibilité)
                $collaborateurIds = [$data['collaborateurId']];
            }

            if (empty($collaborateurIds)) {
                return new JsonResponse(['message' => 'Aucun collaborateur fourni'], Response::HTTP_BAD_REQUEST);
            }

            // Vérifier le quota avant d'ajouter
            if ($quota > 0) {
                $nouveauxCollaborateurs = count($collaborateurIds);
                $totalApresAjout = $assignes + $nouveauxCollaborateurs;
                
                if ($totalApresAjout > $quota) {
                    return new JsonResponse([
                        'message' => 'Quota dépassé',
                        'details' => "Quota: {$quota}, Actuellement affectés: {$assignes}, Tentative d'ajout: {$nouveauxCollaborateurs}"
                    ], Response::HTTP_BAD_REQUEST);
                }
            }

            // Ajouter les collaborateurs (sans supprimer les existants)
            $addedCount = 0;
            $skippedCount = 0;
            
            foreach ($collaborateurIds as $collaborateurId) {
                $collaborateur = $collaborateurRepository->find($collaborateurId);
                
                if (!$collaborateur) {
                    $skippedCount++;
                    continue;
                }

                // Vérifier si le collaborateur n'est pas déjà associé à ce personnel clé
                $dejaAssocie = $collaborateur->getAppelOffresPersonnelCle() === $appelOffresPersonnelCle;
                
                if (!$dejaAssocie) {
                    // Retirer le collaborateur de son ancien personnel clé si nécessaire
                    $ancienPersonnelCle = $collaborateur->getAppelOffresPersonnelCle();
                    if ($ancienPersonnelCle) {
                        $ancienPersonnelCle->removeCollaborateur($collaborateur);
                    }
                    
                    // Ajouter au nouveau personnel clé
                    $appelOffresPersonnelCle->addCollaborateur($collaborateur);
                    $addedCount++;
                } else {
                    $skippedCount++;
                }
            }

            $this->entityManager->flush();

            // Mettre à jour la couleur de la liaison si appelOffresId fourni
            if ($appelOffresId) {
                $appelOffres = $appelOffresRepository->find($appelOffresId);
                if ($appelOffres) {
                    $liaison = $liaisonRepository->findOneBy([
                        'appelOffres' => $appelOffres,
                        'appelOffresPersonnelCle' => $appelOffresPersonnelCle
                    ]);

                    if (!$liaison) {
                        $liaison = new AppelOffresPersonnelCleAppelOffres();
                        $liaison->setAppelOffres($appelOffres);
                        $liaison->setAppelOffresPersonnelCle($appelOffresPersonnelCle);
                        $this->entityManager->persist($liaison);
                    }

                    // Mettre à jour la couleur selon le nombre de collaborateurs
                    $totalCollaborateurs = count($appelOffresPersonnelCle->getCollaborateurs());
                    if ($totalCollaborateurs > 0) {
                        $liaison->setCouleurStatus('vert');
                    } else {
                        $liaison->setCouleurStatus('rouge');
                    }

                    $this->entityManager->flush();
                }
            }

            $message = "{$addedCount} collaborateur(s) ajouté(s)";
            if ($skippedCount > 0) {
                $message .= ", {$skippedCount} déjà associé(s) ou introuvable(s)";
            }

            return new JsonResponse([
                'message' => $message,
                'added' => $addedCount,
                'skipped' => $skippedCount
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'message' => 'Erreur lors de l\'ajout des collaborateurs'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}/collaborateurs/{collaborateurId}', name: 'remove_collaborateur', methods: ['DELETE'])]
    public function removeCollaborateur(
        int $id,
        int $collaborateurId,
        Request $request,
        AppelOffresPersonnelCleRepository $repository,
        CollaborateurRepository $collaborateurRepository,
        AppelOffresRepository $appelOffresRepository,
        AppelOffresPersonnelCleAppelOffresRepository $liaisonRepository,
        TokenStorageInterface $tokenStorage
    ): JsonResponse {
        try {
            $appelOffresPersonnelCle = $repository->find($id);

            if (!$appelOffresPersonnelCle) {
                return new JsonResponse(['message' => 'AppelOffresPersonnelCle not found'], Response::HTTP_NOT_FOUND);
            }

            $collaborateur = $collaborateurRepository->find($collaborateurId);

            if (!$collaborateur) {
                return new JsonResponse(['message' => 'Collaborateur not found'], Response::HTTP_NOT_FOUND);
            }

            // Vérifier que le collaborateur est bien associé à ce personnel clé
            if ($collaborateur->getAppelOffresPersonnelCle() !== $appelOffresPersonnelCle) {
                return new JsonResponse(['message' => 'Ce collaborateur n\'est pas associé à ce personnel clé'], Response::HTTP_BAD_REQUEST);
            }

            // Retirer le collaborateur
            $appelOffresPersonnelCle->removeCollaborateur($collaborateur);
            $this->entityManager->flush();

            // Mettre à jour la couleur de la liaison si appelOffresId fourni
            $appelOffresId = $request->query->get('appelOffresId');
            if ($appelOffresId) {
                $appelOffres = $appelOffresRepository->find($appelOffresId);
                if ($appelOffres) {
                    $liaison = $liaisonRepository->findOneBy([
                        'appelOffres' => $appelOffres,
                        'appelOffresPersonnelCle' => $appelOffresPersonnelCle
                    ]);

                    if ($liaison) {
                        $totalCollaborateurs = count($appelOffresPersonnelCle->getCollaborateurs());
                        if ($totalCollaborateurs > 0) {
                            $liaison->setCouleurStatus('vert');
                        } else {
                            $liaison->setCouleurStatus('rouge');
                        }
                        $this->entityManager->flush();
                    }
                }
            }

            return new JsonResponse(['message' => 'Collaborateur retiré avec succès'], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de la suppression du collaborateur'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/ordre/{appelOffresId}', name: 'update_ordre', methods: ['PUT'])]
    public function updateOrdre(
        int $appelOffresId,
        Request $request,
        AppelOffresRepository $appelOffresRepository,
        AppelOffresPersonnelCleAppelOffresRepository $liaisonRepository
    ): JsonResponse {
        try {
            $appelOffres = $appelOffresRepository->find($appelOffresId);
            if (!$appelOffres) {
                return new JsonResponse(['message' => 'AppelOffres not found'], Response::HTTP_NOT_FOUND);
            }

            $payload = json_decode($request->getContent(), true);
            if (!is_array($payload)) {
                return new JsonResponse(['message' => 'Payload invalide'], Response::HTTP_BAD_REQUEST);
            }

            // Payload attendu : [{ appelOffresPersonnelCleId: X, ordreAffichage: Y }, ...]
            foreach ($payload as $item) {
                if (!isset($item['appelOffresPersonnelCleId'])) {
                    continue;
                }
                $pcId = (int) $item['appelOffresPersonnelCleId'];
                $ordre = isset($item['ordreAffichage']) ? (int) $item['ordreAffichage'] : null;

                $liaison = $liaisonRepository->findOneBy([
                    'appelOffres' => $appelOffresId,
                    'appelOffresPersonnelCle' => $pcId
                ]);

                if ($liaison) {
                    $liaison->setOrdreAffichage($ordre);
                }
            }

            $this->entityManager->flush();

            return new JsonResponse(['message' => 'Ordre mis à jour'], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de la mise à jour de l\'ordre'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, AppelOffresPersonnelCleRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $appelOffresPersonnelCle = $repository->find($id);

            if (!$appelOffresPersonnelCle) {
                return new JsonResponse(['message' => 'AppelOffresPersonnelCle not found'], Response::HTTP_NOT_FOUND);
            }

            foreach ($appelOffresPersonnelCle->getCollaborateurs() as $collaborateur) {
                $collaborateur->setAppelOffresPersonnelCle(null);
            }
            $this->entityManager->remove($appelOffresPersonnelCle);
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'AppelOffresPersonnelCle deleted'], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de la suppression de l\'appel d\'offres personnel clé'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function checkToken(TokenStorageInterface $tokenStorage): void
    {
        $token = $tokenStorage->getToken();

        if (!$token instanceof TokenInterface) {
            throw new AccessDeniedHttpException('Token d\'authentification manquant ou invalide');
        }
    }
}