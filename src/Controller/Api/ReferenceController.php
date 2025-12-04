<?php

namespace App\Controller\Api;

use App\Entity\Reference;
use App\Entity\Lieu;
use App\Entity\Devises;
use App\Entity\CategorieService;
use App\Entity\BailleurFond;
use App\Repository\ReferenceRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/reference')]
class ReferenceController extends AbstractController
{
    public function __construct(
        private ReferenceRepository $referenceRepository,
        private EntityManagerInterface $em,
    ) {}

    /* ============================================================
       Helper : Serialize one reference
       ============================================================ */
    private function serializeReference(Reference $ref): array
    {
        return [
            'referenceID' => $ref->getReferenceID(),
            'referenceRef' => $ref->getReferenceRef(),
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

            'lieuId' => $ref->getLieu()?->getLieuId(),
            'devisesId' => $ref->getDevises()?->getDevisesId(),
            'categorieServiceId' => $ref->getCategorie()?->getCategorieId(),

            // M2M
            'bailleursFond' => array_map(
                fn (BailleurFond $b) => [
                    'id' => $b->getBailleurFondId(),
                    'libelle' => $b->getBailleurFondLibelle(),
                    'acronyme' => $b->getBailleurFondAcronyme(),
                ],
                $ref->getBailleurfonds()->toArray()
            )
        ];
    }

    /* ============================================================
       GET ALL
       ============================================================ */
    #[Route('/all', name: 'ref_all', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $refs = $this->referenceRepository->findAll();
        return $this->json(array_map(fn($r) => $this->serializeReference($r), $refs));
    }

    /* ============================================================
       GET ONE
       ============================================================ */
    #[Route('/{id}', name: 'ref_one', methods: ['GET'])]
    public function getOne(int $id): JsonResponse
    {
        $ref = $this->referenceRepository->find($id);
        if (!$ref) {
            return $this->json(['error' => 'Reference not found'], 404);
        }
        return $this->json($this->serializeReference($ref));
    }

    /* ============================================================
       CREATE
       ============================================================ */
    #[Route('/create', name: 'ref_create', methods: ['POST'])]
    public function create(Request $req): JsonResponse
    {
        $data = json_decode($req->getContent(), true);
        $ref = new Reference();

        $ref->setReferenceRef($data['referenceRef'] ?? null);
        $ref->setReferenceTitre($data['referenceTitre'] ?? null);
        $ref->setReferenceLibelle($data['referenceLibelle'] ?? null);
        $ref->setReferenceUrlFonctionnel($data['referenceUrlFonctionnel'] ?? null);
        $ref->setReferenceDureeExecution($data['referenceDureeExecution'] ?? null);

        $ref->setReferenceDateDemarrage(isset($data['referenceDateDemarrage']) ? new \DateTime($data['referenceDateDemarrage']) : null);
        $ref->setReferenceDateAchevement(isset($data['referenceDateAchevement']) ? new \DateTime($data['referenceDateAchevement']) : null);
        $ref->setReferenceDateReceptionProvisoire(isset($data['referenceDateReceptionProvisoire']) ? new \DateTime($data['referenceDateReceptionProvisoire']) : null);
        $ref->setReferenceDateReceptionDefinitive(isset($data['referenceDateReceptionDefinitive']) ? new \DateTime($data['referenceDateReceptionDefinitive']) : null);

        $ref->setReferenceDureeGarantie($data['referenceDureeGarantie'] ?? null);
        $ref->setReferenceCaracteristiques($data['referenceCaracteristiques'] ?? null);
        $ref->setReferenceDescription($data['referenceDescription'] ?? null);
        $ref->setReferenceDescriptionServiceEffectivementRendus($data['referenceDescriptionServiceEffectivementRendus'] ?? null);
        $ref->setReferenceBudget($data['referenceBudget'] ?? null);
        $ref->setReferencePartBudget($data['referencePartBudget'] ?? null);
        $ref->setReferenceRemarque($data['referenceRemarque'] ?? null);

        // ------- Relations -------
        if (!empty($data['lieuId'])) {
            $lieu = $this->em->getRepository(Lieu::class)->find($data['lieuId']);
            if ($lieu) $ref->setLieu($lieu);
        }

        if (!empty($data['devisesId'])) {
            $devise = $this->em->getRepository(Devises::class)->find($data['devisesId']);
            if ($devise) $ref->setDevises($devise);
        }

        if (!empty($data['categorieServiceId'])) {
            $cat = $this->em->getRepository(CategorieService::class)->find($data['categorieServiceId']);
            if ($cat) $ref->setCategorieService($cat);
        }

        // ------- ManyToMany BailleurFond -------
        if (!empty($data['bailleurFondIds'])) {
            foreach ($data['bailleurFondIds'] as $bid) {
                $bf = $this->em->getRepository(BailleurFond::class)->find($bid);
                if ($bf) {
                    $ref->addBailleurDeFond($bf);
                }
            }
        }

        $this->em->persist($ref);
        $this->em->flush();

        return $this->json(['success' => true, 'id' => $ref->getReferenceID()]);
    }

    /* ============================================================
       UPDATE
       ============================================================ */
    #[Route('/update/{id}', name: 'ref_update', methods: ['PUT'])]
    public function update(int $id, Request $req): JsonResponse
    {
        $ref = $this->referenceRepository->find($id);
        if (!$ref) return $this->json(['error' => 'Reference not found'], 404);

        $data = json_decode($req->getContent(), true);

        // Same setters as create
        foreach ($data as $k => $v) {
            $setter = 'set' . ucfirst($k);
            if (method_exists($ref, $setter)) {
                $ref->$setter($v);
            }
        }

        // Dates
        if (isset($data['referenceDateDemarrage'])) {
            $ref->setReferenceDateDemarrage(new \DateTime($data['referenceDateDemarrage']));
        }

        if (isset($data['referenceDateAchevement'])) {
            $ref->setReferenceDateAchevement(new \DateTime($data['referenceDateAchevement']));
        }

        // M2M reset + update
        $ref->getBailleursDeFonds()->clear();
        if (!empty($data['bailleurFondIds'])) {
            foreach ($data['bailleurFondIds'] as $bid) {
                $bf = $this->em->getRepository(BailleurFond::class)->find($bid);
                if ($bf) $ref->addBailleurDeFond($bf);
            }
        }

        $this->em->flush();
        return $this->json(['success' => true]);
    }

    /* ============================================================
       DELETE
       ============================================================ */
    #[Route('/delete/{id}', name: 'ref_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $ref = $this->referenceRepository->find($id);
        if (!$ref) return $this->json(['error' => 'Reference not found'], 404);

        $this->em->remove($ref);
        $this->em->flush();

        return $this->json(['success' => true]);
    }
}
