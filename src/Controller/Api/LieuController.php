<?php

namespace App\Controller\Api;

use App\Entity\Lieu;
use App\Entity\Pays;
use App\Entity\Projet;
use App\Entity\Reference;
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

class LieuController extends AbstractController
{
    
    #[Route('/api/create/lieu', name: 'api_lieu_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $requestData = json_decode($request->getContent(), true);

           // Vérifier si le lieu existe déjà
        $existingLieu = $entityManager->getRepository(Lieu::class)->findOneBy(['lieuLibelle' => $requestData['lieuLibelle']]);
        if ($existingLieu) {
            return new JsonResponse(['message' => 'Ce lieu existe déjà.'], Response::HTTP_CONFLICT);
        }
        // Créer une nouvelle instance de Lieu
        $lieu = new Lieu();
        $lieu->setLieuLibelle($requestData['lieuLibelle']);

        // Récupérer l'objet Pays en fonction de l'ID fourni dans la requête
        $pays = $entityManager->getRepository(Pays::class)->find($requestData['paysId']);

        // Vérifier si le pays existe
        if (!$pays) {
            return new JsonResponse(['message' => 'Pays non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Affecter le pays au lieu
        $lieu->setPays($pays);

        // Persister l'entité dans la base de données
        $entityManager->persist($lieu);
        $entityManager->flush();

        // Retourner une réponse JSON avec un message de succès
        return new JsonResponse(['message' => 'Lieu créé avec succès'], Response::HTTP_CREATED);
    }

    private function checkToken(TokenStorageInterface $tokenStorage): void
    {
        $token = $tokenStorage->getToken();
        if (!$token) {
            throw new AccessDeniedHttpException('Token d\'authentification manquant ou invalide');
        }
    }
    #[Route('/api/getAll/lieux', name: 'api_lieux_get_all', methods: ['GET'])]
    public function getAll(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        
        // Récupérer les lieux triés par nom
        $lieuxRepository = $entityManager->getRepository(Lieu::class);
        $lieux = $lieuxRepository->findBy([], ['lieuLibelle' => 'ASC']);
        
        $lieuxData = [];
        foreach ($lieux as $lieu) {
            $pays = $lieu->getPays();
            $paysLibelle = ($pays) ? $pays->getPaysLibelle() : 'Pays non spécifié';
        
            $lieuxData[] = [
                'lieuId' => $lieu->getLieuId(),
                'lieuLibelle' => $lieu->getLieuLibelle(),
                'paysLibelle' => $paysLibelle,
            ];
        }
    
        return new JsonResponse($lieuxData, Response::HTTP_OK);
    }
    #[Route('/api/get/lieux/{id}', name: 'api_lieu_show', methods: ['GET'])]
    public function show(Lieu $lieu, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = [
            'lieuId' => $lieu->getLieuId(),
            'lieuLibelle' => $lieu->getLieuLibelle(),
            'paysId' => $lieu->getPays()->getId(),
               
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/put/lieu/{id}', name: 'api_lieu_update', methods: ['PUT'])]
    public function update(Request $request, Lieu $lieu, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $requestData = json_decode($request->getContent(), true);

        $lieu->setLieuLibelle($requestData['lieuLibelle']);

        // Récupérer l'objet Pays en fonction de l'ID fourni dans la requête
        $pays = $entityManager->getRepository(Pays::class)->find($requestData['paysId']);

        // Vérifier si le pays existe
        if (!$pays) {
            return new JsonResponse(['message' => 'Pays non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Affecter le pays au lieu
        $lieu->setPays($pays);

        $entityManager->flush();

        return new JsonResponse(['message' => 'Lieu mis à jour avec succès'], Response::HTTP_OK);
    }

    #[Route('/api/delete/lieu/{id}', name: 'api_lieu_delete', methods: ['DELETE'])]
    public function deleteLieu(Lieu $lieu, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);

        $references = $entityManager->getRepository(Reference::class)->findBy(['lieu' => $lieu]);

        if ($references != null) {
            foreach ($references as $reference) {
                $reference->setLieu(null);
                $entityManager->persist($reference);
            }
            $entityManager->flush();
        }

        // Supprimer le lieu
        $entityManager->remove($lieu);
        $entityManager->flush();

        return new JsonResponse('Lieu supprimé avec succès', Response::HTTP_OK);
    }
  
}
