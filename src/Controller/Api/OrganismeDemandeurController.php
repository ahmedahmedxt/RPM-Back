<?php

namespace App\Controller\Api;

use App\Entity\OrganismeDemandeur;
use App\Entity\Pays;
use App\Entity\NatureOrganismeDemendeur;
use App\Entity\SecteurActivite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route("/api/organisme-demandeurs", name:"api_organisme_demandeur_")]
class OrganismeDemandeurController extends AbstractController
{
    private EntityManagerInterface $em;
    private ValidatorInterface $validator;

    public function __construct(EntityManagerInterface $em, ValidatorInterface $validator)
    {
        $this->em = $em;
        $this->validator = $validator;
    }

    #[Route("", name: "create", methods: ["POST"])]
    public function create(Request $request): JsonResponse
    {
        $contentType = $request->headers->get('content-type') ?? '';
        if (str_contains($contentType, 'application/json')) {
            $data = json_decode($request->getContent(), true);
        } else {
            $data = $request->request->all();
        }

        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid payload'], Response::HTTP_BAD_REQUEST);
        }

        $required = [
            'organismeDemandeurLibelle',
            'organismeDemandeurDescription',
            'organismeDemandeurNomCoordinateur',
            'organismeDemandeurEmailCoordinateur',
            'organismeDemandeurRaisonSocial',
            'organismeDemandeurRaisonSocialShort',
            'organismeDemandeurAdresse',
        ];

