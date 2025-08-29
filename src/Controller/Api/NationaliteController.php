<?php

namespace App\Controller\Api;

use App\Entity\Nationalite;
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


class NationaliteController extends AbstractController
{
   
    #[Route('/api/create/nationalite', name: 'api_nationalite_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true);
    
        // Vérifier si la nationalité existe déjà
        $existingNationalite = $entityManager->getRepository(Nationalite::class)->findOneBy(['nationaliteLibelle' => $data['nationaliteLibelle']]);
        if ($existingNationalite !== null) {
            return new JsonResponse('La nationalité existe déjà', Response::HTTP_CONFLICT);
        }
    
        // Créer une nouvelle nationalité
        $nationalite = new Nationalite();
        $nationalite->setNationaliteLibelle($data['nationaliteLibelle']);
    
        $entityManager->persist($nationalite);
        $entityManager->flush();
    
        return new JsonResponse('Nationalité créée avec succès', Response::HTTP_CREATED);
    }

    #[Route('/api/get/nationalite/{id}', name: 'api_nationalite_show', methods: ['GET'])]
    public function show(Nationalite $nationalite, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = [
            'id' => $nationalite->getId(),
            'nationaliteLibelle' => $nationalite->getNationaliteLibelle(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/getAll/nationalites', name: 'api_nationalite_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        
        // Récupérer les nationalités triées par libellé
        $nationaliteRepository = $entityManager->getRepository(Nationalite::class);
        $nationalites = $nationaliteRepository->findBy([], ['nationaliteLibelle' => 'ASC']);
        
        $data = [];
        foreach ($nationalites as $nationalite) {
            $data[] = [
                'id' => $nationalite->getId(),
                'nationaliteLibelle' => $nationalite->getNationaliteLibelle(),
            ];
        }
    
        return new JsonResponse($data, Response::HTTP_OK);
    }
   

    #[Route('/api/put/nationalite/{id}', name: 'api_nationalite_update', methods: ['PUT'])]
    public function update(Request $request, Nationalite $nationalite, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true);

        $nationalite->setNationaliteLibelle($data['nationaliteLibelle']);

        $entityManager->flush();

        return new JsonResponse('Nationalité mise à jour avec succès', Response::HTTP_OK);
    }

    #[Route('/api/delete/nationalite/{id}', name: 'api_nationalite_delete', methods: ['DELETE'])]
    public function delete(Nationalite $nationalite, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        
        // Récupérer tous les employés liés à cette nationalité
        $employes = $entityManager->getRepository(Employe::class)->findBy(['nationalite' => $nationalite]);

        // Mettre à jour les références à null dans tous les employés liés
        foreach ($employes as $employe) {
            $employe->setNationalite(null);
            $entityManager->persist($employe);
        }
        $entityManager->flush();
        
        // Supprimer la nationalité
        $entityManager->remove($nationalite);
        $entityManager->flush();

        return new JsonResponse('Nationalité supprimée avec succès', Response::HTTP_OK);
    
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
