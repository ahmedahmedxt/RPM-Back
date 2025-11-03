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

    /**
     * Create a new OrganismeDemandeur
     *
     */
    #[Route("", name: "create", methods: ["POST"])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        // required: libelle
        $libelle = $data['organismeDemandeurLibelle'] ?? null;
        if (!$libelle) {
            return $this->json(['errors' => ['organismeDemandeurLibelle' => 'This field is required.']], Response::HTTP_BAD_REQUEST);
        }

        // uniqueness check
        $repo = $this->em->getRepository(OrganismeDemandeur::class);
        if ($repo->findOneBy(['organismeDemandeurLibelle' => $libelle])) {
            return $this->json(['error' => 'An organisme with this libelle already exists.'], Response::HTTP_CONFLICT);
        }

        $organisme = new OrganismeDemandeur();
        $organisme->setOrganismeDemandeurLibelle($libelle);
        // map optional scalar fields
        $this->mapScalars($organisme, $data);

        // Relations (set if provided)
        $this->setRelationsFromData($organisme, $data);

        // Validate entity
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
        $limit = max(1, min(100, (int) $request->query->get('limit', 50)));
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
     * Get one OrganismeDemandeur
     * GET /api/organisme-demandeurs/{id}
     */
    #[Route("/{id}", name: "get", methods: ["GET"])]
    public function getOne(OrganismeDemandeur $organismeDemandeur): JsonResponse
    {
        return $this->json($this->toArray($organismeDemandeur), Response::HTTP_OK);
    }

    /**
     * Update (partial) an OrganismeDemandeur
     * PUT /api/organisme-demandeurs/{id}
     * Accepts a JSON body with any writable fields and relation ids (paysId, natureOrganismeDemendeurId, secteurActiviteId)
     */
    #[Route("/{id}", name: "update", methods: ["PUT"])]
    public function update(Request $request, OrganismeDemandeur $organismeDemandeur): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST);
        }

        // optional fields mapping
        if (array_key_exists('organismeDemandeurLibelle', $data)) {
            $newLib = $data['organismeDemandeurLibelle'];
            if ($newLib !== $organismeDemandeur->getOrganismeDemandeurLibelle()) {
                // uniqueness check
                $exists = $this->em->getRepository(OrganismeDemandeur::class)->findOneBy(['organismeDemandeurLibelle' => $newLib]);
                if ($exists && $exists->getOrganismeDemandeurId() !== $organismeDemandeur->getOrganismeDemandeurId()) {
                    return $this->json(['error' => 'Another organisme with this libelle already exists.'], Response::HTTP_CONFLICT);
                }
            }
            $organismeDemandeur->setOrganismeDemandeurLibelle($newLib);
        }

        $this->mapScalars($organismeDemandeur, $data);
        $this->setRelationsFromData($organismeDemandeur, $data);

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
                'paysId' => $o->getPays()->getPaysId() ?? $o->getPays()->getId(),
                'paysLibelle' => $o->getPays()->getPaysLibelle(),
            ] : null,
            'natureOrganismeDemendeur' => $o->getNatureOrganismeDemendeur() ? [
                'id' => $o->getNatureOrganismeDemendeur()->getNatureOrganismeDemendeurId(),
                'libelle' => $o->getNatureOrganismeDemendeur()->getNatureOrganismeDemendeurLibelle(),
            ] : null,
            'secteurActivite' => $o->getSecteurActivite() ? [
                'id' => $o->getSecteurActivite()->getSecteurActiviteId(),
                'libelle' => $o->getSecteurActivite()->getSecteurActiviteLibelle(),
            ] : null,
        ];
    }
}
