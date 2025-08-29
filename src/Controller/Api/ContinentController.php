<?php

namespace App\Controller\Api;

use App\Entity\Continent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/api/continent', name: 'api_continent_')]
class ContinentController extends AbstractController
{
    #[Route('', name: 'api_get_all_continent', methods: ['GET'])]
    public function getAll(EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer les continents triés par nom
        $continentRepository = $entityManager->getRepository(Continent::class);
        $continent = $continentRepository->findBy([], ['continentName' => 'ASC']);

        $continentData = [];
        foreach ($continent as $continentItem) {
            $continentData[] = [
                'continentId' => $continentItem->getContinentId(),
                'continentName' => $continentItem->getContinentName(),
            ];
        }

        return new JsonResponse($continentData, Response::HTTP_OK);
    }
}
