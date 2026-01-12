<?php

namespace App\Controller\Api;

use App\Entity\Reference;
use App\Entity\ReferenceDocuments;
use App\Entity\TypeDocument;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class ReferenceDocumentsController extends AbstractController
{
    #[Route('/api/create/ref-documents', name: 'api_reference_document_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //$this->checkToken($tokenStorage);
        $requestData = json_decode($request->getContent(), true);

        // Vérifier si le lieu existe déjà
        $existingReference = $entityManager->getRepository(ReferenceDocuments::class)->findOneBy([
            'referenceDocumentsLibelle' => $requestData['referenceDocumentsLibelle'],
            'reference' => $requestData['referenceID']
        ]);
        if ($existingReference) {
            return new JsonResponse(['message' => 'Ce reference document existe déjà.'], Response::HTTP_CONFLICT);
        }
        // Créer une nouvelle instance de Lieu
        $ref = new ReferenceDocuments();
        $ref->setReferenceDocumentsLibelle($requestData['referenceDocumentsLibelle']);

        // Récupérer l'objet Pays en fonction de l'ID fourni dans la requête
        $docType = $entityManager->getRepository(TypeDocument::class)->find($requestData['typeDocumentId']);

        // Vérifier si le continent existe
        if (!$docType) {
            return new JsonResponse(['message' => 'Type document non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Affecter le pays au lieu
        $ref->setTypeDocument($docType);

        // Récupérer l'objet Pays en fonction de l'ID fourni dans la requête
        $reference = $entityManager->getRepository(Reference::class)->find($requestData['referenceID']);

        // Vérifier si le continent existe
        if (!$reference) {
            return new JsonResponse(['message' => 'Reference non trouvé.'], Response::HTTP_NOT_FOUND);
        }

        // Affecter le pays au lieu
        $ref->setReference($reference);

        // Persister l'entité dans la base de données
        $entityManager->persist($ref);
        $entityManager->flush();

        // Retourner une réponse JSON avec un message de succès
        return new JsonResponse(['message' => 'Reference document créé avec succès'], Response::HTTP_CREATED);
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

        // Récupérer les references documents triés par nom
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
    public function update(Request $request,ReferenceDocuments $referenceDocuments,EntityManagerInterface $entityManager): JsonResponse {
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

        if (array_key_exists('referenceID', $requestData)) {
            $reference = $entityManager->getRepository(Reference::class)->find($requestData['referenceID']);
            if (!$reference) {
                return new JsonResponse(['message' => 'Reference non trouvée.'], Response::HTTP_NOT_FOUND);
            }
            $referenceDocuments->setReference($reference);
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
        // Récupérer le token d'authentification de Symfony
        $token = $tokenStorage->getToken();

        // Vérifier si le token d'authentification est présent et est de type TokenInterface
        if (!$token instanceof TokenInterface) {
            throw new AccessDeniedHttpException('Token d\'authentification manquant ou invalide');
        }

    }

    #[Route('/api/delete/ref-documents/{id}', name: 'api_reference_documents_delete', methods: ['DELETE'])]
    public function delete(ReferenceDocuments $referenceDocuments,EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {

        // Supprimer le pays
        $entityManager->remove($referenceDocuments);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Reference document supprimé avec succès'], Response::HTTP_OK);
    }
}
