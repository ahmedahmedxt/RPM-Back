<?php

namespace App\Controller\Api;

use App\Entity\AppelOffresType;
use App\Entity\AppelOffres;
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

class AppelOffreTypeController extends AbstractController
{
    #[Route('/api/create/appeloffres/types', name: 'api_appel_offres_type_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true) ?? [];

        if (empty($data['appelOffresTypeLibelle'])) {
            return new JsonResponse('Le libellé du type est requis', Response::HTTP_BAD_REQUEST);
        }

        // Vérifier si un type avec le même libellé existe déjà
        $existingType = $entityManager->getRepository(AppelOffresType::class)
            ->findOneBy(['appelOffresTypeLibelle' => $data['appelOffresTypeLibelle']]);
        if ($existingType) {
            return new JsonResponse('Un type d\'appel d\'offres avec ce libellé existe déjà', Response::HTTP_CONFLICT);
        }

        // Créer un nouveau type d'appel d'offres
        $type = new AppelOffresType();
        $type->setAppelOffresTypeLibelle($data['appelOffresTypeLibelle']);
        $type->setAppelOffresTypeShort($data['appelOffresTypeShort'] ?? null);

        $entityManager->persist($type);
        $entityManager->flush();

        return new JsonResponse('Type d\'appel d\'offres créé avec succès', Response::HTTP_CREATED);
    }

    #[Route('/api/getAll/appeloffres/types', name: 'api_appel_offres_types', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);

        // Récupérer les types triés par libellé
        $types = $entityManager->getRepository(AppelOffresType::class)
            ->findBy([], ['appelOffresTypeLibelle' => 'ASC']);

        $data = [];
        foreach ($types as $type) {
            $data[] = [
                'appelOffresTypeId' => $type->getAppelOffresTypeId(),
                'appelOffresTypeLibelle' => $type->getAppelOffresTypeLibelle(),
                'appelOffresTypeShort' => $type->getAppelOffresTypeShort(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/get/appeloffres/types/{id}', name: 'api_appel_offres_type_show', methods: ['GET'])]
    public function show(AppelOffresType $type, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = [
            'appelOffresTypeId' => $type->getAppelOffresTypeId(),
            'appelOffresTypeLibelle' => $type->getAppelOffresTypeLibelle(),
            'appelOffresTypeShort' => $type->getAppelOffresTypeShort(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/put/appeloffres/types/{id}', name: 'api_appel_offres_type_update', methods: ['PUT'])]
    public function update(Request $request, AppelOffresType $type, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true) ?? [];

        if (array_key_exists('appelOffresTypeLibelle', $data)) {
            $type->setAppelOffresTypeLibelle($data['appelOffresTypeLibelle']);
        }
        if (array_key_exists('appelOffresTypeShort', $data)) {
            $type->setAppelOffresTypeShort($data['appelOffresTypeShort'] ?? null);
        }

        $entityManager->flush();

        return new JsonResponse('Type d\'appel d\'offres mis à jour avec succès', Response::HTTP_OK);
    }

    #[Route('/api/delete/appeloffres/types/{id}', name: 'api_appel_offres_type_delete', methods: ['DELETE'])]
    public function delete(AppelOffresType $type, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);

        // S'il y a des AppelOffres associés, déréférencer avant suppression
        if (!$type->getAppelOffres()->isEmpty()) {
            foreach ($type->getAppelOffres() as $appel) {
                // côté AppelOffres: setAppelOffresTypeId(null)
                $appel->setAppelOffresTypeId(null);
                $entityManager->persist($appel);
            }
            $entityManager->flush();
        }

        $entityManager->remove($type);
        $entityManager->flush();

        return new JsonResponse('Type d\'appel d\'offres supprimé avec succès', Response::HTTP_OK);
    }

    public function checkToken(TokenStorageInterface $tokenStorage): void
    {
        $token = $tokenStorage->getToken();
        if (!$token instanceof TokenInterface) {
            throw new AccessDeniedHttpException('Token d\'authentification manquant ou invalide');
        }
    }
}