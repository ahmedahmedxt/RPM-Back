<?php

namespace App\Controller\Api;

use App\Entity\Projet;
use App\Entity\Client;
use App\Entity\Categorie;
use App\Entity\Lieu;
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
use App\Repository\ProjetRepository;
use App\Repository\LieuRepository;

class ProjetController extends AbstractController
{
    #[Route('/api/create/projet', name: 'api_projet_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        $requiredFields = ['projetLibelle', 'projetDescription', 'projetReference', 'projetDateDemarrage', 'projetDateAchevement', 'projetUrlFonctionnel', 'projetDescriptionServiceEffectivementRendus'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return new JsonResponse(['message' => 'Missing required fields'], Response::HTTP_BAD_REQUEST);
            }
        }
    
        $projet = new Projet();
        $projet->setProjetLibelle($data['projetLibelle']);
        $projet->setProjetDescription($data['projetDescription']);
        $projet->setProjetReference($data['projetReference']);
        $projet->setProjetDateDemarrage(new \DateTime($data['projetDateDemarrage']));
        $projet->setProjetDateAchevement(new \DateTime($data['projetDateAchevement']));
        $projet->setProjetUrlFonctionnel($data['projetUrlFonctionnel']);
        $projet->setProjetDescriptionServiceEffectivementRendus($data['projetDescriptionServiceEffectivementRendus']);
    
        // Associer le lieu
        if (isset($data['lieuId'])) {
            $lieu = $entityManager->getRepository(Lieu::class)->find($data['lieuId']);
            if (!$lieu) {
                return new JsonResponse(['message' => 'Lieu not found'], Response::HTTP_NOT_FOUND);
            }
            $projet->setLieu($lieu);
        }
    
        // Associer le client
        if (isset($data['clientId'])) {
            $client = $entityManager->getRepository(Client::class)->find($data['clientId']);
            if (!$client) {
                return new JsonResponse(['message' => 'Client not found'], Response::HTTP_NOT_FOUND);
            }
            $projet->setClient($client);
        }
    
        // Associer les catégories au projet
        if (isset($data['categorieIds']) && is_array($data['categorieIds'])) {
            foreach ($data['categorieIds'] as $categorieId) {
                $categorie = $entityManager->getRepository(Categorie::class)->find($categorieId);
                if (!$categorie) {
                    return new JsonResponse(['message' => 'Categorie not found'], Response::HTTP_NOT_FOUND);
                }
                $projet->addCategory($categorie);
            }
        }
    
        $entityManager->persist($projet);
        $entityManager->flush();
    
        return new JsonResponse(['message' => 'Projet créé avec succès', 'id' => $projet->getId()], Response::HTTP_CREATED);
    }

    #[Route('/api/getAll/projets', name: 'api_projet_get_all', methods: ['GET'])]
    public function getAll(EntityManagerInterface $entityManager): JsonResponse
    {
        $projetRepository = $entityManager->getRepository(Projet::class);
        $projets = $projetRepository->findBy([], ['projetLibelle' => 'ASC']);

        $serializedProjets = [];
        foreach ($projets as $projet) {
            $serializedProjets[] = $this->serializeProjetNom($projet);
        }

        return new JsonResponse($serializedProjets, Response::HTTP_OK);
    }

    #[Route('/api/get/projet/{id}', name: 'api_projet_get_one_details', methods: ['GET'])]
    public function getProjetDetails($id, EntityManagerInterface $entityManager): JsonResponse
    {
        $projet = $entityManager->getRepository(Projet::class)->find($id);
        if (!$projet) {
            return new JsonResponse(['message' => 'Projet non trouvé'], Response::HTTP_NOT_FOUND);
        }
        $serializedProjet = $this->serializeProjet($projet);
        return new JsonResponse($serializedProjet, Response::HTTP_OK);
    }

    #[Route('/api/update/projet/{id}', name: 'api_projet_update', methods: ['PUT'])]
    public function update($id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $projet = $entityManager->getRepository(Projet::class)->find($id);
        if (!$projet) {
            return new JsonResponse(['message' => 'Projet non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Mise à jour des champs de base
        if (isset($data['projetLibelle'])) {
            $projet->setProjetLibelle($data['projetLibelle']);
        }
        if (isset($data['projetDescription'])) {
            $projet->setProjetDescription($data['projetDescription']);
        }
        if (isset($data['projetReference'])) {
            $projet->setProjetReference($data['projetReference']);
        }
        if (isset($data['projetDateDemarrage'])) {
            $projet->setProjetDateDemarrage(new \DateTime($data['projetDateDemarrage']));
        }
        if (isset($data['projetDateAchevement'])) {
            $projet->setProjetDateAchevement(new \DateTime($data['projetDateAchevement']));
        }
        if (isset($data['projetUrlFonctionnel'])) {
            $projet->setProjetUrlFonctionnel($data['projetUrlFonctionnel']);
        }
        if (isset($data['projetDescriptionServiceEffectivementRendus'])) {
            $projet->setProjetDescriptionServiceEffectivementRendus($data['projetDescriptionServiceEffectivementRendus']);
        }

        // Mise à jour du lieu
        if (isset($data['lieuId'])) {
            $lieu = $entityManager->getRepository(Lieu::class)->find($data['lieuId']);
            if (!$lieu) {
                return new JsonResponse(['message' => 'Lieu introuvable'], Response::HTTP_NOT_FOUND);
            }
            $projet->setLieu($lieu);
        }

        // Mise à jour du client
        if (isset($data['clientId'])) {
            $client = $entityManager->getRepository(Client::class)->find($data['clientId']);
            if (!$client) {
                return new JsonResponse(['message' => 'Client introuvable'], Response::HTTP_NOT_FOUND);
            }
            $projet->setClient($client);
        }

        // Mise à jour des catégories
        if (isset($data['categorieIds']) && is_array($data['categorieIds'])) {
            // Vider les catégories existantes
            foreach ($projet->getCategories() as $categorie) {
                $projet->removeCategory($categorie);
            }
            
            // Ajouter les nouvelles catégories
            foreach ($data['categorieIds'] as $categorieId) {
                $categorie = $entityManager->getRepository(Categorie::class)->find($categorieId);
                if (!$categorie) {
                    return new JsonResponse(['message' => 'Categorie not found'], Response::HTTP_NOT_FOUND);
                }
                $projet->addCategory($categorie);
            }
        }

        $entityManager->flush();
        return new JsonResponse(['message' => 'Projet modifié avec succès'], Response::HTTP_OK);
    }

    #[Route('/api/delete/projet/{id}', name: 'api_projet_delete', methods: ['DELETE'])]
    public function delete($id, EntityManagerInterface $entityManager): JsonResponse
    {
        $projet = $entityManager->getRepository(Projet::class)->find($id);
        if (!$projet) {
            return new JsonResponse(['message' => 'Projet non trouvé'], Response::HTTP_NOT_FOUND);
        }
        $entityManager->remove($projet);
        $entityManager->flush();
        return new JsonResponse(['message' => 'Projet supprimé avec succès'], Response::HTTP_OK);
    }

    private function serializeProjet(Projet $projet): array
    {
        $categories = [];
        foreach ($projet->getCategories() as $categorie) {
            $categories[] = [
                'id' => $categorie->getId(),
                'categorieLibelle' => $categorie->getCategorieLibelle(),
            ];
        }

        return [
            'id' => $projet->getId(),
            'projetLibelle' => $projet->getProjetLibelle(),
            'projetDescription' => $projet->getProjetDescription(),
            'projetReference' => $projet->getProjetReference(),
            'projetDateDemarrage' => $projet->getProjetDateDemarrage()->format('Y-m-d'),
            'projetDateAchevement' => $projet->getProjetDateAchevement()->format('Y-m-d'),
            'projetUrlFonctionnel' => $projet->getProjetUrlFonctionnel(),
            'projetDescriptionServiceEffectivementRendus' => $projet->getProjetDescriptionServiceEffectivementRendus(),
            'lieuId' => $projet->getLieu() ? $projet->getLieu()->getLieuId() : null,
            'clientId' => $projet->getClient() ? $projet->getClient()->getClientId() : null,
            'categories' => $categories,
        ];
    }

    private function serializeProjetNom(Projet $projet): array
    {
        $categories = [];
        foreach ($projet->getCategories() as $categorie) {
            $categories[] = [
                'id' => $categorie->getId(),
                'categorieLibelle' => $categorie->getCategorieLibelle(),
            ];
        }
       
        return [
            'id' => $projet->getId(),
            'projetLibelle' => $projet->getProjetLibelle(),
            'projetDescription' => $projet->getProjetDescription(),
            'projetReference' => $projet->getProjetReference(),
            'projetDateDemarrage' => $projet->getProjetDateDemarrage()->format('Y-m-d'),
            'projetDateAchevement' => $projet->getProjetDateAchevement()->format('Y-m-d'),
            'projetUrlFonctionnel' => $projet->getProjetUrlFonctionnel(),
            'projetDescriptionServiceEffectivementRendus' => $projet->getProjetDescriptionServiceEffectivementRendus(),
            'lieu' => $projet->getLieu() ? $projet->getLieu()->getLieuLibelle() : null,
            'client' => $projet->getClient() ? $projet->getClient()->getClientRaisonSocial() : null,
            'categories' => $categories,
        ];
    }
}