<?php

namespace App\Controller\Api;

use App\Entity\ProjetEmployePoste;
use App\Entity\Employe;
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
        //$this->checkToken($tokenStorage);
        $projetEmployePostes = $this->projetEmployePosteRepository->findAll();
        $serializedProjetEmployePostes = [];
        foreach ($projetEmployePostes as $projetEmployePoste) {
            // Appel de la fonction serializeProjetEmployePoste() avec les deux arguments requis
            $serializedProjetEmployePostes[] = $this->serializeProjetEmployePosteNom($projetEmployePoste, $tokenStorage);
        }
        return new JsonResponse($serializedProjetEmployePostes, Response::HTTP_OK);
    }
    
    #[Route('/api/getOne/projet-employe-poste/{id}', name: 'api_projet_get_one', methods: ['GET'])]
    public function getOne($id, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
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
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true);

        $projetEmployePoste = new ProjetEmployePoste();
        $projetEmployePoste->setDuree($data['duree']);
        
        // Récupération de l'employé, du projet et du poste à partir de leurs IDs
        $employe = $entityManager->getRepository(Employe::class)->find($data['employe_id']);
        $projet = $entityManager->getRepository(Projet::class)->find($data['projet_id']);
        $poste = $entityManager->getRepository(Poste::class)->find($data['poste_id']);

        // Vérification si les entités sont valides
        if (!$employe || !$projet || !$poste) {
            return new JsonResponse(['message' => 'L\'employé, le projet ou le poste spécifié n\'existe pas.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $projetEmployePoste->setEmploye($employe);
        $projetEmployePoste->setProjet($projet);
        $projetEmployePoste->setPoste($poste);

        $entityManager->persist($projetEmployePoste);
        $entityManager->flush();

        return new JsonResponse($projetEmployePoste, JsonResponse::HTTP_CREATED);
    }

    /**
     * Serialize ProjetEmployePoste entity to array.
     */
    private function serializeProjetEmployePoste(ProjetEmployePoste $projetEmployePoste): array
    {
        return [
            'id' => $projetEmployePoste->getId(),
            'duree' => $projetEmployePoste->getDuree(),
            'employe_id' => $projetEmployePoste->getEmploye()->getId(),
            'projet_id' => $projetEmployePoste->getProjet()->getId(),
            'poste_id' => $projetEmployePoste->getPoste()->getId(),
            // Ajoutez d'autres propriétés de l'entité ProjetEmployePoste si nécessaire
        ];
    }
/**
     * Serialize ProjetEmployePoste entity to array.
     */
    private function serializeProjetEmployePosteNom(ProjetEmployePoste $projetEmployePoste): array
    {
        return [
            'id' => $projetEmployePoste->getId(),
            'duree' => $projetEmployePoste->getDuree(),
            'employe' => $projetEmployePoste->getEmploye()->getPersonneContact(),
            'projet' => $projetEmployePoste->getProjet()->getProjetLibelle(),
            'poste' => $projetEmployePoste->getPoste()->getPosteNom(),
            // Ajoutez d'autres propriétés de l'entité ProjetEmployePoste si nécessaire
        ];
    }


    #[Route('/api/put/projet-employe-poste/{id}', name: 'api_projet_update', methods: ['PUT'])]
public function update(Request $request, $id, TokenStorageInterface $tokenStorage, EntityManagerInterface $entityManager): JsonResponse
{
    //$this->checkToken($tokenStorage);
    $data = json_decode($request->getContent(), true);
    $projetEmployePoste = $this->projetEmployePosteRepository->find($id);

    if (!$projetEmployePoste) {
        return new JsonResponse(['message' => 'Le ProjetEmployePoste spécifié n\'existe pas.'], JsonResponse::HTTP_NOT_FOUND);
    }

    // Mettre à jour les champs nécessaires du ProjetEmployePoste
    $projetEmployePoste->setDuree($data['duree']);

    // Récupération de l'employé, du projet et du poste à partir de leurs IDs
    $employe = $entityManager->getRepository(Employe::class)->find($data['employe_id']);
    $projet = $entityManager->getRepository(Projet::class)->find($data['projet_id']);
    $poste = $entityManager->getRepository(Poste::class)->find($data['poste_id']);

    // Vérification si les entités sont valides
    if (!$employe || !$projet || !$poste) {
        return new JsonResponse(['message' => 'L\'employé, le projet ou le poste spécifié n\'existe pas.'], JsonResponse::HTTP_BAD_REQUEST);
    }

    $projetEmployePoste->setEmploye($employe);
    $projetEmployePoste->setProjet($projet);
    $projetEmployePoste->setPoste($poste);

    // Mettre à jour la base de données
    $entityManager->flush();

    // Renvoyer une réponse JSON pour indiquer que la mise à jour a réussi
    return new JsonResponse(['message' => 'Le ProjetEmployePoste a été mis à jour avec succès.'], JsonResponse::HTTP_OK);
}

    /**
     * Serialize Projet entities to array.
     **/
    private function serializeProjets($projets): array
    {
        $serializedProjets = [];
        foreach ($projets as $projet) {
            $serializedProjets[] = [
                'id' => $projet->getId(),
                // Ajoutez d'autres propriétés du projet si nécessaire
            ];
        }
        return $serializedProjets;
    }

    private function serializeEmployes($employes): array
{
    $serializedEmployes = [];
    
    // Vérifier si $employes est une collection ou une seule entité
    if ($employes instanceof Employe) {
        $serializedEmployes[] = [
            'id' => $employes->getId(),
            // Ajoutez d'autres propriétés de l'employé si nécessaire
        ];
    } elseif (is_array($employes)) {
        foreach ($employes as $employe) {
            $serializedEmployes[] = [
                'id' => $employe->getId(),
                // Ajoutez d'autres propriétés de l'employé si nécessaire
            ];
        }
    }
    
    return $serializedEmployes;
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
