<?php

namespace App\Controller\Api;

use App\Entity\Employe;
use App\Entity\EmployeEducation;
use App\Entity\TypeDiplome;
use App\Repository\EmployeEducationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/employeeducation', name: 'api_employe_education_')]
class EmployeEducationController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    #[Route('', name: 'get_all_employe_education', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $employeEducationRepo = $this->entityManager->getRepository(EmployeEducation::class);
        $employeEducations = $employeEducationRepo->findAll();

        $data = [];
        foreach ($employeEducations as $employeEducation) {
            $data[] = [
                'employeEducationId' => $employeEducation->getEmployeEducationId(),
                'employeEducationNatureEtudes' => $employeEducation->getEmployeEducationNatureEtudes(),
                'employeEducationEtablissement' => $employeEducation->getEmployeEducationEtablissement(),
                'employeEducationAnneeObtention' => $employeEducation->getEmployeEducationAnneeObtention()?->format('Y-m-d'),
                'employeId' => $employeEducation->getEmploye()?->getEmployeId(),
                'typeDiplomeId' => $employeEducation->getTypeDiplome()?->getTypeDiplomeId(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_employe_education', methods: ['GET'])]
    public function getById(EmployeEducation $employeEducation): JsonResponse
    {
        $data = [
            'employeEducationId' => $employeEducation->getEmployeEducationId(),
            'employeEducationNatureEtudes' => $employeEducation->getEmployeEducationNatureEtudes(),
            'employeEducationEtablissement' => $employeEducation->getEmployeEducationEtablissement(),
            'employeEducationAnneeObtention' => $employeEducation->getEmployeEducationAnneeObtention()?->format('Y-m-d'),
            'employeId' => $employeEducation->getEmploye()?->getEmployeId(),
            'typeDiplomeId' => $employeEducation->getTypeDiplome()?->getTypeDiplomeId(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('', name: 'create_employe_education', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $employeEducation = new EmployeEducation();
        $employeEducation->setEmployeEducationNatureEtudes($data['employeEducationNatureEtudes'] ?? null)
            ->setEmployeEducationEtablissement($data['employeEducationEtablissement'] ?? null)
            ->setEmployeEducationAnneeObtention(isset($data['employeEducationAnneeObtention']) ? new \DateTime($data['employeEducationAnneeObtention']) : null);

        if (isset($data['employeId'])) {
            $employe = $this->entityManager->getRepository(Employe::class)->find($data['employeId']);
            if ($employe) {
                $employeEducation->setEmploye($employe);
            }
        }

        if (isset($data['typeDiplomeId'])) {
            $typeDiplome = $this->entityManager->getRepository(TypeDiplome::class)->find($data['typeDiplomeId']);
            if ($typeDiplome) {
                $employeEducation->setTypeDiplome($typeDiplome);
            }
        }

        $this->entityManager->persist($employeEducation);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Employe education created'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update_employe_education', methods: ['PUT'])]
    public function update(int $id, Request $request, EmployeEducationRepository $employeEducationRepository): JsonResponse
    {
        $employeEducation = $employeEducationRepository->find($id);

        if (!$employeEducation) {
            return new JsonResponse(['message' => 'Employe education not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $employeEducation->setEmployeEducationNatureEtudes($data['employeEducationNatureEtudes'] ?? $employeEducation->getEmployeEducationNatureEtudes())
            ->setEmployeEducationEtablissement($data['employeEducationEtablissement'] ?? $employeEducation->getEmployeEducationEtablissement())
            ->setEmployeEducationAnneeObtention(isset($data['employeEducationAnneeObtention']) ? new \DateTime($data['employeEducationAnneeObtention']) : $employeEducation->getEmployeEducationAnneeObtention());

        if (isset($data['employeId'])) {
            $employe = $this->entityManager->getRepository(Employe::class)->find($data['employeId']);
            if ($employe) {
                $employeEducation->setEmploye($employe);
            }
        }

        if (isset($data['typeDiplomeId'])) {
            $typeDiplome = $this->entityManager->getRepository(TypeDiplome::class)->find($data['typeDiplomeId']);
            if ($typeDiplome) {
                $employeEducation->setTypeDiplome($typeDiplome);
            }
        }

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Employe education updated'], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete_employe_education', methods: ['DELETE'])]
    public function delete(int $id, EmployeEducationRepository $employeEducationRepository): JsonResponse
    {
        $employeEducation = $employeEducationRepository->find($id);

        if (!$employeEducation) {
            return new JsonResponse(['message' => 'Employe education not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($employeEducation);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Employe education deleted'], Response::HTTP_OK);
    }
}
