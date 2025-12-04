<?php

namespace App\Controller\Api;

use App\Entity\NiveauEtude;
use App\Repository\NiveauEtudeRepository;
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

#[Route('/api/niveauEtude', name: 'api_niveau_etude_')]
class NiveauEtudeController extends AbstractController
{
    private $entityManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(NiveauEtudeRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $niveauEtudes = $repository->findAll();
            
            $data = [];
            foreach ($niveauEtudes as $niveauEtude) {
                $data[] = [
                    'niveauEtudeId' => $niveauEtude->getNiveauEtudeId(),
                    'niveauEtudeLibelle' => $niveauEtude->getNiveauEtudeLibelle() ?? '',
                    'niveauEtudeDescription' => $niveauEtude->getNiveauEtudeDescription() ?? '',
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
    public function getById(int $id, NiveauEtudeRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $niveauEtude = $repository->find($id);

            if (!$niveauEtude) {
                return new JsonResponse(['message' => 'NiveauEtude not found'], Response::HTTP_NOT_FOUND);
            }

            $data = [
                'niveauEtudeId' => $niveauEtude->getNiveauEtudeId(),
                'niveauEtudeLibelle' => $niveauEtude->getNiveauEtudeLibelle() ?? '',
                'niveauEtudeDescription' => $niveauEtude->getNiveauEtudeDescription() ?? '',
            ];

            return new JsonResponse($data, Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de la récupération du niveau d\'étude'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $niveauEtude = new NiveauEtude();
            $niveauEtude->setNiveauEtudeLibelle($data['niveauEtudeLibelle'] ?? null);
            $niveauEtude->setNiveauEtudeDescription($data['niveauEtudeDescription'] ?? null);

            $this->entityManager->persist($niveauEtude);
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'NiveauEtude created'], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de la création du niveau d\'étude'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request, NiveauEtudeRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $niveauEtude = $repository->find($id);

            if (!$niveauEtude) {
                return new JsonResponse(['message' => 'NiveauEtude not found'], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            $niveauEtude->setNiveauEtudeLibelle($data['niveauEtudeLibelle'] ?? $niveauEtude->getNiveauEtudeLibelle());
            $niveauEtude->setNiveauEtudeDescription($data['niveauEtudeDescription'] ?? $niveauEtude->getNiveauEtudeDescription());

            $this->entityManager->flush();

            return new JsonResponse(['message' => 'NiveauEtude updated'], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de la mise à jour du niveau d\'étude'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, NiveauEtudeRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $niveauEtude = $repository->find($id);

            if (!$niveauEtude) {
                return new JsonResponse(['message' => 'NiveauEtude not found'], Response::HTTP_NOT_FOUND);
            }

            $this->entityManager->remove($niveauEtude);
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'NiveauEtude deleted'], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de la suppression du niveau d\'étude'
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