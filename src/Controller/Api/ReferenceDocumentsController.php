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
        $requestData = json_decode($request->getContent(), true) ?? [];

        $referenceId = (int)($requestData['referenceId'] ?? $requestData['referenceID'] ?? $requestData['reference_id'] ?? 0);
        $typeDocumentId = (int)($requestData['typeDocumentId'] ?? 0);

        $libelle = trim((string)($requestData['referenceDocumentsLibelle'] ?? ''));
        $objet = trim((string)($requestData['referenceDocumentsObjet'] ?? ''));
        $commentaire = (string)($requestData['referenceDocumentsCommentaire'] ?? '');
        $commentaire = trim($commentaire);
        if (mb_strlen($commentaire) > 255) $commentaire = mb_substr($commentaire, 0, 255);

        if ($referenceId <= 0) return new JsonResponse(['message' => 'referenceId is required'], Response::HTTP_BAD_REQUEST);
        if ($typeDocumentId <= 0) return new JsonResponse(['message' => 'typeDocumentId is required'], Response::HTTP_BAD_REQUEST);
        if ($libelle === '') return new JsonResponse(['message' => 'referenceDocumentsLibelle is required'], Response::HTTP_BAD_REQUEST);

        $reference = $entityManager->getRepository(Reference::class)->find($referenceId);
        if (!$reference) return new JsonResponse(['message' => 'Reference non trouvé.'], Response::HTTP_NOT_FOUND);

        $docType = $entityManager->getRepository(TypeDocument::class)->find($typeDocumentId);
        if (!$docType) return new JsonResponse(['message' => 'Type document non trouvé.'], Response::HTTP_NOT_FOUND);

        // ✅ 1 seul document par type pour chaque référence
        $existingSameType = $entityManager->getRepository(ReferenceDocuments::class)->findOneBy([
            'reference' => $reference,
            'typeDocument' => $docType
        ]);
        if ($existingSameType) {
            return new JsonResponse([
                'message' => 'Un document existe déjà pour ce type. (1 seul fichier par type et par référence)'
            ], Response::HTTP_CONFLICT);
        }

        // ✅ objet auto = type libellé si vide
        if ($objet === '') $objet = (string)$docType->getTypeDocumentLibelle();
        if (mb_strlen($objet) > 255) $objet = mb_substr($objet, 0, 255);

        $refDoc = new ReferenceDocuments();
        $refDoc->setReferenceDocumentsLibelle($libelle);
        $refDoc->setReferenceDocumentsObjet($objet);
        $refDoc->setReferenceDocumentsCommentaire($commentaire ?: null);
        $refDoc->setTypeDocument($docType);
        $refDoc->setReference($reference);
        $refDoc->setReferenceDocumentsDate(new \DateTimeImmutable('today'));

        $entityManager->persist($refDoc);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Reference document créé avec succès',
            'referenceDocumentsId' => $refDoc->getReferenceDocumentsId(),
            'referenceDocumentsLibelle' => $refDoc->getReferenceDocumentsLibelle(),
            'referenceDocumentsObjet' => $refDoc->getReferenceDocumentsObjet(),
            'referenceDocumentsDate' => $refDoc->getReferenceDocumentsDate()?->format('Y-m-d'),
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

        return new JsonResponse([
            'referenceDocumentsId' => $referenceDocuments->getReferenceDocumentsId(),
            'referenceDocumentsLibelle' => $referenceDocuments->getReferenceDocumentsLibelle(),
            'referenceDocumentsObjet' => $referenceDocuments->getReferenceDocumentsObjet(),
            'referenceDocumentPath' => $referenceDocuments->getReferenceDocumentPath(),
            'referenceDocumentsDate' => $referenceDocuments->getReferenceDocumentsDate()?->format('Y-m-d'),
            'referenceDocumentsCommentaire' => $referenceDocuments->getReferenceDocumentsCommentaire(),
            'referenceID' => $ref?->getReferenceID(),
            'typeDocument' => $type ? [
                'typeDocumentId' => $type->getTypeDocumentId(),
                'typeDocumentLibelle' => $type->getTypeDocumentLibelle(),
            ] : null,
        ], Response::HTTP_OK);
    }

    #[Route('/api/getAll/ref-documents', name: 'api_get_all_reference_documents', methods: ['GET'])]
    public function getAll(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        $repo = $entityManager->getRepository(ReferenceDocuments::class);
        $rows = $repo->findBy([], ['referenceDocumentsId' => 'ASC']);

        $data = [];
        foreach ($rows as $d) {
            $data[] = [
                'referenceDocumentsId' => $d->getReferenceDocumentsId(),
                'referenceDocumentsLibelle' => $d->getReferenceDocumentsLibelle(),
                'referenceDocumentsObjet' => $d->getReferenceDocumentsObjet(),
                'referenceDocumentsDate' => $d->getReferenceDocumentsDate()?->format('Y-m-d'),
                'referenceDocumentsCommentaire' => $d->getReferenceDocumentsCommentaire(),
                'referenceID' => $d->getReference()?->getReferenceID(),
                'typeDocument' => $d->getTypeDocument() ? [
                    'typeDocumentId' => $d->getTypeDocument()->getTypeDocumentId(),
                    'typeDocumentLibelle' => $d->getTypeDocument()->getTypeDocumentLibelle(),
                ] : null,
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/put/ref-documents/{id}', name: 'api_reference_documents_update', methods: ['PUT'])]
    public function update(Request $request, ReferenceDocuments $referenceDocuments, EntityManagerInterface $entityManager): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true) ?? [];

        if (array_key_exists('referenceDocumentsLibelle', $requestData)) {
            $referenceDocuments->setReferenceDocumentsLibelle(trim((string)$requestData['referenceDocumentsLibelle']));
        }

        if (array_key_exists('referenceDocumentsObjet', $requestData)) {
            $objet = trim((string)$requestData['referenceDocumentsObjet']);
            if (mb_strlen($objet) > 255) $objet = mb_substr($objet, 0, 255);
            $referenceDocuments->setReferenceDocumentsObjet($objet ?: null);
        }

        if (array_key_exists('referenceDocumentsCommentaire', $requestData)) {
            $comm = trim((string)$requestData['referenceDocumentsCommentaire']);
            if (mb_strlen($comm) > 255) $comm = mb_substr($comm, 0, 255);
            $referenceDocuments->setReferenceDocumentsCommentaire($comm ?: null);
        }

        if (array_key_exists('typeDocumentId', $requestData)) {
            $newType = $entityManager->getRepository(TypeDocument::class)->find((int)$requestData['typeDocumentId']);
            if (!$newType) return new JsonResponse(['message' => 'Type document non trouvé.'], Response::HTTP_NOT_FOUND);

            // ✅ Interdire duplication si on change de type
            $ref = $referenceDocuments->getReference();
            $existingSameType = $entityManager->getRepository(ReferenceDocuments::class)->findOneBy([
                'reference' => $ref,
                'typeDocument' => $newType
            ]);
            if ($existingSameType && $existingSameType->getReferenceDocumentsId() !== $referenceDocuments->getReferenceDocumentsId()) {
                return new JsonResponse(['message' => 'Un document existe déjà pour ce type (sur cette référence).'], Response::HTTP_CONFLICT);
            }

            $referenceDocuments->setTypeDocument($newType);

            // si objet vide => auto
            if (!$referenceDocuments->getReferenceDocumentsObjet()) {
                $referenceDocuments->setReferenceDocumentsObjet((string)$newType->getTypeDocumentLibelle());
            }
        }

        $entityManager->flush();
        return new JsonResponse(['message' => 'Reference document mis à jour avec succès'], Response::HTTP_OK);
    }

    #[Route('/api/put/ref-documents-file/{id}', name: 'api_reference_documents_replace_file', methods: ['POST', 'PUT'])]
    public function replaceFile(Request $request, ReferenceDocuments $referenceDocuments, EntityManagerInterface $entityManager, SluggerInterface $slugger): JsonResponse
    {
        $file = $request->files->get('file');
        if (!$file->isValid()) {
            return new JsonResponse(['message' => 'Upload invalide.'], Response::HTTP_BAD_REQUEST);
        }
        if (!$file) {
            return new JsonResponse([
                'message' => 'Fichier manquant (field: file).',
                'files_keys' => array_keys($request->files->all()),
                'request_keys' => array_keys($request->request->all()),
            ], Response::HTTP_BAD_REQUEST);
        }

        $typeDocumentId = $request->request->get('typeDocumentId');
        $commentaire = $request->request->get('referenceDocumentsCommentaire') ?? $request->request->get('commentaire');

        if ($typeDocumentId) {
            $typeDocument = $entityManager->getRepository(TypeDocument::class)->find((int)$typeDocumentId);
            if (!$typeDocument) return new JsonResponse(['message' => 'Type document non trouvé.'], Response::HTTP_NOT_FOUND);

            // ✅ Interdire duplication si on change de type via upload
            $ref = $referenceDocuments->getReference();
            $existingSameType = $entityManager->getRepository(ReferenceDocuments::class)->findOneBy([
                'reference' => $ref,
                'typeDocument' => $typeDocument
            ]);
            if ($existingSameType && $existingSameType->getReferenceDocumentsId() !== $referenceDocuments->getReferenceDocumentsId()) {
                return new JsonResponse(['message' => 'Un document existe déjà pour ce type (sur cette référence).'], Response::HTTP_CONFLICT);
            }

            $referenceDocuments->setTypeDocument($typeDocument);

            // objet auto si vide
            if (!$referenceDocuments->getReferenceDocumentsObjet()) {
                $referenceDocuments->setReferenceDocumentsObjet((string)$typeDocument->getTypeDocumentLibelle());
            }
        }

        if ($commentaire !== null) {
            $comm = trim((string)$commentaire);
            if (mb_strlen($comm) > 255) $comm = mb_substr($comm, 0, 255);
            $referenceDocuments->setReferenceDocumentsCommentaire($comm ?: null);
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/reference';
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0775, true);

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $originalName = $file->getClientOriginalName();
        $originalExt = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));

        $guessedExt = strtolower((string) $file->guessExtension()); 
        $ext = $guessedExt ?: ($originalExt ?: 'bin');             

        $originalFilename = pathinfo($originalName, PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);

        $newFilename = sprintf('%s-%s.%s', $safeFilename, uniqid(), $ext);

        $file->move($uploadDir, $newFilename);

        $referenceDocuments->setReferenceDocumentsLibelle($file->getClientOriginalName());
        $referenceDocuments->setReferenceDocumentPath('/uploads/reference/' . $newFilename);
        $referenceDocuments->setReferenceDocumentsDate(new \DateTimeImmutable('today'));

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
