<?php

namespace App\Controller\Api;

use App\Entity\Devises;
use App\Entity\Reference;
use App\Entity\AppelOffres;
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

#[Route('/api/devises', name: 'api_devises_')]
class DevisesController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(DevisesRepository $repository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $devises = $repository->findAll();

        $data = array_map(function (Devises $d) {
            return [
                'devisesId' => $d->getDevisesId(),
                'devisesLibelle' => $d->getDevisesLibelle(),
                'devisesAcronyme' => $d->getDevisesAcronyme(),
                'deviseSymbole' => $d->getDeviseSymbole(),
            ];
        }, $devises);

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_by_id', methods: ['GET'])]
    public function getById(int $id, DevisesRepository $devisesRepository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $d = $devisesRepository->find($id);

        if (!$d) {
            return new JsonResponse(['message' => 'Devises not found'], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'devisesId' => $d->getDevisesId(),
            'devisesLibelle' => $d->getDevisesLibelle(),
            'devisesAcronyme' => $d->getDevisesAcronyme(),
            'deviseSymbole' => $d->getDeviseSymbole(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $data = json_decode($request->getContent(), true) ?? [];

        // Validation minimale
        if (empty($data['devisesLibelle']) || empty($data['devisesAcronyme'])) {
            return new JsonResponse(['error' => 'devisesLibelle et devisesAcronyme sont requis'], Response::HTTP_BAD_REQUEST);
        }

        $dev = new Devises();
        $dev->setDevisesLibelle($data['devisesLibelle'] ?? null);
        $dev->setDevisesAcronyme($data['devisesAcronyme'] ?? null);
        $dev->setDeviseSymbole($data['deviseSymbole'] ?? null);

        $this->entityManager->persist($dev);
        $this->entityManager->flush();

        return new JsonResponse([
            'message' => 'Devises created',
            'devisesId' => $dev->getDevisesId(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request, DevisesRepository $devisesRepository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $dev = $devisesRepository->find($id);

        if (!$dev) {
            return new JsonResponse(['message' => 'Devises not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true) ?? [];

        if (array_key_exists('devisesLibelle', $data)) {
            $dev->setDevisesLibelle($data['devisesLibelle']);
        }
        if (array_key_exists('devisesAcronyme', $data)) {
            $dev->setDevisesAcronyme($data['devisesAcronyme']);
        }
        if (array_key_exists('deviseSymbole', $data)) {
            $dev->setDeviseSymbole($data['deviseSymbole']);
        }

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Devises updated'], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Devises $devises, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);

        // Déréférencer les References
        $references = $entityManager->getRepository(Reference::class)->findBy(['devises' => $devises]);
        if (!empty($references)) {
            foreach ($references as $reference) {
                $reference->setDevises(null);
                $entityManager->persist($reference);
            }
            $entityManager->flush();
        }

        // Déréférencer les AppelOffres
        $appels = $entityManager->getRepository(AppelOffres::class)->findBy(['appelOffresDevisesId' => $devises]);
        if (!empty($appels)) {
            foreach ($appels as $appel) {
                $appel->setAppelOffresDevisesId(null);
                $entityManager->persist($appel);
            }
            $entityManager->flush();
        }

        $this->entityManager->remove($devises);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Devises deleted'], Response::HTTP_OK);
    }

    public function checkToken(TokenStorageInterface $tokenStorage): void
    {
        $token = $tokenStorage->getToken();
        if (!$token instanceof TokenInterface) {
            throw new AccessDeniedHttpException('Token d\'authentification manquant ou invalide');
        }
    }
}