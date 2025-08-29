<?php

namespace App\Controller\Api;

use App\Entity\AppelOffreType;
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

class AppelOffreTypeController extends AbstractController
{
    #[Route('/api/create/appeloffre/types', name: 'api_appel_offre_type_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true);
    
        // Vérifier si un type d'appel d'offre avec le même nom existe déjà
        $existingType = $entityManager->getRepository(AppelOffreType::class)->findOneBy(['appelOffreType' => $data['appelOffreType']]);
        if ($existingType) {
            return new JsonResponse('Un type d\'appel d\'offre avec ce nom existe déjà', Response::HTTP_CONFLICT);
        }
    
        // Créer un nouveau type d'appel d'offre
        $appelOffreType = new AppelOffreType();
        $appelOffreType->setAppelOffreType($data['appelOffreType']);
    
        $entityManager->persist($appelOffreType);
        $entityManager->flush();
    
        return new JsonResponse('Type d\'appel d\'offre créé avec succès', Response::HTTP_CREATED);
    }
    #[Route('/api/getAll/appeloffre/types', name: 'api_appel_offre_types', methods: ['GET'])]
public function index(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
{
    //$this->checkToken($tokenStorage);
    
    // Récupérer les types d'appels d'offres triés par ordre alphabétique
    $appelOffreTypes = $entityManager->getRepository(AppelOffreType::class)->findBy([], ['appelOffreType' => 'ASC']);
    
    $data = [];

    foreach ($appelOffreTypes as $appelOffreType) {
        $data[] = [
            'appelOffreTypeId' => $appelOffreType->getId(),
            'appelOffreType' => $appelOffreType->getAppelOffreType(),
        ];
    }

    return new JsonResponse($data, Response::HTTP_OK);
}

    #[Route('/api/get/appeloffre/types/{id}', name: 'api_appel_offre_type_show', methods: ['GET'])]
    public function show(AppelOffreType $appelOffreType, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = [
            'appelOffreTypeId' => $appelOffreType->getId(),
            'appelOffreType' => $appelOffreType->getAppelOffreType(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/put/appeloffre/types/{id}', name: 'api_appel_offre_type_update', methods: ['PUT'])]
    public function update(Request $request, AppelOffreType $appelOffreType, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true);

        $appelOffreType->setAppelOffreType($data['appelOffreType']);

        $entityManager->flush();

        return new JsonResponse('Appel d\'offre type mis à jour avec succès', Response::HTTP_OK);
    }

    #[Route('/api/delete/appeloffre/types/{id}', name: 'api_appel_offre_type_delete', methods: ['DELETE'])]
    public function delete(AppelOffreType $appelOffreType, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        
        // Vérifier s'il y a des AppelOffre associés
        if ($appelOffreType->getAppelOffres()->isEmpty()) {
            // Aucun AppelOffre associé, donc supprimer simplement l'AppelOffreType
            $entityManager->remove($appelOffreType);
            $entityManager->flush();
            
            return new JsonResponse('Appel d\'offre type supprimé avec succès', Response::HTTP_OK);
        } else {
            // Des AppelOffre sont associés, mettre à jour les références à null dans chaque AppelOffre
            foreach ($appelOffreType->getAppelOffres() as $appelOffre) {
                $appelOffre->setAppelOffreType(null);
                $entityManager->persist($appelOffre);
            }
            $entityManager->flush();
            
            // Après avoir mis à jour les références, supprimer l'AppelOffreType
            $entityManager->remove($appelOffreType);
            $entityManager->flush();
            
            return new JsonResponse('Les références à l\'Appel d\'offre type ont été supprimées des Appels d\'offre associés, et l\'Appel d\'offre type a été supprimé avec succès.', Response::HTTP_OK);
        }
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
