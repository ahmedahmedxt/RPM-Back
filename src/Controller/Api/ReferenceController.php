<?php

namespace App\Controller\Api;

use App\Entity\Reference;
use App\Entity\Pays;
use App\Entity\Lieu;
use App\Entity\Devises;
use App\Entity\Categorie;
use App\Entity\BailleurFond;
use App\Entity\EnvironnementDeveloppement;
use App\Entity\Technologie;
use App\Entity\Methodologie;
use App\Entity\Role;
use App\Entity\AppelOffres;
use App\Entity\ReferenceCaracteristiqueSpeciale;
use App\Entity\ReferenceDocuments;
use App\Entity\ReferenceCollaborateur;
use App\Repository\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route('/api/reference')]
class ReferenceController extends AbstractController
{
    public function __construct(
        private ReferenceRepository $referenceRepository,
        private EntityManagerInterface $em,
    ) {}

    private function getNextOrdreForCategorie(Categorie $cat): int
    {
        $max = (int) $this->referenceRepository->createQueryBuilder('r')
            ->select('COALESCE(MAX(r.referenceOrdre), 0)')
            ->where('r.categorie = :cat')
            ->setParameter('cat', $cat)
            ->getQuery()
            ->getSingleScalarResult();

        return $max + 1;
    }

    private function shiftOrdresForInsert(Categorie $cat, int $ordreWanted, ?int $excludeReferenceId = null): void
    {
        $ordreWanted = max(1, $ordreWanted);

        $qb = $this->em->createQueryBuilder()
            ->update(Reference::class, 'r')
            ->set('r.referenceOrdre', 'r.referenceOrdre + 1')
            ->where('r.categorie = :cat')
            ->andWhere('r.referenceOrdre >= :ordre')
            ->setParameter('cat', $cat)
            ->setParameter('ordre', $ordreWanted);

        if ($excludeReferenceId) {
            $qb->andWhere('r.referenceID != :rid')
               ->setParameter('rid', $excludeReferenceId);
        }

        $qb->getQuery()->execute();
    }

