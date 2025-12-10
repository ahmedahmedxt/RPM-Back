<?php

namespace App\Controller\Api;

use App\Entity\SourceAppelOffres;
use App\Entity\Pays;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/source-appel-offres', name: 'api_source_appel_offres_')]
class SourceAppelOffresController extends AbstractController
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

        $errors = [];

        if (empty($data['sourceAppelOffresLibelle'])) {
            $errors['sourceAppelOffresLibelle'] = 'This field is required.';
        }

        $url = $data['sourceAppelOffresUrl'] ?? null;

        if (empty($url)) {
            $errors['sourceAppelOffresUrl'] = 'This field is required.';
        } else {
            if (!preg_match('#^https?://[^\s]+$#i', (string)$url)) {
                $errors['sourceAppelOffresUrl'] = 'Invalid URL format. URL must start with http:// or https://';
            }
        }

        if (!empty($errors)) {
            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $item = new SourceAppelOffres();
        $this->mapScalars($item, $data);
        $this->setRelations($item, $data);

        $violations = $this->validator->validate($item);
        if (count($violations) > 0) {
            $payload = [];
            foreach ($violations as $err) {
                $payload[$err->getPropertyPath()] = $err->getMessage();
            }
            return $this->json(['errors' => $payload], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($item);
        $this->em->flush();

        return $this->json($this->toArray($item), Response::HTTP_CREATED);
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $page   = max(1, (int)$request->query->get('page', 1));
        $limit  = max(1, min(100, (int)$request->query->get('limit', 10)));
        $offset = ($page - 1) * $limit;

        $sortField = $request->query->get('sortField', 'sourceAppelOffresLibelle');
        $sortDir   = strtoupper($request->query->get('sortDir', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $search    = trim((string)$request->query->get('search', ''));

        $allowedSortFields = [
            'sourceAppelOffresLibelle' => 't.sourceAppelOffresLibelle',
            'sourceAppelOffresUrl'     => 't.sourceAppelOffresUrl',
            'sourceAppelOffresId'      => 't.sourceAppelOffresId',
        ];

        if (!array_key_exists($sortField, $allowedSortFields) && $sortField !== 'paysId') {
            $sortField = 'sourceAppelOffresLibelle';
        }

        $repo = $entityManager->getRepository(SourceAppelOffres::class);

        $applyFilters = function (\Doctrine\ORM\QueryBuilder $qb) use ($search) {
            if ($search === '') {
                return;
            }

            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('t.sourceAppelOffresLibelle', ':search'),
                    $qb->expr()->like('t.sourceAppelOffresDescription', ':search'),
                    $qb->expr()->like('t.sourceAppelOffresUrl', ':search'),
                    $qb->expr()->like('p.paysLibelle', ':search')
                )
            )
            ->setParameter('search', '%' . $search . '%');
        };

        $qbCount = $repo->createQueryBuilder('t')
            ->leftJoin('t.pays', 'p');

        $applyFilters($qbCount);

        $total = (int)$qbCount
            ->select('COUNT(t.sourceAppelOffresId)')
            ->getQuery()
            ->getSingleScalarResult();

        $qbData = $repo->createQueryBuilder('t')
            ->leftJoin('t.pays', 'p');

        $applyFilters($qbData);

        if ($sortField === 'paysId') {
            $qbData->orderBy('p.paysLibelle', $sortDir)
                ->addOrderBy('t.sourceAppelOffresLibelle', 'ASC');
        } else {
            $qbData->orderBy($allowedSortFields[$sortField], $sortDir);
        }

        $qbData->setFirstResult($offset)
            ->setMaxResults($limit);

        $items = $qbData->getQuery()->getResult();

        return new JsonResponse([
            'page'  => $page,
            'limit' => $limit,
            'total' => $total,
            'data'  => array_map(fn(SourceAppelOffres $o) => $this->toArray($o), $items),
        ], Response::HTTP_OK);
    }


    #[Route('/all', name: 'all', methods: ['GET'])]
    public function all(): JsonResponse
    {
        $repo  = $this->em->getRepository(SourceAppelOffres::class);
        $items = $repo->findBy([], ['sourceAppelOffresLibelle' => 'ASC']);

        $data = array_map(fn(SourceAppelOffres $o) => [
            'sourceAppelOffresId'      => $o->getSourceAppelOffresId(),
            'sourceAppelOffresLibelle' => $o->getSourceAppelOffresLibelle(),
        ], $items);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function getOne(SourceAppelOffres $sourceAppelOffres): JsonResponse
    {
        return $this->json($this->toArray($sourceAppelOffres));
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'POST'])]
    public function update(Request $request, SourceAppelOffres $sourceAppelOffres): JsonResponse
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

        $errors = [];

        if (empty($data['sourceAppelOffresLibelle'])) {
            $errors['sourceAppelOffresLibelle'] = 'This field is required.';
        }

        $url = $data['sourceAppelOffresUrl'] ?? null;

        if (empty($url)) {
            $errors['sourceAppelOffresUrl'] = 'This field is required.';
        } else {
            if (!preg_match('#^https?://[^\s]+$#i', (string)$url)) {
                $errors['sourceAppelOffresUrl'] = 'Invalid URL format. URL must start with http:// or https://';
            }
        }

        if (!empty($errors)) {
            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $this->mapScalars($sourceAppelOffres, $data);
        $this->setRelations($sourceAppelOffres, $data);

        $violations = $this->validator->validate($sourceAppelOffres);
        if (count($violations) > 0) {
            $payload = [];
            foreach ($violations as $err) {
                $payload[$err->getPropertyPath()] = $err->getMessage();
            }
            return $this->json(['errors' => $payload], Response::HTTP_BAD_REQUEST);
        }

        $this->em->flush();

        return $this->json($this->toArray($sourceAppelOffres));
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(SourceAppelOffres $sourceAppelOffres): JsonResponse
    {
        foreach ($sourceAppelOffres->getAppelOffres() as $appel) {
            $appel->setSourceAppelOffres(null);
            $this->em->persist($appel);
        }

        $this->em->remove($sourceAppelOffres);
        $this->em->flush();

        return $this->json(['message' => 'SourceAppelOffres deleted.']);
    }

    private function mapScalars(SourceAppelOffres $o, array $data): void
    {
        $map = [
            'sourceAppelOffresLibelle'     => 'setSourceAppelOffresLibelle',
            'sourceAppelOffresDescription' => 'setSourceAppelOffresDescription',
            'sourceAppelOffresUrl'         => 'setSourceAppelOffresUrl',
        ];

        foreach ($map as $key => $setter) {
            if (array_key_exists($key, $data)) {
                $o->$setter($data[$key]);
            }
        }
    }

    private function setRelations(SourceAppelOffres $o, array $data): void
    {
        if (!empty($data['paysId'])) {
            $p = $this->em->getRepository(Pays::class)->find($data['paysId']);
            $o->setPays($p);
        }
    }

    private function toArray(SourceAppelOffres $o): array
    {
        return [
            'sourceAppelOffresId'          => $o->getSourceAppelOffresId(),
            'sourceAppelOffresLibelle'     => $o->getSourceAppelOffresLibelle(),
            'sourceAppelOffresDescription' => $o->getSourceAppelOffresDescription(),
            'sourceAppelOffresUrl'         => $o->getSourceAppelOffresUrl(),
            'paysId'                       => $o->getPays()?->getPaysId(),
            'paysLibelle'                  => $o->getPays()?->getPaysLibelle(),
        ];
    }
}
