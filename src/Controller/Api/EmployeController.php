<?php

namespace App\Controller\Api;

use App\Entity\Employe;
use App\Repository\EmployePosteRepository;
use App\Repository\EmployeRepository;
use App\Repository\SituationFamilialeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/employe', name: 'api_employe')]
class EmployeController extends AbstractController
{
    private $entityManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(EmployeRepository $repository): JsonResponse
    {
        $employes = $repository->findAll();

        // Custom serialization logic
        $data = array_map(function($employe) {
            return [
                'employeId' => $employe->getEmployeId(),
                'employeNom' => $employe->getEmployeNom(),
                'employePrenom' => $employe->getEmployePrenom(),
                'employeAdresse' => $employe->getEmployeAdresse(),
                'employePoste' => $employe->getEmployePoste()->getEmployePosteLibelle()
            ];
        }, $employes);

        return new JsonResponse($data, Response::HTTP_OK);
    }


    #[Route('/{id}', name: 'get_by_id', methods: ['GET'])]
    public function getById(int $id, EmployeRepository $employeRepository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        // $this->checkToken($tokenStorage);
        $employe = $employeRepository->find($id);

        if (!$employe) {
            return new JsonResponse(['message' => 'Employé not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->serializeEmploye($employe), Response::HTTP_OK);
    }

    #[Route('/details/{id}', name: 'get_details_by_id', methods: ['GET'])]
    public function getEmployeDetails(int $id, EmployeRepository $employeRepository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        // $this->checkToken($tokenStorage);
        $employe = $employeRepository->find($id);

        if (!$employe) {
            return new JsonResponse(['message' => 'Employé not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->serializeEmployeDetails($employe), Response::HTTP_OK);
    }

    private function serializeEmployeDetails(Employe $employe): array
    {
        return [
            'employeNom' => $employe->getEmployeNom(),
            'employePrenom' => $employe->getEmployePrenom(),
            'employeAdresse' => $employe->getEmployeAdresse(),
            'employeLieuNaissance' => $employe->getEmployeLieuNaissance(),
            'employeDateNaissance' => $employe->getEmployeDateNaissance()->format('Y-m-d'),
            'employePrincipaleQualification' => $employe->getEmployePrincipaleQualification(),
            'employePoste' => $employe->getEmployePoste()->getEmployePosteLibelle(),
            'employeAffiliationDesAssociationsGroupPro' => $employe->getEmployeAffiliationDesAssociationsGroupPro(),
            'employeFormationAutre' => $employe->getEmployeFormationAutre(),
            'situationFamiliale' => $employe->getSituationFamiliale()->getSituationFamilialeLibelle(),
            'employeRemarque' => $employe->getEmployeRemarque()
        ];
    }

    private function serializeEmploye(Employe $employe): array
    {
        return [
            'employeNom' => $employe->getEmployeNom(),
            'employePrenom' => $employe->getEmployePrenom(),
            'employeAdresse' => $employe->getEmployeAdresse(),
            'employeLieuNaissance' => $employe->getEmployeLieuNaissance(),
            'employeDateNaissance' => $employe->getEmployeDateNaissance()->format('Y-m-d'),
            'employePrincipaleQualification' => $employe->getEmployePrincipaleQualification(),
            'employePosteId' => $employe->getEmployePoste()->getEmployePosteId(),
            'employeAffiliationDesAssociationsGroupPro' => $employe->getEmployeAffiliationDesAssociationsGroupPro(),
            'employeFormationAutre' => $employe->getEmployeFormationAutre(),
            'situationFamilialeId' => $employe->getSituationFamiliale()->getSituationFamilialeId(),
            'employeRemarque' => $employe->getEmployeRemarque()
        ];
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, SituationFamilialeRepository $situationFamilialeRepository, EmployePosteRepository $employePosteRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $employe = new Employe();
        $employe->setEmployeNom($data['employeNom'] ?? null);
        $employe->setEmployePrenom($data['employePrenom'] ?? null);
        $employe->setEmployeDateNaissance(new \DateTime($data['employeDateNaissance']));
        $employe->setEmployeAdresse($data['employeAdresse'] ?? null);
        $employe->setEmployePrincipaleQualification($data['employePrincipaleQualification'] ?? null);
        $employe->setEmployeFormationAutre($data['employeFormationAutre'] ?? null);
        $employe->setEmployeAffiliationDesAssociationsGroupPro($data['employeAffiliationDesAssociationsGroupPro'] ?? null);
        $employe->setEmployeLieuNaissance($data['employeLieuNaissance'] ?? null);
        $employe->setEmployeRemarque($data['employeRemarque'] ?? null);

        // Handle foreign key for SituationFamiliale
        if (isset($data['situationFamilialeId'])) {
            $situationFamiliale = $situationFamilialeRepository->find($data['situationFamilialeId']);
            if ($situationFamiliale) {
                $employe->setSituationFamiliale($situationFamiliale);
            } else {
                return new JsonResponse(['message' => 'Invalid SituationFamiliale ID'], Response::HTTP_BAD_REQUEST);
            }
        }

        // Handle foreign key for EmployePoste
        if (isset($data['employePosteId'])) {
            $employePoste = $employePosteRepository->find($data['employePosteId']);
            if ($employePoste) {
                $employe->setEmployePoste($employePoste);
            } else {
                return new JsonResponse(['message' => 'Invalid EmployePoste ID'], Response::HTTP_BAD_REQUEST);
            }
        }

        // Persist and save
        $entityManager->persist($employe);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Employe created'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request, EmployeRepository $employeRepository, EntityManagerInterface $entityManager, SituationFamilialeRepository $situationFamilialeRepository, EmployePosteRepository $employePosteRepository): JsonResponse
    {
        $employe = $employeRepository->find($id);

        if (!$employe) {
            return new JsonResponse(['message' => 'Employe not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $employe->setEmployeNom($data['employeNom'] ?? $employe->getEmployeNom());
        $employe->setEmployePrenom($data['employePrenom'] ?? $employe->getEmployePrenom());
        $employe->setEmployeDateNaissance(new \DateTime($data['employeDateNaissance']));
        $employe->setEmployeAdresse($data['employeAdresse'] ?? $employe->getEmployeAdresse());
        $employe->setEmployePrincipaleQualification($data['employePrincipaleQualification'] ?? $employe->getEmployePrincipaleQualification());
        $employe->setEmployeFormationAutre($data['employeFormationAutre'] ?? $employe->getEmployeFormationAutre());
        $employe->setEmployeAffiliationDesAssociationsGroupPro($data['employeAffiliationDesAssociationsGroupPro'] ?? $employe->getEmployeAffiliationDesAssociationsGroupPro());
        $employe->setEmployeLieuNaissance($data['employeLieuNaissance'] ?? $employe->getEmployeLieuNaissance());
        $employe->setEmployeRemarque($data['employeRemarque'] ?? $employe->getEmployeRemarque());

        // Handle foreign key for SituationFamiliale
        if (isset($data['situationFamilialeId'])) {
            $situationFamiliale = $situationFamilialeRepository->find($data['situationFamilialeId']);
            if ($situationFamiliale) {
                $employe->setSituationFamiliale($situationFamiliale);
            } else {
                return new JsonResponse(['message' => 'Invalid SituationFamiliale ID'], Response::HTTP_BAD_REQUEST);
            }
        }

        // Handle foreign key for EmployePoste
        if (isset($data['employePosteId'])) {
            $employePoste = $employePosteRepository->find($data['employePosteId']);
            if ($employePoste) {
                $employe->setEmployePoste($employePoste);
            } else {
                return new JsonResponse(['message' => 'Invalid EmployePoste ID'], Response::HTTP_BAD_REQUEST);
            }
        }

        // Persist and save
        $entityManager->flush();

        return new JsonResponse(['message' => 'Employe updated'], Response::HTTP_OK);
    }


    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, EmployeRepository $employeRepository, TokenStorageInterface $tokenStorage): JsonResponse
    {
        // $this->checkToken($tokenStorage);
        $employe = $employeRepository->find($id);

        if (!$employe) {
            return new JsonResponse(['message' => 'Employé not found'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($employe);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Employé deleted'], Response::HTTP_OK);
    }

    public function checkToken(TokenStorageInterface $tokenStorage): void
    {
        // Récupérer le token d'authentification de Symfony
        $token = $tokenStorage->getToken();

        // Vérifier si le token d'authentification est présent et est de type TokenInterface
        if (!$token instanceof TokenInterface) {
            throw new AccessDeniedHttpException('Token d\'authentification manquant ou invalide');
        }
    }
}
