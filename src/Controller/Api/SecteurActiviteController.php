<?php

namespace App\Controller\Api;

use App\Entity\SecteurActivite;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

#[Route('/api/secteurActivite', name: 'api_secteur_activite_')]
class SecteurActiviteController extends AbstractController
{
    #[Route('/create', name: 'api_secteur_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $requestData = json_decode($request->getContent(), true);

        // Recherche d'un secteur existant avec le même nom
        $existingSecteur = $entityManager->getRepository(SecteurActivite::class)->findOneBy(['secteurActiviteLibelle' => $requestData['secteurActiviteLibelle']]);
        if ($existingSecteur !== null) {
            return new JsonResponse(['message' => 'Le secteur existe déjà'], Response::HTTP_CONFLICT);
        }

        // Créer une nouvelle instance de Secteur
        $secteur = new SecteurActivite();
        $secteur->setSecteurActiviteLibelle($requestData['secteurActiviteLibelle']);
        $secteur->setSecteurActiviteDescription($requestData['secteurActiviteDescription'] ?? null);

        // Persister l'entité dans la base de données
        $entityManager->persist($secteur);
        $entityManager->flush();

        // Retourner une réponse JSON avec un message de succès
        return new JsonResponse(['message' => 'Secteur créé avec succès'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_secteur_get', methods: ['GET'])]
    public function getById(SecteurActivite $secteurActivite, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = [
            'secteurActiviteId' => $secteurActivite->getId(),
            'secteurActiviteLibelle' => $secteurActivite->getSecteurActiviteLibelle(),
            'secteurActiviteDescription' => $secteurActivite->getSecteurActiviteDescription(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('', name: 'api_get_all_secteur', methods: ['GET'])]
    public function getAll(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);

        // Récupérer les pays triés par nom
        $secteurRepository = $entityManager->getRepository(SecteurActivite::class);
        $secteur = $secteurRepository->findBy([], ['secteurActiviteLibelle' => 'ASC']);

        $secteurData = [];
        foreach ($secteur as $secteurItem) {
            $secteurData[] = [
                'secteurActiviteId' => $secteurItem->getId(),
                'secteurActiviteLibelle' => $secteurItem->getSecteurActiviteLibelle(),
                'secteurActiviteDescription' => $secteurItem->getSecteurActiviteDescription(),
            ];
        }

        return new JsonResponse($secteurData, Response::HTTP_OK);
    }

    #[Route('/update/{id}', name: 'api_secteur_update', methods: ['PUT'])]
    public function update(Request $request, SecteurActivite $secteurActivite, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $requestData = json_decode($request->getContent(), true);

        $secteurActivite->setSecteurActiviteLibelle($requestData['secteurActiviteLibelle']);
        $secteurActivite->setSecteurActiviteDescription($requestData['secteurActiviteDescription']);

        $entityManager->flush();

        return new JsonResponse(['message' => 'Secteur mis à jour avec succès'], Response::HTTP_OK);
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

    #[Route('/delete/{id}', name: 'api_secteur_delete', methods: ['DELETE'])]
    public function delete(SecteurActivite $secteurActivite, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);

        // Supprimer le continent
        $entityManager->remove($secteurActivite);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Secteur supprimé avec succès'], Response::HTTP_OK);
    }
}
