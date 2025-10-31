<?php

namespace App\Controller\Api;

use App\Entity\AppelOffres;
use App\Entity\Partenaire;
use App\Entity\AppelOffresPartenaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppelOffresPartenaireController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * Ajouter un partenaire à un appel d'offres avec son rôle
     */
    #[Route('/api/appeloffres/{id}/add-partenaire', name: 'appeloffres_add_partenaire', methods: ['POST'])]
    public function addPartenaire(int $id, Request $request): JsonResponse
    {
        try {
            $appelOffres = $this->em->getRepository(AppelOffres::class)->find($id);
            if (!$appelOffres) {
                return $this->json(['error' => 'Appel d\'offres non trouvé'], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true) ?? [];

            if (!isset($data['partenaireId']) || !isset($data['role'])) {
                return $this->json(['error' => 'partenaireId et role sont requis'], Response::HTTP_BAD_REQUEST);
            }

            $partenaire = $this->em->getRepository(Partenaire::class)->find($data['partenaireId']);
            if (!$partenaire) {
                return $this->json(['error' => 'Partenaire non trouvé'], Response::HTTP_NOT_FOUND);
            }

            // Vérifier si le partenaire n'est pas déjà associé
            $existing = $this->em->getRepository(AppelOffresPartenaire::class)->findOneBy([
                'appelOffres' => $appelOffres,
                'partenaire' => $partenaire
            ]);

            if ($existing) {
                return $this->json(['error' => 'Ce partenaire est déjà associé à cet appel d\'offres'], Response::HTTP_CONFLICT);
            }

            // Créer la liaison avec le rôle
            $aop = new AppelOffresPartenaire();
            $aop->setAppelOffres($appelOffres);
            $aop->setPartenaire($partenaire);
            $aop->setRole($data['role']);

            $this->em->persist($aop);
            $this->em->flush();

            return $this->json([
                'message' => 'Partenaire ajouté avec succès',
                'data' => [
                    'id' => $aop->getId(),
                    'partenaireId' => $partenaire->getPartenaireId(),
                    'partenaireLibelle' => $partenaire->getPartenaireLibelle(),
                    'partenaireAcronyme' => $partenaire->getPartenaireAcronyme(),
                    'role' => $aop->getRole()
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
     * Récupérer tous les partenaires d'un appel d'offres avec leurs rôles
     */
    #[Route('/api/appeloffres/{id}/partenaires', name: 'appeloffres_get_partenaires', methods: ['GET'])]
    public function getPartenaires(int $id): JsonResponse
    {
        try {
            $appelOffres = $this->em->getRepository(AppelOffres::class)->find($id);
            if (!$appelOffres) {
                return $this->json(['error' => 'Appel d\'offres non trouvé'], Response::HTTP_NOT_FOUND);
            }

            $partenaires = [];
            foreach ($appelOffres->getAppelOffresPartenaires() as $aop) {
                $p = $aop->getPartenaire();
                if ($p) {
                    $partenaires[] = [
                        'id' => $aop->getId(),
                        'partenaireId' => $p->getPartenaireId(),
                        'partenaireLibelle' => $p->getPartenaireLibelle(),
                        'partenaireAcronyme' => $p->getPartenaireAcronyme(),
                        'role' => $aop->getRole()
                    ];
                }
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
     * Supprimer un partenaire d'un appel d'offres
     */
    #[Route('/api/appeloffres/partenaire/{id}', name: 'appeloffres_remove_partenaire', methods: ['DELETE'])]
    public function removePartenaire(int $id): JsonResponse
    {
        try {
            $aop = $this->em->getRepository(AppelOffresPartenaire::class)->find($id);

            if (!$aop) {
                return $this->json(['error' => 'Relation non trouvée'], Response::HTTP_NOT_FOUND);
            }

            $this->em->remove($aop);
            $this->em->flush();

            return $this->json(['message' => 'Partenaire retiré avec succès'], Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur lors de la suppression',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Mettre à jour le rôle d'un partenaire dans un appel d'offres
     */
    #[Route('/api/appeloffres/partenaire/{id}/role', name: 'appeloffres_update_role', methods: ['PUT'])]
    public function updateRole(int $id, Request $request): JsonResponse
    {
        try {
            $aop = $this->em->getRepository(AppelOffresPartenaire::class)->find($id);

            if (!$aop) {
                return $this->json(['error' => 'Relation non trouvée'], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true) ?? [];

            if (!isset($data['role'])) {
                return $this->json(['error' => 'Le champ role est requis'], Response::HTTP_BAD_REQUEST);
            }

            $aop->setRole($data['role']);
            $this->em->flush();

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