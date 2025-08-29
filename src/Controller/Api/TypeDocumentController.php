<?php

namespace App\Controller\Api;

use App\Entity\TypeDocument;
use App\Repository\TypeDocumentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/typedocument', name: 'api_type_document_')]
class TypeDocumentController extends AbstractController
{
    private $entityManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    #[Route('', name: 'api_get_all_types', methods: ['GET'])]
    public function getAll(EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer les continents triés par nom
        $typeDocumentRepo = $entityManager->getRepository(TypeDocument::class);
        $types = $typeDocumentRepo->findBy([], ['typeDocumentLibelle' => 'ASC']);

        $typesData = [];
        foreach ($types as $typeItem) {
            $typesData[] = [
                'typeDocumentId' => $typeItem->getTypeDocumentId(),
                'typeDocumentLibelle' => $typeItem->getTypeDocumentLibelle(),
            ];
        }

        return new JsonResponse($typesData, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_by_id', methods: ['GET'])]
    public function getById(int $id, TypeDocumentRepository $typeDocumentRepository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $typeDocument = $typeDocumentRepository->find($id);

        if (!$typeDocument) {
            return new JsonResponse(['message' => 'Type document not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->serializer->serialize($typeDocument, 'json');

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true);

        $typeDocument = new TypeDocument();
        $typeDocument->setTypeDocumentLibelle($data['typeDocumentLibelle'] ?? null);

        $this->entityManager->persist($typeDocument);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Type document created'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request, TypeDocumentRepository $typeDocumentRepository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $typeDocument = $typeDocumentRepository->find($id);

        if (!$typeDocument) {
            return new JsonResponse(['message' => 'Type document not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $typeDocument->setTypeDocumentLibelle($data['typeDocumentLibelle'] ?? $typeDocument->getTypeDocumentLibelle());

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Type document updated'], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, TypeDocumentRepository $typeDocumentRepository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $typeDocument = $typeDocumentRepository->find($id);

        if (!$typeDocument) {
            return new JsonResponse(['message' => 'Type document not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($typeDocument);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Type document deleted'], Response::HTTP_OK);
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