<?php

namespace App\Controller\Api;

use App\Entity\ProjetEmployePoste;
use App\Entity\Collaborateur;
use App\Entity\Projet;
use App\Entity\Poste;
use App\Repository\ProjetEmployePosteRepository;
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

class ProjetEmployePosteController extends AbstractController
{
    private $projetEmployePosteRepository;
    private $entityManager;

    public function __construct(ProjetEmployePosteRepository $projetEmployePosteRepository, EntityManagerInterface $entityManager)
    {
        $this->projetEmployePosteRepository = $projetEmployePosteRepository;
        $this->entityManager = $entityManager;
    }
    
    #[Route('/api/getAll/projet-employe-poste', name: 'api_projet_get', methods: ['GET'])]
    public function index(TokenStorageInterface $tokenStorage): JsonResponse
    {
        $this->checkToken($tokenStorage);
        
        $projetEmployePostes = $this->projetEmployePosteRepository->findAll();
        $serializedProjetEmployePostes = [];
        foreach ($projetEmployePostes as $projetEmployePoste) {
            $serializedProjetEmployePostes[] = $this->serializeProjetEmployePosteNom($projetEmployePoste);
        }
        return new JsonResponse($serializedProjetEmployePostes, Response::HTTP_OK);
    }
    
    #[Route('/api/getOne/projet-employe-poste/{id}', name: 'api_projet_get_one', methods: ['GET'])]
    public function getOne($id, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $this->checkToken($tokenStorage);
        
        $projetEmployePoste = $this->projetEmployePosteRepository->find($id);
        if (!$projetEmployePoste) {
            return new JsonResponse(['message' => 'Le ProjetEmployePoste spécifié n\'existe pas.'], JsonResponse::HTTP_NOT_FOUND);
        }
        $serializedProjetEmployePoste = $this->serializeProjetEmployePoste($projetEmployePoste);
        return new JsonResponse($serializedProjetEmployePoste, Response::HTTP_OK);
    }

    #[Route('/api/create/projet-employe-poste', name: 'api_projet_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $this->checkToken($tokenStorage);
        
        $data = json_decode($request->getContent(), true);

        $projetEmployePoste = new ProjetEmployePoste();
        $projetEmployePoste->setDuree($data['duree'] ?? null);
        
        if (isset($data['collaborateurId'])) {
            $collaborateur = $entityManager->getRepository(Collaborateur::class)->find($data['collaborateurId']);
            if ($collaborateur) {
                $projetEmployePoste->setCollaborateur($collaborateur);
            } else {
                return new JsonResponse(['message' => 'Collaborateur non trouvé'], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        if (isset($data['projetId'])) {
            $projet = $entityManager->getRepository(Projet::class)->find($data['projetId']);
            if ($projet) {
                $projetEmployePoste->setProjet($projet);
            } else {
                return new JsonResponse(['message' => 'Projet non trouvé'], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        if (isset($data['posteId'])) {
            $poste = $entityManager->getRepository(Poste::class)->find($data['posteId']);
            if ($poste) {
                $projetEmployePoste->setPoste($poste);
            } else {
                return new JsonResponse(['message' => 'Poste non trouvé'], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        $entityManager->persist($projetEmployePoste);
        $entityManager->flush();

        return new JsonResponse($this->serializeProjetEmployePoste($projetEmployePoste), JsonResponse::HTTP_CREATED);
    }

    private function serializeProjetEmployePoste(ProjetEmployePoste $projetEmployePoste): array
    {
        return [
            'id' => $projetEmployePoste->getId(),
            'duree' => $projetEmployePoste->getDuree(),
            'collaborateurId' => $projetEmployePoste->getCollaborateur()?->getCollaborateurId(),
            'projetId' => $projetEmployePoste->getProjet()?->getId(),
            'posteId' => $projetEmployePoste->getPoste()?->getId(),
        ];
    }

    private function serializeProjetEmployePosteNom(ProjetEmployePoste $projetEmployePoste): array
    {
        $collaborateur = $projetEmployePoste->getCollaborateur();
        $collaborateurNom = $collaborateur ? ($collaborateur->getCollaborateurPrenom() . ' ' . $collaborateur->getCollaborateurNom()) : null;
        
        return [
            'id' => $projetEmployePoste->getId(),
            'duree' => $projetEmployePoste->getDuree(),
            'collaborateur' => $collaborateurNom,
            'projet' => $projetEmployePoste->getProjet()?->getProjetLibelle(),
            'poste' => $projetEmployePoste->getPoste()?->getPosteNom(),
        ];
    }

    #[Route('/api/put/projet-employe-poste/{id}', name: 'api_projet_update', methods: ['PUT'])]
    public function update(Request $request, $id, TokenStorageInterface $tokenStorage, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->checkToken($tokenStorage);
        
        $data = json_decode($request->getContent(), true);
        $projetEmployePoste = $this->projetEmployePosteRepository->find($id);

        if (!$projetEmployePoste) {
            return new JsonResponse(['message' => 'Le ProjetEmployePoste spécifié n\'existe pas.'], JsonResponse::HTTP_NOT_FOUND);
        }

        if (isset($data['duree'])) {
            $projetEmployePoste->setDuree($data['duree']);
        }

        if (isset($data['collaborateurId'])) {
            $collaborateur = $entityManager->getRepository(Collaborateur::class)->find($data['collaborateurId']);
            if ($collaborateur) {
                $projetEmployePoste->setCollaborateur($collaborateur);
            } else {
                return new JsonResponse(['message' => 'Collaborateur non trouvé'], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        if (isset($data['projetId'])) {
            $projet = $entityManager->getRepository(Projet::class)->find($data['projetId']);
            if ($projet) {
                $projetEmployePoste->setProjet($projet);
            } else {
                return new JsonResponse(['message' => 'Projet non trouvé'], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        if (isset($data['posteId'])) {
            $poste = $entityManager->getRepository(Poste::class)->find($data['posteId']);
            if ($poste) {
                $projetEmployePoste->setPoste($poste);
            } else {
                return new JsonResponse(['message' => 'Poste non trouvé'], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        $entityManager->flush();

        return new JsonResponse(['message' => 'Le ProjetEmployePoste a été mis à jour avec succès.', 'data' => $this->serializeProjetEmployePoste($projetEmployePoste)], JsonResponse::HTTP_OK);
    }

    #[Route('/api/delete/projet-employe-poste/{id}', name: 'api_projet_delete', methods: ['DELETE'])]
    public function delete($id, TokenStorageInterface $tokenStorage, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->checkToken($tokenStorage);
        
        $projetEmployePoste = $this->projetEmployePosteRepository->find($id);

        if (!$projetEmployePoste) {
            return new JsonResponse(['message' => 'Le ProjetEmployePoste spécifié n\'existe pas.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $entityManager->remove($projetEmployePoste);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Le ProjetEmployePoste a été supprimé avec succès.'], JsonResponse::HTTP_OK);
    }

    public function checkToken(TokenStorageInterface $tokenStorage): void
    {
        $token = $tokenStorage->getToken();

        if (!$token instanceof TokenInterface) {
            throw new AccessDeniedHttpException('Token d\'authentification manquant ou invalide');
        }
    }
}