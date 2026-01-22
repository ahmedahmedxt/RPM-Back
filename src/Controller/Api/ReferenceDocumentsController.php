<?php

namespace App\Controller\Api;

use App\Entity\Reference;
use App\Entity\ReferenceDocuments;
use App\Entity\TypeDocument;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\String\Slugger\SluggerInterface;

class ReferenceDocumentsController extends AbstractController
{
    #[Route('/api/create/ref-documents', name: 'api_reference_document_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);

        $requestData = json_decode($request->getContent(), true) ?? [];

        $referenceId = (int)($requestData['referenceId']
            ?? $requestData['referenceID']
            ?? $requestData['reference_id']
            ?? 0);

        $libelle = trim((string)($requestData['referenceDocumentsLibelle'] ?? ''));
        $typeDocumentId = (int)($requestData['typeDocumentId'] ?? 0);

        if ($referenceId <= 0) {
            return new JsonResponse(['message' => 'referenceId is required'], Response::HTTP_BAD_REQUEST);
        }
        if ($typeDocumentId <= 0) {
            return new JsonResponse(['message' => 'typeDocumentId is required'], Response::HTTP_BAD_REQUEST);
        }
        if ($libelle === '') {
            return new JsonResponse(['message' => 'referenceDocumentsLibelle is required'], Response::HTTP_BAD_REQUEST);
        }

