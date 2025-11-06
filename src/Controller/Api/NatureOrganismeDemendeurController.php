<?php

namespace App\Controller\Api;

use App\Entity\NatureOrganismeDemendeur;
use App\Repository\NatureOrganismeDemendeurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

#[Route('/api/nature-organisme-demendeur', name: 'api_nature_organisme_demendeur_')]
class NatureOrganismeDemendeurController extends AbstractController
{
    private EntityManagerInterface $em;
    private ValidatorInterface $validator;
    private TokenStorageInterface $tokenStorage;
    private NatureOrganismeDemendeurRepository $repo;

    public function __construct(
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        TokenStorageInterface $tokenStorage,
        NatureOrganismeDemendeurRepository $repo
    ) {
        $this->em = $em;
        $this->validator = $validator;
        $this->tokenStorage = $tokenStorage;
        $this->repo = $repo;
    }

    /**
     * List (with optional pagination)
     * GET /api/nature-organisme-demendeur
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        // Optionally require authentication:
        // $this->checkToken();

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(200, (int) $request->query->get('limit', 50)));
        $offset = ($page - 1) * $limit;

        $items = $this->repo->findBy([], ['natureOrganismeDemendeurLibelle' => 'ASC'], $limit, $offset);
        $total = (int) $this->em->createQueryBuilder()
            ->select('COUNT(n)')
            ->from(NatureOrganismeDemendeur::class, 'n')
            ->getQuery()
            ->getSingleScalarResult();

        $data = array_map(fn(NatureOrganismeDemendeur $n) => $this->toArray($n), $items);

        return $this->json([
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'data' => $data,
        ], Response::HTTP_OK);
    }

    #[Route('/all', name: 'all', methods: ['GET'])]
    public function all(): JsonResponse
    {
        // get all ordered by libelle
        $items = $this->repo->findBy([], ['natureOrganismeDemendeurLibelle' => 'ASC']);

        $data = array_map(function (NatureOrganismeDemendeur $n) {
            return [
                'id'    => $n->getNatureOrganismeDemendeurId(),
                'libelle' => $n->getNatureOrganismeDemendeurLibelle(),
            ];
        }, $items);

        $response = $this->json($data, Response::HTTP_OK);

        return $response;
    }


    /**
     * Get one
     * GET /api/nature-organisme-demendeur/{id}
     */
    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function getOne(NatureOrganismeDemendeur $nature): JsonResponse
    {
        // $this->checkToken();

        return $this->json($this->toArray($nature), Response::HTTP_OK);
    }

