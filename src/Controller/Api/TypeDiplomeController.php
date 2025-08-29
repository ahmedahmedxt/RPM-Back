<?php

namespace App\Controller\Api;

use App\Entity\TypeDiplome;
use App\Repository\TypeDiplomeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/typediplome', name: 'api_type_diplome_')]
class TypeDiplomeController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    #[Route('', name: 'api_get_all_type_diplome', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $typeDiplomeRepo = $this->entityManager->getRepository(TypeDiplome::class);
        $types = $typeDiplomeRepo->findBy([], ['typeDiplomeLibelle' => 'ASC']);

        $typesData = [];
        foreach ($types as $typeItem) {
            $typesData[] = [
                'typeDiplomeId' => $typeItem->getTypeDiplomeId(),
                'typeDiplomeLibelle' => $typeItem->getTypeDiplomeLibelle(),
            ];
        }

        return new JsonResponse($typesData, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_type_diplome', methods: ['GET'])]
    public function getById(TypeDiplome $typeDiplome): JsonResponse
    {
        // Handle the case where the entity is not found
        if (!$typeDiplome) {
            return new JsonResponse(['message' => 'Type diplôme not found'], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'typeDiplomeId' => $typeDiplome->getTypeDiplomeId(),
            'typeDiplomeLibelle' => $typeDiplome->getTypeDiplomeLibelle(),
        ];
        // Return the serialized data as JSON with the correct response
        return new JsonResponse($data, Response::HTTP_OK);
    }


    #[Route('', name: 'create_type_diplome', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $typeDiplome = new TypeDiplome();
        $typeDiplome->setTypeDiplomeLibelle($data['typeDiplomeLibelle'] ?? null);

        $this->entityManager->persist($typeDiplome);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Type diplôme created'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update_type_diplome', methods: ['PUT'])]
    public function update(int $id, Request $request, TypeDiplomeRepository $typeDiplomeRepository): JsonResponse
    {
        $typeDiplome = $typeDiplomeRepository->find($id);

        if (!$typeDiplome) {
            return new JsonResponse(['message' => 'Type diplôme not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $typeDiplome->setTypeDiplomeLibelle($data['typeDiplomeLibelle'] ?? $typeDiplome->getTypeDiplomeLibelle());

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Type diplôme updated'], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete_type_diplome', methods: ['DELETE'])]
    public function delete(int $id, TypeDiplomeRepository $typeDiplomeRepository): JsonResponse
    {
        $typeDiplome = $typeDiplomeRepository->find($id);

        if (!$typeDiplome) {
            return new JsonResponse(['message' => 'Type diplôme not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($typeDiplome);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Type diplôme deleted'], Response::HTTP_OK);
    }

    private function checkToken(TokenStorageInterface $tokenStorage): void
    {
        $token = $tokenStorage->getToken();

        if (!$token instanceof TokenInterface) {
            throw new AccessDeniedHttpException('Token d\'authentification manquant ou invalide');
        }
    }
}
