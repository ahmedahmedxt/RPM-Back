<?php

namespace App\Controller\Api;

use App\Entity\AppelOffre;
use App\Entity\Partenaire;
use App\Entity\AppelOffrePartenaire;
use App\Repository\AppelOffreRepository;
use App\Repository\PartenaireRepository;
use App\Repository\AppelOffrePartenaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppelOffrePartenaireController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private AppelOffreRepository $appelOffreRepository;
    private PartenaireRepository $partenaireRepository;
    private AppelOffrePartenaireRepository $appelOffrePartenaireRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        AppelOffreRepository $appelOffreRepository,
        PartenaireRepository $partenaireRepository,
        AppelOffrePartenaireRepository $appelOffrePartenaireRepository
    ) {
        $this->entityManager = $entityManager;
        $this->appelOffreRepository = $appelOffreRepository;
        $this->partenaireRepository = $partenaireRepository;
        $this->appelOffrePartenaireRepository = $appelOffrePartenaireRepository;
    }

    /**
     * Ajouter un partenaire à un appel d'offre avec son rôle
     */
    #[Route('/api/appeloffre/{id}/add-partenaire', name: 'appeloffre_add_partenaire', methods: ['POST'])]
    public function addPartenaire(int $id, Request $request): JsonResponse
    {
        try {
            $appelOffre = $this->appelOffreRepository->find($id);
            if (!$appelOffre) {
                return $this->json(['error' => 'Appel d\'offre non trouvé'], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['partenaireId']) || !isset($data['role'])) {
                return $this->json(['error' => 'partenaireId et role sont requis'], Response::HTTP_BAD_REQUEST);
            }

            $partenaire = $this->partenaireRepository->find($data['partenaireId']);
            if (!$partenaire) {
                return $this->json(['error' => 'Partenaire non trouvé'], Response::HTTP_NOT_FOUND);
            }

            // Vérifier si le partenaire n'est pas déjà associé
            $existing = $this->appelOffrePartenaireRepository->findOneBy([
                'appelOffre' => $appelOffre,
                'partenaire' => $partenaire
            ]);

            if ($existing) {
                return $this->json(['error' => 'Ce partenaire est déjà associé à cet appel d\'offre'], Response::HTTP_CONFLICT);
            }

            // Créer la liaison avec le rôle
            $appelOffrePartenaire = new AppelOffrePartenaire();
            $appelOffrePartenaire->setAppelOffre($appelOffre);
            $appelOffrePartenaire->setPartenaire($partenaire);
            $appelOffrePartenaire->setRole($data['role']);

            $this->entityManager->persist($appelOffrePartenaire);
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Partenaire ajouté avec succès',
                'data' => [
                    'id' => $appelOffrePartenaire->getId(),
                    'partenaireId' => $partenaire->getPartenaireId(),
                    'partenaireLibelle' => $partenaire->getPartenaireLibelle(),
                    'partenaireAcronyme' => $partenaire->getPartenaireAcronyme(),
                    'role' => $data['role']
                ]
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de l\'ajout du partenaire',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Récupérer tous les partenaires d'un appel d'offre avec leurs rôles
     */
    #[Route('/api/appeloffre/{id}/partenaires', name: 'appeloffre_get_partenaires', methods: ['GET'])]
    public function getPartenaires(int $id): JsonResponse
    {
        try {
            $appelOffre = $this->appelOffreRepository->find($id);
            if (!$appelOffre) {
                return $this->json(['error' => 'Appel d\'offre non trouvé'], Response::HTTP_NOT_FOUND);
            }

            $partenaires = [];
            foreach ($appelOffre->getAppelOffrePartenaires() as $aop) {
                $partenaires[] = [
                    'id' => $aop->getId(),
                    'partenaireId' => $aop->getPartenaire()->getPartenaireId(),
                    'partenaireLibelle' => $aop->getPartenaire()->getPartenaireLibelle(),
                    'partenaireAcronyme' => $aop->getPartenaire()->getPartenaireAcronyme(),
                    'role' => $aop->getRole()
                ];
            }

            return $this->json($partenaires, Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la récupération des partenaires',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Supprimer un partenaire d'un appel d'offre
     */
    #[Route('/api/appeloffre/partenaire/{id}', name: 'appeloffre_remove_partenaire', methods: ['DELETE'])]
    public function removePartenaire(int $id): JsonResponse
    {
        try {
            $appelOffrePartenaire = $this->appelOffrePartenaireRepository->find($id);
            
            if (!$appelOffrePartenaire) {
                return $this->json(['error' => 'Relation non trouvée'], Response::HTTP_NOT_FOUND);
            }

            $this->entityManager->remove($appelOffrePartenaire);
            $this->entityManager->flush();

            return $this->json(['message' => 'Partenaire retiré avec succès'], Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la suppression',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mettre à jour le rôle d'un partenaire dans un appel d'offre
     */
    #[Route('/api/appeloffre/partenaire/{id}/role', name: 'appeloffre_update_role', methods: ['PUT'])]
    public function updateRole(int $id, Request $request): JsonResponse
    {
        try {
            $appelOffrePartenaire = $this->appelOffrePartenaireRepository->find($id);
            
            if (!$appelOffrePartenaire) {
                return $this->json(['error' => 'Relation non trouvée'], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['role'])) {
                return $this->json(['error' => 'Le champ role est requis'], Response::HTTP_BAD_REQUEST);
            }

            $appelOffrePartenaire->setRole($data['role']);
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Rôle mis à jour avec succès',
                'role' => $data['role']
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la mise à jour',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}