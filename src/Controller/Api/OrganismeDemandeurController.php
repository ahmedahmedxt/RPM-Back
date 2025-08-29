<?php

namespace App\Controller\Api;

use App\Entity\OrganismeDemandeur;
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

class OrganismeDemandeurController extends AbstractController
{
    
    #[Route('/api/create/organisme-demandeurs', name: 'api_organisme_demandeur_create', methods: ['POST'])]
public function create(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
{
    //$this->checkToken($tokenStorage);
    $data = json_decode($request->getContent(), true);

    // Vérifier si l'organisme demandeur existe déjà
    $existingOrganismeDemandeur = $entityManager->getRepository(OrganismeDemandeur::class)->findOneBy(['organismeDemandeurLibelle' => $data['organismeDemandeurLibelle']]);
    if ($existingOrganismeDemandeur !== null) {
        return new JsonResponse('L\'organisme demandeur existe déjà', Response::HTTP_CONFLICT);
    }

    // Créer un nouvel organisme demandeur
    $organismeDemandeur = new OrganismeDemandeur();
    $organismeDemandeur->setOrganismeDemandeurLibelle($data['organismeDemandeurLibelle']);

    $entityManager->persist($organismeDemandeur);
    $entityManager->flush();

    return new JsonResponse('Organisme demandeur créé avec succès', Response::HTTP_CREATED);
}

    #[Route('/api/getAll/organisme-demandeurs', name: 'api_organisme_demandeur_get_all', methods: ['GET'])]
    public function getAll(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        
        // Récupérer les organismes demandeurs triés par libellé
        $organismeDemandeurRepository = $entityManager->getRepository(OrganismeDemandeur::class);
        $organismeDemandeurs = $organismeDemandeurRepository->findBy([], ['organismeDemandeurLibelle' => 'ASC']);
        
        $data = [];
        foreach ($organismeDemandeurs as $organismeDemandeur) {
            $data[] = [
                'organismeDemandeurId' => $organismeDemandeur->getId(),
                'organismeDemandeurLibelle' => $organismeDemandeur->getOrganismeDemandeurLibelle(),
            ];
        }
    
        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/get/organisme-demandeurs/{id}', name: 'api_organisme_demandeur_get', methods: ['GET'])]
    public function getOne(OrganismeDemandeur $organismeDemandeur, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = [
            'organismeDemandeurId' => $organismeDemandeur->getId(),
            'organismeDemandeurLibelle' => $organismeDemandeur->getOrganismeDemandeurLibelle(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/put/organisme-demandeurs/{id}', name: 'api_organisme_demandeur_update', methods: ['PUT'])]
    public function update(Request $request, OrganismeDemandeur $organismeDemandeur, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true);

        $organismeDemandeur->setOrganismeDemandeurLibelle($data['organismeDemandeurLibelle']);

        $entityManager->flush();

        return new JsonResponse('Organisme demandeur mis à jour avec succès', Response::HTTP_OK);
    }

    #[Route('/api/delete/organisme-demandeurs/{id}', name: 'api_organisme_demandeur_delete', methods: ['DELETE'])]
    public function delete(OrganismeDemandeur $organismeDemandeur, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        
        // Vérifier s'il y a des AppelOffre associés à cet OrganismeDemandeur
        if ($organismeDemandeur->getAppelOffres()->isEmpty()) {
            // Aucun AppelOffre associé, donc supprimer simplement l'OrganismeDemandeur
            $entityManager->remove($organismeDemandeur);
            $entityManager->flush();
            
            return new JsonResponse('Organisme demandeur supprimé avec succès', Response::HTTP_OK);
        } else {
            // Des AppelOffre sont associés, mettre à jour les références à null dans chaque AppelOffre
            foreach ($organismeDemandeur->getAppelOffres() as $appelOffre) {
                $appelOffre->setOrganismeDemandeur(null);
                $entityManager->persist($appelOffre);
            }
            $entityManager->flush();
            
            // Après avoir mis à jour les références, supprimer l'OrganismeDemandeur
            $entityManager->remove($organismeDemandeur);
            $entityManager->flush();
            
            return new JsonResponse('Les références à l\'Organisme demandeur ont été supprimées des Appels d\'offre associés, et l\'Organisme demandeur a été supprimé avec succès.', Response::HTTP_OK);
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
