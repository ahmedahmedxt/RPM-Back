<?php

namespace App\Controller\Api;

use App\Entity\Employe;
use App\Entity\Poste;
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

class PosteController extends AbstractController
{
  
    #[Route('/api/postes', name: 'api_poste_create', methods: ['POST'])]
public function create(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
{
    //$this->checkToken($tokenStorage);
    $data = json_decode($request->getContent(), true);

    // Recherche d'un poste existant avec le même nom
    $existingPoste = $entityManager->getRepository(Poste::class)->findOneBy(['posteNom' => $data['posteNom']]);
    if ($existingPoste !== null) {
        return new JsonResponse(['message' => 'Le poste existe déjà'], Response::HTTP_CONFLICT);
    }

    // Création d'une nouvelle instance de Poste
    $poste = new Poste();
    $poste->setPosteNom($data['posteNom']);

    // Persistance de l'entité dans la base de données
    $entityManager->persist($poste);
    $entityManager->flush();

    // Retourner une réponse JSON avec un message de succès
    return new JsonResponse(['message' => 'Poste créé avec succès'], Response::HTTP_CREATED);
}
    #[Route('/api/postes/{id}', name: 'api_poste_get', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $poste = $entityManager->getRepository(Poste::class)->find($id);

        if (!$poste) {
            return new JsonResponse(['message' => 'Poste non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $poste->getId(),
            'posteNom' => $poste->getPosteNom(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/postes/{id}', name: 'api_poste_update', methods: ['PUT'])]
    public function update(int $id, Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $poste = $entityManager->getRepository(Poste::class)->find($id);

        if (!$poste) {
            return new JsonResponse(['message' => 'Poste non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $poste->setPosteNom($data['posteNom']);

        $entityManager->flush();

        return new JsonResponse('Poste mis à jour avec succès', Response::HTTP_OK);
    }

    #[Route('/api/postes/{id}', name: 'api_poste_delete', methods: ['DELETE'])]
    public function deletePoste(int $id, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        
        $poste = $entityManager->getRepository(Poste::class)->find($id);

        if (!$poste) {
            return new JsonResponse(['message' => 'Poste non trouvé'], Response::HTTP_NOT_FOUND);
        }

        /*
        // Récupérer tous les employés qui ont ce poste
        $employes = $entityManager->getRepository(Employe::class)->findBy(['poste' => $poste]);

        // Mettre à jour les références à null dans tous les employés liés
        foreach ($employes as $employe) {
            $employe->setPoste(null); // Mettez à jour la référence appropriée à null
            $entityManager->persist($employe);
        }
        $entityManager->flush();
        */
        // Supprimer le poste
        $entityManager->remove($poste);
        $entityManager->flush();

        return new JsonResponse('Poste supprimé avec succès', Response::HTTP_OK);
    }
    #[Route('/api/postes', name: 'api_poste_list', methods: ['GET'])]
public function list(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
{
    //$this->checkToken($tokenStorage);
    
    // Récupérer les postes triés par nom
    $posteRepository = $entityManager->getRepository(Poste::class);
    $postes = $posteRepository->findBy([], ['posteNom' => 'ASC']);
    
    $data = [];
    foreach ($postes as $poste) {
        $data[] = [
            'id' => $poste->getId(),
            'posteNom' => $poste->getPosteNom(),
            // Ajoutez d'autres attributs du poste si nécessaire
        ];
    }

    return new JsonResponse($data, Response::HTTP_OK);
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
