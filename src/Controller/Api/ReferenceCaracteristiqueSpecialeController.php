<?php

namespace App\Controller\Api;

use App\Entity\ReferenceCaracteristiqueSpeciale;
use App\Repository\ReferenceCaracteristiqueSpecialeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

#[Route('/api', name: 'api_reference_caracteristique_speciale_')]
class ReferenceCaracteristiqueSpecialeController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/getAll/referenceCaracteristiqueSpeciale', name: 'get_all', methods: ['GET'])]
    public function getAll(ReferenceCaracteristiqueSpecialeRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $referenceCaracteristiqueSpeciales = $repository->findAll();
            
            $data = [];
            foreach ($referenceCaracteristiqueSpeciales as $referenceCaracteristiqueSpeciale) {
                $data[] = [
                    'referenceCaracteristiqueSpecialeId' => $referenceCaracteristiqueSpeciale->getReferenceCaracteristiqueSpecialeId(),
                    'referenceCaracteristiqueSpecialeTitre' => $referenceCaracteristiqueSpeciale->getReferenceCaracteristiqueSpecialeTitre() ?? '',
                    'referenceCaracteristiqueSpecialeDescription' => $referenceCaracteristiqueSpeciale->getReferenceCaracteristiqueSpecialeDescription() ?? '',
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

    #[Route('/referenceCaracteristiqueSpeciale/{id}', name: 'get_by_id', methods: ['GET'])]
    public function getById(int $id, ReferenceCaracteristiqueSpecialeRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $referenceCaracteristiqueSpeciale = $repository->find($id);

            if (!$referenceCaracteristiqueSpeciale) {
                return new JsonResponse(['message' => 'ReferenceCaracteristiqueSpeciale not found'], Response::HTTP_NOT_FOUND);
            }

            $data = [
                'referenceCaracteristiqueSpecialeId' => $referenceCaracteristiqueSpeciale->getReferenceCaracteristiqueSpecialeId(),
                'referenceCaracteristiqueSpecialeTitre' => $referenceCaracteristiqueSpeciale->getReferenceCaracteristiqueSpecialeTitre() ?? '',
                'referenceCaracteristiqueSpecialeDescription' => $referenceCaracteristiqueSpeciale->getReferenceCaracteristiqueSpecialeDescription() ?? '',
            ];

            return new JsonResponse($data, Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de la récupération de la référence caractéristique spéciale'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/referenceCaracteristiqueSpeciale', name: 'create', methods: ['POST'])]
    public function create(Request $request, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $referenceCaracteristiqueSpeciale = new ReferenceCaracteristiqueSpeciale();
            $referenceCaracteristiqueSpeciale->setReferenceCaracteristiqueSpecialeTitre($data['referenceCaracteristiqueSpecialeTitre'] ?? null);
            $referenceCaracteristiqueSpeciale->setReferenceCaracteristiqueSpecialeDescription($data['referenceCaracteristiqueSpecialeDescription'] ?? null);

            $this->entityManager->persist($referenceCaracteristiqueSpeciale);
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'ReferenceCaracteristiqueSpeciale created'], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de la création de la référence caractéristique spéciale'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/referenceCaracteristiqueSpeciale/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request, ReferenceCaracteristiqueSpecialeRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $referenceCaracteristiqueSpeciale = $repository->find($id);

            if (!$referenceCaracteristiqueSpeciale) {
                return new JsonResponse(['message' => 'ReferenceCaracteristiqueSpeciale not found'], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            $referenceCaracteristiqueSpeciale->setReferenceCaracteristiqueSpecialeTitre($data['referenceCaracteristiqueSpecialeTitre'] ?? $referenceCaracteristiqueSpeciale->getReferenceCaracteristiqueSpecialeTitre());
            $referenceCaracteristiqueSpeciale->setReferenceCaracteristiqueSpecialeDescription($data['referenceCaracteristiqueSpecialeDescription'] ?? $referenceCaracteristiqueSpeciale->getReferenceCaracteristiqueSpecialeDescription());

            $this->entityManager->flush();

            return new JsonResponse(['message' => 'ReferenceCaracteristiqueSpeciale updated'], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de la mise à jour de la référence caractéristique spéciale'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/referenceCaracteristiqueSpeciale/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, ReferenceCaracteristiqueSpecialeRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        try {
            $referenceCaracteristiqueSpeciale = $repository->find($id);

            if (!$referenceCaracteristiqueSpeciale) {
                return new JsonResponse(['message' => 'ReferenceCaracteristiqueSpeciale not found'], Response::HTTP_NOT_FOUND);
            }

            $this->entityManager->remove($referenceCaracteristiqueSpeciale);
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'ReferenceCaracteristiqueSpeciale deleted'], Response::HTTP_OK);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
                'message' => 'Erreur lors de la suppression de la référence caractéristique spéciale'
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