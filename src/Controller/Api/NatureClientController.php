<?php

namespace App\Controller\Api;

use App\Entity\NatureClient;
use App\Entity\Client;
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

class NatureClientController extends AbstractController
{
    #[Route('/api/nature-clients', name: 'api_nature_client_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true);
    
        // Vérifier si la nature de client existe déjà
        $existingNatureClient = $entityManager->getRepository(NatureClient::class)->findOneBy(['natureClient' => $data['natureClient']]);
        if ($existingNatureClient !== null) {
            return new JsonResponse('La nature de client existe déjà', Response::HTTP_CONFLICT);
        }
    
        // Créer une nouvelle nature de client
        $natureClient = new NatureClient();
        $natureClient->setNatureClient($data['natureClient']);
        $natureClient->setNatureClientDescription($data['natureClientDescription'] ?? null);
    
        $entityManager->persist($natureClient);
        $entityManager->flush();
    
        return new JsonResponse('Nature client créée avec succès', Response::HTTP_CREATED);
    }

    #[Route('/api/nature-clients', name: 'api_nature_client_get_all', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        
        // Récupérer les natures des clients triées par nom
        $natureClientRepository = $entityManager->getRepository(NatureClient::class);
        $natureClients = $natureClientRepository->findBy([], ['natureClient' => 'ASC']);
        
        $data = [];
        foreach ($natureClients as $natureClient) {
            $data[] = [
                'id' => $natureClient->getId(),
                'natureClientLibelle' => $natureClient->getNatureClient(),
                'natureClientDescription' => $natureClient->getNatureClientDescription(),
            ];
        }
    
        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/nature-clients/{id}', name: 'api_nature_client_get', methods: ['GET'])]
    public function show(NatureClient $natureClient, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = [
            'id' => $natureClient->getId(),
            'natureClientLibelle' => $natureClient->getNatureClient(),
            'natureClientDescription' => $natureClient->getNatureClientDescription(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/nature-clients/{id}', name: 'api_nature_client_update', methods: ['PUT'])]
    public function update(Request $request, NatureClient $natureClient, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true);

        $natureClient->setNatureClient($data['natureClientLibelle']);
        $natureClient->setNatureClientDescription($data['natureClientDescription']);

        $entityManager->flush();

        return new JsonResponse('Nature client mise à jour avec succès', Response::HTTP_OK);
    }

    #[Route('/api/nature-clients/{id}', name: 'api_nature_client_delete', methods: ['DELETE'])]
    public function delete(NatureClient $natureClient, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        
        // Récupérer tous les clients liés à cette NatureClient
        $clients = $entityManager->getRepository(Client::class)->findBy(['natureClient' => $natureClient]);

        // Mettre à jour les références à null dans tous les clients liés
        foreach ($clients as $client) {
            $client->setNatureClient(null);
            $entityManager->persist($client);
        }
        $entityManager->flush();
        
        // Supprimer la NatureClient
        $entityManager->remove($natureClient);
        $entityManager->flush();

        return new JsonResponse('NatureClient supprimée avec succès', Response::HTTP_OK);
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