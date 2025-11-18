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

#[Route('/api/organisme-demandeurs', name: 'api_organisme_demandeur_')]
class OrganismeDemandeurController extends AbstractController
{
    private EntityManagerInterface $em;
    private ValidatorInterface $validator;

    public function __construct(EntityManagerInterface $em, ValidatorInterface $validator)
    {
        $this->em = $em;
        $this->validator = $validator;
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $contentType = $request->headers->get('content-type') ?? '';
        $data = null;

        if (stripos($contentType, 'application/json') !== false) {
            $data = json_decode($request->getContent(), true);
        } else {
            $data = $request->request->all();
        }

        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid payload (expected JSON or form-data)'], Response::HTTP_BAD_REQUEST);
        }

        $required = ['organismeDemandeurRaisonSociale', 'organismeDemandeurRaisonSocialeShort'];
        $aliases = [
            'organismeDemandeurRaisonSociale' => ['organismeDemandeurRaisonSociale', 'organisme_demandeur_raison_sociale', 'organismeDemandeurRaisonSocial'],
            'organismeDemandeurRaisonSocialeShort' => ['organismeDemandeurRaisonSocialeShort', 'organisme_demandeur_raison_sociale_short', 'organismeDemandeurRaisonSocialShort'],
        ];

        $missing = [];
        foreach ($aliases as $fieldKey => $names) {
            $ok = false;
            foreach ($names as $n) {
                if (array_key_exists($n, $data) && $data[$n] !== null && ( !is_string($data[$n]) || trim((string)$data[$n]) !== '' )) {
                    $ok = true;
                    $data[$fieldKey] = $data[$n];
                    break;
                }
            }
            if (!$ok) {
                $missing[] = $fieldKey;
            }
        }

