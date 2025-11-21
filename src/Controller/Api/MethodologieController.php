<?php

namespace App\Controller\Api;

use App\Entity\Methodologie;
use App\Repository\MethodologieRepository;
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

#[Route('/api/methodologie', name: 'api_methodologie_')]
class MethodologieController extends AbstractController
{
    private $entityManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(MethodologieRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $methodologies = $repository->findAll();
            
            $data = [];
            foreach ($methodologies as $methodologie) {
                $data[] = [
                    'methodologieId' => $methodologie->getMethodologieId(),
                    'methodologieLibelle' => $methodologie->getMethodologieLibelle() ?? '',
                    'methodologieDescription' => $methodologie->getMethodologieDescription() ?? '',
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

    #[Route('/{id}', name: 'get_by_id', methods: ['GET'])]
    public function getById(int $id, MethodologieRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $methodologie = $repository->find($id);

            if (!$methodologie) {
                return new JsonResponse(['message' => 'Methodologie not found'], Response::HTTP_NOT_FOUND);
            }

            $data = [
                'methodologieId' => $methodologie->getMethodologieId(),
                'methodologieLibelle' => $methodologie->getMethodologieLibelle() ?? '',
                'methodologieDescription' => $methodologie->getMethodologieDescription() ?? '',
            ];

            return new JsonResponse($data, Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de la récupération de la méthodologie'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $methodologie = new Methodologie();
            $methodologie->setMethodologieLibelle($data['methodologieLibelle'] ?? null);
            $methodologie->setMethodologieDescription($data['methodologieDescription'] ?? null);

            $this->entityManager->persist($methodologie);
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'Methodologie created'], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de la création de la méthodologie'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request, MethodologieRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $methodologie = $repository->find($id);

            if (!$methodologie) {
                return new JsonResponse(['message' => 'Methodologie not found'], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            $methodologie->setMethodologieLibelle($data['methodologieLibelle'] ?? $methodologie->getMethodologieLibelle());
            $methodologie->setMethodologieDescription($data['methodologieDescription'] ?? $methodologie->getMethodologieDescription());

            $this->entityManager->flush();

            return new JsonResponse(['message' => 'Methodologie updated'], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de la mise à jour de la méthodologie'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, MethodologieRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $methodologie = $repository->find($id);

            if (!$methodologie) {
                return new JsonResponse(['message' => 'Methodologie not found'], Response::HTTP_NOT_FOUND);
            }

            $this->entityManager->remove($methodologie);
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'Methodologie deleted'], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de la suppression de la méthodologie'
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