<?php

namespace App\Controller\Api;

use App\Entity\BailleurFond;
use App\Entity\Role;
use App\Repository\BailleurFondRepository;
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
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/role', name: 'api_role_')]
class RoleController extends AbstractController
{
    private $entityManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(RoleRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $roles = $repository->findAll();
        $data = $this->serializer->serialize($roles, 'json');

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'get_by_id', methods: ['GET'])]
    public function getById(int $id, RoleRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $role = $repository->find($id);

        if (!$role) {
            return new JsonResponse(['message' => 'Role not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->serializer->serialize($role, 'json');

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true);

        $role = new Role();
        $role->setRoleLibelle($data['roleLibelle'] ?? null);
        $role->setRoleShort($data['roleShort'] ?? null);

        $this->entityManager->persist($role);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Role created'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request, RoleRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $role = $repository->find($id);

        if (!$role) {
            return new JsonResponse(['message' => 'Role not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $role->setRoleLibelle($data['roleLibelle'] ?? $role->getRoleLibelle());
        $role->setRoleShort($data['roleShort'] ?? $role->getRoleShort());

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Role updated'], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, RoleRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $role = $repository->find($id);

        if (!$role) {
            return new JsonResponse(['message' => 'Role not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($role);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Role deleted'], Response::HTTP_OK);
    }

    public function checkToken(TokenStorageInterface $tokenStorage): void
    {
        // Récupérer le token d'authentification de Symfony
        $token = $tokenStorage->getToken();

        // Vérifier si le token d'authentification est présent et est de type TokenInterface
        if (!$token instanceof TokenInterface) {
            throw new AccessDeniedHttpException('Token d\'authentification manquant ou invalide');
        }
    }
}