        if (!empty($missing)) {
            $errors = array_combine($missing, array_fill(0, count($missing), 'This field is required.'));
            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $organisme = new OrganismeDemandeur();

        $this->mapScalars($organisme, $data);

        $logoFile = $request->files->get('organismeDemandeurLogo') ?: $request->files->get('organismeDemandeurLogo');
        if ($logoFile) {
            $uploadsDir = rtrim($this->getParameter('uploads_directory'), '/\\') . '/organismes';
            if (!is_dir($uploadsDir)) {
                @mkdir($uploadsDir, 0755, true);
            }
            $safeName = uniqid('org_', true) . '.' . ($logoFile->guessExtension() ?: 'png');
            try {
                $logoFile->move($uploadsDir, $safeName);
                $organisme->setOrganismeDemandeurLogo('/uploads/organismes/' . $safeName);
            } catch (\Throwable $e) {
                return $this->json(['error' => 'Failed to store uploaded logo: '.$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } else {
            $base64Key = $data['organismeDemandeurLogo'] ?? $data['organismeDemandeurLogo'] ?? null;
            if (!empty($base64Key) && is_string($base64Key)) {
                $publicPath = $this->saveBase64Logo($base64Key);
                if ($publicPath === null) {
                    return $this->json(['error' => 'Invalid base64 image.'], Response::HTTP_BAD_REQUEST);
                }
                $organisme->setOrganismeDemandeurLogo($publicPath);
            }
        }

        $this->setRelations($organisme, $data);

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

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $page = max(1, (int) $request->query->get('page', 1));
            $limit = max(1, min(100, (int) $request->query->get('limit', 10)));
            $offset = ($page - 1) * $limit;

            $repo = $this->em->getRepository(OrganismeDemandeur::class);
            $qb = $repo->createQueryBuilder('o')
                ->orderBy('o.organismeDemandeurRaisonSociale', 'ASC')
                ->setFirstResult($offset)
                ->setMaxResults($limit);
            $items = $qb->getQuery()->getResult();

            $total = (int) $this->em->createQueryBuilder()
                ->select('COUNT(o)')
                ->from(OrganismeDemandeur::class, 'o')
                ->getQuery()
                ->getSingleScalarResult();

            return $this->json([
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'data' => array_map(fn($o) => $this->toArray($o), $items),
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            error_log('Erreur dans list(): ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return $this->json([
                'error' => 'Erreur lors de la récupération des organismes demandeurs',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/all', name: 'all', methods: ['GET'])]
    public function all(): JsonResponse
    {
        try {
            $repo = $this->em->getRepository(OrganismeDemandeur::class);
            $qb = $repo->createQueryBuilder('o')
                ->orderBy('o.organismeDemandeurRaisonSociale', 'ASC');
            $items = $qb->getQuery()->getResult();

            $data = array_map(function(OrganismeDemandeur $o) {
                $raisonSociale = $o->getOrganismeDemandeurRaisonSociale();
                $libelle = $o->getOrganismeDemandeurLibelle();
                return [
                    'organismeDemandeurId' => $o->getOrganismeDemandeurId(),
                    'organismeDemandeurRaisonSociale' => $raisonSociale,
                    'organismeDemandeurLibelle' => $libelle ?? $raisonSociale ?? '',
                ];
            }, $items);

            return $this->json($data);
        } catch (\Exception $e) {
            error_log('Erreur dans all(): ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            return $this->json([
                'error' => 'Erreur lors de la récupération des organismes demandeurs',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function getOne(OrganismeDemandeur $organismeDemandeur): JsonResponse
    {
        return $this->json($this->toArray($organismeDemandeur));
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'POST'])]
    public function update(Request $request, OrganismeDemandeur $organismeDemandeur): JsonResponse
    {
        $contentType = $request->headers->get('content-type') ?? '';
        $data = stripos($contentType, 'application/json') !== false
            ? json_decode($request->getContent(), true)
            : $request->request->all();

        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid payload (expected JSON or form-data)'], Response::HTTP_BAD_REQUEST);
        }

        $this->mapScalars($organismeDemandeur, $data);
        $this->setRelations($organismeDemandeur, $data);

        $logoFile = $request->files->get('organismeDemandeurLogo');
        if ($logoFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
            $publicPath = $this->storeUploadedLogo($logoFile, $organismeDemandeur->getOrganismeDemandeurLogo());
            if ($publicPath === null) {
                return $this->json(['error' => 'Failed to store uploaded logo.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $organismeDemandeur->setOrganismeDemandeurLogo($publicPath);
        } else {
            $base64Key = $data['organismeDemandeurLogo'] ?? $data['organismeDemandeurLogo'] ?? null;
            if (!empty($base64Key) && is_string($base64Key)) {
                $publicPath = $this->saveBase64Logo($base64Key, $organismeDemandeur->getOrganismeDemandeurLogo());
                if ($publicPath === null) {
                    return $this->json(['error' => 'Invalid base64 image.'], Response::HTTP_BAD_REQUEST);
                }
                $organismeDemandeur->setOrganismeDemandeurLogo($publicPath);
            } elseif (!empty($data['removeLogo']) && in_array($data['removeLogo'], ['1', 1, true, 'true'], true)) {
                $this->deletePublicLogo($organismeDemandeur->getOrganismeDemandeurLogo());
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

    private function storeUploadedLogo(\Symfony\Component\HttpFoundation\File\UploadedFile $uploadedLogo, ?string $oldPublicPath = null): ?string
    {
        $uploadsDir = rtrim($this->getParameter('uploads_directory'), '/\\') . '/organismes';
        if (!is_dir($uploadsDir) && !@mkdir($uploadsDir, 0755, true) && !is_dir($uploadsDir)) {
            return null;
        }

        $ext = $uploadedLogo->guessExtension() ?: 'png';
        $safeName = uniqid('org_', true) . '.' . preg_replace('/[^a-z0-9]+/i', '', $ext);
        try {
            $uploadedLogo->move($uploadsDir, $safeName);
            if ($oldPublicPath) {
                $this->deletePublicLogo($oldPublicPath);
            }
            return '/uploads/organismes/' . $safeName;
        } catch (\Throwable $e) {
            return null;
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(OrganismeDemandeur $organismeDemandeur): JsonResponse
    {
        foreach ($organismeDemandeur->getAppelOffres() as $appel) {
            $appel->setAppelOffresOrganismeDemandeurId(null);
            $this->em->persist($appel);
        }
        $this->em->remove($organismeDemandeur);
        $this->em->flush();

        return $this->json(['message' => 'OrganismeDemandeur deleted.']);
    }

    private function mapScalars(OrganismeDemandeur $o, array $data): void
    {
        $map = [
            'organismeDemandeurRaisonSociale' => 'setOrganismeDemandeurRaisonSociale',
            'organismeDemandeurRaisonSocialeShort' => 'setOrganismeDemandeurRaisonSocialeShort',
            'organismeDemandeurDescription' => 'setOrganismeDemandeurDescription',
            'organismeDemandeurLibelle' => 'setOrganismeDemandeurLibelle',
            'organismeDemandeurAcronyme' => 'setOrganismeDemandeurAcronyme',
            'organismeDemandeurCoordinateurPrenomNom' => 'setOrganismeDemandeurCoordinateurPrenomNom',
            'organismeDemandeurCoordinateurEmail' => 'setOrganismeDemandeurCoordinateurEmail',
            'organismeDemandeurCoordinateurTel' => 'setOrganismeDemandeurCoordinateurTel',
            'organismeDemandeurAdresse' => 'setOrganismeDemandeurAdresse',
            'organismeDemandeurTelephone' => 'setOrganismeDemandeurTelephone',
            'organismeDemandeurEmail' => 'setOrganismeDemandeurEmail',
            'organismeDemandeurPersonneContactPrenomNom1' => 'setOrganismeDemandeurPersonneContactPrenomNom1',
            'organismeDemandeurPersonneContactTelephone1' => 'setOrganismeDemandeurPersonneContactTelephone1',
            'organismeDemandeurPersonneContactEmail1' => 'setOrganismeDemandeurPersonneContactEmail1',
            'organismeDemandeurPersonneContactPrenomNom2' => 'setOrganismeDemandeurPersonneContactPrenomNom2',
            'organismeDemandeurPersonneContactTelephone2' => 'setOrganismeDemandeurPersonneContactTelephone2',
            'organismeDemandeurPersonneContactEmail2' => 'setOrganismeDemandeurPersonneContactEmail2',
            'organismeDemandeurPersonneContactPrenomNom3' => 'setOrganismeDemandeurPersonneContactPrenomNom3',
            'organismeDemandeurPersonneContactTelephone3' => 'setOrganismeDemandeurPersonneContactTelephone3',
            'organismeDemandeurPersonneContactEmail3' => 'setOrganismeDemandeurPersonneContactEmail3',
        ];

        foreach ($map as $key => $setter) {
            if (array_key_exists($key, $data)) {
                $o->$setter($data[$key]);
            }
        }
    }

    private function setRelations(OrganismeDemandeur $o, array $data): void
    {
        if (!empty($data['paysId'])) {
            $pays = $this->em->getRepository(Pays::class)->find($data['paysId']);
            $o->setPays($pays);
        }

        if (!empty($data['natureOrganismeDemendeurId'])) {
            $nature = $this->em->getRepository(NatureOrganismeDemendeur::class)->find($data['natureOrganismeDemendeurId']);
            $o->setNatureOrganismeDemendeur($nature);
        }

        if (!empty($data['secteurActiviteId'])) {
            $secteur = $this->em->getRepository(SecteurActivite::class)->find($data['secteurActiviteId']);
            $o->setSecteurActivite($secteur);
        }
    }

    private function saveBase64Logo(string $base64, ?string $oldPublicPath = null): ?string
    {
        if (preg_match('#^data:(image/[^;]+);base64,#', $base64, $m)) {
            $mime = $m[1];
            $base64 = substr($base64, strpos($base64, ',') + 1);
            $ext = explode('/', $mime)[1] ?? 'png';
        } else {
            $ext = 'png';
        }

        $decoded = base64_decode($base64, true);
        if ($decoded === false) {
            return null;
        }

        $uploadsDir = rtrim($this->getParameter('uploads_directory'), '/\\') . '/organismes';
        if (!is_dir($uploadsDir)) {
            @mkdir($uploadsDir, 0755, true);
        }

        $safeName = uniqid('org_', true) . '.' . $ext;
        $path = $uploadsDir . '/' . $safeName;
        file_put_contents($path, $decoded);

        if ($oldPublicPath) {
            $this->deletePublicLogo($oldPublicPath);
        }

        return '/uploads/organismes/' . $safeName;
    }

    private function deletePublicLogo(?string $publicPath): void
    {
        if (empty($publicPath)) {
            return;
        }

        $uploadsDir = rtrim($this->getParameter('uploads_directory'), '/\\') . '/organismes';
        $filename = basename($publicPath);
        $fullPath = $uploadsDir . '/' . $filename;

        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }
    }

    private function toArray(OrganismeDemandeur $o): array
    {
        try {
            $nature = $o->getNatureOrganismeDemendeur();
            $secteur = $o->getSecteurActivite();
            $pays = $o->getPays();

            return [
                'organismeDemandeurId' => $o->getOrganismeDemandeurId(),
                'organismeDemandeurLibelle' => $o->getOrganismeDemandeurLibelle(),
                'organismeDemandeurRaisonSociale' => $o->getOrganismeDemandeurRaisonSociale(),
                'organismeDemandeurRaisonSocialeShort' => $o->getOrganismeDemandeurRaisonSocialeShort(),
                'organismeDemandeurDescription' => $o->getOrganismeDemandeurDescription(),
                'organismeDemandeurAcronyme' => $o->getOrganismeDemandeurAcronyme(),
                'organismeDemandeurLogo' => $o->getOrganismeDemandeurLogo(),
                'organismeDemandeurCoordinateurPrenomNom' => $o->getOrganismeDemandeurCoordinateurPrenomNom(),
                'organismeDemandeurCoordinateurEmail' => $o->getOrganismeDemandeurCoordinateurEmail(),
                'organismeDemandeurCoordinateurTel' => $o->getOrganismeDemandeurCoordinateurTel(),
                'organismeDemandeurAdresse' => $o->getOrganismeDemandeurAdresse(),
                'organismeDemandeurTelephone' => $o->getOrganismeDemandeurTelephone(),
                'organismeDemandeurEmail' => $o->getOrganismeDemandeurEmail(),
                'organismeDemandeurPersonneContactPrenomNom1' => $o->getOrganismeDemandeurPersonneContactPrenomNom1(),
                'organismeDemandeurPersonneContactTelephone1' => $o->getOrganismeDemandeurPersonneContactTelephone1(),
                'organismeDemandeurPersonneContactEmail1' => $o->getOrganismeDemandeurPersonneContactEmail1(),
                'organismeDemandeurPersonneContactPrenomNom2' => $o->getOrganismeDemandeurPersonneContactPrenomNom2(),
                'organismeDemandeurPersonneContactTelephone2' => $o->getOrganismeDemandeurPersonneContactTelephone2(),
                'organismeDemandeurPersonneContactEmail2' => $o->getOrganismeDemandeurPersonneContactEmail2(),
                'organismeDemandeurPersonneContactPrenomNom3' => $o->getOrganismeDemandeurPersonneContactPrenomNom3(),
                'organismeDemandeurPersonneContactTelephone3' => $o->getOrganismeDemandeurPersonneContactTelephone3(),
                'organismeDemandeurPersonneContactEmail3' => $o->getOrganismeDemandeurPersonneContactEmail3(),
                'pays' => $pays?->getPaysLibelle(),
                'natureOrganismeDemendeur' => ($nature && method_exists($nature, 'getNatureOrganismeDemendeurLibelle')) ? $nature->getNatureOrganismeDemendeurLibelle() : null,
                'secteurActivite' => ($secteur && method_exists($secteur, 'getSecteurActiviteLibelle')) ? $secteur->getSecteurActiviteLibelle() : null,
            ];
        } catch (\Exception $e) {
            error_log('Erreur dans toArray: ' . $e->getMessage());
            return [
                'organismeDemandeurId' => $o->getOrganismeDemandeurId(),
                'organismeDemandeurRaisonSociale' => $o->getOrganismeDemandeurRaisonSociale(),
                'organismeDemandeurRaisonSocialeShort' => $o->getOrganismeDemandeurRaisonSocialeShort(),
            ];
        }
    }
}