<?php

namespace App\Controller\Api;

use App\Entity\Poste;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PosteController extends AbstractController
{
  
    #[Route('/api/postes', name: 'api_poste_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $existingPoste = $entityManager->getRepository(Poste::class)->findOneBy(['posteNom' => $data['posteNom']]);
        if ($existingPoste !== null) {
            return new JsonResponse(['message' => 'Le poste existe déjà'], Response::HTTP_CONFLICT);
        }

        $poste = new Poste();
        $poste->setPosteNom($data['posteNom']);

        $entityManager->persist($poste);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Poste créé avec succès'], Response::HTTP_CREATED);
    }
    #[Route('/api/postes/{id}', name: 'api_poste_get', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $poste = $entityManager->getRepository(Poste::class)->find($id);

        if (!$poste) {
            return new JsonResponse(['message' => 'Poste non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $poste->getId(),
            'posteNom' => $poste->getPosteNom(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/postes/{id}', name: 'api_poste_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $poste = $entityManager->getRepository(Poste::class)->find($id);

        if (!$poste) {
            return new JsonResponse(['message' => 'Poste non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $poste->setPosteNom($data['posteNom']);

        $entityManager->flush();

        return new JsonResponse('Poste mis à jour avec succès', Response::HTTP_OK);
    }

    #[Route('/api/postes/{id}', name: 'api_poste_delete', methods: ['DELETE'])]
    public function deletePoste(int $id, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $poste = $entityManager->getRepository(Poste::class)->find($id);

        if (!$poste) {
            return new JsonResponse(['message' => 'Poste non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($poste);
        $entityManager->flush();

        return new JsonResponse('Poste supprimé avec succès', Response::HTTP_OK);
    }
    #[Route('/api/postes', name: 'api_poste_list', methods: ['GET'])]
    public function list(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $posteRepository = $entityManager->getRepository(Poste::class);
        $postes = $posteRepository->findBy([], ['posteNom' => 'ASC']);
        
        $data = [];
        foreach ($postes as $poste) {
            $data[] = [
                'id' => $poste->getId(),
                'posteNom' => $poste->getPosteNom(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }
    public function checkToken(TokenStorageInterface $tokenStorage): void
    {
        $token = $tokenStorage->getToken();

        if (!$token instanceof TokenInterface) {
            throw new AccessDeniedHttpException('Token d\'authentification manquant ou invalide');
        }
    }
}