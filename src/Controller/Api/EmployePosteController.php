<?php

namespace App\Controller\Api;

use App\Entity\EmployePoste;
use App\Repository\EmployePosteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/employeposte', name: 'api_employe_poste_')]
class EmployePosteController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    #[Route('', name: 'api_get_all_employe_poste', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $employePosteRepo = $this->entityManager->getRepository(EmployePoste::class);
        $postes = $employePosteRepo->findBy([], ['employePosteLibelle' => 'ASC']);

        $postesData = [];
        foreach ($postes as $posteItem) {
            $postesData[] = [
                'employePosteId' => $posteItem->getEmployePosteId(),
                'employePosteLibelle' => $posteItem->getEmployePosteLibelle(),
            ];
        }

        return new JsonResponse($postesData, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_employe_poste', methods: ['GET'])]
    public function getById(EmployePoste $employePoste): JsonResponse
    {
        $data = [
            'employePosteId' => $employePoste->getEmployePosteId(),
            'employePosteLibelle' => $employePoste->getEmployePosteLibelle(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('', name: 'create_employe_poste', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $employePoste = new EmployePoste();
        $employePoste->setEmployePosteLibelle($data['employePosteLibelle'] ?? null);

        $this->entityManager->persist($employePoste);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Employe poste created'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update_employe_poste', methods: ['PUT'])]
    public function update(int $id, Request $request, EmployePosteRepository $employePosteRepository): JsonResponse
    {
        $employePoste = $employePosteRepository->find($id);

        if (!$employePoste) {
            return new JsonResponse(['message' => 'Employe poste not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $employePoste->setEmployePosteLibelle($data['employePosteLibelle'] ?? $employePoste->getEmployePosteLibelle());

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Employe poste updated'], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete_employe_poste', methods: ['DELETE'])]
    public function delete(int $id, EmployePosteRepository $employePosteRepository): JsonResponse
    {
        $employePoste = $employePosteRepository->find($id);

        if (!$employePoste) {
            return new JsonResponse(['message' => 'Employe poste not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($employePoste);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Employe poste deleted'], Response::HTTP_OK);
    }
}
