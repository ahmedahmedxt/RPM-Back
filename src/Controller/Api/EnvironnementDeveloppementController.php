<?php

namespace App\Controller\Api;

use App\Entity\EnvironnementDeveloppement;
use App\Repository\EnvironnementDeveloppementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

#[Route('/api', name: 'api_environnementdeveloppement_')]
class EnvironnementDeveloppementController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/getAll/environnementsdeveloppement', name: 'get_all_paginated', methods: ['GET'])]
    public function getAllPaginated(Request $request, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $page = max(1, (int) $request->query->get('page', 1));
            $limit = max(1, min(100, (int) $request->query->get('limit', 10)));
            $offset = ($page - 1) * $limit;

            $sortField = $request->query->get('sortField', 'environnementDeveloppementLibelle');
            $sortDir = $request->query->get('sortDir', 'ASC');
            $search = trim($request->query->get('search', ''));

            $allowedSortFields = ['environnementDeveloppementLibelle', 'environnementDeveloppementDescription', 'environnementDeveloppementId'];
            if (!in_array($sortField, $allowedSortFields)) {
                $sortField = 'environnementDeveloppementLibelle';
            }
            $sortDir = strtoupper($sortDir) === 'DESC' ? 'DESC' : 'ASC';

            $repo = $this->entityManager->getRepository(EnvironnementDeveloppement::class);
            $qb = $repo->createQueryBuilder('e');

            if (!empty($search)) {
                $qb->where('e.environnementDeveloppementLibelle LIKE :search OR e.environnementDeveloppementDescription LIKE :search')
                   ->setParameter('search', '%' . $search . '%');
            }

            $total = (int) (clone $qb)
                ->select('COUNT(e)')
                ->getQuery()
                ->getSingleScalarResult();

            $qb->orderBy('e.' . $sortField, $sortDir);

            $qb->setFirstResult($offset)
               ->setMaxResults($limit);

            $items = $qb->getQuery()->getResult();

            $data = [];
            foreach ($items as $environnement) {
                $data[] = [
                    'environnementDeveloppementId' => $environnement->getEnvironnementDeveloppementId(),
                    'environnementDeveloppementLibelle' => $environnement->getEnvironnementDeveloppementLibelle() ?? '',
                    'environnementDeveloppementDescription' => $environnement->getEnvironnementDeveloppementDescription() ?? '',
                ];
            }

            return new JsonResponse([
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'data' => $data
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'page' => 1,
                'limit' => 10,
                'total' => 0,
                'data' => []
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/environnementdeveloppement', name: 'get_all_legacy', methods: ['GET'])]
    public function getAll(EnvironnementDeveloppementRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $env = $repository->findAll();
            
            $data = [];
            foreach ($env as $environnement) {
                $data[] = [
                    'environnementDeveloppementId' => $environnement->getEnvironnementDeveloppementId(),
                    'environnementDeveloppementLibelle' => $environnement->getEnvironnementDeveloppementLibelle() ?? '',
                    'environnementDeveloppementDescription' => $environnement->getEnvironnementDeveloppementDescription() ?? '',
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

    #[Route('/environnementdeveloppement/{id}', name: 'get_by_id', methods: ['GET'])]
    public function getById(int $id, EnvironnementDeveloppementRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $environnement = $repository->find($id);

            if (!$environnement) {
                return new JsonResponse(['message' => 'EnvironnementDeveloppement not found'], Response::HTTP_NOT_FOUND);
            }

            $data = [
                'environnementDeveloppementId' => $environnement->getEnvironnementDeveloppementId(),
                'environnementDeveloppementLibelle' => $environnement->getEnvironnementDeveloppementLibelle() ?? '',
                'environnementDeveloppementDescription' => $environnement->getEnvironnementDeveloppementDescription() ?? '',
            ];

            return new JsonResponse($data, Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de la récupération de l\'environnement de développement'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/environnementdeveloppement', name: 'create', methods: ['POST'])]
    public function create(Request $request, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $environnement = new EnvironnementDeveloppement();
            $environnement->setEnvironnementDeveloppementLibelle($data['environnementDeveloppementLibelle'] ?? null);
            $environnement->setEnvironnementDeveloppementDescription($data['environnementDeveloppementDescription'] ?? null);

            $this->entityManager->persist($environnement);
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'EnvironnementDeveloppement created'], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de la création de l\'environnement de développement'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/environnementdeveloppement/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request, EnvironnementDeveloppementRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $environnement = $repository->find($id);

            if (!$environnement) {
                return new JsonResponse(['message' => 'EnvironnementDeveloppement not found'], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            $environnement->setEnvironnementDeveloppementLibelle($data['environnementDeveloppementLibelle'] ?? $environnement->getEnvironnementDeveloppementLibelle());
            $environnement->setEnvironnementDeveloppementDescription($data['environnementDeveloppementDescription'] ?? $environnement->getEnvironnementDeveloppementDescription());

            $this->entityManager->flush();

            return new JsonResponse(['message' => 'EnvironnementDeveloppement updated'], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de la mise à jour de l\'environnement de développement'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/environnementdeveloppement/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, EnvironnementDeveloppementRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $environnement = $repository->find($id);

            if (!$environnement) {
                return new JsonResponse(['message' => 'EnvironnementDeveloppement not found'], Response::HTTP_NOT_FOUND);
            }

            $this->entityManager->remove($environnement);
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'EnvironnementDeveloppement deleted'], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de la suppression de l\'environnement de développement'
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