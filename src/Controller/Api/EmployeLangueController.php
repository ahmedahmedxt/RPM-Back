<?php

namespace App\Controller\Api;

use App\Entity\EmployeLangue;
use App\Repository\EmployeLangueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/employelangue', name: 'api_employe_langue_')]
class EmployeLangueController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    #[Route('', name: 'get_all_employe_langue', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $employeLangueRepo = $this->entityManager->getRepository(EmployeLangue::class);
        $employeLangues = $employeLangueRepo->findAll();

        $data = [];
        foreach ($employeLangues as $employeLangue) {
            $data[] = [
                'employeLangueId' => $employeLangue->getEmployeLangueId(),
                'employeeLangueLue' => $employeLangue->getEmployeeLangueLue(),
                'employeeLangueEcrite' => $employeLangue->getEmployeeLangueEcrite(),
                'employeeLangueParlee' => $employeLangue->getEmployeeLangueParlee(),
                'employeLangueNiveauId' => $employeLangue->getEmployeLangueNiveauId()?->getEmployeLangueNiveauId(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_employe_langue', methods: ['GET'])]
    public function getById(EmployeLangue $employeLangue): JsonResponse
    {
        $data = [
            'employeLangueId' => $employeLangue->getEmployeLangueId(),
            'employeeLangueLue' => $employeLangue->getEmployeeLangueLue(),
            'employeeLangueEcrite' => $employeLangue->getEmployeeLangueEcrite(),
            'employeeLangueParlee' => $employeLangue->getEmployeeLangueParlee(),
            'employeLangueNiveauId' => $employeLangue->getEmployeLangueNiveauId()?->getEmployeLangueNiveauId(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('', name: 'create_employe_langue', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $employeLangue = new EmployeLangue();
        $employeLangue->setEmployeeLangueLue($data['employeeLangueLue'] ?? null)
            ->setEmployeeLangueEcrite($data['employeeLangueEcrite'] ?? null)
            ->setEmployeeLangueParlee($data['employeeLangueParlee'] ?? null);

        if (isset($data['employeLangueNiveauId'])) {
            $niveau = $this->entityManager->getRepository(EmployeLangueNiveau::class)->find($data['employeLangueNiveauId']);
            if ($niveau) {
                $employeLangue->setEmployeLangueNiveauId($niveau);
            }
        }

        $this->entityManager->persist($employeLangue);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Employe langue created'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update_employe_langue', methods: ['PUT'])]
    public function update(int $id, Request $request, EmployeLangueRepository $employeLangueRepository): JsonResponse
    {
        $employeLangue = $employeLangueRepository->find($id);

        if (!$employeLangue) {
            return new JsonResponse(['message' => 'Employe langue not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $employeLangue->setEmployeeLangueLue($data['employeeLangueLue'] ?? $employeLangue->getEmployeeLangueLue())
            ->setEmployeeLangueEcrite($data['employeeLangueEcrite'] ?? $employeLangue->getEmployeeLangueEcrite())
            ->setEmployeeLangueParlee($data['employeeLangueParlee'] ?? $employeLangue->getEmployeeLangueParlee());

        if (isset($data['employeLangueNiveauId'])) {
            $niveau = $this->entityManager->getRepository(EmployeLangueNiveau::class)->find($data['employeLangueNiveauId']);
            if ($niveau) {
                $employeLangue->setEmployeLangueNiveauId($niveau);
            }
        }

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Employe langue updated'], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete_employe_langue', methods: ['DELETE'])]
    public function delete(int $id, EmployeLangueRepository $employeLangueRepository): JsonResponse
    {
        $employeLangue = $employeLangueRepository->find($id);

        if (!$employeLangue) {
            return new JsonResponse(['message' => 'Employe langue not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($employeLangue);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Employe langue deleted'], Response::HTTP_OK);
    }
}
