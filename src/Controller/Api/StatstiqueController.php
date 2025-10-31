<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CallsForTendersRepository;
use App\Repository\EmployeRepository;
use App\Repository\ProjetRepository;
use App\Repository\ClientRepository;
use App\Repository\AppelOffresRepository;

class StatstiqueController extends AbstractController
{
    private $callsForTendersRepository;
    private $employeeRepository;
    private $projectRepository;
    private $clientRepository;
    private $appelOffresRepository;

    public function __construct(
        CallsForTendersRepository $callsForTendersRepository,
        EmployeRepository $employeeRepository,
        ProjetRepository $projectRepository,
        ClientRepository $clientRepository,
        AppelOffresRepository $appelOffresRepository
    ) {
        $this->callsForTendersRepository = $callsForTendersRepository;
        $this->employeeRepository = $employeeRepository;
        $this->projectRepository = $projectRepository;
        $this->clientRepository = $clientRepository;
        $this->appelOffresRepository = $appelOffresRepository;
    }

    #[Route('/api/statistics/employees', name: 'api_statistics_employees')]
    public function getEmployeesStatistics(): JsonResponse
    {
        $totalEmployees = $this->employeeRepository->count([]);

        return new JsonResponse(['totalEmployees' => $totalEmployees]);
    }

    #[Route('/api/statistics/projects', name: 'api_statistics_projects')]
    public function getProjectsStatistics(): JsonResponse
    {
        $totalProjects = $this->projectRepository->count([]);

        return new JsonResponse(['totalProjects' => $totalProjects]);
    }

    #[Route('/api/statistics/clients', name: 'api_statistics_clients')]
    public function getClientsStatistics(): JsonResponse
    {
        $totalClients = $this->clientRepository->count([]);

        return new JsonResponse(['totalClients' => $totalClients]);
    }

    #[Route('/api/statistics/AppeslOffres', name: 'api_statistics_appels_offres')]
    public function getAppelsOffresStatistics(): JsonResponse
    {
        $totalAppeslOffres = $this->appelOffresRepository->count([]);

        return new JsonResponse(['totalAppeslOffres' => $totalAppeslOffres]);
    }
}