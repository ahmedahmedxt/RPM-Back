<?php

namespace App\Controller\Api;

use App\Entity\Technologie;
use App\Repository\TechnologieRepository;
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

#[Route('/api/technologie', name: 'api_technologie_')]
class TechnologieController extends AbstractController
{
    private $entityManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(TechnologieRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $technologies = $repository->findAll();
        $data = $this->serializer->serialize($technologies, 'json');

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'get_by_id', methods: ['GET'])]
    public function getById(int $id, TechnologieRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $technologie = $repository->find($id);

        if (!$technologie) {
            return new JsonResponse(['message' => 'Technologie not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->serializer->serialize($technologie, 'json');

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true);

        $technologie = new Technologie();
        $technologie->setReferenceTechnologieLibelle($data['referenceTechnologieLibelle'] ?? null);
        $technologie->setReferenceTechnologieDescription($data['referenceTechnologieDescription'] ?? null);

        $this->entityManager->persist($technologie);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Technologie created'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request, TechnologieRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $technologie = $repository->find($id);

        if (!$technologie) {
            return new JsonResponse(['message' => 'Technologie not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $technologie->setReferenceTechnologieLibelle($data['referenceTechnologieLibelle'] ?? $technologie->getReferenceTechnologieLibelle());
        $technologie->setReferenceTechnologieDescription($data['referenceTechnologieDescription'] ?? $technologie->getReferenceTechnologieDescription());

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Technologie updated'], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, TechnologieRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $technologie = $repository->find($id);

        if (!$technologie) {
            return new JsonResponse(['message' => 'Technologie not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($technologie);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Technologie deleted'], Response::HTTP_OK);
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
