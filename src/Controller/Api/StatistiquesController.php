<?php

namespace App\Controller\Api;

use App\Repository\AppelOffresRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/statistiques', name: 'api_statistiques_')]
class StatistiquesController extends AbstractController
{
    private AppelOffresRepository $appelOffresRepository;

    public function __construct(AppelOffresRepository $appelOffresRepository)
    {
        $this->appelOffresRepository = $appelOffresRepository;
    }

    /**
     * 📊 Statistiques globales
     */
    #[Route('/global', name: 'global', methods: ['GET'])]
    public function getStatistiquesGlobal(): JsonResponse
    {
        try {
            $stats = [
                'total' => $this->appelOffresRepository->count([]),
                'participes' => $this->appelOffresRepository->count(['appelOffresParticipation' => 1]),
                'nonParticipes' => $this->appelOffresRepository->count(['appelOffresParticipation' => 0]),
                'gagnes' => $this->appelOffresRepository->count(['appelOffresEtat' => 'GAGNE']),
                'perdus' => $this->appelOffresRepository->countPerdus(),
                'enAttente' => $this->appelOffresRepository->count(['appelOffresEtat' => 'EN_ATTENTE']),
                'annules' => $this->appelOffresRepository->count(['appelOffresEtat' => 'ANNULE']),
                'reportes' => $this->appelOffresRepository->count(['appelOffresEtat' => 'REPORTE']),
            ];

            return $this->json($stats);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 🏆 AO par état (Gagné, Perdu, En attente, etc.)
     */
    #[Route('/par-etat', name: 'par_etat', methods: ['GET'])]
    public function getStatistiquesParEtat(): JsonResponse
    {
        try {
            $stats = $this->appelOffresRepository->getStatistiquesParEtat();
            return $this->json($stats);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 👥 AO par participation
     */
    #[Route('/par-participation', name: 'par_participation', methods: ['GET'])]
    public function getStatistiquesParParticipation(): JsonResponse
    {
        try {
            $participes = $this->appelOffresRepository->count(['appelOffresParticipation' => 1]);
            $nonParticipes = $this->appelOffresRepository->count(['appelOffresParticipation' => 0]);

            return $this->json([
                ['label' => 'Participé', 'value' => $participes],
                ['label' => 'Non Participé', 'value' => $nonParticipes]
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 🌍 AO par pays
     */
    #[Route('/par-pays', name: 'par_pays', methods: ['GET'])]
    public function getStatistiquesParPays(): JsonResponse
    {
        try {
            $stats = $this->appelOffresRepository->getStatistiquesParPays();
            return $this->json($stats);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 📅 AO par mois (année en cours)
     */
    #[Route('/par-mois', name: 'par_mois', methods: ['GET'])]
    public function getStatistiquesParMois(): JsonResponse
    {
        try {
            $annee = date('Y');
            $stats = $this->appelOffresRepository->getStatistiquesParMois($annee);
            return $this->json($stats);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 📅 AO par mois avec filtre d'année
     */
    #[Route('/par-mois/{annee}', name: 'par_mois_annee', methods: ['GET'])]
    public function getStatistiquesParMoisAnnee(int $annee): JsonResponse
    {
        try {
            $stats = $this->appelOffresRepository->getStatistiquesParMois($annee);
            return $this->json($stats);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 🎯 Taux de succès
     */
    #[Route('/taux-succes', name: 'taux_succes', methods: ['GET'])]
    public function getTauxSucces(): JsonResponse
    {
        try {
            $participes = $this->appelOffresRepository->count(['appelOffresParticipation' => 1]);
            $gagnes = $this->appelOffresRepository->count(['appelOffresEtat' => 'GAGNE']);
            
            $tauxSucces = $participes > 0 ? round(($gagnes / $participes) * 100, 2) : 0;

            return $this->json([
                'participes' => $participes,
                'gagnes' => $gagnes,
                'tauxSucces' => $tauxSucces
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}