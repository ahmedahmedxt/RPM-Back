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
    public function list(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = max(1, min(100, (int)$request->query->get('limit', 10)));
        $offset = ($page - 1) * $limit;

        $sortField = $request->query->get('sortField', 'organismeDemandeurRaisonSociale');
        $sortDir = strtoupper($request->query->get('sortDir', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $search = trim((string)$request->query->get('search', ''));

        $allowedSortFields = [
            'organismeDemandeurRaisonSociale' => 't.organismeDemandeurRaisonSociale',
            'organismeDemandeurRaisonSocialeShort' => 't.organismeDemandeurRaisonSocialeShort',
            'organismeDemandeurCoordinateurPrenomNom' => 't.organismeDemandeurCoordinateurPrenomNom',
            'organismeDemandeurTelephone' => 't.organismeDemandeurTelephone',
            'organismeDemandeurId' => 't.organismeDemandeurId',
        ];

        if (!array_key_exists($sortField, $allowedSortFields) && $sortField !== 'paysId') {
            $sortField = 'organismeDemandeurRaisonSociale';
        }

        $repo = $entityManager->getRepository(OrganismeDemandeur::class);

        $applyFilters = function(\Doctrine\ORM\QueryBuilder $qb) use ($search) {
            if ($search === '') {
                return;
            }

            $qb->leftJoin('t.pays', 'p');

            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('t.organismeDemandeurRaisonSociale', ':search'),
                    $qb->expr()->like('t.organismeDemandeurRaisonSocialeShort', ':search'),
                    $qb->expr()->like('t.organismeDemandeurCoordinateurPrenomNom', ':search'),
                    $qb->expr()->like('t.organismeDemandeurTelephone', ':search'),
                    $qb->expr()->like('p.paysLibelle', ':search')
                )
            )
            ->setParameter('search', '%' . $search . '%');
        };

        $qbCount = $repo->createQueryBuilder('t');
        $applyFilters($qbCount);
        $total = (int) $qbCount
            ->select('COUNT(t.organismeDemandeurId)')
            ->getQuery()
            ->getSingleScalarResult();

        $qbData = $repo->createQueryBuilder('t');
        $applyFilters($qbData);

        if ($sortField === 'paysId') {
            $qbData->leftJoin('t.pays', 'p');
            $qbData->orderBy('p.paysLibelle', $sortDir);
        } else {
            $qbData->orderBy($allowedSortFields[$sortField], $sortDir);
        }

        $qbData->setFirstResult($offset)
            ->setMaxResults($limit);

        $items = $qbData->getQuery()->getResult();

        return new JsonResponse([
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'data' => array_map(fn($o) => $this->toArray($o), $items),
        ], Response::HTTP_OK);
    }

  

    #[Route('/all', name: 'all', methods: ['GET'])]
    public function all(): JsonResponse
    {
        $repo = $this->em->getRepository(OrganismeDemandeur::class);
        $items = $repo->findBy([], ['organismeDemandeurRaisonSociale' => 'ASC']);

        $data = array_map(fn(OrganismeDemandeur $o) => [
            'organismeDemandeurId' => $o->getOrganismeDemandeurId(),
            'organismeDemandeurRaisonSociale' => $o->getOrganismeDemandeurRaisonSociale(),
            'organismeDemandeurRaisonSocialeShort' => $o->getOrganismeDemandeurRaisonSocialeShort(),
        ], $items);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function getOne(OrganismeDemandeur $organismeDemandeur): JsonResponse
    {
        return $this->json($this->toArray($organismeDemandeur));
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'POST'])]
    public function update(Request $request, OrganismeDemandeur $organismeDemandeur): JsonResponse{
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

        $this->mapScalars($organismeDemandeur, $data);
        $this->setRelations($organismeDemandeur, $data);

        /** @var UploadedFile|null $logoFile */
        $logoFile = $request->files->get('organismeDemandeurLogo');

        $removeLogo = (bool) $request->request->get('removeLogo', false);

        if ($logoFile instanceof UploadedFile) {
            $uploadsDir = rtrim($this->getParameter('uploads_directory'), '/\\') . '/organismes';
            if (!is_dir($uploadsDir)) {
                @mkdir($uploadsDir, 0755, true);
            }

            $safeName = uniqid('org_', true) . '.' . ($logoFile->guessExtension() ?: 'png');

            try {
                $logoFile->move($uploadsDir, $safeName);
            } catch (\Throwable $e) {
                return $this->json(['error' => 'Failed to store uploaded logo: '.$e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $this->deleteExistingLogoFile($organismeDemandeur);

            $organismeDemandeur->setOrganismeDemandeurLogo('/uploads/organismes/' . $safeName);
        } elseif ($removeLogo) {
            $this->deleteExistingLogoFile($organismeDemandeur);
            $organismeDemandeur->setOrganismeDemandeurLogo(null);
        } else {
            $base64Key = $data['organismeDemandeurLogo'] ?? ($data['organisme_demandeur_raison_sociale_logo'] ?? null);
            if (!empty($base64Key) && is_string($base64Key)) {
                $publicPath = $this->saveBase64Logo($base64Key);
                if ($publicPath === null) {
                    return $this->json(['error' => 'Invalid base64 image.'], Response::HTTP_BAD_REQUEST);
                }
                $this->deleteExistingLogoFile($organismeDemandeur);
                $organismeDemandeur->setOrganismeDemandeurLogo($publicPath);
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

        return $this->json($this->toArray($organismeDemandeur));
    }


    private function deleteExistingLogoFile(OrganismeDemandeur $organisme)
    {
        $existingPath = $organisme->getOrganismeDemandeurLogo();
        if (!$existingPath) return;

        if (str_starts_with($existingPath, '/uploads/organismes/')) {
            $full = $this->getParameter('kernel.project_dir') . '/public' . $existingPath;
            if (file_exists($full)) {
                @unlink($full);
            }
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

    private function saveBase64Logo(string $base64): ?string
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

        return '/uploads/organismes/' . $safeName;
    }

    private function toArray(OrganismeDemandeur $o): array
    {
        return [
            'organismeDemandeurId' => $o->getOrganismeDemandeurId(),
            'organismeDemandeurRaisonSociale' => $o->getOrganismeDemandeurRaisonSociale(),
            'organismeDemandeurRaisonSocialeShort' => $o->getOrganismeDemandeurRaisonSocialeShort(),
            'organismeDemandeurDescription' => $o->getOrganismeDemandeurDescription(),
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
            'pays' => $o->getPays()?->getPaysLibelle(),
            'natureOrganismeDemendeur' => $o->getNatureOrganismeDemendeur()?->getNatureOrganismeDemendeurLibelle(),
            'secteurActivite' => $o->getSecteurActivite()?->getSecteurActiviteLibelle(),
        ];
    }
}
