<?php

namespace App\Controller\Api;

use App\Entity\CollaborateurEducation;
use App\Entity\TypeDiplome;
use App\Repository\CollaborateurEducationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api', name: 'api_collaborateur_education_')]
class CollaborateurEducationController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    #[Route('/getAll/collaborateureducation', name: 'get_all_paginated', methods: ['GET'])]
    public function getAllPaginated(Request $request): JsonResponse
    {
        try {
            $page = max(1, (int) $request->query->get('page', 1));
            $limit = max(1, min(100, (int) $request->query->get('limit', 10)));
            $offset = ($page - 1) * $limit;

            $sortField = $request->query->get('sortField', 'collaborateurEducationId');
            $sortDir = $request->query->get('sortDir', 'ASC');
            $search = trim($request->query->get('search', ''));

            $allowedSortFields = [
                'collaborateurEducationId',
                'collaborateurEducationNatureEtudes',
                'collaborateurEducationEtablissement',
                'collaborateurEducationAnneeObtention'
            ];
            
            if (!in_array($sortField, $allowedSortFields)) {
                $sortField = 'collaborateurEducationId';
            }
            $sortDir = strtoupper($sortDir) === 'DESC' ? 'DESC' : 'ASC';

            $repo = $this->entityManager->getRepository(CollaborateurEducation::class);
            $qb = $repo->createQueryBuilder('ce');

            $qb->leftJoin('ce.typeDiplome', 'td');

            if (!empty($search)) {
                $qb->where(
                    $qb->expr()->orX(
                        $qb->expr()->like('ce.collaborateurEducationNatureEtudes', ':search'),
                        $qb->expr()->like('ce.collaborateurEducationEtablissement', ':search'),
                        $qb->expr()->like('ce.collaborateurEducationAnneeObtention', ':search'),
                        $qb->expr()->like('td.typeDiplomeLibelle', ':search')
                    )
                )
                ->setParameter('search', '%' . $search . '%');
            }

            $total = (int) (clone $qb)
                ->select('COUNT(ce)')
                ->getQuery()
                ->getSingleScalarResult();

            $qb->orderBy('ce.' . $sortField, $sortDir);

            $qb->setFirstResult($offset)
               ->setMaxResults($limit);

            $items = $qb->getQuery()->getResult();

            $data = [];
            foreach ($items as $education) {
                $typeDiplome = $education->getTypeDiplome();
                
                $data[] = [
                    'collaborateurEducationId' => $education->getCollaborateurEducationId(),
                    'collaborateurEducationNatureEtudes' => $education->getCollaborateurEducationNatureEtudes(),
                    'collaborateurEducationEtablissement' => $education->getCollaborateurEducationEtablissement(),
                    'collaborateurEducationAnneeObtention' => $education->getCollaborateurEducationAnneeObtention(),
                    'typeDiplomeId' => $typeDiplome ? $typeDiplome->getTypeDiplomeId() : null,
                    'typeDiplome' => $typeDiplome ? [
                        'typeDiplomeId' => $typeDiplome->getTypeDiplomeId(),
                        'typeDiplomeLibelle' => $typeDiplome->getTypeDiplomeLibelle(),
                    ] : null,
                ];
            }

            return new JsonResponse([
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'data' => $data
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            error_log('Erreur dans getAllPaginated: ' . $e->getMessage());
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de la récupération des données',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/collaborateureducation', name: 'get_all_collaborateur_education', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        try {
            $collaborateurEducationRepo = $this->entityManager->getRepository(CollaborateurEducation::class);
            $collaborateurEducations = $collaborateurEducationRepo->findAll();

            $data = [];
            foreach ($collaborateurEducations as $collaborateurEducation) {
                $typeDiplome = $collaborateurEducation->getTypeDiplome();
                
                $data[] = [
                    'collaborateurEducationId' => $collaborateurEducation->getCollaborateurEducationId(),
                    'collaborateurEducationNatureEtudes' => $collaborateurEducation->getCollaborateurEducationNatureEtudes(),
                    'collaborateurEducationEtablissement' => $collaborateurEducation->getCollaborateurEducationEtablissement(),
                    'collaborateurEducationAnneeObtention' => $collaborateurEducation->getCollaborateurEducationAnneeObtention(),
                    'typeDiplomeId' => $typeDiplome ? $typeDiplome->getTypeDiplomeId() : null,
                    'typeDiplome' => $typeDiplome ? [
                        'typeDiplomeId' => $typeDiplome->getTypeDiplomeId(),
                        'typeDiplomeLibelle' => $typeDiplome->getTypeDiplomeLibelle(),
                    ] : null,
                ];
            }

            return new JsonResponse($data, Response::HTTP_OK);
        } catch (\Exception $e) {
            error_log('Erreur dans getAll: ' . $e->getMessage());
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de la récupération des données',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/collaborateureducation/{id}', name: 'get_collaborateur_education', methods: ['GET'])]
    public function getById(CollaborateurEducation $collaborateurEducation): JsonResponse
    {
        try {
            $typeDiplome = $collaborateurEducation->getTypeDiplome();
            
            $data = [
                'collaborateurEducationId' => $collaborateurEducation->getCollaborateurEducationId(),
                'collaborateurEducationNatureEtudes' => $collaborateurEducation->getCollaborateurEducationNatureEtudes(),
                'collaborateurEducationEtablissement' => $collaborateurEducation->getCollaborateurEducationEtablissement(),
                'collaborateurEducationAnneeObtention' => $collaborateurEducation->getCollaborateurEducationAnneeObtention(),
                'typeDiplomeId' => $typeDiplome ? $typeDiplome->getTypeDiplomeId() : null,
                'typeDiplome' => $typeDiplome ? [
                    'typeDiplomeId' => $typeDiplome->getTypeDiplomeId(),
                    'typeDiplomeLibelle' => $typeDiplome->getTypeDiplomeLibelle(),
                ] : null,
            ];

            return new JsonResponse($data, Response::HTTP_OK);
        } catch (\Exception $e) {
            error_log('Erreur dans getById: ' . $e->getMessage());
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de la récupération des données',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/collaborateureducation', name: 'create_collaborateur_education', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['typeDiplomeId'])) {
                return new JsonResponse([
                    'error' => 'typeDiplomeId est requis'
                ], Response::HTTP_BAD_REQUEST);
            }

            $collaborateurEducation = new CollaborateurEducation();
            $collaborateurEducation->setCollaborateurEducationNatureEtudes($data['collaborateurEducationNatureEtudes'] ?? null)
                ->setCollaborateurEducationEtablissement($data['collaborateurEducationEtablissement'] ?? null)
                ->setCollaborateurEducationAnneeObtention(isset($data['collaborateurEducationAnneeObtention']) ? (int)$data['collaborateurEducationAnneeObtention'] : null);

            $typeDiplome = $this->entityManager->getRepository(TypeDiplome::class)->find($data['typeDiplomeId']);
            if (!$typeDiplome) {
                return new JsonResponse(['error' => 'TypeDiplome not found'], Response::HTTP_NOT_FOUND);
            }

            $collaborateurEducation->setTypeDiplome($typeDiplome);

            $this->entityManager->persist($collaborateurEducation);
            $this->entityManager->flush();

            return new JsonResponse([
                'message' => 'Collaborateur education created successfully',
                'collaborateurEducationId' => $collaborateurEducation->getCollaborateurEducationId()
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            error_log('Erreur dans create: ' . $e->getMessage());
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de la création',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/collaborateureducation/{id}', name: 'update_collaborateur_education', methods: ['PUT'])]
    public function update(int $id, Request $request, CollaborateurEducationRepository $collaborateurEducationRepository): JsonResponse
    {
        try {
            $collaborateurEducation = $collaborateurEducationRepository->find($id);

            if (!$collaborateurEducation) {
                return new JsonResponse(['message' => 'Collaborateur education not found'], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);
            $collaborateurEducation->setCollaborateurEducationNatureEtudes($data['collaborateurEducationNatureEtudes'] ?? $collaborateurEducation->getCollaborateurEducationNatureEtudes())
                ->setCollaborateurEducationEtablissement($data['collaborateurEducationEtablissement'] ?? $collaborateurEducation->getCollaborateurEducationEtablissement())
                ->setCollaborateurEducationAnneeObtention(isset($data['collaborateurEducationAnneeObtention']) ? (int)$data['collaborateurEducationAnneeObtention'] : $collaborateurEducation->getCollaborateurEducationAnneeObtention());

            if (isset($data['typeDiplomeId'])) {
                $typeDiplome = $this->entityManager->getRepository(TypeDiplome::class)->find($data['typeDiplomeId']);
                if (!$typeDiplome) {
                    return new JsonResponse(['error' => 'TypeDiplome not found'], Response::HTTP_NOT_FOUND);
                }
                $collaborateurEducation->setTypeDiplome($typeDiplome);
            }

            $this->entityManager->flush();

            return new JsonResponse(['message' => 'Collaborateur education updated successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            error_log('Erreur dans update: ' . $e->getMessage());
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de la mise à jour',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/collaborateureducation/{id}', name: 'delete_collaborateur_education', methods: ['DELETE'])]
    public function delete(int $id, CollaborateurEducationRepository $collaborateurEducationRepository): JsonResponse
    {
        try {
            $collaborateurEducation = $collaborateurEducationRepository->find($id);

            if (!$collaborateurEducation) {
                return new JsonResponse(['message' => 'Collaborateur education not found'], Response::HTTP_NOT_FOUND);
            }

            // Doctrine gère automatiquement la suppression des relations ManyToMany
            // Pas besoin de supprimer manuellement les relations avec les collaborateurs
            $this->entityManager->remove($collaborateurEducation);
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'Collaborateur education deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            error_log('Erreur dans delete: ' . $e->getMessage());
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de la suppression',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}