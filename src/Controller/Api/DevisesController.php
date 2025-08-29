<?php

namespace App\Controller\Api;

use App\Entity\Devises;
use App\Entity\Reference;
use App\Repository\DevisesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/devises', name: 'api_devises_')]
class DevisesController extends AbstractController
{
    private $entityManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(DevisesRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $devises = $repository->findAll();
        $data = $this->serializer->serialize($devises, 'json');

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'get_by_id', methods: ['GET'])]
    public function getById(int $id, DevisesRepository $devisesRepository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $devises = $devisesRepository->find($id);

        if (!$devises) {
            return new JsonResponse(['message' => 'Devises not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->serializer->serialize($devises, 'json');

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true);

        $devises = new Devises();
        $devises->setDevisesLibelle($data['devisesLibelle'] ?? null);
        $devises->setDevisesAcronyme($data['devisesAcronyme'] ?? null);

        $this->entityManager->persist($devises);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Devises created'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request, DevisesRepository $devisesRepository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $devises = $devisesRepository->find($id);

        if (!$devises) {
            return new JsonResponse(['message' => 'Devises not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $devises->setDevisesLibelle($data['devisesLibelle'] ?? $devises->getDevisesLibelle());
        $devises->setDevisesAcronyme($data['devisesAcronyme'] ?? $devises->getDevisesAcronyme());

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Devises updated'], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Devises $devises, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $references = $entityManager->getRepository(Reference::class)->findBy(['devises' => $devises]);

        if ($references != null) {
            foreach ($references as $reference) {
                $reference->setDevises(null);
                $entityManager->persist($reference);
            }
            $entityManager->flush();
        }

        $this->entityManager->remove($devises);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Devises deleted'], Response::HTTP_OK);
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
