<?php

namespace App\Controller\Api;

use App\Entity\Role;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

#[Route('/api', name: 'api_role_')]
class RoleController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/getAll/roles', name: 'get_all_roles', methods: ['GET'])]
    public function getAllRoles(RoleRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $roles = $repository->findAll();
            
            $data = [];
            foreach ($roles as $role) {
                $data[] = [
                    'roleId' => $role->getRoleId(),
                    'roleLibelle' => $role->getRoleLibelle() ?? '',
                    'roleShort' => $role->getRoleShort() ?? '',
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

    #[Route('/role', name: 'get_all', methods: ['GET'])]
    public function getAll(RoleRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $roles = $repository->findAll();
            
            $data = [];
            foreach ($roles as $role) {
                $data[] = [
                    'roleId' => $role->getRoleId(),
                    'roleLibelle' => $role->getRoleLibelle() ?? '',
                    'roleShort' => $role->getRoleShort() ?? '',
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

    #[Route('/role/{id}', name: 'get_by_id', methods: ['GET'])]
    public function getById(int $id, RoleRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $role = $repository->find($id);

            if (!$role) {
                return new JsonResponse(['message' => 'Role not found'], Response::HTTP_NOT_FOUND);
            }

            $data = [
                'roleId' => $role->getRoleId(),
                'roleLibelle' => $role->getRoleLibelle() ?? '',
                'roleShort' => $role->getRoleShort() ?? '',
            ];

            return new JsonResponse($data, Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de la récupération du rôle'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/role', name: 'create', methods: ['POST'])]
    public function create(Request $request, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $role = new Role();
            $role->setRoleLibelle($data['roleLibelle'] ?? null);
            $role->setRoleShort($data['roleShort'] ?? null);

            $this->entityManager->persist($role);
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'Role created'], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de la création du rôle'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/role/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request, RoleRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $role = $repository->find($id);

            if (!$role) {
                return new JsonResponse(['message' => 'Role not found'], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            $role->setRoleLibelle($data['roleLibelle'] ?? $role->getRoleLibelle());
            $role->setRoleShort($data['roleShort'] ?? $role->getRoleShort());

            $this->entityManager->flush();

            return new JsonResponse(['message' => 'Role updated'], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de la mise à jour du rôle'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/role/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, RoleRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $role = $repository->find($id);

            if (!$role) {
                return new JsonResponse(['message' => 'Role not found'], Response::HTTP_NOT_FOUND);
            }

            $this->entityManager->remove($role);
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'Role deleted'], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de la suppression du rôle'
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