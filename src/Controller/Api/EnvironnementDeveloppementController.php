<?php

namespace App\Controller\Api;

use App\Entity\EnvironnementDeveloppement;
use App\Repository\EnvironnementDeveloppementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/environnementdeveloppement', name: 'api_environnementdeveloppement_')]
class EnvironnementDeveloppementController extends AbstractController
{
    private $entityManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(EnvironnementDeveloppementRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $env = $repository->findAll();
        $data = $this->serializer->serialize($env, 'json');

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'get_by_id', methods: ['GET'])]
    public function getById(int $id, EnvironnementDeveloppementRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $environnement = $repository->find($id);

        if (!$environnement) {
            return new JsonResponse(['message' => 'EnvironnementDeveloppement not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->serializer->serialize($environnement, 'json');

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true);

        $environnement = new EnvironnementDeveloppement();
        $environnement->setEnvironnementDeveloppementLibelle($data['environnementDeveloppementLibelle'] ?? null);
        $environnement->setEnvironnementDeveloppementDescription($data['environnementDeveloppementDescription'] ?? null);

        $this->entityManager->persist($environnement);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'EnvironnementDeveloppement created'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request, EnvironnementDeveloppementRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $environnement = $repository->find($id);

        if (!$environnement) {
            return new JsonResponse(['message' => 'EnvironnementDeveloppement not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $environnement->setEnvironnementDeveloppementLibelle($data['environnementDeveloppementLibelle'] ?? $environnement->getEnvironnementDeveloppementLibelle());
        $environnement->setEnvironnementDeveloppementDescription($data['environnementDeveloppementDescription'] ?? $environnement->getEnvironnementDeveloppementDescription());

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'EnvironnementDeveloppement updated'], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, EnvironnementDeveloppementRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $environnement = $repository->find($id);

        if (!$environnement) {
            return new JsonResponse(['message' => 'EnvironnementDeveloppement not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($environnement);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'EnvironnementDeveloppement deleted'], Response::HTTP_OK);
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
