<?php

namespace App\Controller\Api;

use App\Entity\Partenaire;
use App\Repository\PartenaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class PartenaireController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private PartenaireRepository $partenaireRepository;
    private SerializerInterface $serializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        PartenaireRepository $partenaireRepository,
        SerializerInterface $serializer
    ) {
        $this->entityManager = $entityManager;
        $this->partenaireRepository = $partenaireRepository;
        $this->serializer = $serializer;
    }

    #[Route('/api/getAll/partenaires', name: 'partenaire_getAll', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        try {
            $partenaires = $this->partenaireRepository->findAll();
            
            $data = $this->serializer->serialize(
                $partenaires,
                'json',
                ['groups' => 'partenaire:read']
            );

            return new JsonResponse($data, Response::HTTP_OK, [], true);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la récupération des partenaires',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/get/partenaire/{id}', name: 'partenaire_get', methods: ['GET'])]
    public function getOne(int $id): JsonResponse
    {
        try {
            $partenaire = $this->partenaireRepository->find($id);

            if (!$partenaire) {
                return $this->json([
                    'error' => 'Partenaire non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }

            $data = $this->serializer->serialize(
                $partenaire,
                'json',
                ['groups' => 'partenaire:read']
            );

            return new JsonResponse($data, Response::HTTP_OK, [], true);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la récupération du partenaire',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/create/partenaire', name: 'partenaire_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true) ?? [];

            if (empty($data['partenaireRaisonSociale'])) {
                return $this->json([
                    'error' => 'La raison sociale du partenaire est obligatoire'
                ], Response::HTTP_BAD_REQUEST);
            }

            $partenaire = new Partenaire();
            $partenaire->setPartenaireRaisonSociale($data['partenaireRaisonSociale']);

            if (isset($data['partenaireRaisonSocialeShort'])) {
                $partenaire->setPartenaireRaisonSocialeShort($data['partenaireRaisonSocialeShort']);
            }

            $partenaire->setPartenairePremierResponsable($data['partenairePremierResponsable'] ?? null);
            $partenaire->setPartenairePremierResponsableEmail($data['partenairePremierResponsableEmail'] ?? null);
            $partenaire->setPartenairePremierResponsableTelephone($data['partenairePremierResponsableTelephone'] ?? null);
            $partenaire->setPartenaireAdresse($data['partenaireAdresse'] ?? null);
            $partenaire->setPartenaireEmail($data['partenaireEmail'] ?? null);
            $partenaire->setPartenaireTelephone1($data['partenaireTelephone1'] ?? null);
            $partenaire->setPartenaireTelephone2($data['partenaireTelephone2'] ?? null);
            $partenaire->setPartenaireSiteWeb($data['partenaireSiteWeb'] ?? null);
            $partenaire->setPartenaireLinkedIn($data['partenaireLinkedIn'] ?? null);
            $partenaire->setPartenaireFacebook($data['partenaireFacebook'] ?? null);

            $this->entityManager->persist($partenaire);
            $this->entityManager->flush();

            $responseData = $this->serializer->serialize(
                $partenaire,
                'json',
                ['groups' => 'partenaire:read']
            );

            return new JsonResponse($responseData, Response::HTTP_CREATED, [], true);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la création du partenaire',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/edit/partenaire/{id}', name: 'partenaire_edit', methods: ['PUT'])]
    public function edit(int $id, Request $request): JsonResponse
    {
        try {
            $partenaire = $this->partenaireRepository->find($id);

            if (!$partenaire) {
                return $this->json([
                    'error' => 'Partenaire non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true) ?? [];

            if (isset($data['partenaireRaisonSociale'])) {
                $partenaire->setPartenaireRaisonSociale($data['partenaireRaisonSociale']);
            }
            if (isset($data['partenaireRaisonSocialeShort'])) {
                $partenaire->setPartenaireRaisonSocialeShort($data['partenaireRaisonSocialeShort']);
            }
            if (array_key_exists('partenairePremierResponsable', $data)) {
                $partenaire->setPartenairePremierResponsable($data['partenairePremierResponsable']);
            }
            if (array_key_exists('partenairePremierResponsableEmail', $data)) {
                $partenaire->setPartenairePremierResponsableEmail($data['partenairePremierResponsableEmail']);
            }
            if (array_key_exists('partenairePremierResponsableTelephone', $data)) {
                $partenaire->setPartenairePremierResponsableTelephone($data['partenairePremierResponsableTelephone']);
            }
            if (array_key_exists('partenaireAdresse', $data)) {
                $partenaire->setPartenaireAdresse($data['partenaireAdresse']);
            }
            if (array_key_exists('partenaireEmail', $data)) {
                $partenaire->setPartenaireEmail($data['partenaireEmail']);
            }
            if (array_key_exists('partenaireTelephone1', $data)) {
                $partenaire->setPartenaireTelephone1($data['partenaireTelephone1']);
            }
            if (array_key_exists('partenaireTelephone2', $data)) {
                $partenaire->setPartenaireTelephone2($data['partenaireTelephone2']);
            }
            if (array_key_exists('partenaireSiteWeb', $data)) {
                $partenaire->setPartenaireSiteWeb($data['partenaireSiteWeb']);
            }
            if (array_key_exists('partenaireLinkedIn', $data)) {
                $partenaire->setPartenaireLinkedIn($data['partenaireLinkedIn']);
            }
            if (array_key_exists('partenaireFacebook', $data)) {
                $partenaire->setPartenaireFacebook($data['partenaireFacebook']);
            }

            $this->entityManager->flush();

            $responseData = $this->serializer->serialize(
                $partenaire,
                'json',
                ['groups' => 'partenaire:read']
            );

            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la modification du partenaire',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/delete/partenaire/{id}', name: 'partenaire_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $partenaire = $this->partenaireRepository->find($id);

            if (!$partenaire) {
                return $this->json([
                    'error' => 'Partenaire non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }

            $this->entityManager->remove($partenaire);
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Partenaire supprimé avec succès'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la suppression du partenaire',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/search/partenaires', name: 'partenaire_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        try {
            $searchTerm = $request->query->get('q', '');
            
            $partenaires = $this->partenaireRepository->findByRaisonSociale($searchTerm);

            $data = $this->serializer->serialize(
                $partenaires,
                'json',
                ['groups' => 'partenaire:read']
            );

            return new JsonResponse($data, Response::HTTP_OK, [], true);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la recherche',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}