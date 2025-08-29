<?php

namespace App\Controller\Api;

use App\Entity\ProjetPreuve;
use App\Entity\Projet;
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

class ProjetPreuveController extends AbstractController
{
    #[Route('/api/getAll/projet-preuves', name: 'api_projet_preuve_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        
        // Récupérer les preuves de projet
        $projetPreuves = $entityManager->getRepository(ProjetPreuve::class)->findBy([], ['projetPreuveLibelle' => 'ASC']);
        
        // Sérialiser les preuves de projet
        $serializedProjetPreuves = [];
        foreach ($projetPreuves as $projetPreuve) {
            $serializedProjetPreuves[] = $this->serializeProjetPreuveNom($projetPreuve);
        }
    
        return new JsonResponse($serializedProjetPreuves, Response::HTTP_OK);
    }
    #[Route('/api/get/projet-preuves/{id}', name: 'api_projet_preuve_show', methods: ['GET'])]
    public function show(ProjetPreuve $projetPreuve, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        return new JsonResponse($this->serializeProjetPreuve($projetPreuve), Response::HTTP_OK);
    }

    #[Route('/api/create/projet-preuves', name: 'api_projet_preuve_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true);

        $projetPreuve = new ProjetPreuve();
        $projetPreuve->setProjetPreuveLibelle($data['projetPreuveLibelle']);

        // Récupérer le projet associé
        $projet = $entityManager->getRepository(Projet::class)->find($data['projetId']);
        if (!$projet) {
            return new JsonResponse(['message' => 'Projet introuvable'], Response::HTTP_NOT_FOUND);
        }
        $projetPreuve->setProjet($projet);

        $entityManager->persist($projetPreuve);
        $entityManager->flush();

        return new JsonResponse('Projet preuve créé avec succès', Response::HTTP_CREATED);
    }

    #[Route('/api/put/projet-preuves/{id}', name: 'api_projet_preuve_update', methods: ['PUT'])]
    public function update(Request $request, ProjetPreuve $projetPreuve, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true);

        $projetPreuve->setProjetPreuveLibelle($data['projetPreuveLibelle']);

        // Mise à jour du projet associé
        if (isset($data['projetId'])) {
            $projet = $entityManager->getRepository(Projet::class)->find($data['projetId']);
            if (!$projet) {
                return new JsonResponse(['message' => 'Projet introuvable'], Response::HTTP_NOT_FOUND);
            }
            $projetPreuve->setProjet($projet);
        }

        $entityManager->flush();

        return new JsonResponse('Projet preuve mis à jour avec succès', Response::HTTP_OK);
    }

    #[Route('/api/delete/projet-preuves/{id}', name: 'api_projet_preuve_delete', methods: ['DELETE'])]
    public function delete(ProjetPreuve $projetPreuve, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $entityManager->remove($projetPreuve);
        $entityManager->flush();

        return new JsonResponse('Projet preuve supprimé avec succès', Response::HTTP_OK);
    }

    /**
     * Serialize ProjetPreuve entity to array.
     */
    private function serializeProjetPreuveNom(ProjetPreuve $projetPreuve): array
    {
        return [
            'projetPreuveId' => $projetPreuve->getId(),
            'projetPreuveLibelle' => $projetPreuve->getProjetPreuveLibelle(),
            'projet' => $projetPreuve->getProjet() ? $projetPreuve->getProjet()->getProjetLibelle() : null,
                // Ajoutez d'autres attributs du projet que vous souhaitez inclure
        
        ];
    }
     /**
     * Serialize ProjetPreuve entity to array.
     */
    private function serializeProjetPreuve(ProjetPreuve $projetPreuve): array
    {
        return [
            'projetPreuveId' => $projetPreuve->getId(),
            'projetPreuveLibelle' => $projetPreuve->getProjetPreuveLibelle(),
            'projetId' => $projetPreuve->getProjet() ? $projetPreuve->getProjet()->getId() : null,
                // Ajoutez d'autres attributs du projet que vous souhaitez inclure
        
        ];
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
