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
    public function getAll(AppelOffresPersonnelCleRepository $repository, Request $request, AppelOffresRepository $appelOffresRepository, AppelOffresPersonnelCleAppelOffresRepository $liaisonRepository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $appelOffresId = $request->query->get('appelOffresId');
            
            $appelOffresPersonnelCles = $repository->findAll();
            
            $data = [];
            foreach ($appelOffresPersonnelCles as $appelOffresPersonnelCle) {
                $niveauEtude = $appelOffresPersonnelCle->getNiveauEtude();
                $collaborateurs = $appelOffresPersonnelCle->getCollaborateurs();
                
                $couleurStatus = null;
                $appelOffres = null;
                
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
                        }
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
                    'appelOffresId' => $appelOffres ? $appelOffres->getAppelOffresId() : null,
                    'appelOffresObjet' => $appelOffres ? $appelOffres->getAppelOffresObjet() : null,
                    'niveauEtudeId' => $niveauEtude ? $niveauEtude->getNiveauEtudeId() : null,
                    'niveauEtudeLibelle' => $niveauEtude ? $niveauEtude->getNiveauEtudeLibelle() : null,
                    'collaborateurs' => $collaborateursData
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
    public function getByAppelOffres(int $appelOffresId, AppelOffresPersonnelCleRepository $repository, AppelOffresRepository $appelOffresRepository, AppelOffresPersonnelCleAppelOffresRepository $liaisonRepository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $appelOffres = $appelOffresRepository->find($appelOffresId);
            
            if (!$appelOffres) {
                return new JsonResponse(['message' => 'AppelOffres not found'], Response::HTTP_NOT_FOUND);
            }

            $appelOffresPersonnelCles = $repository->findAll();
            
            $data = [];
            foreach ($appelOffresPersonnelCles as $appelOffresPersonnelCle) {
                $niveauEtude = $appelOffresPersonnelCle->getNiveauEtude();
                $collaborateurs = $appelOffresPersonnelCle->getCollaborateurs();
                
                $liaison = $liaisonRepository->findOneBy([
                    'appelOffres' => $appelOffres,
                    'appelOffresPersonnelCle' => $appelOffresPersonnelCle
                ]);
                
                $couleurStatus = $liaison ? $liaison->getCouleurStatus() : null;
                
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
                    'appelOffresId' => $appelOffres->getAppelOffresId(),
                    'appelOffresObjet' => $appelOffres->getAppelOffresObjet(),
                    'niveauEtudeId' => $niveauEtude ? $niveauEtude->getNiveauEtudeId() : null,
                    'niveauEtudeLibelle' => $niveauEtude ? $niveauEtude->getNiveauEtudeLibelle() : null,
                    'collaborateurs' => $collaborateursData
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
                    'collaborateurTelephone1' => $collaborateur->getCollaborateurTelephone1()
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
                'collaborateurs' => $collaborateursData
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
    public function create(Request $request, AppelOffresRepository $appelOffresRepository, NiveauEtudeRepository $niveauEtudeRepository, CollaborateurRepository $collaborateurRepository, AppelOffresPersonnelCleAppelOffresRepository $liaisonRepository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $appelOffresPersonnelCle = new AppelOffresPersonnelCle();
            $appelOffresPersonnelCle->setAppelOffresPersonnelCleIntitule($data['appelOffresPersonnelCleIntitule'] ?? null);
            $appelOffresPersonnelCle->setAppelOffresPersonnelCleDescription($data['appelOffresPersonnelCleDescription'] ?? null);
            $appelOffresPersonnelCle->setAppelOffresPersonnelCleNiveauEtudeMin($data['appelOffresPersonnelCleNiveauEtudeMin'] ?? null);
            $appelOffresPersonnelCle->setAppelOffresPersonnelCleNbrAnneeExperience($data['appelOffresPersonnelCleNbrAnneeExperience'] ?? null);

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
                    $this->entityManager->persist($liaison);
                    $this->entityManager->flush();
                }
            }

            if (isset($data['collaborateurIds']) && is_array($data['collaborateurIds'])) {
                foreach ($data['collaborateurIds'] as $collaborateurId) {
                    $collaborateur = $collaborateurRepository->find($collaborateurId);
                    if ($collaborateur) {
                        $appelOffresPersonnelCle->addCollaborateur($collaborateur);
                    }
                }
                $this->entityManager->flush();
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
    public function update(int $id, Request $request, AppelOffresPersonnelCleRepository $repository, AppelOffresRepository $appelOffresRepository, NiveauEtudeRepository $niveauEtudeRepository, CollaborateurRepository $collaborateurRepository, AppelOffresPersonnelCleAppelOffresRepository $liaisonRepository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $appelOffresPersonnelCle = $repository->find($id);

            if (!$appelOffresPersonnelCle) {
                return new JsonResponse(['message' => 'AppelOffresPersonnelCle not found'], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            $appelOffresPersonnelCle->setAppelOffresPersonnelCleIntitule($data['appelOffresPersonnelCleIntitule'] ?? $appelOffresPersonnelCle->getAppelOffresPersonnelCleIntitule());
            $appelOffresPersonnelCle->setAppelOffresPersonnelCleDescription($data['appelOffresPersonnelCleDescription'] ?? $appelOffresPersonnelCle->getAppelOffresPersonnelCleDescription());
            $appelOffresPersonnelCle->setAppelOffresPersonnelCleNiveauEtudeMin($data['appelOffresPersonnelCleNiveauEtudeMin'] ?? $appelOffresPersonnelCle->getAppelOffresPersonnelCleNiveauEtudeMin());
            $appelOffresPersonnelCle->setAppelOffresPersonnelCleNbrAnneeExperience($data['appelOffresPersonnelCleNbrAnneeExperience'] ?? $appelOffresPersonnelCle->getAppelOffresPersonnelCleNbrAnneeExperience());

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

            if (isset($data['collaborateurIds']) && is_array($data['collaborateurIds'])) {
                foreach ($appelOffresPersonnelCle->getCollaborateurs() as $existingCollaborateur) {
                    $appelOffresPersonnelCle->removeCollaborateur($existingCollaborateur);
                }
                
                foreach ($data['collaborateurIds'] as $collaborateurId) {
                    $collaborateur = $collaborateurRepository->find($collaborateurId);
                    if ($collaborateur) {
                        $appelOffresPersonnelCle->addCollaborateur($collaborateur);
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
    public function updateCouleur(int $id, Request $request, AppelOffresPersonnelCleRepository $repository, AppelOffresRepository $appelOffresRepository, AppelOffresPersonnelCleAppelOffresRepository $liaisonRepository, TokenStorageInterface $tokenStorage): JsonResponse
    {
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
    public function addCollaborateurs(int $id, Request $request, AppelOffresPersonnelCleRepository $repository, CollaborateurRepository $collaborateurRepository, AppelOffresRepository $appelOffresRepository, AppelOffresPersonnelCleAppelOffresRepository $liaisonRepository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $appelOffresPersonnelCle = $repository->find($id);

            if (!$appelOffresPersonnelCle) {
                return new JsonResponse(['message' => 'AppelOffresPersonnelCle not found'], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            if (isset($data['collaborateurIds']) && is_array($data['collaborateurIds'])) {
                foreach ($data['collaborateurIds'] as $collaborateurId) {
                    $collaborateur = $collaborateurRepository->find($collaborateurId);
                    if ($collaborateur && !$appelOffresPersonnelCle->getCollaborateurs()->contains($collaborateur)) {
                        $appelOffresPersonnelCle->addCollaborateur($collaborateur);
                    }
                }
                $this->entityManager->flush();
            }

            if (isset($data['appelOffresId']) && $data['appelOffresId'] !== null) {
                $appelOffres = $appelOffresRepository->find($data['appelOffresId']);
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

                    if (count($appelOffresPersonnelCle->getCollaborateurs()) > 0) {
                        $liaison->setCouleurStatus('vert');
                    }
                    
                    $this->entityManager->flush();
                }
            }

            return new JsonResponse(['message' => 'Collaborateurs added'], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de l\'ajout des collaborateurs'
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