        $missing = [];
        foreach ($required as $k) {
            if (!array_key_exists($k, $data) || $data[$k] === null || (is_string($data[$k]) && trim($data[$k]) === '')) {
                $missing[] = $k;
            }
        }
        if (!empty($missing)) {
            $errors = array_combine($missing, array_fill(0, count($missing), 'This field is required.'));
            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $libelle = $data['organismeDemandeurLibelle'];
        $repo = $this->em->getRepository(OrganismeDemandeur::class);
        if ($repo->findOneBy(['organismeDemandeurLibelle' => $libelle])) {
            return $this->json(['error' => 'An organisme with this libelle already exists.'], Response::HTTP_CONFLICT);
        }

        $organisme = new OrganismeDemandeur();
        $organisme->setOrganismeDemandeurLibelle($libelle);

        $this->mapScalars($organisme, $data);

        if (!empty($data['organismeDemandeurLogo']) && is_string($data['organismeDemandeurLogo'])) {
            $base64 = $data['organismeDemandeurLogo'];

            if (preg_match('#^data:(image/[^;]+);base64,#', $base64, $m)) {
                $mime = $m[1];
                $base64 = substr($base64, strpos($base64, ',') + 1);
                $ext = explode('/', $mime)[1] ?? 'png';
            } else {
                $ext = 'png';
            }

            $decoded = base64_decode($base64);
            if ($decoded === false) {
                return $this->json(['error' => 'Invalid base64 image for organismeDemandeurLogo.'], Response::HTTP_BAD_REQUEST);
            }

            $uploadsDir = $this->getParameter('uploads_directory') . '/organismes';
            if (!is_dir($uploadsDir) && !@mkdir($uploadsDir, 0755, true) && !is_dir($uploadsDir)) {
                return $this->json(['error' => 'Unable to create upload directory.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $safeName = uniqid('org_', true) . '.' . $ext;
            $filePath = rtrim($uploadsDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safeName;

            try {
                file_put_contents($filePath, $decoded);
                $publicPath = '/uploads/organismes/' . $safeName;
                $organisme->setOrganismeDemandeurLogo($publicPath);
            } catch (\Throwable $e) {
                return $this->json(['error' => 'Failed to save logo file: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        if (!empty($data['paysId'])) {
            $pays = $this->em->getRepository(Pays::class)->find((int)$data['paysId']);
            if ($pays) {
                $organisme->setPays($pays);
            }
        }

        if (!empty($data['nature_organisme_demendeur_id'])) {
            $nature = $this->em->getRepository(NatureOrganismeDemendeur::class)->find((int)$data['nature_organisme_demendeur_id']);
            if ($nature) {
                $organisme->setNatureOrganismeDemendeur($nature);
            }
        }

        if (!empty($data['secteur_activite_id'])) {
            $secteur = $this->em->getRepository(SecteurActivite::class)->find((int)$data['secteur_activite_id']);
            if ($secteur) {
                $organisme->setSecteurActivite($secteur);
            }
        }

        $errors = $this->validator->validate($organisme);
        if (count($errors) > 0) {
            $payload = [];
            foreach ($errors as $err) {
                $payload[$err->getPropertyPath()] = $err->getMessage();
            }
            return $this->json(['errors' => $payload], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($organisme);
        $this->em->flush();

        return $this->json($this->toArray($organisme), Response::HTTP_CREATED);
    }

    /**
     * Get all OrganismeDemandeur (supports ?page & ?limit)
     * GET /api/organisme-demandeurs
     */
    #[Route("", name: "list", methods: ["GET"])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(100, (int) $request->query->get('limit', 10)));
        $offset = ($page - 1) * $limit;

        $repo = $this->em->getRepository(OrganismeDemandeur::class);
        $items = $repo->findBy([], ['organismeDemandeurLibelle' => 'ASC'], $limit, $offset);
        $total = (int) $this->em->createQueryBuilder()
            ->select('COUNT(o)')
            ->from(OrganismeDemandeur::class, 'o')
            ->getQuery()
            ->getSingleScalarResult();

        $data = array_map(fn(OrganismeDemandeur $o) => $this->toArray($o), $items);

        return $this->json([
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'data' => $data,
        ], Response::HTTP_OK);
    }

    /**
     * Get all OrganismeDemandeur (for dropdowns)
     * GET /api/organisme-demandeurs/all
     */
    #[Route("/all", name: "all", methods: ["GET"])]
    public function all(): JsonResponse
    {
        $repo = $this->em->getRepository(OrganismeDemandeur::class);

        $items = $repo->findBy([], ['organismeDemandeurLibelle' => 'ASC']);

        $data = array_map(static function (OrganismeDemandeur $o) {
            return [
                'organismeDemandeurId' => $o->getOrganismeDemandeurId(),
                'organismeDemandeurLibelle' => $o->getOrganismeDemandeurLibelle(),
            ];
        }, $items);

        return $this->json($data, Response::HTTP_OK);
    }


    /**
     * Get one OrganismeDemandeur
     * GET /api/organisme-demandeurs/{id}
     */
    #[Route("/{id}", name: "get", methods: ["GET"])]
    public function getOne(OrganismeDemandeur $organismeDemandeur): JsonResponse
    {
        return $this->json($this->toArray($organismeDemandeur), Response::HTTP_OK);
    }

    #[Route("/{id}", name: "update", methods: ["PUT","POST"])]
    public function update(Request $request, OrganismeDemandeur $organismeDemandeur): JsonResponse
    {
        $contentType = $request->headers->get('content-type') ?? '';

        if (stripos($contentType, 'application/json') !== false) {
            $data = json_decode($request->getContent(), true);
        } else {
            $data = $request->request->all();
        }

        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid payload'], Response::HTTP_BAD_REQUEST);
        }

        if (array_key_exists('organismeDemandeurLibelle', $data)) {
            $newLib = trim((string)$data['organismeDemandeurLibelle']);
            if ($newLib !== $organismeDemandeur->getOrganismeDemandeurLibelle()) {
                $exists = $this->em->getRepository(OrganismeDemandeur::class)
                            ->findOneBy(['organismeDemandeurLibelle' => $newLib]);
                if ($exists && $exists->getOrganismeDemandeurId() !== $organismeDemandeur->getOrganismeDemandeurId()) {
                    return $this->json(['error' => 'Another organisme with this libelle already exists.'], Response::HTTP_CONFLICT);
                }
            }
            $organismeDemandeur->setOrganismeDemandeurLibelle($newLib);
        }

        $this->mapScalars($organismeDemandeur, $data);

        $this->setRelationsFromData($organismeDemandeur, $data);

        /** @var UploadedFile|null $logoFile */
        $logoFile = $request->files->get('organismeDemandeurLogo');

        $removeLogo = false;
        if (array_key_exists('removeLogo', $data)) {
            $rv = $data['removeLogo'];
            $removeLogo = ($rv === true || $rv === '1' || $rv === 1 || $rv === 'true' || $rv === 'on');
        }

        $uploadsDir = rtrim($this->getParameter('uploads_directory'), '/\\') . '/organismes';
        if (!is_dir($uploadsDir)) {
            @mkdir($uploadsDir, 0755, true);
        }

        $deleteOldLogo = function (?string $oldPath) {
            if (!$oldPath) {
                return;
            }
            $abs = $this->getParameter('kernel.project_dir') . '/public' . $oldPath;
            if (is_file($abs)) {
                @unlink($abs);
            }
        };

        if ($logoFile instanceof UploadedFile) {
            $safeName = uniqid('org_', true) . '.' . ($logoFile->guessExtension() ?: 'png');
            try {
                $logoFile->move($uploadsDir, $safeName);
                $old = $organismeDemandeur->getOrganismeDemandeurLogo();
                $deleteOldLogo($old);
                $organismeDemandeur->setOrganismeDemandeurLogo('/uploads/organismes/' . $safeName);
            } catch (\Throwable $e) {
                return $this->json(['error' => 'Failed to upload file: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            if (!empty($data['organismeDemandeurLogo']) && is_string($data['organismeDemandeurLogo'])) {
                $base64 = $data['organismeDemandeurLogo'];

                $ext = 'png';
                if (preg_match('#^data:(image/[^;]+);base64,#', $base64, $m)) {
                    $mime = $m[1];
                    $base64 = substr($base64, strpos($base64, ',') + 1);
                    $parts = explode('/', $mime);
                    if (isset($parts[1])) {
                        $ext = preg_replace('/[^a-z0-9]+/i', '', $parts[1]);
                    }
                } else {
                    $decodedTest = base64_decode($base64, true);
                    if ($decodedTest !== false) {
                        $finfo = new \finfo(FILEINFO_MIME_TYPE);
                        $mimeType = $finfo->buffer($decodedTest);
                        if ($mimeType && str_starts_with($mimeType, 'image/')) {
                            $parts = explode('/', $mimeType);
                            $ext = $parts[1] ?? $ext;
                        }
                        unset($decodedTest);
                    }
                }

                $decoded = base64_decode($base64, true);
                if ($decoded === false) {
                    return $this->json(['error' => 'Invalid base64 image for organismeDemandeurLogo.'], Response::HTTP_BAD_REQUEST);
                }

                $safeName = uniqid('org_', true) . '.' . $ext;
                $filePath = rtrim($uploadsDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safeName;

                try {
                    file_put_contents($filePath, $decoded);
                    $old = $organismeDemandeur->getOrganismeDemandeurLogo();
                    $deleteOldLogo($old);
                    $organismeDemandeur->setOrganismeDemandeurLogo('/uploads/organismes/' . $safeName);
                } catch (\Throwable $e) {
                    return $this->json(['error' => 'Failed to save logo file: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } elseif ($removeLogo) {
                $old = $organismeDemandeur->getOrganismeDemandeurLogo();
                $deleteOldLogo($old);
                $organismeDemandeur->setOrganismeDemandeurLogo(null);
            }
        }

        $errors = $this->validator->validate($organismeDemandeur);
        if (count($errors) > 0) {
            $payload = [];
            foreach ($errors as $err) {
                $payload[$err->getPropertyPath()] = $err->getMessage();
            }
            return $this->json(['errors' => $payload], Response::HTTP_BAD_REQUEST);
        }

        $this->em->flush();

        return $this->json($this->toArray($organismeDemandeur), Response::HTTP_OK);
    }



    /**
     * Delete an OrganismeDemandeur
     * DELETE /api/organisme-demandeurs/{id}
     *
     * Behavior:
     * - If no AppelOffres linked, deletes it directly
     * - If AppelOffres exist, set their FK to null, persist them, then remove the organisme
     */
    #[Route("/{id}", name: "delete", methods: ["DELETE"])]
    public function delete(OrganismeDemandeur $organismeDemandeur): JsonResponse
    {
        // Dereference appelOffres if any
        $appelOffres = $organismeDemandeur->getAppelOffres();
        if (!$appelOffres->isEmpty()) {
            foreach ($appelOffres as $appel) {
                // keep same property name your colleague uses
                $appel->setAppelOffresOrganismeDemandeurId(null);
                $this->em->persist($appel);
            }
            $this->em->flush();
        }

        $this->em->remove($organismeDemandeur);
        $this->em->flush();

        return $this->json(['message' => 'OrganismeDemandeur deleted.'], Response::HTTP_OK);
    }

    // -----------------------
    // Helpers
    // -----------------------

    /**
     * Map scalar fields (strings) from input data to the entity
     * Accepts keys matching your entity column names.
     */
    private function mapScalars(OrganismeDemandeur $o, array $data): void
    {
        $map = [
            'organismeDemandeurDescription' => 'setOrganismeDemandeurDescription',
            'organismeDemandeurAcronyme' => 'setOrganismeDemandeurAcronyme',
            'organismeDemandeurLogo' => 'setOrganismeDemandeurLogo',
            'organismeDemandeurNomCoordinateur' => 'setOrganismeDemandeurNomCoordinateur',
            'organismeDemandeurEmailCoordinateur' => 'setOrganismeDemandeurEmailCoordinateur',
            'organismeDemandeurRaisonSocial' => 'setOrganismeDemandeurRaisonSocial',
            'organismeDemandeurRaisonSocialShort' => 'setOrganismeDemandeurRaisonSocialShort',
            'organismeDemandeurAdresse' => 'setOrganismeDemandeurAdresse',
            'organismeDemandeurPersonneContact1' => 'setOrganismeDemandeurPersonneContact1',
            'organismeDemandeurPersonneTelephonne1' => 'setOrganismeDemandeurPersonneTelephonne1',
            'organismeDemandeurPersonneContact2' => 'setOrganismeDemandeurPersonneContact2',
            'organismeDemandeurTelephone2' => 'setOrganismeDemandeurTelephone2',
            'organismeDemandeurEmail2' => 'setOrganismeDemandeurEmail2',
            'organismeDemandeurPersonneContact3' => 'setOrganismeDemandeurPersonneContact3',
            'organismeDemandeurTelephone3' => 'setOrganismeDemandeurTelephone3',
            'organismeDemandeurEmail3' => 'setOrganismeDemandeurEmail3',
        ];

        foreach ($map as $key => $setter) {
            if (array_key_exists($key, $data)) {
                $o->$setter($data[$key]);
            }
        }
    }

    /**
     * Set relations (Pays, NatureOrganismeDemendeur, SecteurActivite) if provided by id.
     * Accepts keys: paysId, natureOrganismeDemendeurId, secteurActiviteId
     */
    private function setRelationsFromData(OrganismeDemandeur $o, array $data): void
    {
        // Pays
        if (array_key_exists('paysId', $data)) {
            $pays = null;
            if ($data['paysId'] !== null) {
                $pays = $this->em->getRepository(Pays::class)->find($data['paysId']);
                if (!$pays) {
                    throw new \InvalidArgumentException(sprintf('Pays with id %s not found', $data['paysId']));
                }
            }
            $o->setPays($pays);
        }

        // NatureOrganismeDemendeur
        if (array_key_exists('natureOrganismeDemendeurId', $data)) {
            $nature = null;
            if ($data['natureOrganismeDemendeurId'] !== null) {
                $nature = $this->em->getRepository(NatureOrganismeDemendeur::class)->find($data['natureOrganismeDemendeurId']);
                if (!$nature) {
                    throw new \InvalidArgumentException(sprintf('NatureOrganismeDemendeur with id %s not found', $data['natureOrganismeDemendeurId']));
                }
            }
            $o->setNatureOrganismeDemendeur($nature);
        }

        // SecteurActivite
        if (array_key_exists('secteurActiviteId', $data)) {
            $secteur = null;
            if ($data['secteurActiviteId'] !== null) {
                $secteur = $this->em->getRepository(SecteurActivite::class)->find($data['secteurActiviteId']);
                if (!$secteur) {
                    throw new \InvalidArgumentException(sprintf('SecteurActivite with id %s not found', $data['secteurActiviteId']));
                }
            }
            $o->setSecteurActivite($secteur);
        }
    }

    /**
     * Convert entity to array for JSON responses (explicit fields only)
     */
    private function toArray(OrganismeDemandeur $o): array
    {
        return [
            'organismeDemandeurId' => $o->getOrganismeDemandeurId() ?? $o->getId(),
            'organismeDemandeurLibelle' => $o->getOrganismeDemandeurLibelle(),
            'organismeDemandeurDescription' => $o->getOrganismeDemandeurDescription(),
            'organismeDemandeurAcronyme' => $o->getOrganismeDemandeurAcronyme(),
            'organismeDemandeurLogo' => $o->getOrganismeDemandeurLogo(),
            'organismeDemandeurNomCoordinateur' => $o->getOrganismeDemandeurNomCoordinateur(),
            'organismeDemandeurEmailCoordinateur' => $o->getOrganismeDemandeurEmailCoordinateur(),
            'organismeDemandeurRaisonSocial' => $o->getOrganismeDemandeurRaisonSocial(),
            'organismeDemandeurRaisonSocialShort' => $o->getOrganismeDemandeurRaisonSocialShort(),
            'organismeDemandeurAdresse' => $o->getOrganismeDemandeurAdresse(),
            'organismeDemandeurPersonneContact1' => $o->getOrganismeDemandeurPersonneContact1(),
            'organismeDemandeurPersonneTelephonne1' => $o->getOrganismeDemandeurPersonneTelephonne1(),
            'organismeDemandeurPersonneContact2' => $o->getOrganismeDemandeurPersonneContact2(),
            'organismeDemandeurTelephone2' => $o->getOrganismeDemandeurTelephone2(),
            'organismeDemandeurEmail2' => $o->getOrganismeDemandeurEmail2(),
            'organismeDemandeurPersonneContact3' => $o->getOrganismeDemandeurPersonneContact3(),
            'organismeDemandeurTelephone3' => $o->getOrganismeDemandeurTelephone3(),
            'organismeDemandeurEmail3' => $o->getOrganismeDemandeurEmail3(),
            'pays' => $o->getPays() ? [
                'paysId' => $o->getPays()->getPaysId(),
                'paysLibelle' => $o->getPays()->getPaysLibelle(),
            ] : null,
            'natureOrganismeDemendeur' => $o->getNatureOrganismeDemendeur() ? [
                'id' => $o->getNatureOrganismeDemendeur()->getNatureOrganismeDemendeurId(),
                'libelle' => $o->getNatureOrganismeDemendeur()->getNatureOrganismeDemendeurLibelle(),
            ] : null,
            'secteurActivite' => $o->getSecteurActivite() ? [
                'secteurActiviteId' => $o->getSecteurActivite()->getSecteurActiviteId(),
                'secteurActiviteLibelle' => $o->getSecteurActivite()->getSecteurActiviteLibelle(),
            ] : null,
        ];
    }
}
