<?php

namespace App\Controller\Api;

use App\Entity\BailleurFond;
use App\Repository\BailleurFondRepository;
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

#[Route('/api/bailleurfond', name: 'api_bailleurfond_')]
class BailleurFondController extends AbstractController
{
    private $entityManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(BailleurFondRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $bailleurs = $repository->findAll();
        $data = $this->serializer->serialize($bailleurs, 'json');

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'get_by_id', methods: ['GET'])]
    public function getById(int $id, BailleurFondRepository $bailleurFondRepository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $bailleurFond = $bailleurFondRepository->find($id);

        if (!$bailleurFond) {
            return new JsonResponse(['message' => 'BailleurFond not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->serializer->serialize($bailleurFond, 'json');

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true);

        $bailleurFond = new BailleurFond();
        $bailleurFond->setBailleurFondLibelle($data['bailleurFondLibelle'] ?? null);
        $bailleurFond->setBailleurFondAcronyme($data['bailleurFondAcronyme'] ?? null);

        $this->entityManager->persist($bailleurFond);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'BailleurFond created'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request, BailleurFondRepository $bailleurFondRepository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $bailleurFond = $bailleurFondRepository->find($id);

        if (!$bailleurFond) {
            return new JsonResponse(['message' => 'BailleurFond not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $bailleurFond->setBailleurFondLibelle($data['bailleurFondLibelle'] ?? $bailleurFond->getBailleurFondLibelle());
        $bailleurFond->setBailleurFondAcronyme($data['bailleurFondAcronyme'] ?? $bailleurFond->getBailleurFondAcronyme());

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'BailleurFond updated'], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, BailleurFondRepository $bailleurFondRepository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $bailleurFond = $bailleurFondRepository->find($id);

        if (!$bailleurFond) {
            return new JsonResponse(['message' => 'BailleurFond not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($bailleurFond);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'BailleurFond deleted'], Response::HTTP_OK);
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
