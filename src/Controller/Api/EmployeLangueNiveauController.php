<?php

namespace App\Controller\Api;

use App\Entity\EmployeLangueNiveau;
use App\Repository\EmployeLangueNiveauRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/employelangueniveau', name: 'api_employe_langue_niveau_')]
class EmployeLangueNiveauController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    #[Route('', name: 'api_get_all_employe_langue_niveau', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $employeLangueNiveauRepo = $this->entityManager->getRepository(EmployeLangueNiveau::class);
        $niveaux = $employeLangueNiveauRepo->findBy([], ['employeLangueNiveauLibelle' => 'ASC']);

        $niveauxData = [];
        foreach ($niveaux as $niveau) {
            $niveauxData[] = [
                'employeLangueNiveauId' => $niveau->getEmployeLangueNiveauId(),
                'employeLangueNiveauLibelle' => $niveau->getEmployeLangueNiveauLibelle(),
            ];
        }

        return new JsonResponse($niveauxData, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_employe_langue_niveau', methods: ['GET'])]
    public function getById(EmployeLangueNiveau $employeLangueNiveau): JsonResponse
    {
        $data = [
            'employeLangueNiveauId' => $employeLangueNiveau->getEmployeLangueNiveauId(),
            'employeLangueNiveauLibelle' => $employeLangueNiveau->getEmployeLangueNiveauLibelle(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('', name: 'create_employe_langue_niveau', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $employeLangueNiveau = new EmployeLangueNiveau();
        $employeLangueNiveau->setEmployeLangueNiveauLibelle($data['employeLangueNiveauLibelle'] ?? null);

        $this->entityManager->persist($employeLangueNiveau);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Employe langue niveau created'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update_employe_langue_niveau', methods: ['PUT'])]
    public function update(int $id, Request $request, EmployeLangueNiveauRepository $employeLangueNiveauRepository): JsonResponse
    {
        $employeLangueNiveau = $employeLangueNiveauRepository->find($id);

        if (!$employeLangueNiveau) {
            return new JsonResponse(['message' => 'Employe langue niveau not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $employeLangueNiveau->setEmployeLangueNiveauLibelle($data['employeLangueNiveauLibelle'] ?? $employeLangueNiveau->getEmployeLangueNiveauLibelle());

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Employe langue niveau updated'], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete_employe_langue_niveau', methods: ['DELETE'])]
    public function delete(int $id, EmployeLangueNiveauRepository $employeLangueNiveauRepository): JsonResponse
    {
        $employeLangueNiveau = $employeLangueNiveauRepository->find($id);

        if (!$employeLangueNiveau) {
            return new JsonResponse(['message' => 'Employe langue niveau not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($employeLangueNiveau);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Employe langue niveau deleted'], Response::HTTP_OK);
    }
}
