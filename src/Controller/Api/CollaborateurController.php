<?php

namespace App\Controller\Api;

use App\Entity\Collaborateur;
use App\Entity\CollaborateurEducation;
use App\Entity\TypeDiplome;
use App\Repository\CollaborateurRepository;
use App\Repository\PaysRepository;
use App\Repository\NationaliteRepository;
use App\Repository\TypeDiplomeRepository;
use App\Repository\CollaborateurEducationRepository;
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

#[Route('/api/collaborateur', name: 'api_collaborateur')]
class CollaborateurController extends AbstractController
{
    private $entityManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(CollaborateurRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $this->checkToken($tokenStorage);
        
        $collaborateurs = $repository->findAll();

        $data = array_map(function($collaborateur) {
            $appelOffresPersonnelCle = $collaborateur->getAppelOffresPersonnelCle();
            
            $educations = [];
            foreach ($collaborateur->getEducations() as $education) {
                $educations[] = [
                    'collaborateurEducationId' => $education->getCollaborateurEducationId(),
                    'collaborateurEducationNatureEtudes' => $education->getCollaborateurEducationNatureEtudes(),
                    'collaborateurEducationEtablissement' => $education->getCollaborateurEducationEtablissement(),
                    'collaborateurEducationAnneeObtention' => $education->getCollaborateurEducationAnneeObtention(),
                    'typeDiplomeId' => $education->getTypeDiplome()?->getTypeDiplomeId(),
                    'typeDiplomeLibelle' => $education->getTypeDiplome()?->getTypeDiplomeLibelle(),
                ];
            }
            
            return [
                'collaborateurId' => $collaborateur->getCollaborateurId(),
                'collaborateurNom' => $collaborateur->getCollaborateurNom(),
                'collaborateurPrenom' => $collaborateur->getCollaborateurPrenom(),
                'collaborateurAdresse' => $collaborateur->getCollaborateurAdresse(),
                'collaborateurEmail1' => $collaborateur->getCollaborateurEmail1(),
                'collaborateurTelephone1' => $collaborateur->getCollaborateurTelephone1(),
                'pays' => $collaborateur->getPays() ? $collaborateur->getPays()->getPaysLibelle() : null,
                'nationalite' => $collaborateur->getNationalite() ? $collaborateur->getNationalite()->getNationaliteLibelle() : null,
                'appelOffresPersonnelCleId' => $appelOffresPersonnelCle ? $appelOffresPersonnelCle->getAppelOffresPersonnelCleId() : null,
                'appelOffresPersonnelCleIntitule' => $appelOffresPersonnelCle ? $appelOffresPersonnelCle->getAppelOffresPersonnelCleIntitule() : null,
                'educations' => $educations
            ];
        }, $collaborateurs);

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_by_id', methods: ['GET'])]
    public function getById(int $id, CollaborateurRepository $collaborateurRepository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $this->checkToken($tokenStorage);
        
        $collaborateur = $collaborateurRepository->find($id);

        if (!$collaborateur) {
            return new JsonResponse(['message' => 'Collaborateur not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->serializeCollaborateur($collaborateur), Response::HTTP_OK);
    }

    #[Route('/details/{id}', name: 'get_details_by_id', methods: ['GET'])]
    public function getCollaborateurDetails(int $id, CollaborateurRepository $collaborateurRepository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $this->checkToken($tokenStorage);
        
        $collaborateur = $collaborateurRepository->find($id);

        if (!$collaborateur) {
            return new JsonResponse(['message' => 'Collaborateur not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->serializeCollaborateurDetails($collaborateur), Response::HTTP_OK);
    }

    private function serializeCollaborateurDetails(Collaborateur $collaborateur): array
    {
        $appelOffresPersonnelCle = $collaborateur->getAppelOffresPersonnelCle();
        
        $educations = [];
        foreach ($collaborateur->getEducations() as $education) {
            $educations[] = [
                'collaborateurEducationId' => $education->getCollaborateurEducationId(),
                'collaborateurEducationNatureEtudes' => $education->getCollaborateurEducationNatureEtudes(),
                'collaborateurEducationEtablissement' => $education->getCollaborateurEducationEtablissement(),
                'collaborateurEducationAnneeObtention' => $education->getCollaborateurEducationAnneeObtention(),
                'typeDiplomeId' => $education->getTypeDiplome()?->getTypeDiplomeId(),
                'typeDiplomeLibelle' => $education->getTypeDiplome()?->getTypeDiplomeLibelle(),
            ];
        }
        
        return [
            'collaborateurId' => $collaborateur->getCollaborateurId(),
            'collaborateurNom' => $collaborateur->getCollaborateurNom(),
            'collaborateurPrenom' => $collaborateur->getCollaborateurPrenom(),
            'collaborateurAdresse' => $collaborateur->getCollaborateurAdresse(),
            'collaborateurLieuNaissance' => $collaborateur->getCollaborateurLieuNaissance(),
            'collaborateurDateNaissance' => $collaborateur->getCollaborateurDateNaissance() ? $collaborateur->getCollaborateurDateNaissance()->format('Y-m-d') : null,
            'collaborateurEmail1' => $collaborateur->getCollaborateurEmail1(),
            'collaborateurEmail2' => $collaborateur->getCollaborateurEmail2(),
            'collaborateurTelephone1' => $collaborateur->getCollaborateurTelephone1(),
            'collaborateurTelephone2' => $collaborateur->getCollaborateurTelephone2(),
            'collaborateurCV' => $collaborateur->getCollaborateurCV(),
            'pays' => $collaborateur->getPays() ? [
                'paysId' => $collaborateur->getPays()->getPaysId(),
                'paysLibelle' => $collaborateur->getPays()->getPaysLibelle()
            ] : null,
            'nationalite' => $collaborateur->getNationalite() ? [
                'id' => $collaborateur->getNationalite()->getId(),
                'nationaliteLibelle' => $collaborateur->getNationalite()->getNationaliteLibelle()
            ] : null,
            'appelOffresPersonnelCleId' => $appelOffresPersonnelCle ? $appelOffresPersonnelCle->getAppelOffresPersonnelCleId() : null,
            'appelOffresPersonnelCleIntitule' => $appelOffresPersonnelCle ? $appelOffresPersonnelCle->getAppelOffresPersonnelCleIntitule() : null,
            'educations' => $educations
        ];
    }

    private function serializeCollaborateur(Collaborateur $collaborateur): array
    {
        $appelOffresPersonnelCle = $collaborateur->getAppelOffresPersonnelCle();
        
        $educations = [];
        foreach ($collaborateur->getEducations() as $education) {
            $educations[] = [
                'collaborateurEducationId' => $education->getCollaborateurEducationId(),
                'collaborateurEducationNatureEtudes' => $education->getCollaborateurEducationNatureEtudes(),
                'collaborateurEducationEtablissement' => $education->getCollaborateurEducationEtablissement(),
                'collaborateurEducationAnneeObtention' => $education->getCollaborateurEducationAnneeObtention(),
                'typeDiplomeId' => $education->getTypeDiplome()?->getTypeDiplomeId(),
                'typeDiplomeLibelle' => $education->getTypeDiplome()?->getTypeDiplomeLibelle(),
            ];
        }
        
        return [
            'collaborateurId' => $collaborateur->getCollaborateurId(),
            'collaborateurNom' => $collaborateur->getCollaborateurNom(),
            'collaborateurPrenom' => $collaborateur->getCollaborateurPrenom(),
            'collaborateurAdresse' => $collaborateur->getCollaborateurAdresse(),
            'collaborateurLieuNaissance' => $collaborateur->getCollaborateurLieuNaissance(),
            'collaborateurDateNaissance' => $collaborateur->getCollaborateurDateNaissance() ? $collaborateur->getCollaborateurDateNaissance()->format('Y-m-d') : null,
            'collaborateurEmail1' => $collaborateur->getCollaborateurEmail1(),
            'collaborateurEmail2' => $collaborateur->getCollaborateurEmail2(),
            'collaborateurTelephone1' => $collaborateur->getCollaborateurTelephone1(),
            'collaborateurTelephone2' => $collaborateur->getCollaborateurTelephone2(),
            'collaborateurCV' => $collaborateur->getCollaborateurCV(),
            'paysId' => $collaborateur->getPays() ? $collaborateur->getPays()->getPaysId() : null,
            'nationaliteId' => $collaborateur->getNationalite() ? $collaborateur->getNationalite()->getId() : null,
            'appelOffresPersonnelCleId' => $appelOffresPersonnelCle ? $appelOffresPersonnelCle->getAppelOffresPersonnelCleId() : null,
            'educations' => $educations
        ];
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request, 
        EntityManagerInterface $entityManager, 
        PaysRepository $paysRepository, 
        NationaliteRepository $nationaliteRepository,
        CollaborateurEducationRepository $collaborateurEducationRepository,
        TokenStorageInterface $tokenStorage
    ): JsonResponse {
        $this->checkToken($tokenStorage);
        
        $data = json_decode($request->getContent(), true);

        $collaborateur = new Collaborateur();
        $collaborateur->setCollaborateurNom($data['collaborateurNom'] ?? null);
        $collaborateur->setCollaborateurPrenom($data['collaborateurPrenom'] ?? null);
        if (isset($data['collaborateurDateNaissance'])) {
            $collaborateur->setCollaborateurDateNaissance(new \DateTime($data['collaborateurDateNaissance']));
        }
        $collaborateur->setCollaborateurAdresse($data['collaborateurAdresse'] ?? null);
        $collaborateur->setCollaborateurLieuNaissance($data['collaborateurLieuNaissance'] ?? null);
        $collaborateur->setCollaborateurEmail1($data['collaborateurEmail1'] ?? null);
        $collaborateur->setCollaborateurEmail2($data['collaborateurEmail2'] ?? null);
        $collaborateur->setCollaborateurTelephone1($data['collaborateurTelephone1'] ?? null);
        $collaborateur->setCollaborateurTelephone2($data['collaborateurTelephone2'] ?? null);
        $collaborateur->setCollaborateurCV($data['collaborateurCV'] ?? null);

        if (isset($data['paysId'])) {
            $pays = $paysRepository->find($data['paysId']);
            if ($pays) {
                $collaborateur->setPays($pays);
            } else {
                return new JsonResponse(['message' => 'Invalid Pays ID'], Response::HTTP_BAD_REQUEST);
            }
        }

        if (isset($data['nationaliteId'])) {
            $nationalite = $nationaliteRepository->find($data['nationaliteId']);
            if ($nationalite) {
                $collaborateur->setNationalite($nationalite);
            } else {
                return new JsonResponse(['message' => 'Invalid Nationalite ID'], Response::HTTP_BAD_REQUEST);
            }
        }

        if (isset($data['educations']) && is_array($data['educations'])) {
            foreach ($data['educations'] as $educationData) {
                if (isset($educationData['collaborateurEducationId'])) {
                    $education = $collaborateurEducationRepository->find($educationData['collaborateurEducationId']);
                    if ($education) {
                        $collaborateur->addEducation($education);
                    }
                }
            }
        }

        $entityManager->persist($collaborateur);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Collaborateur created', 'id' => $collaborateur->getCollaborateurId()], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(
        int $id, 
        Request $request, 
        CollaborateurRepository $collaborateurRepository, 
        EntityManagerInterface $entityManager, 
        PaysRepository $paysRepository, 
        NationaliteRepository $nationaliteRepository,
        CollaborateurEducationRepository $collaborateurEducationRepository,
        TokenStorageInterface $tokenStorage
    ): JsonResponse {
        $this->checkToken($tokenStorage);
        
        $collaborateur = $collaborateurRepository->find($id);

        if (!$collaborateur) {
            return new JsonResponse(['message' => 'Collaborateur not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $collaborateur->setCollaborateurNom($data['collaborateurNom'] ?? $collaborateur->getCollaborateurNom());
        $collaborateur->setCollaborateurPrenom($data['collaborateurPrenom'] ?? $collaborateur->getCollaborateurPrenom());
        if (isset($data['collaborateurDateNaissance'])) {
            $collaborateur->setCollaborateurDateNaissance(new \DateTime($data['collaborateurDateNaissance']));
        }
        $collaborateur->setCollaborateurAdresse($data['collaborateurAdresse'] ?? $collaborateur->getCollaborateurAdresse());
        $collaborateur->setCollaborateurLieuNaissance($data['collaborateurLieuNaissance'] ?? $collaborateur->getCollaborateurLieuNaissance());
        $collaborateur->setCollaborateurEmail1($data['collaborateurEmail1'] ?? $collaborateur->getCollaborateurEmail1());
        $collaborateur->setCollaborateurEmail2($data['collaborateurEmail2'] ?? $collaborateur->getCollaborateurEmail2());
        $collaborateur->setCollaborateurTelephone1($data['collaborateurTelephone1'] ?? $collaborateur->getCollaborateurTelephone1());
        $collaborateur->setCollaborateurTelephone2($data['collaborateurTelephone2'] ?? $collaborateur->getCollaborateurTelephone2());
        $collaborateur->setCollaborateurCV($data['collaborateurCV'] ?? $collaborateur->getCollaborateurCV());

        if (isset($data['paysId'])) {
            $pays = $paysRepository->find($data['paysId']);
            if ($pays) {
                $collaborateur->setPays($pays);
            } else {
                return new JsonResponse(['message' => 'Invalid Pays ID'], Response::HTTP_BAD_REQUEST);
            }
        }

        if (isset($data['nationaliteId'])) {
            $nationalite = $nationaliteRepository->find($data['nationaliteId']);
            if ($nationalite) {
                $collaborateur->setNationalite($nationalite);
            } else {
                return new JsonResponse(['message' => 'Invalid Nationalite ID'], Response::HTTP_BAD_REQUEST);
            }
        }

        if (isset($data['educations']) && is_array($data['educations'])) {
            $collaborateur->getEducations()->clear();
            
            foreach ($data['educations'] as $educationData) {
                if (isset($educationData['collaborateurEducationId'])) {
                    $education = $collaborateurEducationRepository->find($educationData['collaborateurEducationId']);
                    if ($education) {
                        $collaborateur->addEducation($education);
                    }
                }
            }
        }

        $entityManager->flush();

        return new JsonResponse(['message' => 'Collaborateur updated'], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, CollaborateurRepository $collaborateurRepository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $this->checkToken($tokenStorage);
        
        $collaborateur = $collaborateurRepository->find($id);

        if (!$collaborateur) {
            return new JsonResponse(['message' => 'Collaborateur not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($collaborateur);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Collaborateur deleted'], Response::HTTP_OK);
    }

    public function checkToken(TokenStorageInterface $tokenStorage): void
    {
        $token = $tokenStorage->getToken();

        if (!$token instanceof TokenInterface) {
            throw new AccessDeniedHttpException('Token d\'authentification manquant ou invalide');
        }
    }
}