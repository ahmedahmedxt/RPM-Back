<?php

namespace App\Controller\Api;

use App\Entity\MoyenLivraison;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Repository\MoyenLivraisonRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MoyenLivraisonController extends AbstractController
{
    
    #[Route('/api/create/moyen-livraisons', name: 'api_moyen_livraison_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, MoyenLivraisonRepository $moyenLivraisonRepository, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['error' => 'Données JSON invalides'], Response::HTTP_BAD_REQUEST);
        }

        if (!isset($data['moyenLivraisonLibelle']) || empty(trim($data['moyenLivraisonLibelle']))) {
            return new JsonResponse(['error' => 'Le libellé est requis'], Response::HTTP_BAD_REQUEST);
        }

        $moyenLivraisonLibelle = trim($data['moyenLivraisonLibelle']);
        $moyenLivraisonShort = isset($data['moyenLivraisonShort']) ? trim($data['moyenLivraisonShort']) : '';

        if (strlen($moyenLivraisonLibelle) > 255) {
            return new JsonResponse(['error' => 'Le libellé ne peut pas dépasser 255 caractères'], Response::HTTP_BAD_REQUEST);
        }

        if (strlen($moyenLivraisonShort) > 10) {
            return new JsonResponse(['error' => 'L\'acronyme ne peut pas dépasser 10 caractères'], Response::HTTP_BAD_REQUEST);
        }

        $existingMoyenLivraison = $moyenLivraisonRepository->findOneBy(['moyenLivraisonLibelle' => $moyenLivraisonLibelle]);

        if ($existingMoyenLivraison) {
            return new JsonResponse(['error' => 'Le moyen de livraison existe déjà'], Response::HTTP_CONFLICT);
        }

        $moyenLivraison = new MoyenLivraison();
        $moyenLivraison->setMoyenLivraisonLibelle($moyenLivraisonLibelle);
        $moyenLivraison->setMoyenLivraisonShort($moyenLivraisonShort);

        $errors = $validator->validate($moyenLivraison);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }
            return new JsonResponse(['error' => implode(', ', $errorMessages)], Response::HTTP_BAD_REQUEST);
        }

        try {
            $entityManager->persist($moyenLivraison);
            $entityManager->flush();

            return new JsonResponse([
                'message' => 'Moyen de livraison créé avec succès',
                'id' => $moyenLivraison->getMoyenLivraisonId()
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la création: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    #[Route('/api/getAll/moyen-livraisons', name: 'api_moyen_livraison_get_all', methods: ['GET'])]
    public function getAll(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $moyenLivraisonRepository = $entityManager->getRepository(MoyenLivraison::class);
        $moyenLivraisons = $moyenLivraisonRepository->findBy([], ['moyenLivraisonLibelle' => 'ASC']);
        
        $data = [];
        foreach ($moyenLivraisons as $moyenLivraison) {
            $data[] = [
                'moyenLivraisonId' => $moyenLivraison->getMoyenLivraisonId(),
                'moyenLivraisonLibelle' => $moyenLivraison->getMoyenLivraisonLibelle(),
                'moyenLivraisonShort' => $moyenLivraison->getMoyenLivraisonShort(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/get/moyen-livraisons/{id}', name: 'api_moyen_livraison_get', methods: ['GET'])]
    public function getOne(MoyenLivraison $moyenLivraison, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $data = [
            'moyenLivraisonId' => $moyenLivraison->getMoyenLivraisonId(),
            'moyenLivraisonLibelle' => $moyenLivraison->getMoyenLivraisonLibelle(),
            'moyenLivraisonShort' => $moyenLivraison->getMoyenLivraisonShort(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/put/moyen-livraisons/{id}', name: 'api_moyen_livraison_update', methods: ['PUT'])]
    public function update(Request $request, MoyenLivraison $moyenLivraison, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (array_key_exists('moyenLivraisonLibelle', $data) && !empty(trim($data['moyenLivraisonLibelle']))) {
            $moyenLivraison->setMoyenLivraisonLibelle(trim($data['moyenLivraisonLibelle']));
        }
        if (array_key_exists('moyenLivraisonShort', $data)) {
            $moyenLivraison->setMoyenLivraisonShort(trim($data['moyenLivraisonShort'] ?? ''));
        }

        $entityManager->flush();

        return new JsonResponse(['message' => 'Moyen de livraison mis à jour avec succès'], Response::HTTP_OK);
    }

    #[Route('/api/delete/moyen-livraisons/{id}', name: 'api_moyen_livraison_delete', methods: ['DELETE'])]
    public function delete(MoyenLivraison $moyenLivraison, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $entityManager->remove($moyenLivraison);
        $entityManager->flush();
        
        return new JsonResponse(['message' => 'Le moyen de livraison a été supprimé avec succès'], Response::HTTP_OK);
    }

    public function checkToken(TokenStorageInterface $tokenStorage): void
    {
        $token = $tokenStorage->getToken();

        if (!$token instanceof TokenInterface) {
            throw new AccessDeniedHttpException('Token d\'authentification manquant ou invalide');
        }
    }
}