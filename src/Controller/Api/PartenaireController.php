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

    /**
     * Récupérer tous les partenaires
     */
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

    /**
     * Récupérer un partenaire par ID
     */
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

    /**
     * Créer un nouveau partenaire
     */
    #[Route('/api/create/partenaire', name: 'partenaire_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true) ?? [];

            // Validation
            if (empty($data['partenaireLibelle'])) {
                return $this->json([
                    'error' => 'Le libellé du partenaire est obligatoire'
                ], Response::HTTP_BAD_REQUEST);
            }

            $partenaire = new Partenaire();
            $partenaire->setPartenaireLibelle($data['partenaireLibelle']);

            if (isset($data['partenaireAcronyme'])) {
                $partenaire->setPartenaireAcronyme($data['partenaireAcronyme']);
            }

            // Champs harmonisés (noms "partenaire...") + rétrocompat
            $partenaire->setPartenairePremierResponsable($data['partenairePremierResponsable'] ?? ($data['premierResponsable'] ?? null));
            $partenaire->setPartenairePremierResponsableEmail($data['partenairePremierResponsableEmail'] ?? ($data['prEmail'] ?? null));
            $partenaire->setPartenairePremierResponsableTelephone($data['partenairePremierResponsableTelephone'] ?? ($data['prTel'] ?? null));
            $partenaire->setPartenairePremierResponsableAdresse($data['partenairePremierResponsableAdresse'] ?? ($data['adresse'] ?? null));

            $partenaire->setPartenairePays($data['partenairePays'] ?? ($data['pays'] ?? null));
            $partenaire->setPartenaireEmail($data['partenaireEmail'] ?? ($data['email'] ?? null));
            $partenaire->setPartenaireTelephone1($data['partenaireTelephone1'] ?? ($data['tel1'] ?? null));
            $partenaire->setPartenaireTelephone2($data['partenaireTelephone2'] ?? ($data['tel2'] ?? null));
            $partenaire->setPartenaireSiteWeb($data['partenaireSiteWeb'] ?? ($data['siteWeb'] ?? null));
            $partenaire->setPartenaireLinkedIn($data['partenaireLinkedIn'] ?? ($data['linkedIn'] ?? null));

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

    /**
     * Modifier un partenaire
     */
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

            if (isset($data['partenaireLibelle'])) {
                $partenaire->setPartenaireLibelle($data['partenaireLibelle']);
            }
            if (isset($data['partenaireAcronyme'])) {
                $partenaire->setPartenaireAcronyme($data['partenaireAcronyme']);
            }

            // Champs harmonisés (noms "partenaire...") + rétrocompat
            if (array_key_exists('partenairePremierResponsable', $data) || array_key_exists('premierResponsable', $data)) {
                $partenaire->setPartenairePremierResponsable($data['partenairePremierResponsable'] ?? ($data['premierResponsable'] ?? null));
            }
            if (array_key_exists('partenairePremierResponsableEmail', $data) || array_key_exists('prEmail', $data)) {
                $partenaire->setPartenairePremierResponsableEmail($data['partenairePremierResponsableEmail'] ?? ($data['prEmail'] ?? null));
            }
            if (array_key_exists('partenairePremierResponsableTelephone', $data) || array_key_exists('prTel', $data)) {
                $partenaire->setPartenairePremierResponsableTelephone($data['partenairePremierResponsableTelephone'] ?? ($data['prTel'] ?? null));
            }
            if (array_key_exists('partenairePremierResponsableAdresse', $data) || array_key_exists('adresse', $data)) {
                $partenaire->setPartenairePremierResponsableAdresse($data['partenairePremierResponsableAdresse'] ?? ($data['adresse'] ?? null));
            }

            if (array_key_exists('partenairePays', $data) || array_key_exists('pays', $data)) {
                $partenaire->setPartenairePays($data['partenairePays'] ?? ($data['pays'] ?? null));
            }
            if (array_key_exists('partenaireEmail', $data) || array_key_exists('email', $data)) {
                $partenaire->setPartenaireEmail($data['partenaireEmail'] ?? ($data['email'] ?? null));
            }
            if (array_key_exists('partenaireTelephone1', $data) || array_key_exists('tel1', $data)) {
                $partenaire->setPartenaireTelephone1($data['partenaireTelephone1'] ?? ($data['tel1'] ?? null));
            }
            if (array_key_exists('partenaireTelephone2', $data) || array_key_exists('tel2', $data)) {
                $partenaire->setPartenaireTelephone2($data['partenaireTelephone2'] ?? ($data['tel2'] ?? null));
            }
            if (array_key_exists('partenaireSiteWeb', $data) || array_key_exists('siteWeb', $data)) {
                $partenaire->setPartenaireSiteWeb($data['partenaireSiteWeb'] ?? ($data['siteWeb'] ?? null));
            }
            if (array_key_exists('partenaireLinkedIn', $data) || array_key_exists('linkedIn', $data)) {
                $partenaire->setPartenaireLinkedIn($data['partenaireLinkedIn'] ?? ($data['linkedIn'] ?? null));
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

    /**
     * Supprimer un partenaire
     */
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

    /**
     * Rechercher des partenaires par libellé
     */
    #[Route('/api/search/partenaires', name: 'partenaire_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        try {
            $searchTerm = $request->query->get('q', '');
            
            $partenaires = $this->partenaireRepository->findByLibelle($searchTerm);

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