        $reference = $entityManager->getRepository(Reference::class)->find($referenceId);
        if (!$reference) {
            return new JsonResponse(['message' => 'Reference non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        $docType = $entityManager->getRepository(TypeDocument::class)->find($typeDocumentId);
        if (!$docType) {
            return new JsonResponse(['message' => 'Type document non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        $existingReference = $entityManager->getRepository(ReferenceDocuments::class)->findOneBy([
            'referenceDocumentsLibelle' => $libelle,
            'reference' => $reference
        ]);
        if ($existingReference) {
            return new JsonResponse(['message' => 'Ce reference document existe déjà.'], Response::HTTP_CONFLICT);
        }

        $refDoc = new ReferenceDocuments();
        $refDoc->setReferenceDocumentsLibelle($libelle);
        $refDoc->setTypeDocument($docType);
        $refDoc->setReference($reference);

        if (array_key_exists('referenceDocumentsCommentaire', $requestData)) {
            $refDoc->setReferenceDocumentsCommentaire($requestData['referenceDocumentsCommentaire']);
        }

        $entityManager->persist($refDoc);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Reference document créé avec succès',
            'referenceDocumentsId' => $refDoc->getReferenceDocumentsId(),
            'referenceDocumentsLibelle' => $refDoc->getReferenceDocumentsLibelle(),
            'referenceDocumentsCommentaire' => $refDoc->getReferenceDocumentsCommentaire(),
            'referenceID' => $reference?->getReferenceID(),
            'typeDocument' => [
                'typeDocumentId' => $docType->getTypeDocumentId(),
                'typeDocumentLibelle' => $docType->getTypeDocumentLibelle(),
            ],
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/get/ref-documents/{id}', name: 'api_reference_document_show', methods: ['GET'])]
    public function show(ReferenceDocuments $referenceDocuments): JsonResponse
    {
        $type = $referenceDocuments->getTypeDocument();
        $ref  = $referenceDocuments->getReference();

        $data = [
            'referenceDocumentsId' => $referenceDocuments->getReferenceDocumentsId(),
            'referenceDocumentsLibelle' => $referenceDocuments->getReferenceDocumentsLibelle(),
            'referenceDocumentPath' => $referenceDocuments->getReferenceDocumentPath(),
            'referenceDocumentsCommentaire' => $referenceDocuments->getReferenceDocumentsCommentaire(),
            'referenceID' => $ref?->getReferenceID(),
            'typeDocument' => $type ? [
                'typeDocumentId' => $type->getTypeDocumentId(),
                'typeDocumentLibelle' => $type->getTypeDocumentLibelle(),
            ] : null,
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/getAll/ref-documents', name: 'api_get_all_reference_documents', methods: ['GET'])]
    public function getAll(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);

        $repo = $entityManager->getRepository(ReferenceDocuments::class);
        $ref = $repo->findBy([], ['referenceDocumentsId' => 'ASC']);

        $refData = [];
        foreach ($ref as $refItem) {
            $reference = $refItem->getReference();
            $typeDoc = $refItem->getTypeDocument();

            $referenceLibelle = ($reference) ? $reference->getReferenceLibelle() : 'Reference non spécifié';
            $typeDocLibelle = ($typeDoc) ? $typeDoc->getTypeDocumentLibelle() : 'Type de document non spécifié';

            $refData[] = [
                'referenceDocumentsId' => $refItem->getReferenceDocumentsId(),
                'referenceDocumentsLibelle' => $refItem->getReferenceDocumentsLibelle(),
                'referenceLibelle' => $referenceLibelle,
                'typeDocLibelle' => $typeDocLibelle,
            ];
        }

        return new JsonResponse($refData, Response::HTTP_OK);
    }

    #[Route('/api/put/ref-documents/{id}', name: 'api_reference_documents_update', methods: ['PUT'])]
    public function update(Request $request, ReferenceDocuments $referenceDocuments, EntityManagerInterface $entityManager): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true) ?? [];

        if (array_key_exists('referenceDocumentsLibelle', $requestData)) {
            $referenceDocuments->setReferenceDocumentsLibelle($requestData['referenceDocumentsLibelle']);
        }

        if (array_key_exists('referenceDocumentsCommentaire', $requestData)) {
            $referenceDocuments->setReferenceDocumentsCommentaire($requestData['referenceDocumentsCommentaire']);
        }

        if (array_key_exists('typeDocumentId', $requestData)) {
            $typeDocument = $entityManager->getRepository(TypeDocument::class)->find($requestData['typeDocumentId']);
            if (!$typeDocument) {
                return new JsonResponse(['message' => 'Type document non trouvé.'], Response::HTTP_NOT_FOUND);
            }
            $referenceDocuments->setTypeDocument($typeDocument);
        }

        if (array_key_exists('referenceId', $requestData) || array_key_exists('referenceID', $requestData)) {
            $referenceId = (int)($requestData['referenceId'] ?? $requestData['referenceID'] ?? 0);
            if ($referenceId > 0) {
                $reference = $entityManager->getRepository(Reference::class)->find($referenceId);
                if (!$reference) {
                    return new JsonResponse(['message' => 'Reference non trouvée.'], Response::HTTP_NOT_FOUND);
                }
                $referenceDocuments->setReference($reference);
            }
        }

        $entityManager->flush();

        return new JsonResponse(['message' => 'Reference document mis à jour avec succès'], Response::HTTP_OK);
    }

    #[Route('/api/put/ref-documents-file/{id}', name: 'api_reference_documents_replace_file', methods: ['POST', 'PUT'])]
    public function replaceFile(Request $request, ReferenceDocuments $referenceDocuments, EntityManagerInterface $entityManager, SluggerInterface $slugger): JsonResponse
    {
        $file = $request->files->get('file');

        if (!$file) {
            return new JsonResponse([
                'message' => 'Fichier manquant (field: file).',
                'files_keys' => array_keys($request->files->all()),
                'request_keys' => array_keys($request->request->all()),
            ], Response::HTTP_BAD_REQUEST);
        }

        $typeDocumentId = $request->request->get('typeDocumentId');
        $commentaire = $request->request->get('referenceDocumentsCommentaire');
        if ($commentaire === null) {
            $commentaire = $request->request->get('commentaire');
        }

        if ($typeDocumentId) {
            $typeDocument = $entityManager->getRepository(TypeDocument::class)->find($typeDocumentId);
            if (!$typeDocument) {
                return new JsonResponse(['message' => 'Type document non trouvé.'], Response::HTTP_NOT_FOUND);
            }
            $referenceDocuments->setTypeDocument($typeDocument);
        }

        if ($commentaire !== null) {
            $referenceDocuments->setReferenceDocumentsCommentaire($commentaire);
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/reference';
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0775, true);

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        $file->move($uploadDir, $newFilename);

        $referenceDocuments->setReferenceDocumentsLibelle($file->getClientOriginalName());
        $referenceDocuments->setReferenceDocumentPath('/uploads/reference/' . $newFilename);

        $entityManager->flush();

        return new JsonResponse(['message' => 'Fichier remplacé avec succès'], Response::HTTP_OK);
    }

    public function checkToken(TokenStorageInterface $tokenStorage): void
    {
        $token = $tokenStorage->getToken();
        if (!$token instanceof TokenInterface) {
            throw new AccessDeniedHttpException('Token d\'authentification manquant ou invalide');
        }
    }

    #[Route('/api/delete/ref-documents/{id}', name: 'api_reference_documents_delete', methods: ['DELETE'])]
    public function delete(ReferenceDocuments $referenceDocuments, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $entityManager->remove($referenceDocuments);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Reference document supprimé avec succès'], Response::HTTP_OK);
    }
}
