<?php

namespace App\Controller\Api;

use App\Entity\Categorie;
use App\Entity\Reference;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CategorieController extends AbstractController
{
    #[Route('/api/create/categorie', name: 'api_categorie_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true);
    
        // Vérifier si une catégorie avec le même nom existe déjà
        $existingCategory = $entityManager->getRepository(Categorie::class)->findOneBy(['categorieLibelle' => $data['categorieLibelle']]);
        if ($existingCategory) {
            return new JsonResponse('Une catégorie avec ce nom existe déjà', Response::HTTP_CONFLICT);
        }
    
        // Créer une nouvelle catégorie
        $categorie = new Categorie();
        $categorie->setCategorieLibelle($data['categorieLibelle']);
        $categorie->setCategorieShort($data['categorieShort']);
        $categorie->setCategorieCodeRef($data['categorieCodeRef']);
        $categorie->setCategorieCodeCouleur($data['categorieCodeCouleur']);
    
        $entityManager->persist($categorie);
        $entityManager->flush();
    
        return new JsonResponse('Catégorie créée avec succès', Response::HTTP_CREATED);
    }

    #[Route('/api/getAll/categorie', name: 'api_categorie_get_all', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        
        // Récupérer les catégories triées par ordre
        $categories = $entityManager->getRepository(Categorie::class)->findBy([], ['categorieCodeRef' => 'ASC']);
        
        $data = [];

        foreach ($categories as $categorie) {
            $data[] = [
                'categorieId' => $categorie->getId(),
                'categorieLibelle' => $categorie->getCategorieLibelle(),
                'categorieCodeRef' => $categorie->getCategorieCodeRef(),
                'categorieCodeCouleur' => $categorie->getCategorieCodeCouleur(),
                'categorieShort' => $categorie->getCategorieShort(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/get/categorie/{id}', name: 'api_categorie_get', methods: ['GET'])]
    public function show(Categorie $categorie, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = [
            'categorieId' => $categorie->getId(),
            'categorieLibelle' => $categorie->getCategorieLibelle(),
            'categorieCodeRef' => $categorie->getCategorieCodeRef(),
            'categorieCodeCouleur' => $categorie->getCategorieCodeCouleur(),
            'categorieShort' => $categorie->getCategorieShort(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/put/categorie/{id}', name: 'api_categorie_update', methods: ['PUT'])]
    public function update(Request $request, Categorie $categorie, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true);

        $newCodeRef = $data['categorieCodeRef'];
        $oldCodeRef = $categorie->getCategorieCodeRef();

        // Vérifier si une autre catégorie a déjà le nouveau code
        $otherCategorie = $entityManager->getRepository(Categorie::class)->findOneBy(['categorieCodeRef' => $newCodeRef]);

        if ($otherCategorie && $otherCategorie->getId() !== $categorie->getId()) {
            // Échanger les codes
            $otherCategorie->setCategorieCodeRef($oldCodeRef);
            $entityManager->persist($otherCategorie);
        }

        $categorie->setCategorieLibelle($data['categorieLibelle']);
        $categorie->setCategorieShort($data['categorieShort']);
        $categorie->setCategorieCodeRef($newCodeRef);
        $categorie->setCategorieCodeCouleur($data['categorieCodeCouleur']);

        $entityManager->flush();

        return new JsonResponse('Catégorie mise à jour avec succès', Response::HTTP_OK);
    }

    #[Route('/api/update/categorie/order', name: 'api_categorie_update_order', methods: ['PUT'])]
    public function updateOrder(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return new JsonResponse(['error' => 'Invalid data format'], Response::HTTP_BAD_REQUEST);
        }

        foreach ($data as $item) {
            if (!isset($item['id']) || !isset($item['position'])) {
                continue;
            }

            $categorie = $entityManager->getRepository(Categorie::class)->find($item['id']);
            if ($categorie) {
                $categorie->setCategorieCodeRef($item['position']);
                $entityManager->persist($categorie);
            }
        }

        $entityManager->flush();

        return new JsonResponse(['message' => 'Ordre mis à jour avec succès'], Response::HTTP_OK);
    }

    #[Route('/api/delete/categorie/{id}', name: 'api_categorie_delete', methods: ['DELETE'])]
    public function delete(Categorie $categorie, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);

        $references = $entityManager->getRepository(Reference::class)->findBy(['categorie' => $categorie]);

        if ($references != null) {
            foreach ($references as $reference) {
                $reference->setCategorie(null);
                $entityManager->persist($reference);
            }
            $entityManager->flush();
        }

        $entityManager->remove($categorie);
        $entityManager->flush();

        return new JsonResponse('Catégorie supprimée avec succès', Response::HTTP_OK);
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