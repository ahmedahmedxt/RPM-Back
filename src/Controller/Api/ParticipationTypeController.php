<?php

namespace App\Controller\Api;

use App\Entity\ParticipationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ParticipationTypeController extends AbstractController
{
    #[Route('/api/getAll/participationTypes', name: 'participation_type_getAll', methods: ['GET'])]
    public function getAll(EntityManagerInterface $em): JsonResponse
    {
        try {
            $repository = $em->getRepository(ParticipationType::class);
            $participationTypes = $repository->findAll();
            
            $data = array_map(function($type) {
                return [
                    'participationTypeId' => $type->getParticipationTypeId(),
                    'participationTypeLibelle' => $type->getParticipationTypeLibelle(),
                ];
            }, $participationTypes);

            return new JsonResponse($data, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la récupération des types de participation',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/get/participationType/{id}', name: 'participation_type_get', methods: ['GET'])]
    public function getOne(int $id, EntityManagerInterface $em): JsonResponse
    {
        try {
            $repository = $em->getRepository(ParticipationType::class);
            $participationType = $repository->find($id);

            if (!$participationType) {
                return new JsonResponse([
                    'error' => 'Type de participation non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }

            $data = [
                'participationTypeId' => $participationType->getParticipationTypeId(),
                'participationTypeLibelle' => $participationType->getParticipationTypeLibelle(),
            ];

            return new JsonResponse($data, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la récupération du type de participation',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}