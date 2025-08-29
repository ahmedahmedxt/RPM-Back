<?php

namespace App\Controller\Api;

use App\Entity\Client;
use App\Entity\Continent;
use App\Entity\Pays;
use App\Entity\Lieu;
use App\Entity\AppelOffre;
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


class PaysController extends AbstractController
{
    #[Route('/api/create/pays', name: 'api_pays_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $requestData = json_decode($request->getContent(), true);

        // Vérifier si le lieu existe déjà
        $existingPays = $entityManager->getRepository(Pays::class)->findOneBy(['paysLibelle' => $requestData['paysLibelle']]);
        if ($existingPays) {
            return new JsonResponse(['message' => 'Ce pays existe déjà.'], Response::HTTP_CONFLICT);
        }
        // Créer une nouvelle instance de Lieu
        $pays = new Pays();
        $pays->setPaysLibelle($requestData['paysLibelle']);
        $pays->setPaysCapitale($requestData['paysCapitale']);

        // Récupérer l'objet Pays en fonction de l'ID fourni dans la requête
            $continent = $entityManager->getRepository(Continent::class)->find($requestData['continentId']);

        // Vérifier si le continent existe
        if (!$continent) {
            return new JsonResponse(['message' => 'Continent non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Affecter le pays au lieu
        $pays->setContinent($continent);

        // Persister l'entité dans la base de données
        $entityManager->persist($pays);
        $entityManager->flush();

        // Retourner une réponse JSON avec un message de succès
        return new JsonResponse(['message' => 'Pays créé avec succès'], Response::HTTP_CREATED);
    }

    #[Route('/api/get/pays/{id}', name: 'api_pays_show', methods: ['GET'])]
    public function show(Pays $pays, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = [
            'paysId' => $pays->getId(),
            'paysLibelle' => $pays->getPaysLibelle(),
            'paysCapitale' => $pays->getPaysCapitale(),
            'continentId' => $pays->getContinent()->getContinentId(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }
    #[Route('/api/getAll/pays', name: 'api_get_all_pays', methods: ['GET'])]
public function getAll(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
{
    //$this->checkToken($tokenStorage);
    
    // Récupérer les pays triés par nom
    $paysRepository = $entityManager->getRepository(Pays::class);
    $pays = $paysRepository->findBy([], ['paysLibelle' => 'ASC']);
    
    $paysData = [];
    foreach ($pays as $paysItem) {
        $continent = $paysItem->getContinent();
        $continentName = ($continent) ? $continent->getContinentName() : 'Continent non spécifié';

        $paysData[] = [
            'paysId' => $paysItem->getId(),
            'paysLibelle' => $paysItem->getPaysLibelle(),
            'paysCapitale' => $paysItem->getPaysCapitale(),
            'continentName' => $continentName,
        ];
    }

    return new JsonResponse($paysData, Response::HTTP_OK);
}
 
    #[Route('/api/put/pays/{id}', name: 'api_pays_update', methods: ['PUT'])]
    public function update(Request $request, Pays $pays, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $requestData = json_decode($request->getContent(), true);

        $pays->setPaysLibelle($requestData['paysLibelle']);
        $pays->setPaysCapitale($requestData['paysCapitale']);
        $continent = $entityManager->getRepository(Continent::class)->find($requestData['continentId']);
        $pays->setContinent($continent);


        $entityManager->flush();

        return new JsonResponse(['message' => 'Pays mis à jour avec succès'], Response::HTTP_OK);
    }

    #[Route('/api/delete/pays/{id}', name: 'api_pays_delete', methods: ['DELETE'])]
    public function delete(Pays $pays, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        // Récupérer tous les appels d'offre qui ont ce pays
        $lieux = $entityManager->getRepository(Lieu::class)->findBy(['pays' => $pays]);
        $clients = $entityManager->getRepository(Client::class)->findBy(['pays' => $pays]);

        if ($lieux != null) {
            foreach ($lieux as $lieu) {
                $lieu->setPays(null);
                $entityManager->persist($lieu);
            }
            $entityManager->flush();
        }

        if ($clients != null) {
            foreach ($clients as $client) {
                $client->setPays(null);
                $entityManager->persist($client);
            }
            $entityManager->flush();
        }

        // Supprimer le pays
        $entityManager->remove($pays);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Pays supprimé avec succès'], Response::HTTP_OK);
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