    /**
     * Create
     * POST /api/nature-organisme-demendeur
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->checkToken();

        $contentType = $request->headers->get('content-type') ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $data = json_decode($request->getContent(), true);
        } else {
            $data = $request->request->all();
        }

        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid payload'], Response::HTTP_BAD_REQUEST);
        }

        $lib = trim((string)($data['natureOrganismeDemendeurLibelle'] ?? $data['nature_organisme_demendeur_libelle'] ?? ''));

        if ($lib === '') {
            return $this->json(['errors' => ['natureOrganismeDemendeurLibelle' => 'Le libellé est requis.']], Response::HTTP_BAD_REQUEST);
        }

        // uniqueness
        if ($this->repo->findOneBy(['natureOrganismeDemendeurLibelle' => $lib])) {
            return $this->json(['error' => 'Une nature avec ce libellé existe déjà.'], Response::HTTP_CONFLICT);
        }

        $entity = new NatureOrganismeDemendeur();
        $entity->setNatureOrganismeDemendeurLibelle($lib);
        if (array_key_exists('natureOrganismeDemendeurDescription', $data) || array_key_exists('nature_organisme_demendeur_description', $data)) {
            $desc = $data['natureOrganismeDemendeurDescription'] ?? $data['nature_organisme_demendeur_description'] ?? null;
            $entity->setNatureOrganismeDemendeurDescription($desc);
        }

        // Validate
        $errors = $this->validator->validate($entity);
        if (count($errors) > 0) {
            $payload = [];
            foreach ($errors as $err) {
                $payload[$err->getPropertyPath()] = $err->getMessage();
            }
            return $this->json(['errors' => $payload], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($entity);
        $this->em->flush();

        return $this->json($this->toArray($entity), Response::HTTP_CREATED);
    }

    /**
     * Update (partial)
     * PUT|POST /api/nature-organisme-demendeur/{id}
     */
    #[Route('/{id}', name: 'update', methods: ['PUT', 'POST'])]
    public function update(Request $request, NatureOrganismeDemendeur $nature): JsonResponse
    {
        $this->checkToken();

        $contentType = $request->headers->get('content-type') ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $data = json_decode($request->getContent(), true);
        } else {
            $data = $request->request->all();
        }

        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid payload'], Response::HTTP_BAD_REQUEST);
        }

        if (array_key_exists('natureOrganismeDemendeurLibelle', $data) || array_key_exists('nature_organisme_demendeur_libelle', $data)) {
            $newLib = trim((string)($data['natureOrganismeDemendeurLibelle'] ?? $data['nature_organisme_demendeur_libelle']));
            if ($newLib !== $nature->getNatureOrganismeDemendeurLibelle()) {
                $exists = $this->repo->findOneBy(['natureOrganismeDemendeurLibelle' => $newLib]);
                if ($exists && $exists->getNatureOrganismeDemendeurId() !== $nature->getNatureOrganismeDemendeurId()) {
                    return $this->json(['error' => 'Une nature avec ce libellé existe déjà.'], Response::HTTP_CONFLICT);
                }
                $nature->setNatureOrganismeDemendeurLibelle($newLib);
            }
        }

        if (array_key_exists('natureOrganismeDemendeurDescription', $data) || array_key_exists('nature_organisme_demendeur_description', $data)) {
            $desc = $data['natureOrganismeDemendeurDescription'] ?? $data['nature_organisme_demendeur_description'] ?? null;
            $nature->setNatureOrganismeDemendeurDescription($desc);
        }

        $errors = $this->validator->validate($nature);
        if (count($errors) > 0) {
            $payload = [];
            foreach ($errors as $err) {
                $payload[$err->getPropertyPath()] = $err->getMessage();
            }
            return $this->json(['errors' => $payload], Response::HTTP_BAD_REQUEST);
        }

        $this->em->flush();

        return $this->json($this->toArray($nature), Response::HTTP_OK);
    }

    /**
     * Delete
     * DELETE /api/nature-organisme-demendeur/{id}
     *
     * If there are OrganismeDemandeur linked, the relation will be set to NULL (if DB allows)
     * or the remove will fail (DB constraint). We try to dereference first.
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(NatureOrganismeDemendeur $nature): JsonResponse
    {
        $this->checkToken();

        // Dereference related organismes if any
        foreach ($nature->getOrganismesDemandeurs() as $org) {
            try {
                $org->setNatureOrganismeDemendeur(null);
                $this->em->persist($org);
            } catch (\Throwable $e) {
                // ignore individual failures — we'll try to delete anyway
            }
        }
        $this->em->flush();

        $this->em->remove($nature);
        $this->em->flush();

        return $this->json(['message' => 'Deleted'], Response::HTTP_OK);
    }

    // -------------------------
    // Helpers
    // -------------------------

    private function toArray(NatureOrganismeDemendeur $n): array
    {
        return [
            'id' => $n->getNatureOrganismeDemendeurId(),
            'natureOrganismeDemendeurLibelle' => $n->getNatureOrganismeDemendeurLibelle(),
            'natureOrganismeDemendeurDescription' => $n->getNatureOrganismeDemendeurDescription(),
            'organismesCount' => $n->getOrganismesDemandeurs()->count(),
        ];
    }

    /**
     * Basic token check — throws AccessDeniedHttpException if missing/invalid token.
     * Works with TokenStorageInterface and Lexik JWT token as long as token is present.
     */
    public function checkToken(): void
    {
        $token = $this->tokenStorage->getToken();
        if (!$token instanceof TokenInterface) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Authentication token required.');
        }
    }
}
