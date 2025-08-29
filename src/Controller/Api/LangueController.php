<?php

namespace App\Controller\Api;

use App\Entity\Employe;
use App\Entity\Langue;
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

class LangueController extends AbstractController
{
    #[Route('/api/create/langue', name: 'api_langue_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true);

        // Vérifier si la langue existe déjà
        $existingLangue = $entityManager->getRepository(Langue::class)->findOneBy(['langueNom' => $data['langueNom']]);
        if ($existingLangue) {
            return new JsonResponse('Cette langue existe déjà', Response::HTTP_CONFLICT);
        }

        $langue = new Langue();
        $langue->setLangueNom($data['langueNom']);

        $entityManager->persist($langue);
        $entityManager->flush();

        return new JsonResponse('Langue créée avec succès', Response::HTTP_CREATED);
    }


    #[Route('/api/get/langue/{id}', name: 'api_langue_get', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $langue = $entityManager->getRepository(Langue::class)->find($id);

        if (!$langue) {
            return new JsonResponse(['message' => 'Langue non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $langue->getId(),
            'langueNom' => $langue->getLangueNom(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/put/langue/{id}', name: 'api_langue_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $langue = $entityManager->getRepository(Langue::class)->find($id);

        if (!$langue) {
            return new JsonResponse(['message' => 'Langue non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $langue->setLangueNom($data['langueNom']);

        $entityManager->flush();

        return new JsonResponse('Langue mise à jour avec succès', Response::HTTP_OK);
    }

    #[Route('/api/delete/langue/{id}', name: 'api_langue_delete', methods: ['DELETE'])]
    public function delete(int $id, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $langue = $entityManager->getRepository(Langue::class)->find($id);

        if (!$langue) {
            return new JsonResponse(['message' => 'Langue non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($langue);
        $entityManager->flush();

        return new JsonResponse('Langue supprimée avec succès', Response::HTTP_OK);
    }

    #[Route('/api/getAll/langues', name: 'api_langue_list', methods: ['GET'])]
public function list(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
{
    //$this->checkToken($tokenStorage);
    
    // Récupérer les langues triées par nom
    $langues = $entityManager->getRepository(Langue::class)->findBy([], ['langueNom' => 'ASC']);
    
    $data = [];
    foreach ($langues as $langue) {
        $data[] = [
            'id' => $langue->getId(),
            'langueNom' => $langue->getLangueNom(),
        ];
    }

    return new JsonResponse($data, Response::HTTP_OK);
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
