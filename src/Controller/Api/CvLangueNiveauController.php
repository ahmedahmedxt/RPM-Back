<?php

namespace App\Controller\Api;

use App\Entity\CvLangueNiveau;
use App\Repository\CvLangueNiveauRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/cv-langue-niveau', name: 'api_cv_langue_niveau')]
class CvLangueNiveauController extends AbstractController
{
    private $entityManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    #[Route('', name: 'api_get_all_niveau', methods: ['GET'])]
    public function getAll(EntityManagerInterface $entityManager, CvLangueNiveauRepository $repo): JsonResponse
    {
        // Récupérer les continents triés par nom
        $langueNiveau = $repo->findBy([], ['cvLangueNiveauLibelle' => 'ASC']);

        $niveauData = [];
        foreach ($langueNiveau as $niveau) {
            $niveauData[] = [
                'cvLangueNiveauId' => $niveau->getCvLangueNiveauId(),
                'cvLangueNiveauLibelle' => $niveau->getCvLangueNiveauLibelle(),
            ];
        }

        return new JsonResponse($niveauData, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_niveau_by_id', methods: ['GET'])]
    public function getById(int $id, CvLangueNiveauRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $niveau = $repository->find($id);

        if (!$niveau) {
            return new JsonResponse(['message' => 'Langue niveau not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->serializer->serialize($niveau, 'json');

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'create_niveau', methods: ['POST'])]
    public function create(Request $request, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true);

        $niveau = new CvLangueNiveau();
        $niveau->setCvLangueNiveauLibelle($data['cvLangueNiveauLibelle'] ?? null);

        $this->entityManager->persist($niveau);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Langue niveau created'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update_langue_niveau', methods: ['PUT'])]
    public function update(int $id, Request $request, CvLangueNiveauRepository $repo, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $niveau = $repo->find($id);

        if (!$niveau) {
            return new JsonResponse(['message' => 'Langue niveau not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $niveau->setCvLangueNiveauLibelle($data['cvLangueNiveauLibelle'] ?? $niveau->getCvLangueNiveauLibelle());

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Langue niveau updated'], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete_langue_niveau', methods: ['DELETE'])]
    public function delete(int $id, CvLangueNiveauRepository $repo, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $niveau = $repo->find($id);

        if (!$niveau) {
            return new JsonResponse(['message' => 'Langue niveau not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($niveau);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Langue niveau deleted'], Response::HTTP_OK);
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