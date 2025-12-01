<?php

namespace App\Controller\Api;

use App\Entity\Collaborateur;
use App\Entity\EmployeExperience;
use App\Entity\Pays;
use App\Repository\EmployeExperienceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/employeexperience', name: 'api_employe_experience_')]
class EmployeExperienceController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    #[Route('', name: 'get_all_employe_experience', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $employeExperienceRepo = $this->entityManager->getRepository(EmployeExperience::class);
        $employeExperiences = $employeExperienceRepo->findAll();

        $data = [];
        foreach ($employeExperiences as $employeExperience) {
            $data[] = [
                'employeExperienceId' => $employeExperience->getEmployeExperienceId(),
                'employeExperienceOrganismeEmployeur' => $employeExperience->getEmployeExperienceOrganismeEmployeur(),
                'employeExperiencePeriode' => $employeExperience->getEmployeExperiencePeriode(),
                'employeExperienceFonctionOccupe' => $employeExperience->getEmployeExperienceFonctionOccupe(),
                'collaborateurId' => $employeExperience->getCollaborateur()?->getCollaborateurId(),
                'paysId' => $employeExperience->getPays()?->getPaysId(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get_employe_experience', methods: ['GET'])]
    public function getById(EmployeExperience $employeExperience): JsonResponse
    {
        $data = [
            'employeExperienceId' => $employeExperience->getEmployeExperienceId(),
            'employeExperienceOrganismeEmployeur' => $employeExperience->getEmployeExperienceOrganismeEmployeur(),
            'employeExperiencePeriode' => $employeExperience->getEmployeExperiencePeriode(),
            'employeExperienceFonctionOccupe' => $employeExperience->getEmployeExperienceFonctionOccupe(),
            'collaborateurId' => $employeExperience->getCollaborateur()?->getCollaborateurId(),
            'paysId' => $employeExperience->getPays()?->getPaysId(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('', name: 'create_employe_experience', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $employeExperience = new EmployeExperience();
        $employeExperience->setEmployeExperienceOrganismeEmployeur($data['employeExperienceOrganismeEmployeur'] ?? null)
            ->setEmployeExperiencePeriode($data['employeExperiencePeriode'] ?? null)
            ->setEmployeExperienceFonctionOccupe($data['employeExperienceFonctionOccupe'] ?? null);

        if (isset($data['collaborateurId'])) {
            $collaborateur = $this->entityManager->getRepository(Collaborateur::class)->find($data['collaborateurId']);
            if ($collaborateur) {
                $employeExperience->setCollaborateur($collaborateur);
            }
        }

        if (isset($data['paysId'])) {
            $pays = $this->entityManager->getRepository(Pays::class)->find($data['paysId']);
            if ($pays) {
                $employeExperience->setPays($pays);
            }
        }

        $this->entityManager->persist($employeExperience);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Employe experience created'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update_employe_experience', methods: ['PUT'])]
    public function update(int $id, Request $request, EmployeExperienceRepository $employeExperienceRepository): JsonResponse
    {
        $employeExperience = $employeExperienceRepository->find($id);

        if (!$employeExperience) {
            return new JsonResponse(['message' => 'Employe experience not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $employeExperience->setEmployeExperienceOrganismeEmployeur($data['employeExperienceOrganismeEmployeur'] ?? $employeExperience->getEmployeExperienceOrganismeEmployeur())
            ->setEmployeExperiencePeriode($data['employeExperiencePeriode'] ?? $employeExperience->getEmployeExperiencePeriode())
            ->setEmployeExperienceFonctionOccupe($data['employeExperienceFonctionOccupe'] ?? $employeExperience->getEmployeExperienceFonctionOccupe());

        if (isset($data['collaborateurId'])) {
            $collaborateur = $this->entityManager->getRepository(Collaborateur::class)->find($data['collaborateurId']);
            if ($collaborateur) {
                $employeExperience->setCollaborateur($collaborateur);
            }
        }

        if (isset($data['paysId'])) {
            $pays = $this->entityManager->getRepository(Pays::class)->find($data['paysId']);
            if ($pays) {
                $employeExperience->setPays($pays);
            }
        }

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Employe experience updated'], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete_employe_experience', methods: ['DELETE'])]
    public function delete(int $id, EmployeExperienceRepository $employeExperienceRepository): JsonResponse
    {
        $employeExperience = $employeExperienceRepository->find($id);

        if (!$employeExperience) {
            return new JsonResponse(['message' => 'Employe experience not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($employeExperience);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Employe experience deleted'], Response::HTTP_OK);
    }
}