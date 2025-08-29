<?php

namespace App\Controller\Api;

use App\Entity\SituationFamiliale;
use App\Entity\Employe; 
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

class SituationFamilialeController extends AbstractController
{
    #[Route('/api/getAll/situations-familiales', name: 'api_situation_familiale_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        
        // Récupérer les situations familiales triées par ordre alphabétique
        $situationsFamiliales = $entityManager->getRepository(SituationFamiliale::class)->findBy([], ['situationFamilialeLibelle' => 'ASC']);
        
        // Sérialiser les situations familiales
        $serializedSituationsFamiliales = [];
        foreach ($situationsFamiliales as $situationFamiliale) {
            $serializedSituationsFamiliales[] = [
                'id' => $situationFamiliale->getSituationFamilialeId(),
                'situationFamiliale' => $situationFamiliale->getSituationFamilialeLibelle(),
            ];
        }
    
        return new JsonResponse($serializedSituationsFamiliales, Response::HTTP_OK);
    }

    #[Route('/api/get/situations-familiales/{id}', name: 'api_situation_familiale_show', methods: ['GET'])]
    public function show(SituationFamiliale $situationFamiliale, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = [
            'id' => $situationFamiliale->getSituationFamilialeId(),
            'situationFamiliale' => $situationFamiliale->getSituationFamilialeLibelle(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/create/situations-familiales', name: 'api_situation_familiale_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true);
    
        // Recherche d'une situation familiale existante avec le même libellé
        $existingSituation = $entityManager->getRepository(SituationFamiliale::class)->findOneBy(['situationFamilialeLibelle' => $data['situationFamiliale']]);
        if ($existingSituation !== null) {
            return new JsonResponse(['message' => 'La situation familiale existe déjà'], Response::HTTP_CONFLICT);
        }
    
        // Création d'une nouvelle instance de SituationFamiliale
        $situationFamiliale = new SituationFamiliale();
        $situationFamiliale->setSituationFamilialeLibelle($data['situationFamiliale']);
    
        // Persistance de l'entité dans la base de données
        $entityManager->persist($situationFamiliale);
        $entityManager->flush();
    
        // Retourner une réponse JSON avec un message de succès
        return new JsonResponse(['message' => 'Situation familiale créée avec succès'], Response::HTTP_CREATED);
    }

    #[Route('/api/put/situations-familiales/{id}', name: 'api_situation_familiale_update', methods: ['PUT'])]
    public function update(Request $request, SituationFamiliale $situationFamiliale, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true);

        $situationFamiliale->setSituationFamilialeLibelle($data['situationFamiliale']);

        $entityManager->flush();

        return new JsonResponse('Situation familiale mise à jour avec succès', Response::HTTP_OK);
    }

    #[Route('/api/delete/situations-familiales/{id}', name: 'api_situation_familiale_delete', methods: ['DELETE'])]
    public function delete(SituationFamiliale $situationFamiliale, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        
        // Récupérer tous les employés liés à cette situation familiale
        $employes = $entityManager->getRepository(Employe::class)->findBy(['situationFamilialeLibelle' => $situationFamiliale]);

        // Mettre à jour les références à null dans tous les employés liés
        foreach ($employes as $employe) {
            $employe->setSituationFamiliale(null);
            $entityManager->persist($employe);
        }
        $entityManager->flush();
        
        // Supprimer la situation familiale
        $entityManager->remove($situationFamiliale);
        $entityManager->flush();

        return new JsonResponse('Situation familiale supprimée avec succès', Response::HTTP_OK);
    
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