    private function serializeReference(Reference $ref): array
    {
        $pays = $ref->getPays();
        $paysData = $pays ? [
            'id'      => $pays->getPaysId(),
            'libelle' => $pays->getPaysLibelle(),
        ] : null;

        $lieu = $ref->getLieu();
        $lieuData = $lieu ? [
            'id'      => $lieu->getLieuId(),
            'libelle' => $lieu->getLieuLibelle(),
        ] : null;

        $dev = $ref->getDevises();
        $devData = $dev ? [
            'id'      => $dev->getDevisesId(),
            'libelle' => $dev->getDevisesLibelle(),
            'acronyme' => $dev->getDevisesAcronyme(),
            'symbole' => $dev->getDeviseSymbole(),
        ] : null;

        $cat = $ref->getCategorie();
        $catData = $cat ? [
            'id'      => $cat->getId(),
            'libelle' => $cat->getCategorieLibelle(),
            'short'   => $cat->getCategorieShort(),
        ] : null;

        return [
            'referenceID'   => $ref->getReferenceID(),
            'referenceRef'  => $ref->getReferenceRef(),
            'referenceOrdre' => $ref->getReferenceOrdre(),

            'referenceTitre' => $ref->getReferenceTitre(),
            'referenceLibelle' => $ref->getReferenceLibelle(),
            'referenceUrlFonctionnel' => $ref->getReferenceUrlFonctionnel(),
            'referenceDureeExecution' => $ref->getReferenceDureeExecution(),
            'referenceDateDemarrage' => $ref->getReferenceDateDemarrage()?->format('Y-m-d'),
            'referenceDateAchevement' => $ref->getReferenceDateAchevement()?->format('Y-m-d'),
            'referenceDateReceptionProvisoire' => $ref->getReferenceDateReceptionProvisoire()?->format('Y-m-d'),
            'referenceDateReceptionDefinitive' => $ref->getReferenceDateReceptionDefinitive()?->format('Y-m-d'),
            'referenceDureeGarantie' => $ref->getReferenceDureeGarantie(),
            'referenceCaracteristiques' => $ref->getReferenceCaracteristiques(),
            'referenceDescription' => $ref->getReferenceDescription(),
            'referenceDescriptionServiceEffectivementRendus' => $ref->getReferenceDescriptionServiceEffectivementRendus(),
            'referenceBudget' => $ref->getReferenceBudget(),
            'referencePartBudget' => $ref->getReferencePartBudget(),
            'referenceRemarque' => $ref->getReferenceRemarque(),

            'pays'      => $paysData,
            'lieu'      => $lieuData,
            'devises'   => $devData,
            'categorie' => $catData,

            'bailleursFond' => array_map(
                fn (BailleurFond $b) => [
                    'id'       => $b->getBailleurFondId(),
                    'libelle'  => $b->getBailleurFondLibelle(),
                    'acronyme' => $b->getBailleurFondAcronyme(),
                ],
                $ref->getBailleurfonds()->toArray()
            ),
            'environnementsDeveloppement' => array_map(
                fn (EnvironnementDeveloppement $env) => [
                    'id'       => $env->getEnvironnementDeveloppementId(),
                    'libelle'  => $env->getEnvironnementDeveloppementLibelle(),
                ],
                $ref->getEnvironnementsDeveloppement()->toArray()
            ),
            'technologies' => array_map(
                fn (Technologie $t) => [
                    'id'      => $t->getTechnologieId(),
                    'libelle' => $t->getReferenceTechnologieLibelle(),
                ],
                $ref->getTechnologies()->toArray()
            ),
            'methodologies' => array_map(
                fn (Methodologie $m) => [
                    'id'      => $m->getMethodologieId(),
                    'libelle' => $m->getMethodologieLibelle(),
                ],
                $ref->getMethodologies()->toArray()
            ),
            'roles' => array_map(
                fn (Role $r) => [
                    'id'      => $r->getRoleId(),
                    'libelle' => $r->getRoleLibelle(),
                    'short'   => $r->getRoleShort(),
                ],
                $ref->getRolesReference()->toArray()
            ),
            'appelOffres' => array_map(
                fn (AppelOffres $ao) => [
                    'id'      => $ao->getAppelOffresId(),
                    'libelle' => $ao->getAppelOffresObjet(),
                ],
                $ref->getAppelOffres()->toArray()
            ),
            'referenceCaracteristiqueSpeciales' => array_map(
                fn (ReferenceCaracteristiqueSpeciale $cs) => [
                    'id'      => $cs->getReferenceCaracteristiqueSpecialeId(),
                    'libelle' => $cs->getReferenceCaracteristiqueSpecialeTitre(),
                ],
                $ref->getReferenceCaracteristiqueSpeciales()->toArray()
            ),
            'referenceDocuments' => array_map(
                fn (ReferenceDocuments $doc) => [
                    'id'    => $doc->getReferenceDocumentsId(),
                    'type'  => $doc->getTypeDocument(),
                    'libelle' => $doc->getReferenceDocumentsLibelle(),
                ],
                $ref->getReferenceDocuments()->toArray()
            ),
        ];
    }

