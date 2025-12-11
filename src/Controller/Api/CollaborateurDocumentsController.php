<?php

namespace App\Controller\Api;

use App\Entity\CollaborateurDocuments;
use App\Entity\Collaborateur;
use App\Entity\TypeDocument;
use App\Repository\CollaborateurDocumentsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/collaborateurdocuments', name: 'api_collaborateur_documents_')]
class CollaborateurDocumentsController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    #[Route('/getAll', name: 'get_all_paginated', methods: ['GET'])]
    public function getAllPaginated(Request $request): JsonResponse
    {
        try {
            $page = max(1, (int) $request->query->get('page', 1));
            $limit = max(1, min(100, (int) $request->query->get('limit', 10)));
            $offset = ($page - 1) * $limit;

            $sortField = $request->query->get('sortField', 'collaborateurDocumentsId');
            $sortDir = $request->query->get('sortDir', 'ASC');
            $search = trim($request->query->get('search', ''));

            $allowedSortFields = [
                'collaborateurDocumentsId',
                'collaborateurDocumentsPdf',
                'collaborateurDocumentsType'
            ];
            
            if (!in_array($sortField, $allowedSortFields)) {
                $sortField = 'collaborateurDocumentsId';
            }
            $sortDir = strtoupper($sortDir) === 'DESC' ? 'DESC' : 'ASC';

            $repo = $this->entityManager->getRepository(CollaborateurDocuments::class);
            $qb = $repo->createQueryBuilder('cd');

            $qb->leftJoin('cd.collaborateurDocumentsType', 'td')
               ->leftJoin('cd.collaborateur', 'c');

            if (!empty($search)) {
                $qb->where(
                    $qb->expr()->orX(
                        $qb->expr()->like('cd.collaborateurDocumentsPdf', ':search'),
                        $qb->expr()->like('td.typeDocumentLibelle', ':search'),
                        $qb->expr()->like('c.collaborateurNom', ':search'),
                        $qb->expr()->like('c.collaborateurPrenom', ':search')
                    )
                )
                ->setParameter('search', '%' . $search . '%');
            }

            $total = (int) (clone $qb)
                ->select('COUNT(cd)')
                ->getQuery()
                ->getSingleScalarResult();

            $qb->orderBy('cd.' . $sortField, $sortDir);

            $qb->setFirstResult($offset)
               ->setMaxResults($limit);

            $items = $qb->getQuery()->getResult();

            $data = [];
            foreach ($items as $document) {
                $typeDocument = $document->getCollaborateurDocumentsType();
                $collaborateur = $document->getCollaborateur();
                
                $data[] = [
                    'collaborateurDocumentsId' => $document->getCollaborateurDocumentsId(),
                    'collaborateurDocumentsPdf' => $document->getCollaborateurDocumentsPdf(),
                    'collaborateurDocumentsTypeId' => $typeDocument ? $typeDocument->getTypeDocumentId() : null,
                    'typeDocument' => $typeDocument ? [
                        'typeDocumentId' => $typeDocument->getTypeDocumentId(),
                        'typeDocumentLibelle' => $typeDocument->getTypeDocumentLibelle(),
                    ] : null,
                    'collaborateurId' => $collaborateur ? $collaborateur->getCollaborateurId() : null,
                    'collaborateur' => $collaborateur ? [
                        'collaborateurId' => $collaborateur->getCollaborateurId(),
                        'collaborateurNom' => $collaborateur->getCollaborateurNom(),
                        'collaborateurPrenom' => $collaborateur->getCollaborateurPrenom(),
                    ] : null,
                ];
            }

            return new JsonResponse([
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'data' => $data
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            error_log('Erreur dans getAllPaginated: ' . $e->getMessage());
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de la récupération des données',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('', name: 'get_all', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        try {
            $repo = $this->entityManager->getRepository(CollaborateurDocuments::class);
            $documents = $repo->findAll();

            $data = [];
            foreach ($documents as $document) {
                $typeDocument = $document->getCollaborateurDocumentsType();
                $collaborateur = $document->getCollaborateur();
                
                $data[] = [
                    'collaborateurDocumentsId' => $document->getCollaborateurDocumentsId(),
                    'collaborateurDocumentsPdf' => $document->getCollaborateurDocumentsPdf(),
                    'collaborateurDocumentsTypeId' => $typeDocument ? $typeDocument->getTypeDocumentId() : null,
                    'typeDocument' => $typeDocument ? [
                        'typeDocumentId' => $typeDocument->getTypeDocumentId(),
                        'typeDocumentLibelle' => $typeDocument->getTypeDocumentLibelle(),
                    ] : null,
                    'collaborateurId' => $collaborateur ? $collaborateur->getCollaborateurId() : null,
                    'collaborateur' => $collaborateur ? [
                        'collaborateurId' => $collaborateur->getCollaborateurId(),
                        'collaborateurNom' => $collaborateur->getCollaborateurNom(),
                        'collaborateurPrenom' => $collaborateur->getCollaborateurPrenom(),
                    ] : null,
                ];
            }

            return new JsonResponse($data, Response::HTTP_OK);
        } catch (\Exception $e) {
            error_log('Erreur dans getAll: ' . $e->getMessage());
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de la récupération des données',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'get_by_id', methods: ['GET'])]
    public function getById(int $id): JsonResponse
    {
        try {
            $document = $this->entityManager->getRepository(CollaborateurDocuments::class)->find($id);

            if (!$document) {
                return new JsonResponse([
                    'error' => 'Document non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }

            $typeDocument = $document->getCollaborateurDocumentsType();
            $collaborateur = $document->getCollaborateur();
            
            $data = [
                'collaborateurDocumentsId' => $document->getCollaborateurDocumentsId(),
                'collaborateurDocumentsPdf' => $document->getCollaborateurDocumentsPdf(),
                'collaborateurDocumentsTypeId' => $typeDocument ? $typeDocument->getTypeDocumentId() : null,
                'typeDocument' => $typeDocument ? [
                    'typeDocumentId' => $typeDocument->getTypeDocumentId(),
                    'typeDocumentLibelle' => $typeDocument->getTypeDocumentLibelle(),
                ] : null,
                'collaborateurId' => $collaborateur ? $collaborateur->getCollaborateurId() : null,
                'collaborateur' => $collaborateur ? [
                    'collaborateurId' => $collaborateur->getCollaborateurId(),
                    'collaborateurNom' => $collaborateur->getCollaborateurNom(),
                    'collaborateurPrenom' => $collaborateur->getCollaborateurPrenom(),
                ] : null,
            ];

            return new JsonResponse($data, Response::HTTP_OK);
        } catch (\Exception $e) {
            error_log('Erreur dans getById: ' . $e->getMessage());
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de la récupération des données',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['collaborateurId'])) {
                return new JsonResponse([
                    'error' => 'collaborateurId est requis'
                ], Response::HTTP_BAD_REQUEST);
            }

            if (!isset($data['collaborateurDocumentsTypeId'])) {
                return new JsonResponse([
                    'error' => 'collaborateurDocumentsTypeId est requis'
                ], Response::HTTP_BAD_REQUEST);
            }

            $document = new CollaborateurDocuments();
            $document->setCollaborateurDocumentsPdf($data['collaborateurDocumentsPdf'] ?? null);

            $collaborateur = $this->entityManager->getRepository(Collaborateur::class)->find($data['collaborateurId']);
            if (!$collaborateur) {
                return new JsonResponse([
                    'error' => 'Collaborateur non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }
            $document->setCollaborateur($collaborateur);

            $typeDocument = $this->entityManager->getRepository(TypeDocument::class)->find($data['collaborateurDocumentsTypeId']);
            if (!$typeDocument) {
                return new JsonResponse([
                    'error' => 'TypeDocument non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }
            $document->setCollaborateurDocumentsType($typeDocument);

            $this->entityManager->persist($document);
            $this->entityManager->flush();

            return new JsonResponse([
                'message' => 'Document créé avec succès',
                'collaborateurDocumentsId' => $document->getCollaborateurDocumentsId()
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            error_log('Erreur dans create: ' . $e->getMessage());
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de la création',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $document = $this->entityManager->getRepository(CollaborateurDocuments::class)->find($id);

            if (!$document) {
                return new JsonResponse([
                    'error' => 'Document non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            if (isset($data['collaborateurDocumentsPdf'])) {
                $document->setCollaborateurDocumentsPdf($data['collaborateurDocumentsPdf']);
            }

            if (isset($data['collaborateurId'])) {
                $collaborateur = $this->entityManager->getRepository(Collaborateur::class)->find($data['collaborateurId']);
                if (!$collaborateur) {
                    return new JsonResponse([
                        'error' => 'Collaborateur non trouvé'
                    ], Response::HTTP_NOT_FOUND);
                }
                $document->setCollaborateur($collaborateur);
            }

            if (isset($data['collaborateurDocumentsTypeId'])) {
                $typeDocument = $this->entityManager->getRepository(TypeDocument::class)->find($data['collaborateurDocumentsTypeId']);
                if (!$typeDocument) {
                    return new JsonResponse([
                        'error' => 'TypeDocument non trouvé'
                    ], Response::HTTP_NOT_FOUND);
                }
                $document->setCollaborateurDocumentsType($typeDocument);
            }

            $this->entityManager->flush();

            return new JsonResponse([
                'message' => 'Document mis à jour avec succès'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            error_log('Erreur dans update: ' . $e->getMessage());
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de la mise à jour',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $document = $this->entityManager->getRepository(CollaborateurDocuments::class)->find($id);

            if (!$document) {
                return new JsonResponse([
                    'error' => 'Document non trouvé'
                ], Response::HTTP_NOT_FOUND);
            }

            $this->entityManager->remove($document);
            $this->entityManager->flush();

            return new JsonResponse([
                'message' => 'Document supprimé avec succès'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            error_log('Erreur dans delete: ' . $e->getMessage());
            return new JsonResponse([
                'error' => 'Une erreur est survenue lors de la suppression',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}