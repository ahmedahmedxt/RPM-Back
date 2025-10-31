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

class MoyenLivraisonController extends AbstractController
{
    
    #[Route('/api/create/moyen-livraisons', name: 'api_moyen_livraison_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, MoyenLivraisonRepository $moyenLivraisonRepository): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true);

        // Vérifier si le moyen de livraison existe déjà (sur le libellé)
        $existingMoyenLivraison = $moyenLivraisonRepository->findOneBy(['moyenLivraisonLibelle' => $data['moyenLivraisonLibelle'] ?? null]);

        if ($existingMoyenLivraison) {
            return new JsonResponse('Le moyen de livraison existe déjà', Response::HTTP_CONFLICT);
        }

        // Créer un nouveau moyen de livraison
        $moyenLivraison = new MoyenLivraison();
        $moyenLivraison->setMoyenLivraisonLibelle($data['moyenLivraisonLibelle'] ?? null);
        $moyenLivraison->setMoyenLivraisonShort($data['moyenLivraisonShort'] ?? '');

        $entityManager->persist($moyenLivraison);
        $entityManager->flush();

        return new JsonResponse('Moyen de livraison créé avec succès', Response::HTTP_CREATED);
    }
    
    #[Route('/api/getAll/moyen-livraisons', name: 'api_moyen_livraison_get_all', methods: ['GET'])]
    public function getAll(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        
        // Récupérer les moyens de livraison triés par libellé
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
        //$this->checkToken($tokenStorage);
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
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true);

        if (array_key_exists('moyenLivraisonLibelle', $data)) {
            $moyenLivraison->setMoyenLivraisonLibelle($data['moyenLivraisonLibelle']);
        }
        if (array_key_exists('moyenLivraisonShort', $data)) {
            $moyenLivraison->setMoyenLivraisonShort($data['moyenLivraisonShort'] ?? '');
        }

        $entityManager->flush();

        return new JsonResponse('Moyen de livraison mis à jour avec succès', Response::HTTP_OK);
    }

    #[Route('/api/delete/moyen-livraisons/{id}', name: 'api_moyen_livraison_delete', methods: ['DELETE'])]
    public function delete(MoyenLivraison $moyenLivraison, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $entityManager->remove($moyenLivraison);
        $entityManager->flush();
        
        return new JsonResponse('Les références au Moyen de livraison ont été supprimées des Appels d\'offre associés, et le Moyen de livraison a été supprimé avec succès.', Response::HTTP_OK);
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