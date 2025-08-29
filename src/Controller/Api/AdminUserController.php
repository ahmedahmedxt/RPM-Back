<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Doctrine\ORM\EntityManagerInterface;

class AdminUserController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('api/users', name: 'get_all_users', methods: ['GET'])]
    public function getAllUsers(UserRepository $userRepository,TokenStorageInterface $tokenStorage): Response
    {
        //$this->checkToken($tokenStorage);
        

        // Récupérer tous les utilisateurs depuis le UserRepository
        $users = $userRepository->findAll();

        // Filtrer les utilisateurs pour exclure l'administrateur
        $filteredUsers = array_filter($users, function(User $user) {
            return !in_array('ROLE_ADMIN', $user->getRoles());
        });

        // Convertir les objets User en un tableau associatif de données utilisateur
        $userData = [];
        foreach ($filteredUsers as $user) {
            $userData[] = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                // Ajoutez d'autres propriétés selon vos besoins
            ];
        }

        // Retourner les données de tous les utilisateurs sous forme de réponse JSON
        return new JsonResponse($userData);
    }

    #[Route('api/users/{id}', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser($id, TokenStorageInterface $tokenStorage): Response
    {
        //$this->checkToken($tokenStorage);

        // Récupérer l'utilisateur à supprimer
        $user = $this->entityManager->getRepository(User::class)->find($id);

        // Vérifier si l'utilisateur existe
        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Supprimer l'utilisateur de la base de données
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        // Retourner une réponse indiquant que l'utilisateur a été supprimé avec succès
        return new JsonResponse(['message' => 'User deleted successfully'], Response::HTTP_OK);
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