    #[Route('/all', name: 'ref_all', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $refs = $this->referenceRepository->findAll();
        return $this->json(array_map(fn (Reference $r) => $this->serializeReference($r), $refs));
    }

    #[Route('/list', name: 'ref_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $size = max(1, min(100, (int) $request->query->get('size', 10)));

        $sortField = $request->query->get('sortField', 'referenceID');
        $sortDir   = strtolower((string) $request->query->get('sortDir', 'asc')) === 'desc' ? 'DESC' : 'ASC';
        $search    = trim((string) $request->query->get('search', ''));

        $categorieId = (int) $request->query->get('categorieId', 0);

        $allowedSortFields = [
            'referenceID',
            'referenceRef',
            'referenceTitre',
            'referenceDateDemarrage',
            'referenceDureeExecution',
            'referenceDateAchevement',
            'referenceOrdre',
        ];
        if (!in_array($sortField, $allowedSortFields, true)) {
            $sortField = 'referenceID';
        }

        $qb = $this->referenceRepository->createQueryBuilder('r');

        if ($categorieId > 0) {
            $qb->andWhere('IDENTITY(r.categorie) = :catId')
            ->setParameter('catId', $categorieId);

            $clientRequestedSort = $request->query->has('sortField') && $request->query->get('sortField') !== null && $request->query->get('sortField') !== '';
            if (!$clientRequestedSort) {
                $sortField = 'referenceOrdre';
                $sortDir   = 'ASC';
            }
        }

        if ($search !== '') {
            $qb->andWhere(
                'r.referenceRef LIKE :s OR r.referenceTitre LIKE :s OR r.referenceLibelle LIKE :s OR r.referenceDureeExecution LIKE :s OR r.referenceDateDemarrage LIKE :s OR r.referenceDateAchevement LIKE :s'
            )->setParameter('s', '%' . $search . '%');
        }

        $qbCount = clone $qb;
        $total = (int) $qbCount->select('COUNT(r.referenceID)')->getQuery()->getSingleScalarResult();

        $qb->orderBy('r.' . $sortField, $sortDir)
        ->setFirstResult(($page - 1) * $size)
        ->setMaxResults($size);

        $refs = $qb->getQuery()->getResult();

        return $this->json([
            'data'  => array_map(fn (Reference $r) => $this->serializeReference($r), $refs),
            'total' => $total,
            'page'  => $page,
        ]);
    }


    #[Route('/{id}', name: 'ref_one', methods: ['GET'])]
    public function getOne(int $id): JsonResponse
    {
        $ref = $this->referenceRepository->find($id);
        if (!$ref) {
            return $this->json(['error' => 'Reference not found'], 404);
        }
        return $this->json($this->serializeReference($ref));
    }

    #[Route('/create', name: 'ref_create', methods: ['POST'])]
    public function create(Request $req): JsonResponse
    {
        $data = json_decode($req->getContent(), true);
        $ref = new Reference();

        $ref->setReferenceTitre($data['referenceTitre'] ?? null);
        $ref->setReferenceLibelle($data['referenceLibelle'] ?? null);
        $ref->setReferenceUrlFonctionnel($data['referenceUrlFonctionnel'] ?? null);
        $ref->setReferenceDureeExecution($data['referenceDureeExecution'] ?? null);

        $ref->setReferenceDateDemarrage(!empty($data['referenceDateDemarrage']) ? new \DateTime($data['referenceDateDemarrage']) : null);
        $ref->setReferenceDateAchevement(!empty($data['referenceDateAchevement']) ? new \DateTime($data['referenceDateAchevement']) : null);
        $ref->setReferenceDateReceptionProvisoire(!empty($data['referenceDateReceptionProvisoire']) ? new \DateTime($data['referenceDateReceptionProvisoire']) : null);
        $ref->setReferenceDateReceptionDefinitive(!empty($data['referenceDateReceptionDefinitive']) ? new \DateTime($data['referenceDateReceptionDefinitive']) : null);

        $ref->setReferenceDureeGarantie($data['referenceDureeGarantie'] ?? null);
        $ref->setReferenceCaracteristiques($data['referenceCaracteristiques'] ?? null);
        $ref->setReferenceDescription($data['referenceDescription'] ?? null);
        $ref->setReferenceDescriptionServiceEffectivementRendus($data['referenceDescriptionServiceEffectivementRendus'] ?? null);
        $ref->setReferenceBudget($data['referenceBudget'] ?? null);
        $ref->setReferencePartBudget($data['referencePartBudget'] ?? null);
        $ref->setReferenceRemarque($data['referenceRemarque'] ?? null);

        if (!empty($data['paysId'])) {
            $pays = $this->em->getRepository(Pays::class)->find($data['paysId']);
            if ($pays) {
                $ref->setPays($pays);
            }
        }

        if (!empty($data['lieuId'])) {
            $lieu = $this->em->getRepository(Lieu::class)->find($data['lieuId']);
            if ($lieu) {
                $ref->setLieu($lieu);
            }
        }

        if (!empty($data['devisesId'])) {
            $devise = $this->em->getRepository(Devises::class)->find($data['devisesId']);
            if ($devise) {
                $ref->setDevises($devise);
            }
        }

        if (empty($data['categorieId'])) {
            return $this->json(['error' => 'categorieId is required'], 400);
        }

        $cat = $this->em->getRepository(Categorie::class)->find($data['categorieId']);
        if (!$cat) {
            return $this->json(['error' => 'Invalid categorieId'], 400);
        }
        $ref->setCategorie($cat);

        $ordreWanted = !empty($data['referenceOrdre']) ? (int) $data['referenceOrdre'] : null;

        if ($ordreWanted && method_exists($ref, 'setReferenceOrdre')) {
            $this->shiftOrdresForInsert($cat, $ordreWanted, null);
            $ref->setReferenceOrdre($ordreWanted);
        } else {
            $next = $this->getNextOrdreForCategorie($cat);
            if (method_exists($ref, 'setReferenceOrdre')) {
                $ref->setReferenceOrdre($next);
            }
        }

        if (method_exists($ref, 'rebuildReferenceRefFromCategorieShort')) {
            try {
                $ref->rebuildReferenceRefFromCategorieShort($cat->getCategorieShort());
            } catch (\Throwable $e) {
                $ref->rebuildReferenceRefFromCategorieShort();
            }
        }

        if (!empty($data['bailleurFondIds']) && is_array($data['bailleurFondIds'])) {
            foreach ($data['bailleurFondIds'] as $bid) {
                $bf = $this->em->getRepository(BailleurFond::class)->find($bid);
                if ($bf) {
                    $ref->addBailleurfond($bf);
                }
            }
        }

        if (!empty($data['environnementDeveloppementIds']) && is_array($data['environnementDeveloppementIds'])) {
            foreach ($data['environnementDeveloppementIds'] as $eid) {
                $env = $this->em->getRepository(EnvironnementDeveloppement::class)->find($eid);
                if ($env) {
                    $ref->addEnvironnementDeveloppement($env);
                }
            }
        }

        if (!empty($data['technologieIds']) && is_array($data['technologieIds'])) {
            foreach ($data['technologieIds'] as $tid) {
                $tech = $this->em->getRepository(Technologie::class)->find($tid);
                if ($tech) {
                    $ref->addTechnologie($tech);
                }
            }
        }

        if (!empty($data['methodologieIds']) && is_array($data['methodologieIds'])) {
            foreach ($data['methodologieIds'] as $mid) {
                $meth = $this->em->getRepository(Methodologie::class)->find($mid);
                if ($meth) {
                    $ref->addMethodologie($meth);
                }
            }
        }

        if (!empty($data['roleIds']) && is_array($data['roleIds'])) {
            foreach ($data['roleIds'] as $rid) {
                $role = $this->em->getRepository(Role::class)->find($rid);
                if ($role) {
                    $ref->addRoleReference($role);
                }
            }
        }

        if (!empty($data['appelOffresIds']) && is_array($data['appelOffresIds'])) {
            foreach ($data['appelOffresIds'] as $aid) {
                $ao = $this->em->getRepository(AppelOffres::class)->find($aid);
                if ($ao) {
                    $ref->addAppelOffres($ao);
                }
            }
        }

        if (!empty($data['referenceCaracteristiqueSpecialeIds']) && is_array($data['referenceCaracteristiqueSpecialeIds'])) {
            foreach ($data['referenceCaracteristiqueSpecialeIds'] as $cid) {
                $cs = $this->em->getRepository(ReferenceCaracteristiqueSpeciale::class)->find($cid);
                if ($cs) {
                    $ref->addReferenceCaracteristiqueSpeciale($cs);
                }
            }
        }

        $this->em->persist($ref);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'id'      => $ref->getReferenceID(),
            'data'    => $this->serializeReference($ref),
        ]);
    }

    #[Route('/update/{id}', name: 'ref_update', methods: ['PUT'])]
    public function update(int $id, Request $req): JsonResponse
    {
        $ref = $this->referenceRepository->find($id);
        if (!$ref) {
            return $this->json(['error' => 'Reference not found'], 404);
        }

        $data = json_decode($req->getContent(), true);

        $needRebuildRef = false;
        $oldCategorie = $ref->getCategorie();

        if (array_key_exists('referenceOrdre', $data) && method_exists($ref, 'setReferenceOrdre')) {
            $wanted = (int) $data['referenceOrdre'];
            $wanted = max(1, $wanted);

            $catForShift = $ref->getCategorie();
            if ($catForShift) {
                $this->shiftOrdresForInsert($catForShift, $wanted, $ref->getReferenceID());
            }

            $ref->setReferenceOrdre($wanted);
            $needRebuildRef = true;
        }

        if (array_key_exists('referenceTitre', $data)) {
            $ref->setReferenceTitre($data['referenceTitre']);
        }
        if (array_key_exists('referenceLibelle', $data)) {
            $ref->setReferenceLibelle($data['referenceLibelle']);
        }
        if (array_key_exists('referenceUrlFonctionnel', $data)) {
            $ref->setReferenceUrlFonctionnel($data['referenceUrlFonctionnel']);
        }
        if (array_key_exists('referenceDureeExecution', $data)) {
            $ref->setReferenceDureeExecution($data['referenceDureeExecution']);
        }

        if (array_key_exists('referenceDateDemarrage', $data)) {
            $ref->setReferenceDateDemarrage(
                $data['referenceDateDemarrage'] ? new \DateTime($data['referenceDateDemarrage']) : null
            );
        }
        if (array_key_exists('referenceDateAchevement', $data)) {
            $ref->setReferenceDateAchevement(
                $data['referenceDateAchevement'] ? new \DateTime($data['referenceDateAchevement']) : null
            );
        }
        if (array_key_exists('referenceDateReceptionProvisoire', $data)) {
            $ref->setReferenceDateReceptionProvisoire(
                $data['referenceDateReceptionProvisoire'] ? new \DateTime($data['referenceDateReceptionProvisoire']) : null
            );
        }
        if (array_key_exists('referenceDateReceptionDefinitive', $data)) {
            $ref->setReferenceDateReceptionDefinitive(
                $data['referenceDateReceptionDefinitive'] ? new \DateTime($data['referenceDateReceptionDefinitive']) : null
            );
        }

        if (array_key_exists('referenceDureeGarantie', $data)) {
            $ref->setReferenceDureeGarantie($data['referenceDureeGarantie']);
        }
        if (array_key_exists('referenceCaracteristiques', $data)) {
            $ref->setReferenceCaracteristiques($data['referenceCaracteristiques']);
        }
        if (array_key_exists('referenceDescription', $data)) {
            $ref->setReferenceDescription($data['referenceDescription']);
        }
        if (array_key_exists('referenceDescriptionServiceEffectivementRendus', $data)) {
            $ref->setReferenceDescriptionServiceEffectivementRendus($data['referenceDescriptionServiceEffectivementRendus']);
        }
        if (array_key_exists('referenceBudget', $data)) {
            $ref->setReferenceBudget($data['referenceBudget']);
        }
        if (array_key_exists('referencePartBudget', $data)) {
            $ref->setReferencePartBudget($data['referencePartBudget']);
        }
        if (array_key_exists('referenceRemarque', $data)) {
            $ref->setReferenceRemarque($data['referenceRemarque']);
        }

        if (array_key_exists('paysId', $data)) {
            $pays = $data['paysId']
                ? $this->em->getRepository(Pays::class)->find($data['paysId'])
                : null;
            $ref->setPays($pays);
        }

        if (array_key_exists('lieuId', $data)) {
            $lieu = $data['lieuId']
                ? $this->em->getRepository(Lieu::class)->find($data['lieuId'])
                : null;
            $ref->setLieu($lieu);
        }

        if (array_key_exists('devisesId', $data)) {
            $devise = $data['devisesId']
                ? $this->em->getRepository(Devises::class)->find($data['devisesId'])
                : null;
            $ref->setDevises($devise);
        }

        if (array_key_exists('categorieId', $data)) {
            $cat = $data['categorieId']
                ? $this->em->getRepository(Categorie::class)->find($data['categorieId'])
                : null;

            if (!$cat) {
                return $this->json(['error' => 'Invalid categorieId'], 400);
            }

            if ($oldCategorie && $cat->getId() !== $oldCategorie->getId()) {
                if (!array_key_exists('referenceOrdre', $data) && method_exists($ref, 'setReferenceOrdre')) {
                    $ref->setReferenceOrdre($this->getNextOrdreForCategorie($cat));
                }
                $needRebuildRef = true;
            }

            $ref->setCategorie($cat);
            $needRebuildRef = true;
        }

        if (array_key_exists('bailleurFondIds', $data)) {
            $ref->getBailleurfonds()->clear();
            if (is_array($data['bailleurFondIds'])) {
                foreach ($data['bailleurFondIds'] as $bid) {
                    $bf = $this->em->getRepository(BailleurFond::class)->find($bid);
                    if ($bf) {
                        $ref->addBailleurfond($bf);
                    }
                }
            }
        }

        if (array_key_exists('environnementDeveloppementIds', $data)) {
            $ref->getEnvironnementsDeveloppement()->clear();
            if (is_array($data['environnementDeveloppementIds'])) {
                foreach ($data['environnementDeveloppementIds'] as $eid) {
                    $env = $this->em->getRepository(EnvironnementDeveloppement::class)->find($eid);
                    if ($env) {
                        $ref->addEnvironnementDeveloppement($env);
                    }
                }
            }
        }

        if (array_key_exists('technologieIds', $data)) {
            $ref->getTechnologies()->clear();
            if (is_array($data['technologieIds'])) {
                foreach ($data['technologieIds'] as $tid) {
                    $tech = $this->em->getRepository(Technologie::class)->find($tid);
                    if ($tech) {
                        $ref->addTechnologie($tech);
                    }
                }
            }
        }

        if (array_key_exists('methodologieIds', $data)) {
            $ref->getMethodologies()->clear();
            if (is_array($data['methodologieIds'])) {
                foreach ($data['methodologieIds'] as $mid) {
                    $meth = $this->em->getRepository(Methodologie::class)->find($mid);
                    if ($meth) {
                        $ref->addMethodologie($meth);
                    }
                }
            }
        }

        if (array_key_exists('roleIds', $data)) {
            $ref->getRolesReference()->clear();
            if (is_array($data['roleIds'])) {
                foreach ($data['roleIds'] as $rid) {
                    $role = $this->em->getRepository(Role::class)->find($rid);
                    if ($role) {
                        $ref->addRoleReference($role);
                    }
                }
            }
        }

        if (array_key_exists('appelOffresIds', $data)) {
            $ref->getAppelOffres()->clear();
            if (is_array($data['appelOffresIds'])) {
                foreach ($data['appelOffresIds'] as $aid) {
                    $ao = $this->em->getRepository(AppelOffres::class)->find($aid);
                    if ($ao) {
                        $ref->addAppelOffres($ao);
                    }
                }
            }
        }

        if (array_key_exists('referenceCaracteristiqueSpecialeIds', $data)) {
            $ref->getReferenceCaracteristiqueSpeciales()->clear();
            if (is_array($data['referenceCaracteristiqueSpecialeIds'])) {
                foreach ($data['referenceCaracteristiqueSpecialeIds'] as $cid) {
                    $cs = $this->em->getRepository(ReferenceCaracteristiqueSpeciale::class)->find($cid);
                    if ($cs) {
                        $ref->addReferenceCaracteristiqueSpeciale($cs);
                    }
                }
            }
        }

        if ($needRebuildRef && method_exists($ref, 'rebuildReferenceRefFromCategorieShort')) {
            $cat = $ref->getCategorie();
            if ($cat) {
                try {
                    $ref->rebuildReferenceRefFromCategorieShort($cat->getCategorieShort());
                } catch (\Throwable $e) {
                    $ref->rebuildReferenceRefFromCategorieShort();
                }
            }
        }

        $this->em->flush();

        return $this->json([
            'success' => true,
            'data'    => $this->serializeReference($ref),
        ]);
    }

    #[Route('/reorder', name: 'ref_reorder', methods: ['POST'])]
    public function reorder(Request $req): JsonResponse
    {
        $data = json_decode($req->getContent(), true) ?? [];

        $movedId  = (int)($data['movedId'] ?? 0);
        $targetId = (int)($data['targetId'] ?? 0);
        $position = strtolower((string)($data['position'] ?? ''));

        if ($movedId <= 0 || $targetId <= 0 || !in_array($position, ['before', 'after'], true)) {
            return $this->json(['success' => false, 'error' => 'Invalid payload'], Response::HTTP_BAD_REQUEST);
        }

        $conn = $this->em->getConnection();
        $conn->beginTransaction();

        try {
            $movedCatId = $conn->fetchOne(
                "SELECT categorieId FROM `reference` WHERE referenceID = :id",
                ['id' => $movedId]
            );
            $targetCatId = $conn->fetchOne(
                "SELECT categorieId FROM `reference` WHERE referenceID = :id",
                ['id' => $targetId]
            );

            if ($movedCatId === false || $targetCatId === false) {
                $conn->rollBack();
                return $this->json(['success' => false, 'error' => 'Reference not found (DB)'], 404);
            }

            $movedCatId  = (int)$movedCatId;
            $targetCatId = (int)$targetCatId;

            if ($movedCatId <= 0 || $targetCatId <= 0) {
                $conn->rollBack();
                return $this->json([
                    'success' => false,
                    'error'   => 'categorieId missing on moved/target reference (NULL or 0)',
                    'movedCatId' => $movedCatId,
                    'targetCatId' => $targetCatId,
                ], 400);
            }

            if ($movedCatId !== $targetCatId) {
                $conn->rollBack();
                return $this->json(['success' => false, 'error' => 'Moved and target are not in the same category'], 400);
            }

            $catId = $movedCatId;

            $rows = $conn->fetchAllAssociative(
                "SELECT referenceID
                FROM `reference`
                WHERE categorieId = :catId
                ORDER BY referenceOrdre ASC, referenceID ASC
                FOR UPDATE",
                ['catId' => $catId]
            );

            if (!$rows) {
                $conn->commit();
                return $this->json(['success' => true, 'message' => 'No references in category', 'catId' => $catId]);
            }

            $ids = array_map(fn($r) => (int)$r['referenceID'], $rows);

            $oldIndex    = array_search($movedId, $ids, true);
            $targetIndex = array_search($targetId, $ids, true);

            if ($oldIndex === false || $targetIndex === false) {
                $conn->rollBack();
                return $this->json(['success' => false, 'error' => 'Ids not found in category list'], 400);
            }

            if ($movedId === $targetId) {
                $conn->commit();
                return $this->json(['success' => true, 'message' => 'No change', 'catId' => $catId]);
            }

            array_splice($ids, $oldIndex, 1);
            $targetIndex = array_search($targetId, $ids, true);

            $insertIndex = ($position === 'before') ? $targetIndex : ($targetIndex + 1);
            array_splice($ids, $insertIndex, 0, [$movedId]);

            $OFFSET = 1000000;

            $caseParts = [];
            $params = ['catId' => $catId];

            foreach ($ids as $i => $id) {
                $caseParts[] = "WHEN referenceID = :id_$i THEN :ord_$i";
                $params["id_$i"]  = $id;
                $params["ord_$i"] = $OFFSET + ($i + 1);
            }

            $caseSql = implode("\n", $caseParts);

            $affectedA = $conn->executeStatement(
                "UPDATE `reference`
                SET referenceOrdre = CASE
                    $caseSql
                    ELSE referenceOrdre
                END
                WHERE categorieId = :catId",
                $params
            );

            $affectedB = $conn->executeStatement(
                "UPDATE `reference`
                SET referenceOrdre = referenceOrdre - :off
                WHERE categorieId = :catId",
                ['off' => $OFFSET, 'catId' => $catId]
            );

            $affectedTmp = $conn->executeStatement(
                "UPDATE `reference`
                SET referenceRef = CONCAT('__TMP__', referenceID)
                WHERE categorieId = :catId",
                ['catId' => $catId]
            );

            $affectedFinal = $conn->executeStatement(
                "UPDATE `reference` r
                JOIN categorie c ON c.categorieId = r.categorieId
                SET r.referenceRef = CONCAT('REF.', UPPER(TRIM(c.categorieShort)), ' ', LPAD(r.referenceOrdre, 3, '0'))
                WHERE r.categorieId = :catId",
                ['catId' => $catId]
            );

            $conn->commit();

            return $this->json([
                'success'  => true,
                'movedId'  => $movedId,
                'targetId' => $targetId,
                'position' => $position,
                'catId'    => $catId,
                'affected' => [
                    'phaseA' => $affectedA,
                    'phaseB' => $affectedB,
                    'tmpRef' => $affectedTmp,
                    'finalRef' => $affectedFinal,
                ],
            ]);

        } catch (\Throwable $e) {
            $conn->rollBack();
            return $this->json(['success' => false, 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    #[Route('/delete/{id}', name: 'ref_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $ref = $this->referenceRepository->find($id);
        if (!$ref) {
            return $this->json(['error' => 'Reference not found'], 404);
        }

        $this->em->remove($ref);
        $this->em->flush();

        return $this->json(['success' => true]);
    }
}
