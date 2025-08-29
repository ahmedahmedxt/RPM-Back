<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use App\Entity\Client;
use App\Entity\Devises;
use App\Entity\Categorie;
use App\Entity\Lieu;

use App\Entity\Reference;
use App\Repository\ReferenceRepository;

use App\Entity\Methodologie;
use App\Repository\MethodologieRepository;

use App\Entity\Technologie;
use App\Repository\TechnologieRepository;

use App\Entity\Role;
use App\Repository\RoleRepository;

use App\Entity\EnvironnementDeveloppement;
use App\Repository\EnvironnementDeveloppementRepository;

use App\Entity\BailleurFond;
use App\Repository\BailleurFondRepository;

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Element\Cell;
use setasign\Fpdi\Fpdi;

use setasign\Fpdi\PdfReader;






class ReferenceController extends AbstractController
{

    private $referenceRepository; 
     #[Route('/api/references/by-category/{id}', name: 'references_by_category', methods: ['GET'])]
    public function getReferencesByCategory(int $id, ReferenceRepository $referenceRepository): JsonResponse
    {
        $references = $referenceRepository->findBy(['categorie' => $id]);
        
        $data = [];
        foreach ($references as $ref) {
            $data[] = [
                'id' => $ref->getId(),
                'ref' => $ref->getRef(),
                'titre' => $ref->getTitre()
            ];
        }

        return new JsonResponse($data);
    }

    public function __construct(ReferenceRepository $referenceRepository)
    {
        $this->referenceRepository = $referenceRepository;
    }
    private function serializeReference(Reference $reference): array
    {
        $methodologies = [];
        foreach ($reference->getMethodologies() as $methodologie) {
            $methodologies[] = [
                'methodeologieLibelle' => $methodologie->getMethodologieLibelle(),
            ];
        }

        $technologies = [];
        foreach ($reference->getTechnologies() as $technologie) {
            $technologies[] = [
                'technologieLibelle' => $technologie->getReferenceTechnologieLibelle(),
            ];
        }

        $roles = [];
        foreach ($reference->getRoles() as $role) {
            $roles[] = [
                'roleLibelle' => $role->getRoleLibelle(),
            ];
        }

        $environnements = [];
        foreach ($reference->getEnvironnementdeveloppements() as $environnement) {
            $environnements[] = [
                'environnementDeveloppementLibelle' => $environnement->getEnvironnementDeveloppementLibelle(),
            ];
        }

        $bailleursFonds = [];
        foreach ($reference->getBailleurfonds() as $bailleurFond) {
            $bailleursFonds[] = [
                'bailleurFondLibelle' => $bailleurFond->getBailleurFondLibelle(),
                'bailleurFondId' => $bailleurFond->getBailleurFondId(),
            ];
        }
        return [
            'referenceID' => $reference->getReferenceID(),
            'clientId' => $reference->getClient() ? $reference->getClient()->getClientRaisonSocialShort() : null,
            'deviseId' => $reference->getDevises() ? $reference->getDevises()->getDevisesLibelle() : null,
            'lieuId' => $reference->getLieu() ? $reference->getLieu()->getLieuLibelle() : null,
            'categorie' => $reference->getCategorie() ? $reference->getCategorie()->getCategorieLibelle() : null,
            'categorieId' => $reference->getCategorie() ? $reference->getCategorie()->getId() : null,
            'referenceRef' => $reference->getReferenceRef(),
            'referenceTitre' => $reference->getReferenceTitre(),
            'referenceLibelle' => $reference->getReferenceLibelle(),
            'referenceUrlFonctionnel' => $reference->getReferenceUrlFonctionnel(),
            'referenceDuree' => $reference->getReferenceDuree(),
            'referenceDateDemarrage' => $reference->getReferenceDateDemarrage()->format('Y-m-d'),
            'referenceDateAchevement' => $reference->getReferenceDateAchevement()->format('Y-m-d'),
            'referenceAnneeAchevement' => $reference->getReferenceAnneeAchevement(),
            'referenceDateReceptionProvisoire' => $reference->getReferenceDateReceptionProvisoire()->format('Y-m-d'),
            'referenceDateReceptionDefinitive' => $reference->getReferenceDateReceptionDefinitive() ? $reference->getReferenceDateReceptionDefinitive()->format('Y-m-d') : null,
            'referenceCaracteristiques' => $reference->getReferenceCaracteristiques(),
            'referenceDescription' => $reference->getReferenceDescription(),
            'referenceDescriptionServiceEffectivemenetRendus' => $reference->getReferenceDescriptionServiceEffectivemenetRendus(),
            'referenceDureeGarantie' => $reference->getReferenceDureeGarantie(),
            'referenceBudget' => $reference->getReferenceBudget(),
            'referencePartBudgetGroupement' => $reference->getReferencePartBudgetGroupement(),
            'referenceRemarque' => $reference->getReferenceRemarque(),
            'methodologies' => $methodologies,
            'technologies' => $technologies,
            'paysId' => $reference->getLieu() ? $reference->getLieu()->getPays()->getId() : null,
            'roles' => $roles,
            'environnements' => $environnements,
            'bailleursFonds' => $bailleursFonds,
        ];
    }

    #[Route('/api/getAll/references', name: 'api_reference_get_all', methods: ['GET'])]
    public function getAll(ReferenceRepository $referenceRepository): JsonResponse
    {
        $references = $referenceRepository->findAll();
        $serializedReferences = [];
        foreach ($references as $reference) {
            $serializedReferences[] = $this->serializeReference($reference);
        }
        return new JsonResponse($serializedReferences, Response::HTTP_OK);
    }

    #[Route('/api/getOne/reference/{id}', name: 'api_reference_get_one', methods: ['GET'])]
    public function getReferenceOne($id, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //// $this->checkToken($tokenStorage);
        $reference = $entityManager->getRepository(Reference::class)->find($id);
        if (!$reference) {
            return new JsonResponse(['message' => 'Reference non trouvée'], Response::HTTP_NOT_FOUND);
        }
        $serializedReference = $this->serializeReferenceNom($reference);
        return new JsonResponse($serializedReference, Response::HTTP_OK);
    }

    private function serializeReferenceNom(Reference $reference): array
    {
        $methodologies = [];
        foreach ($reference->getMethodologies() as $methodologie) {
            $methodologies[] = [
                'methodologieId' => $methodologie->getMethodologieId(),
            ];
        }

        $technologies = [];
        foreach ($reference->getTechnologies() as $technologie) {
            $technologies[] = [
                'technologieId' => $technologie->getTechnologieId(),
            ];
        }

        $roles = [];
        foreach ($reference->getRoles() as $role) {
            $roles[] = [
                'roleId' => $role->getRoleId(),
            ];
        }

        $environnements = [];
        foreach ($reference->getEnvironnementdeveloppements() as $environnement) {
            $environnements[] = [
                'environnementDeveloppementId' => $environnement->getEnvironnementDeveloppementId(),
            ];
        }

        $bailleursFonds = [];
        foreach ($reference->getBailleurfonds() as $bailleurFond) {
            $bailleursFonds[] = [
                'bailleurFondId' => $bailleurFond->getBailleurFondId(),
            ];
        }
        return [
            'referenceID' => $reference->getReferenceID(),
            'clientId' => $reference->getClient() ? $reference->getClient()->getClientId() : null,
            'devisesId' => $reference->getDevises() ? $reference->getDevises()->getDevisesId() : null,
            'lieuId' => $reference->getLieu() ? $reference->getLieu()->getLieuId() : null,
            'categorieId' => $reference->getCategorie() ? $reference->getCategorie()->getId() : null,
            'referenceRef' => $reference->getReferenceRef(),
            'referenceTitre' => $reference->getReferenceTitre(),
            'referenceLibelle' => $reference->getReferenceLibelle(),
            'referenceUrlFonctionnel' => $reference->getReferenceUrlFonctionnel(),
            'referenceDuree' => $reference->getReferenceDuree(),
            'referenceDateDemarrage' => $reference->getReferenceDateDemarrage()->format('Y-m-d'),
            'referenceDateAchevement' => $reference->getReferenceDateAchevement()->format('Y-m-d'),
            'referenceAnneeAchevement' => $reference->getReferenceAnneeAchevement(),
            'referenceDateReceptionProvisoire' => $reference->getReferenceDateReceptionProvisoire()->format('Y-m-d'),
            'referenceDateReceptionDefinitive' => $reference->getReferenceDateReceptionDefinitive()->format('Y-m-d'),
            'referenceCaracteristiques' => $reference->getReferenceCaracteristiques(),
            'referenceDescription' => $reference->getReferenceDescription(),
            'referenceDescriptionServiceEffectivemenetRendus' => $reference->getReferenceDescriptionServiceEffectivemenetRendus(),
            'referenceDureeGarantie' => $reference->getReferenceDureeGarantie(),
            'referenceBudget' => $reference->getReferenceBudget(),
            'referencePartBudgetGroupement' => $reference->getReferencePartBudgetGroupement(),
            'referenceRemarque' => $reference->getReferenceRemarque(),
            'paysId' => $reference->getLieu() ? $reference->getLieu()->getPays()->getId() : null,
            'methodologies' => $methodologies,
            'technologies' => $technologies,
            'roles' => $roles,
            'environnements' => $environnements,
            'bailleursFonds' => $bailleursFonds,
        ];
    }

    #[Route('/api/lieux/byPays/{paysId}', name: 'api_lieux_get_all_by_pays', methods: ['GET'])]
    public function getAllByPays(EntityManagerInterface $entityManager, int $paysId): JsonResponse
    {
        // Récupérer les lieux triés par nom pour un pays donné
        $lieuxRepository = $entityManager->getRepository(Lieu::class);
        $lieux = $lieuxRepository->findBy(['pays' => $paysId], ['lieuLibelle' => 'ASC']);

        $lieuxData = [];
        foreach ($lieux as $lieu) {
            $pays = $lieu->getPays();
            $paysNom = ($pays) ? $pays->getPaysLibelle() : 'Pays non spécifié';

            $lieuxData[] = [
                'lieuId' => $lieu->getLieuId(),
                'lieuLibelle' => $lieu->getLieuLibelle(),
                'paysLibelle' => $paysNom,
            ];
        }

        return new JsonResponse($lieuxData, Response::HTTP_OK);
    }

    #[Route('/api/get/reference/{id}', name: 'api_reference_get', methods: ['GET'])]
    public function getReference($id, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //// $this->checkToken($tokenStorage);
        $reference = $entityManager->getRepository(Reference::class)->find($id);
        if (!$reference) {
            return new JsonResponse(['message' => 'Reference non trouvée'], Response::HTTP_NOT_FOUND);
        }
        $serializedReference = $this->serializeReference($reference);
        return new JsonResponse($serializedReference, Response::HTTP_OK);
    }

    #[Route('/api/delete/reference/{id}', name: 'api_reference_delete', methods: ['DELETE'])]
    public function delete($id, EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): JsonResponse
    {
        //// $this->checkToken($tokenStorage);
        $reference = $entityManager->getRepository(Reference::class)->find($id);
        if (!$reference) {
            return new JsonResponse(['message' => 'Reference non trouvée'], Response::HTTP_NOT_FOUND);
        }
        $entityManager->remove($reference);
        $entityManager->flush();
        return new JsonResponse(['message' => 'Reference supprimée avec succès'], Response::HTTP_OK);
    }

    #[Route('/api/create/reference', name: 'api_reference_create', methods: ['POST'])]
    public function createReference(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Get JSON data from the request
        $data = json_decode($request->getContent(), true);
        //dd($data);

        $reference = new Reference();
        $reference->setReferenceRef($data['referenceRef'] ?? null);
        $reference->setReferenceTitre($data['referenceTitre'] ?? null);
        $reference->setReferenceLibelle($data['referenceLibelle'] ?? null);
        $reference->setReferenceUrlFonctionnel($data['referenceUrlFonctionnel'] ?? null);
        $reference->setReferenceDuree($data['referenceDuree'] ?? null);
        $reference->setReferenceDateDemarrage(isset($data['referenceDateDemarrage']) ? new \DateTime($data['referenceDateDemarrage']) : null);
        $reference->setReferenceDateAchevement(isset($data['referenceDateAchevement']) ? new \DateTime($data['referenceDateAchevement']) : null);
        if ($reference->getReferenceDateAchevement()) {
            $reference->setReferenceAnneeAchevement($reference->getReferenceDateAchevement()->format('Y'));
        } else {
            $reference->setReferenceAnneeAchevement(null);
        }
        $reference->setReferenceDateReceptionProvisoire(isset($data['referenceDateReceptionProvisoire']) ? new \DateTime($data['referenceDateReceptionProvisoire']) : null);
        $reference->setReferenceDateReceptionDefinitive(isset($data['referenceDateReceptionDefinitive']) ? new \DateTime($data['referenceDateReceptionDefinitive']) : null);
        $reference->setReferenceCaracteristiques($data['referenceCaracteristiques'] ?? null);
        $reference->setReferenceDescription($data['referenceDescription'] ?? null);
        $reference->setReferenceDescriptionServiceEffectivemenetRendus($data['referenceDescriptionServiceEffectivemenetRendus'] ?? null);
        $reference->setReferenceDureeGarantie($data['referenceDureeGarantie'] ?? null);
        $reference->setReferenceBudget($data['referenceBudget'] ?? null);
        $reference->setReferencePartBudgetGroupement($data['referencePartBudgetGroupement'] ?? null);
        $reference->setReferenceRemarque($data['referenceRemarque'] ?? null);

        // Associate Lieu
        if (isset($data['lieuId'])) {
            $lieu = $entityManager->getRepository(Lieu::class)->find($data['lieuId']);
            if (!$lieu) {
                return new JsonResponse(['message' => 'Lieu not found'], Response::HTTP_NOT_FOUND);
            }
            $reference->setLieu($lieu);
        }

        // Associate Client
        if (isset($data['clientId'])) {
            $client = $entityManager->getRepository(Client::class)->find($data['clientId']);
            if (!$client) {
                return new JsonResponse(['message' => 'Client not found'], Response::HTTP_NOT_FOUND);
            }
            $reference->setClient($client);
        }

        // Associate Categorie
        if (isset($data['categorieId'])) {
            $categorie = $entityManager->getRepository(Categorie::class)->find($data['categorieId']);
            if (!$categorie) {
                return new JsonResponse(['message' => 'Categorie not found'], Response::HTTP_NOT_FOUND);
            }
            $reference->setCategorie($categorie);
        }

        // Associate Devises
        if (isset($data['devisesId'])) {
            $devises = $entityManager->getRepository(Devises::class)->find($data['devisesId']);
            if (!$devises) {
                return new JsonResponse(['message' => 'Devises not found'], Response::HTTP_NOT_FOUND);
            }
            $reference->setDevises($devises);
        }

        // Associate many-to-many relationships
        $this->addManyToManyRelationships($data, $reference, $entityManager);

        $entityManager->persist($reference);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Reference created successfully'], Response::HTTP_CREATED);
    }

    private function addManyToManyRelationships(array $data, Reference $reference, EntityManagerInterface $entityManager): void
    {
        // Handle BailleurFond
        if (isset($data['bailleurFondIds'])) {
            foreach ($data['bailleurFondIds'] as $bailleurFondId) {
                $bailleurFond = $entityManager->getRepository(BailleurFond::class)->find($bailleurFondId);
                if ($bailleurFond) {
                    $reference->addBailleurfond($bailleurFond);
                }
            }
        }

        // Handle Role
        if (isset($data['roleIds'])) {
            foreach ($data['roleIds'] as $roleId) {
                $role = $entityManager->getRepository(Role::class)->find($roleId);
                if ($role) {
                    $reference->addRole($role);
                }
            }
        }

        // Handle Technologie
        if (isset($data['technologieIds'])) {
            foreach ($data['technologieIds'] as $technologieId) {
                $technologie = $entityManager->getRepository(Technologie::class)->find($technologieId);
                if ($technologie) {
                    $reference->addTechnologie($technologie);
                }
            }
        }

        // Handle Methodologie
        if (isset($data['methodologieIds'])) {
            foreach ($data['methodologieIds'] as $methodologieId) {
                $methodologie = $entityManager->getRepository(Methodologie::class)->find($methodologieId);
                if ($methodologie) {
                    $reference->addMethodologie($methodologie);
                }
            }
        }

        // Handle EnvironnementDeveloppement
        if (isset($data['environnementIds'])) {
            foreach ($data['environnementIds'] as $environnementDeveloppementId) {
                $environnementDeveloppement = $entityManager->getRepository(EnvironnementDeveloppement::class)->find($environnementDeveloppementId);
                if ($environnementDeveloppement) {
                    $reference->addEnvironnementdeveloppement($environnementDeveloppement);
                }
            }
        }
    }

    #[Route('/api/edit/reference/{id}', name: 'api_reference_edit', methods: ['PUT'])]
    public function updateReference(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Find the existing reference
        $reference = $entityManager->getRepository(Reference::class)->find($id);
        if (!$reference) {
            return new JsonResponse(['message' => 'Reference non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Update basic fields
        $reference->setReferenceRef($data['referenceRef'] ?? $reference->getReferenceRef());
        $reference->setReferenceTitre($data['referenceTitre'] ?? $reference->getReferenceTitre());
        $reference->setReferenceLibelle($data['referenceLibelle'] ?? $reference->getReferenceLibelle());
        $reference->setReferenceUrlFonctionnel($data['referenceUrlFonctionnel'] ?? $reference->getReferenceUrlFonctionnel());
        $reference->setReferenceDuree($data['referenceDuree'] ?? $reference->getReferenceDuree());
        $reference->setReferenceDateDemarrage(isset($data['referenceDateDemarrage']) ? new \DateTime($data['referenceDateDemarrage']) : $reference->getReferenceDateDemarrage());
        $reference->setReferenceDateAchevement(isset($data['referenceDateAchevement']) ? new \DateTime($data['referenceDateAchevement']) : $reference->getReferenceDateAchevement());
        if ($reference->getReferenceDateAchevement()) {
            $reference->setReferenceAnneeAchevement($reference->getReferenceDateAchevement()->format('Y'));
        } else {
            $reference->setReferenceAnneeAchevement(null);
        }
        $reference->setReferenceDateReceptionProvisoire(isset($data['referenceDateReceptionProvisoire']) ? new \DateTime($data['referenceDateReceptionProvisoire']) : $reference->getReferenceDateReceptionProvisoire());
        $reference->setReferenceDateReceptionDefinitive(isset($data['referenceDateReceptionDefinitive']) ? new \DateTime($data['referenceDateReceptionDefinitive']) : $reference->getReferenceDateReceptionDefinitive());
        $reference->setReferenceCaracteristiques($data['referenceCaracteristiques'] ?? $reference->getReferenceCaracteristiques());
        $reference->setReferenceDescription($data['referenceDescription'] ?? $reference->getReferenceDescription());
        $reference->setReferenceDescriptionServiceEffectivemenetRendus($data['referenceDescriptionServiceEffectivemenetRendus'] ?? $reference->getReferenceDescriptionServiceEffectivemenetRendus());
        $reference->setReferenceDureeGarantie($data['referenceDureeGarantie'] ?? $reference->getReferenceDureeGarantie());
        $reference->setReferenceBudget($data['referenceBudget'] ?? $reference->getReferenceBudget());
        $reference->setReferencePartBudgetGroupement($data['referencePartBudgetGroupement'] ?? $reference->getReferencePartBudgetGroupement());
        $reference->setReferenceRemarque($data['referenceRemarque'] ?? $reference->getReferenceRemarque());

        // Update associations (Lieu, Client, Categorie, Devises)
        $this->updateAssociations($data, $reference, $entityManager);

        // Clear existing many-to-many relationships before adding new ones
        $reference->getBailleurfonds()->clear();
        $reference->getRoles()->clear();
        $reference->getTechnologies()->clear();
        $reference->getMethodologies()->clear();
        $reference->getEnvironnementdeveloppements()->clear();

        // Add many-to-many relationships
        $this->addManyToManyRelationships($data, $reference, $entityManager);

        $entityManager->flush();

        return new JsonResponse(['message' => 'Référence mise à jour avec succès'], Response::HTTP_OK);
    }

    private function updateAssociations(array $data, Reference $reference, EntityManagerInterface $entityManager): void
    {
        if (isset($data['lieuId'])) {
            $lieu = $entityManager->getRepository(Lieu::class)->find($data['lieuId']);
            if (!$lieu) {
                throw new \Exception('Lieu non trouvé');
            }
            $reference->setLieu($lieu);
        }

        if (isset($data['clientId'])) {
            $client = $entityManager->getRepository(Client::class)->find($data['clientId']);
            if (!$client) {
                throw new \Exception('Client non trouvé');
            }
            $reference->setClient($client);
        }

        if (isset($data['categorieId'])) {
            $categorie = $entityManager->getRepository(Categorie::class)->find($data['categorieId']);
            if (!$categorie) {
                throw new \Exception('Catégorie non trouvée');
            }
            $reference->setCategorie($categorie);
        }

        if (isset($data['devisesId'])) {
            $devises = $entityManager->getRepository(Devises::class)->find($data['devisesId']);
            if (!$devises) {
                throw new \Exception('Devises non trouvées');
            }
            $reference->setDevises($devises);
        }
    }


    //associates
    private function serializeMethodologie(Methodologie $methodologie): array
    {
        return [
            'methodologieId' => $methodologie->getMethodologieId(),
            'methodologieLibelle' => $methodologie->getMethodologieLibelle(),
            'methodologieDescription' => $methodologie->getMethodologieDescription()
        ];
    }

    private function serializeTechnologie(Technologie $technologie): array
    {
        return [
            'technologieId' => $technologie->getTechnologieId(),
            'referenceTechnologieLibelle' => $technologie->getReferenceTechnologieLibelle(),
            'referenceTechnologieDescription' => $technologie->getReferenceTechnologieDescription(),
        ];
    }

    private function serializeRole(Role $role): array
    {
        return [
            'roleId' => $role->getRoleId(),
            'roleLibelle' => $role->getRoleLibelle(),
            'roleShort' => $role->getRoleShort(),
        ];
    }

    private function serializeEnvironnementDeveloppement(EnvironnementDeveloppement $environnementDeveloppement): array
    {
        return [
            'environnementDeveloppementId' => $environnementDeveloppement->getEnvironnementDeveloppementId(),
            'environnementDeveloppementLibelle' => $environnementDeveloppement->getEnvironnementDeveloppementLibelle(),
            'environnementDeveloppementDescription' => $environnementDeveloppement->getEnvironnementDeveloppementDescription()
        ];
    }

    private function serializeBailleurFond(BailleurFond $bailleurFond): array
    {
        return [
            'bailleurFondId' => $bailleurFond->getBailleurFondId(),
            'bailleurFondLibelle' => $bailleurFond->getBailleurFondLibelle(),
            'bailleurFondAcronyme' => $bailleurFond->getBailleurFondAcronyme(),
        ];
    }

    #[Route('/api/getAll/methodologies', name: 'app_methodologies_get_all')]
    public function getAllMethodologies(MethodologieRepository $methodologieRepository): JsonResponse
    {
        $methodologies = $methodologieRepository->findBy([], ['methodologieLibelle' => 'ASC']);
        $serializedMethodologies = [];

        foreach ($methodologies as $methodologie) {
            $serializedMethodologies[] = $this->serializeMethodologie($methodologie);
        }

        return new JsonResponse($serializedMethodologies, Response::HTTP_OK);
    }

    #[Route('/api/getAll/technologies', name: 'app_technologies_get_all')]
    public function getAllTechnologies(TechnologieRepository $technologieRepository): JsonResponse
    {
        $technologies = $technologieRepository->findBy([], ['referenceTechnologieLibelle' => 'ASC']);
        $serializedTechnologies = [];

        foreach ($technologies as $technologie) {
            $serializedTechnologies[] = $this->serializeTechnologie($technologie);
        }

        return new JsonResponse($serializedTechnologies, Response::HTTP_OK);
    }

    #[Route('/api/getAll/roles', name: 'app_roles_get_all')]
    public function getAllRoles(RoleRepository $roleRepository): JsonResponse
    {
        $roles = $roleRepository->findBy([], ['roleLibelle' => 'ASC']);
        $serializedRoles = [];

        foreach ($roles as $role) {
            $serializedRoles[] = $this->serializeRole($role);
        }

        return new JsonResponse($serializedRoles, Response::HTTP_OK);
    }

    #[Route('/api/getAll/environnementDeveloppements', name: 'app_environnement_developpement_get_all')]
    public function getAllEnvironnementDeveloppements(EnvironnementDeveloppementRepository $environnementDeveloppementRepository): JsonResponse
    {
        $environnementDeveloppements = $environnementDeveloppementRepository->findBy([], ['environnementDeveloppementLibelle' => 'ASC']);
        $serializedEnvironnementDeveloppements = [];

        foreach ($environnementDeveloppements as $environnementDeveloppement) {
            $serializedEnvironnementDeveloppements[] = $this->serializeEnvironnementDeveloppement($environnementDeveloppement);
        }

        return new JsonResponse($serializedEnvironnementDeveloppements, Response::HTTP_OK);
    }

    #[Route('/api/getAll/bailleurFonds', name: 'app_bailleur_fond_get_all')]
    public function getAllBailleurFonds(BailleurFondRepository $bailleurFondRepository): JsonResponse
    {
        $bailleurFonds = $bailleurFondRepository->findBy([], ['bailleurFondLibelle' => 'ASC']);
        $serializedBailleurFonds = [];

        foreach ($bailleurFonds as $bailleurFond) {
            $serializedBailleurFonds[] = $this->serializeBailleurFond($bailleurFond);
        }

        return new JsonResponse($serializedBailleurFonds, Response::HTTP_OK);
    }

    #[Route('/api/rapportpdf/all', name: 'app_rapportPDF_all')]
    public function generateAllRapportPdf(Request $request): Response
    {


        $data = json_decode($request->getContent(), true);
        $referenceIds = $data['referenceIds'] ?? [];

        if (empty($referenceIds)) {
            throw $this->createNotFoundException('Aucune référence trouvée.');
        }

        $references = $this->referenceRepository->findBy(['referenceID' => $referenceIds]);

        // $references = $this->referenceRepository->findAll();
        if (!$references) {
            throw $this->createNotFoundException('Aucune référence trouvée.');
        }

        // Initialize Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true); // Enable PHP inside HTML if needed
        $dompdf = new Dompdf($options);

        $pdfContent = '';

        foreach ($references as $reference) {
            // Generate HTML content for each reference
            $pdfContent .= $this->generateRapportPdfContent($reference);
            // $pdfContent .= '<div style="page-break-before: always;"></div>'; // Add page break between references
        }

        // Load HTML content into Dompdf
        $dompdf->loadHtml($pdfContent);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Output the combined PDF
        $output = $dompdf->output();

        return new Response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="all_references.pdf"',
        ]);
    }


    private function generateRapportPdfContent($reference): string
    {


        $client = $reference->getClient();
        $lieu = $reference->getLieu();
        $categoriecouleur = $reference->getCategorie()->getCategorieCodeCouleur();
        $referenceRef = $reference->getReferenceRef();
        $referenceTitre = $reference->getReferenceTitre();
        $referenceLibelle = $reference->getReferenceLibelle();
        $referenceUrlFonctionnel = $reference->getReferenceUrlFonctionnel();
        $referenceDuree = $reference->getReferenceDuree();
        $referenceDateDemarrage = $reference->getReferenceDateDemarrage();
        $referenceDateAchevement = $reference->getReferenceDateAchevement();
        $referenceDateReceptionProvisoire = $reference->getReferenceDateReceptionProvisoire();
        $referenceDateReceptionDefinitive = $reference->getReferenceDateReceptionDefinitive();
        $referenceCaracteristiques = $reference->getReferenceCaracteristiques();
        $referenceDescriptionServiceEffectivemenetRendus = $reference->getReferenceDescriptionServiceEffectivemenetRendus();

        // Format Technologies
        $technologies = [];
        foreach ($reference->getTechnologies() as $technologie) {
            $technologies[] = $technologie->getReferenceTechnologieLibelle();
        }

        // Format Methodologies
        $methodologies = [];
        foreach ($reference->getMethodologies() as $methodologie) {
            $methodologies[] = $methodologie->getMethodologieLibelle();
        }

        // Format Environnement Developpements
        $environnementdeveloppements = [];
        foreach ($reference->getEnvironnementdeveloppements() as $environnement) {
            $environnementdeveloppements[] = $environnement->getEnvironnementDeveloppementLibelle();
        }

        $html = '
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; margin: 10px; box-sizing: border-box; }
        .header { color: white; text-align: center; padding: 15px; border-bottom: 3px solid; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 10px; border: 1px solid; background-color: #f9f9f9; }
        .bold { font-weight: bold; text-align: left; }
        .left-align { text-align: left; }
        .full-width { width: 100%; }
        .half-width { width: 50%; }
        .three-quarters { width: 70%; }
        .one-quarter { width: 30%; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <div class="header" style="background-color: ' . htmlspecialchars($categoriecouleur) . '; border-bottom: 3px solid ' . htmlspecialchars($categoriecouleur) . ';">
        <h2>REF ' . htmlspecialchars($referenceRef) . ' : ' . htmlspecialchars($referenceTitre) . '</h2>
    </div>
    <table>
        <tr>
            <td class="three-quarters" colspan="2" style="border: 1px solid ' . $categoriecouleur . ';">
                <strong>Nom du projet :</strong>' . htmlspecialchars($referenceLibelle) . '
            </td>
          
            <td class="one-quarter" style="border: 1px solid ' . $categoriecouleur . ';">
                <div class="bold">URL fonctionnel:</div>
                <div class="left-align">' . htmlspecialchars($referenceUrlFonctionnel) . '</div>
            </td>
        </tr>
        <tr>
            <td class="half-width" colspan="2" style="border: 1px solid ' . $categoriecouleur . ';">
                 <strong>Pays : </strong>' . htmlspecialchars($lieu->getPays()->getPaysLibelle()) . '
                 <br> 
                 <strong>Lieu : </strong>' . htmlspecialchars($lieu->getLieuLibelle()) . '
            </td>
            
            <td class="one-quarter" style="border: 1px solid ' . $categoriecouleur . ';">
                <div class="bold">Durée du projet (mois):</div>
                <div class="left-align">' . htmlspecialchars($referenceDuree) . ' mois</div>
            </td>
        </tr>
        <tr>
            <td class="full-width" colspan="3" style="border: 1px solid ' . $categoriecouleur . ';">
            <strong>Nature du client </strong>(Privée, publique, association ou autres) :
                ' . htmlspecialchars($client->getNatureClient()->getNatureClient()) . '
            </td>
        </tr>
        <tr>
            <td class="half-width"  style="border: 1px solid ' . $categoriecouleur . ';">
                <div class="bold">Nom et adresse du Client:</div>
                <div class="left-align">
                    ' . htmlspecialchars($client->getClientRaisonSocial()) . '
                    <br>
                    ' . htmlspecialchars($client->getClientAdresse()) . '
                     <br>
                    ' . htmlspecialchars($client->getClientPersonneContact1()) . '
                    <br>
                    ' . htmlspecialchars($client->getClientEmail()) . '
                    <br> TEL:
                    ' . htmlspecialchars($client->getClientTelephone1()) . '
                </div>
            </td>
            
            <td  colspan="2" style="border: 1px solid ' . $categoriecouleur . ';">
                <div class="bold">Équipe:</div>
                <div class="left-align"></div>
            </td>
            
        </tr>
        <tr>
            <td class="half-width"  style="border: 1px solid ' . $categoriecouleur . ';">
            <strong>Date de démarrage</strong> (mois/année): ' . htmlspecialchars($referenceDateDemarrage->format('m/Y')) . '
                <br>
            <strong>Date d\'achèvement</strong> (mois/année): ' . htmlspecialchars($referenceDateAchevement->format('m/Y')) . '
            </td>
            
            <td colspan="2" style="border: 1px solid ' . $categoriecouleur . ';">
                <strong>Date de reception provisoire</strong> (mois/année): ' . htmlspecialchars($referenceDateReceptionProvisoire->format('m/Y')) . '
                <br>
                <strong>Date de reception definitive</strong> (mois/année):' . htmlspecialchars($referenceDateReceptionDefinitive->format('m/Y')) . '
            </td>
            
        </tr>
        <tr>
            <td class="full-width" colspan="3" style="border: 1px solid ' . $categoriecouleur . ';">
                <strong>Caractéristiques du projet :</strong><br>' . htmlspecialchars($referenceCaracteristiques) . '
                <br><br>
                <strong>Spécifique techniques: </strong>' .  htmlspecialchars(implode("; ", $environnementdeveloppements)) . '
                <br>
                <strong>Technologies: </strong>' . htmlspecialchars(implode("; ", $technologies)) . '
                <br>
                <strong>Méthodologie: </strong>' . htmlspecialchars(implode("; ", $methodologies)) . '
            </td>
        </tr>
        <tr>
            <td class="full-width" colspan="3" style="border: 1px solid ' . $categoriecouleur . ';">
                <div class="bold">Description des services effectivement rendus:</div>
                <div class="left-align">' . $referenceDescriptionServiceEffectivemenetRendus . '</div>
            </td>
        </tr>
    </table>
</body>
</html>';

        return $html;
    }




    #[Route('/api/rapportpdf/{id}', name: 'app_rapportPDF_id', requirements: ['id' => '\d+'])]
    public function generateRapportPdf(Request $request, int $id): Response
    {
        $ref = $this->referenceRepository->find($id);

        if (!$ref) {
            throw $this->createNotFoundException('La référence avec l\'ID ' . $id . ' n\'existe pas.');
        }

        $client = $ref->getClient();
        $lieu = $ref->getLieu();
        $categoriecouleur = $ref->getCategorie()->getCategorieCodeCouleur();
        $referenceRef = $ref->getReferenceRef();
        $referenceTitre = $ref->getReferenceTitre();
        $referenceLibelle = $ref->getReferenceLibelle();
        $referenceUrlFonctionnel = $ref->getReferenceUrlFonctionnel();
        $referenceDuree = $ref->getReferenceDuree();
        $referenceDateDemarrage = $ref->getReferenceDateDemarrage();
        $referenceDateAchevement = $ref->getReferenceDateAchevement();
        $referenceDateReceptionProvisoire = $ref->getReferenceDateReceptionProvisoire();
        $referenceDateReceptionDefinitive = $ref->getReferenceDateReceptionDefinitive();
        $referenceCaracteristiques = $ref->getReferenceCaracteristiques();
        $referenceDescriptionServiceEffectivemenetRendus = $ref->getReferenceDescriptionServiceEffectivemenetRendus();

        // Format Technologies
        $technologies = [];
        foreach ($ref->getTechnologies() as $technologie) {
            $technologies[] = $technologie->getReferenceTechnologieLibelle();
        }

        // Format Methodologies
        $methodologies = [];
        foreach ($ref->getMethodologies() as $methodologie) {
            $methodologies[] = $methodologie->getMethodologieLibelle();
        }

        // Format Environnement Developpements
        $environnementdeveloppements = [];
        foreach ($ref->getEnvironnementdeveloppements() as $environnement) {
            $environnementdeveloppements[] = $environnement->getEnvironnementDeveloppementLibelle();
        }

        $html = '
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; margin: 10px; box-sizing: border-box; }
        .header { background-color: ' . $categoriecouleur . '; color: white; text-align: center; padding: 15px; border-bottom: 3px solid ' . $categoriecouleur . '; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 10px; border: 1px solid ' . $categoriecouleur . '; background-color: #f9f9f9; }
        .bold { font-weight: bold; text-align: left; }
        .left-align { text-align: left; }
        .full-width { width: 100%; }
        .half-width { width: 50%; }
        .three-quarters { width: 70%; }
        .one-quarter { width: 30%; }
        
    </style>
</head>
<body>
    <div class="header">
        <h2>REF ' . htmlspecialchars($referenceRef) . ' : ' . htmlspecialchars($referenceTitre) . '</h2>
    </div>
    <table>
        <tr>
            <td class="three-quarters" colspan="2">
                <strong>Nom du projet :</strong>' . htmlspecialchars($referenceLibelle) . '
            </td>
          
            <td class="one-quarter">
                <div class="bold">URL fonctionnel:</div>
                <div class="left-align">' . htmlspecialchars($referenceUrlFonctionnel) . '</div>
            </td>
        </tr>
        <tr>
            <td class="half-width" colspan="2">
                 <strong>Pays : </strong>' . htmlspecialchars($lieu->getPays()->getPaysLibelle()) . '
                 <br> 
                 <strong>Lieu : </strong>' . htmlspecialchars($lieu->getLieuLibelle()) . '
            </td>
            
            <td class="one-quarter">
                <div class="bold">Durée du projet (mois):</div>
                <div class="left-align">' . htmlspecialchars($referenceDuree) . ' mois</div>
            </td>
        </tr>
        <tr>
            <td class="full-width" colspan="3">
            <strong>Nature du client </strong>(Privée, publique, association ou autres) :
                ' . htmlspecialchars($client->getNatureClient()->getNatureClient()) . '
            </td>
        </tr>
        <tr>
            <td class="half-width"  >
                <div class="bold">Nom et adresse du Client:</div>
                <div class="left-align">
                    ' . htmlspecialchars($client->getClientRaisonSocial()) . '
                    <br>
                    ' . htmlspecialchars($client->getClientAdresse()) . '
                     <br>
                    ' . htmlspecialchars($client->getClientPersonneContact1()) . '
                    <br>
                    ' . htmlspecialchars($client->getClientEmail()) . '
                    <br> TEL:
                    ' . htmlspecialchars($client->getClientTelephone1()) . '
                </div>
            </td>
            
            <td  colspan="2">
                <div class="bold">Équipe:</div>
                <div class="left-align"></div>
            </td>
            
        </tr>
        <tr>
            <td class="half-width" >
            <strong>Date de démarrage</strong> (mois/année): ' . htmlspecialchars($referenceDateDemarrage->format('m/Y')) . '
                <br>
            <strong>Date d\'achèvement</strong> (mois/année): ' . htmlspecialchars($referenceDateAchevement->format('m/Y')) . '
            </td>
            
            <td colspan="2" >
                <strong>Date de reception provisoire</strong> (mois/année): ' . htmlspecialchars($referenceDateReceptionProvisoire->format('m/Y')) . '
                <br>
                <strong>Date de reception definitive</strong> (mois/année):' . htmlspecialchars($referenceDateReceptionDefinitive->format('m/Y')) . '
            </td>
            
        </tr>
        <tr>
            <td class="full-width" colspan="3">
                <strong>Caractéristiques du projet :</strong><br>' . htmlspecialchars($referenceCaracteristiques) . '
                <br><br>
                <strong>Spécifique techniques: </strong>' .  htmlspecialchars(implode("; ", $environnementdeveloppements)) . '
                <br>
                <strong>Technologies: </strong>' . htmlspecialchars(implode("; ", $technologies)) . '
                <br>
                <strong>Méthodologie: </strong>' . htmlspecialchars(implode("; ", $methodologies)) . '
            </td>
        </tr>
        <tr>
            <td class="full-width" colspan="3">
                <div class="bold">Description des services effectivement rendus:</div>
                <div class="left-align">' . $referenceDescriptionServiceEffectivemenetRendus . '</div>
            </td>
        </tr>
    </table>
</body>
</html>';

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Récupérer le contenu du PDF et le retourner en réponse HTTP
        $output = $dompdf->output();

        return new Response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="projet_' . $id . '.pdf"'
        ]);
    }



    #[Route('/api/rapportpdf/{id}', name: 'app_rapportPDF_id', requirements: ['id' => '\d+'])]
    public function generatRapportPdf(Request $request, int $id): Response
    {
        $ref = $this->referenceRepository->find($id);

        if (!$ref) {
            throw $this->createNotFoundException('La référence avec l\'ID ' . $id . ' n\'existe pas.');
        }

        $client = $ref->getClient();
        $lieu = $ref->getLieu();
        $categoriecouleur = $ref->getCategorie()->getCategorieCodeCouleur();
        $referenceRef = $ref->getReferenceRef();
        $referenceTitre = $ref->getReferenceTitre();
        $referenceLibelle = $ref->getReferenceLibelle();
        $referenceUrlFonctionnel = $ref->getReferenceUrlFonctionnel();
        $referenceDuree = $ref->getReferenceDuree();
        $referenceDateDemarrage = $ref->getReferenceDateDemarrage();
        $referenceDateAchevement = $ref->getReferenceDateAchevement();
        $referenceDateReceptionProvisoire = $ref->getReferenceDateReceptionProvisoire();
        $referenceDateReceptionDefinitive = $ref->getReferenceDateReceptionDefinitive();
        $referenceCaracteristiques = $ref->getReferenceCaracteristiques();
        $referenceDescriptionServiceEffectivemenetRendus = $ref->getReferenceDescriptionServiceEffectivemenetRendus();

        // Format Technologies
        $technologies = [];
        foreach ($ref->getTechnologies() as $technologie) {
            $technologies[] = $technologie->getReferenceTechnologieLibelle();
        }

        // Format Methodologies
        $methodologies = [];
        foreach ($ref->getMethodologies() as $methodologie) {
            $methodologies[] = $methodologie->getMethodologieLibelle();
        }

        // Format Environnement Developpements
        $environnementdeveloppements = [];
        foreach ($ref->getEnvironnementdeveloppements() as $environnement) {
            $environnementdeveloppements[] = $environnement->getEnvironnementDeveloppementLibelle();
        }

        $html = '
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; margin: 10px; box-sizing: border-box; }
        .header { background-color: ' . $categoriecouleur . '; color: white; text-align: center; padding: 15px; border-bottom: 3px solid ' . $categoriecouleur . '; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 10px; border: 1px solid ' . $categoriecouleur . '; background-color: #f9f9f9; }
        .bold { font-weight: bold; text-align: left; }
        .left-align { text-align: left; }
        .full-width { width: 100%; }
        .half-width { width: 50%; }
        .three-quarters { width: 70%; }
        .one-quarter { width: 30%; }
        
    </style>
</head>
<body>
    <div class="header">
        <h2>REF ' . htmlspecialchars($referenceRef) . ' : ' . htmlspecialchars($referenceTitre) . '</h2>
    </div>
    <table>
        <tr>
            <td class="three-quarters" colspan="2">
                <strong>Nom du projet :</strong>' . htmlspecialchars($referenceLibelle) . '
            </td>
          
            <td class="one-quarter">
                <div class="bold">URL fonctionnel:</div>
                <div class="left-align">' . htmlspecialchars($referenceUrlFonctionnel) . '</div>
            </td>
        </tr>
        <tr>
            <td class="half-width" colspan="2">
                 <strong>Pays : </strong>' . htmlspecialchars($lieu->getPays()->getPaysLibelle()) . '
                 <br> 
                 <strong>Lieu : </strong>' . htmlspecialchars($lieu->getLieuLibelle()) . '
            </td>
            
            <td class="one-quarter">
                <div class="bold">Durée du projet (mois):</div>
                <div class="left-align">' . htmlspecialchars($referenceDuree) . ' mois</div>
            </td>
        </tr>
        <tr>
            <td class="full-width" colspan="3">
            <strong>Nature du client </strong>(Privée, publique, association ou autres) :
                ' . htmlspecialchars($client->getNatureClient()->getNatureClient()) . '
            </td>
        </tr>
        <tr>
            <td class="half-width"  >
                <div class="bold">Nom et adresse du Client:</div>
                <div class="left-align">
                    ' . htmlspecialchars($client->getClientRaisonSocial()) . '
                    <br>
                    ' . htmlspecialchars($client->getClientAdresse()) . '
                     <br>
                    ' . htmlspecialchars($client->getClientPersonneContact1()) . '
                    <br>
                    ' . htmlspecialchars($client->getClientEmail()) . '
                    <br> TEL:
                    ' . htmlspecialchars($client->getClientTelephone1()) . '
                </div>
            </td>
            
            <td  colspan="2">
                <div class="bold">Équipe:</div>
                <div class="left-align"></div>
            </td>
            
        </tr>
        <tr>
            <td class="half-width" >
            <strong>Date de démarrage</strong> (mois/année): ' . htmlspecialchars($referenceDateDemarrage->format('m/Y')) . '
                <br>
            <strong>Date d\'achèvement</strong> (mois/année): ' . htmlspecialchars($referenceDateAchevement->format('m/Y')) . '
            </td>
            
            <td colspan="2" >
                <strong>Date de reception provisoire</strong> (mois/année): ' . htmlspecialchars($referenceDateReceptionProvisoire->format('m/Y')) . '
                <br>
                <strong>Date de reception definitive</strong> (mois/année):' . htmlspecialchars($referenceDateReceptionDefinitive->format('m/Y')) . '
            </td>
            
        </tr>
        <tr>
            <td class="full-width" colspan="3">
                <strong>Caractéristiques du projet :</strong><br>' . htmlspecialchars($referenceCaracteristiques) . '
                <br><br>
                <strong>Spécifique techniques: </strong>' .  htmlspecialchars(implode("; ", $environnementdeveloppements)) . '
                <br>
                <strong>Technologies: </strong>' . htmlspecialchars(implode("; ", $technologies)) . '
                <br>
                <strong>Méthodologie: </strong>' . htmlspecialchars(implode("; ", $methodologies)) . '
            </td>
        </tr>
        <tr>
            <td class="full-width" colspan="3">
                <div class="bold">Description des services effectivement rendus:</div>
                <div class="left-align">' . $referenceDescriptionServiceEffectivemenetRendus . '</div>
            </td>
        </tr>
    </table>
</body>
</html>';

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Récupérer le contenu du PDF et le retourner en réponse HTTP
        $output = $dompdf->output();

        return new Response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="projet_' . $id . '.pdf"'
        ]);
    }

    #[Route('/api/rapportword/all', name: 'app_rapportWORD_all')]
    public function generateAllRapportWORD(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $referenceIds = $data['referenceIds'] ?? [];


        if (empty($referenceIds)) {
            throw $this->createNotFoundException('Aucune référence trouvée.');
        }

        $references = $this->referenceRepository->findBy(['referenceID' => $referenceIds]);

        if (!$references) {
            throw $this->createNotFoundException('Aucune référence trouvée.');
        }

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // Common table styles


        foreach ($references as $index => $ref) {
            $categoriecouleur = $ref->getCategorie()->getCategorieCodeCouleur();
            $referenceRef = $ref->getReferenceRef();
            $referenceTitre = $ref->getReferenceTitre();
            $referenceLibelle = $ref->getReferenceLibelle();
            $referenceUrlFonctionnel = $ref->getReferenceUrlFonctionnel();
            $lieu = $ref->getLieu();
            $referenceDuree = $ref->getReferenceDuree();
            $referenceDateDemarrage = $ref->getReferenceDateDemarrage();
            $referenceDateAchevement = $ref->getReferenceDateAchevement();
            $referenceDateReceptionProvisoire = $ref->getReferenceDateReceptionProvisoire();
            $referenceDateReceptionDefinitive = $ref->getReferenceDateReceptionDefinitive();
            $referenceCaracteristiques = $ref->getReferenceCaracteristiques();
            $referenceDescriptionServiceEffectivemenetRendus = $ref->getReferenceDescriptionServiceEffectivemenetRendus();
            $client = $ref->getClient();
            $tableStyle = ['borderSize' => 6,'borderColor' => $categoriecouleur, 'cellMargin' => 100];
            $cellStyle = [];
            $boldFontStyle = ['bold' => true, 'size' => 11];
            $regularFontStyle = ['bold' => false, 'color' => '000000', 'size' => 10];
            // Format Technologies
            $technologies = [];
            foreach ($ref->getTechnologies() as $technologie) {
                $technologies[] = $technologie->getReferenceTechnologieLibelle();
            }

            // Format Methodologies
            $methodologies = [];
            foreach ($ref->getMethodologies() as $methodologie) {
                $methodologies[] = $methodologie->getMethodologieLibelle();
            }

            // Format Environnement Developpements
            $environnementdeveloppements = [];
            foreach ($ref->getEnvironnementdeveloppements() as $environnement) {
                $environnementdeveloppements[] = $environnement->getEnvironnementDeveloppementLibelle();
            }

            // Add table for each reference
            $table = $section->addTable($tableStyle);

            $headerFontStyle = ['bold' => true, 'color' => 'FFFFFF', 'size' => 14];
            $headerCellStyle = ['bgColor' => $categoriecouleur, 'valign' => 'center']; // Vertical alignment
            $headerParagraphStyle = ['align' => 'center']; // Horizontal alignment

            // Add header in the first cell
            $table->addRow(900);
            $table->addCell(9200, ['gridSpan' => 3] + $headerCellStyle)->addText('REF ' . $referenceRef . ' : ' . $referenceTitre, $headerFontStyle, $headerParagraphStyle);

            // Row 1
            $table->addRow();
            $this->addCellWithMultipleLabelsAndValues($table, [
                ['Nom du projet : ', $referenceLibelle]
            ], $cellStyle, $boldFontStyle, $regularFontStyle, 6900);
            $this->addCellWithMultipleLabelsAndValues($table, [
                ['URL fonctionnel: ', $referenceUrlFonctionnel]
            ], $cellStyle, $boldFontStyle, $regularFontStyle, 2600);

            // Row 2
            $table->addRow();
            $this->addCellWithMultipleLabelsAndValues($table, [
                ['Pays : ', $lieu->getPays()->getPaysLibelle()],
                ['Lieu : ', $lieu->getLieuLibelle()]
            ], $cellStyle, $boldFontStyle, $regularFontStyle, 6900);
            $this->addCellWithMultipleLabelsAndValues($table, [
                ['Durée du projet (mois): ', $referenceDuree . ' mois']
            ], $cellStyle, $boldFontStyle, $regularFontStyle, 2600);

            // Row 3
            $this->addSingleCellFullWidth($table, 'Nature du client (Privée, publique, association ou autres) : ' . $client->getNatureClient()->getNatureClient(), $cellStyle, $regularFontStyle);


            // Row 4
            $table->addRow();
            $cell = $table->addCell(4500, $cellStyle);
            $textRun = $cell->addTextRun();

            // Adding the label "Nom et adresse du Client:" in bold
            $textRun->addText('Nom et adresse du Client: ', $boldFontStyle);
            $textRun->addTextBreak(1);

            // Adding the client's information with line breaks after each part
            $textRun->addText($client->getClientRaisonSocial(), $regularFontStyle);
            $textRun->addTextBreak(1);

            $textRun->addText($client->getClientAdresse(), $regularFontStyle);
            $textRun->addTextBreak(1);

            $textRun->addText($client->getClientPersonneContact1(), $regularFontStyle);
            $textRun->addTextBreak(1);

            $textRun->addText($client->getClientEmail(), $regularFontStyle);
            $textRun->addTextBreak(1);

            $textRun->addText($client->getClientTelephone1(), $regularFontStyle);

            $this->addCellWithMultipleLabelsAndValues($table, [
                ['Équipe:', '']
            ], $cellStyle, $boldFontStyle, $regularFontStyle, 5000);

            // Row 5
            $table->addRow();
            $this->addCellWithMultipleLabelsAndValues($table, [
                ['Date de démarrage (mois/année): ', $referenceDateDemarrage->format('m/Y')],
                ['Date d\'achèvement (mois/année): ', $referenceDateAchevement->format('m/Y')]
            ], $cellStyle, $boldFontStyle, $regularFontStyle, 4500);
            $this->addCellWithMultipleLabelsAndValues($table, [
                ['Date de reception provisoire (mois/année): ', $referenceDateReceptionProvisoire->format('m/Y')],
                ['Date de reception definitive (mois/année): ', $referenceDateReceptionDefinitive->format('m/Y')]
            ], $cellStyle, $boldFontStyle, $regularFontStyle, 5000);

            // Row 6
            $this->addFullWidthCell($table, [
                ['Caractéristiques du projet : ', $referenceCaracteristiques],
                ['Spécifique techniques: ', implode(", ", $environnementdeveloppements)],
                ['Technologies: ', implode(", ", $technologies)],
                ['Méthodologie: ', implode(", ", $methodologies)]
            ], $cellStyle, $boldFontStyle, $regularFontStyle);

            // Row 7
            $table->addRow();

            // Add a cell that spans the full width of the table
            $cell = $table->addCell(9500, array_merge($cellStyle, ['gridSpan' => 3])); // Adjust 'gridSpan' to match the number of columns in your table
            $textRun = $cell->addTextRun();

            // Adding the label in bold
            $textRun->addText('Description des services effectivement rendus: ', $boldFontStyle);

            // Adding the HTML content to the cell
            $this->addHtmlContentToPhpWord($cell, $referenceDescriptionServiceEffectivemenetRendus);


            if ($index < count($references) - 1) {
                $section->addPageBreak();
            }
        }

        $fileName = 'all_references.docx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $phpWordWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $phpWordWriter->save($tempFile);

        return $this->file($tempFile, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }


    #[Route('/api/rapportword/{id}', name: 'app_rapportWORD_id', requirements: ['id' => '\d+'])]
    public function generateRapportWORD(Request $request, int $id): Response
    {
        $ref = $this->referenceRepository->find($id);

        if (!$ref) {
            throw $this->createNotFoundException('La référence avec l\'ID ' . $id . ' n\'existe pas.');
        }

        $referenceID = $ref->getReferenceID();
        $client = $ref->getClient();
        $devises = $ref->getDevises();
        $lieu = $ref->getLieu();
        $categoriecouleur = $ref->getCategorie()->getCategorieCodeCouleur();
        $referenceRef = $ref->getReferenceRef();
        $referenceTitre = $ref->getReferenceTitre();
        $referenceLibelle = $ref->getReferenceLibelle();
        $referenceUrlFonctionnel = $ref->getReferenceUrlFonctionnel();
        $referenceDuree = $ref->getReferenceDuree();
        $referenceDateDemarrage = $ref->getReferenceDateDemarrage();
        $referenceDateAchevement = $ref->getReferenceDateAchevement();
        $referenceAnneeAchevement = $ref->getReferenceAnneeAchevement();
        $referenceDateReceptionProvisoire = $ref->getReferenceDateReceptionProvisoire();
        $referenceDateReceptionDefinitive = $ref->getReferenceDateReceptionDefinitive();
        $referenceCaracteristiques = $ref->getReferenceCaracteristiques();
        $referenceDescription = $ref->getReferenceDescription();
        $referenceDescriptionServiceEffectivemenetRendus = $ref->getReferenceDescriptionServiceEffectivemenetRendus();
        $referenceDureeGarantie = $ref->getReferenceDureeGarantie();
        $referenceBudget = $ref->getReferenceBudget();
        $referencePartBudgetGroupement = $ref->getReferencePartBudgetGroupement();
        $referenceRemarque = $ref->getReferenceRemarque();
        $bailleurfonds = $ref->getBailleurfonds();
        $roles = $ref->getRoles();


        // Format Technologies
        $technologies = [];
        foreach ($ref->getTechnologies() as $technologie) {
            $technologies[] = $technologie->getReferenceTechnologieLibelle();
        }

        // Format Methodologies
        $methodologies = [];
        foreach ($ref->getMethodologies() as $methodologie) {
            $methodologies[] = $methodologie->getMethodologieLibelle();
        }

        // Format Environnement Developpements
        $environnementdeveloppements = [];
        foreach ($ref->getEnvironnementdeveloppements() as $environnement) {
            $environnementdeveloppements[] = $environnement->getEnvironnementDeveloppementLibelle();
        }

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // Add table
        $tableStyle = ['borderSize' => 6, 'borderColor' => $categoriecouleur, 'cellMargin' => 100];
        $cellStyle = [];
        $boldFontStyle = ['bold' => true, 'size' => 11];
        $regularFontStyle = ['bold' => false, 'color' => '000000', 'size' => 10];

        $table = $section->addTable($tableStyle);

        // Header row style,
        $headerFontStyle = ['bold' => true, 'color' => 'FFFFFF', 'size' => 14];
        $headerCellStyle = ['bgColor' => $categoriecouleur, 'valign' => 'center']; // Vertical alignment
        $headerParagraphStyle = ['align' => 'center']; // Horizontal alignment

        // Add header in the first cell
        $table->addRow(900);
        $table->addCell(9200, ['gridSpan' => 3] + $headerCellStyle)->addText('REF ' . $referenceRef . ' : ' . $referenceTitre, $headerFontStyle, $headerParagraphStyle);

        // Row 1
        $table->addRow();
        $this->addCellWithMultipleLabelsAndValues($table, [
            ['Nom du projet : ', $referenceLibelle]
        ], $cellStyle, $boldFontStyle, $regularFontStyle, 6900);
        $this->addCellWithMultipleLabelsAndValues($table, [
            ['URL fonctionnel: ', $referenceUrlFonctionnel]
        ], $cellStyle, $boldFontStyle, $regularFontStyle, 2600);

        // Row 2
        $table->addRow();
        $this->addCellWithMultipleLabelsAndValues($table, [
            ['Pays : ', $lieu->getPays()->getPaysLibelle()],
            ['Lieu : ', $lieu->getLieuLibelle()]
        ], $cellStyle, $boldFontStyle, $regularFontStyle, 6900);
        $this->addCellWithMultipleLabelsAndValues($table, [
            ['Durée du projet (mois): ', $referenceDuree . ' mois']
        ], $cellStyle, $boldFontStyle, $regularFontStyle, 2600);

        // Row 3
        $this->addSingleCellFullWidth($table, 'Nature du client (Privée, publique, association ou autres) : ' . $client->getNatureClient()->getNatureClient(), $cellStyle, $regularFontStyle);


        // Row 4
        $table->addRow();
        $cell = $table->addCell(4500, $cellStyle);
        $textRun = $cell->addTextRun();

        // Adding the label "Nom et adresse du Client:" in bold
        $textRun->addText('Nom et adresse du Client: ', $boldFontStyle);
        $textRun->addTextBreak(1);

        // Adding the client's information with line breaks after each part
        $textRun->addText($client->getClientRaisonSocial(), $regularFontStyle);
        $textRun->addTextBreak(1);

        $textRun->addText($client->getClientAdresse(), $regularFontStyle);
        $textRun->addTextBreak(1);

        $textRun->addText($client->getClientPersonneContact1(), $regularFontStyle);
        $textRun->addTextBreak(1);

        $textRun->addText($client->getClientEmail(), $regularFontStyle);
        $textRun->addTextBreak(1);

        $textRun->addText($client->getClientTelephone1(), $regularFontStyle);

        $this->addCellWithMultipleLabelsAndValues($table, [
            ['Équipe:', '']
        ], $cellStyle, $boldFontStyle, $regularFontStyle, 5000);

        // Row 5
        $table->addRow();
        $this->addCellWithMultipleLabelsAndValues($table, [
            ['Date de démarrage (mois/année): ', $referenceDateDemarrage->format('m/Y')],
            ['Date d\'achèvement (mois/année): ', $referenceDateAchevement->format('m/Y')]
        ], $cellStyle, $boldFontStyle, $regularFontStyle, 4500);
        $this->addCellWithMultipleLabelsAndValues($table, [
            ['Date de reception provisoire (mois/année): ', $referenceDateReceptionProvisoire->format('m/Y')],
            ['Date de reception definitive (mois/année): ', $referenceDateReceptionDefinitive->format('m/Y')]
        ], $cellStyle, $boldFontStyle, $regularFontStyle, 5000);

        // Row 6
        $this->addFullWidthCell($table, [
            ['Caractéristiques du projet : ', $referenceCaracteristiques],
            ['Spécifique techniques: ', implode(", ", $environnementdeveloppements)],
            ['Technologies: ', implode(", ", $technologies)],
            ['Méthodologie: ', implode(", ", $methodologies)]
        ], $cellStyle, $boldFontStyle, $regularFontStyle);

        // Row 7
        $table->addRow();

        // Add a cell that spans the full width of the table
        $cell = $table->addCell(9500, array_merge($cellStyle, ['gridSpan' => 3])); // Adjust 'gridSpan' to match the number of columns in your table
        $textRun = $cell->addTextRun();

        // Adding the label in bold
        $textRun->addText('Description des services effectivement rendus: ', $boldFontStyle);

        // Adding the HTML content to the cell
        $this->addHtmlContentToPhpWord($cell, $referenceDescriptionServiceEffectivemenetRendus);


        $fileName = 'document.docx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);
        $phpWordWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $phpWordWriter->save($tempFile);

        return $this->file($tempFile, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    public function addCellWithMultipleLabelsAndValues($table, array $labelValuePairs, $cellStyle, $boldFontStyle, $regularFontStyle, $size)
    {
        $cell = $table->addCell($size);
        $textRun = $cell->addTextRun();

        $totalPairs = count($labelValuePairs);

        foreach ($labelValuePairs as $index => $pair) {
            $label = $pair[0];
            $value = $pair[1];

            // Add the label in bold
            $textRun->addText($label, $boldFontStyle);

            // Add the value in regular font style
            $textRun->addText($value, $regularFontStyle);

            // Add a new line only if it's not the last pair
            if ($index < $totalPairs - 1) {
                $textRun->addTextBreak(1);
            }
        }
    }

    public function addFullWidthCell($table, array $labelValuePairs, $cellStyle, $boldFontStyle, $regularFontStyle)
    {
        // Adding a row
        $table->addRow();

        // Add a cell that spans the entire row width
        $cell = $table->addCell(9500, array_merge($cellStyle, ['gridSpan' => 3]));  // Use gridSpan if the table has multiple columns
        $textRun = $cell->addTextRun();

        foreach ($labelValuePairs as $pair) {
            $label = $pair[0];
            $value = $pair[1];

            // Add the label in bold
            $textRun->addText($label, $boldFontStyle);

            // Add the value in regular font style
            $textRun->addText($value, $regularFontStyle);

            // Add a new line after each label-value pair
            $textRun->addTextBreak(1);
        }
    }

    public function addSingleCellFullWidth($table, $content, $cellStyle, $fontStyle, $rowHeight = 800) {
        // Add a new row to the table with specified height
        $table->addRow($rowHeight);

        // Add a single cell with gridSpan to cover the entire width of the table
        $cell = $table->addCell(9500, ['gridSpan' => 3] + $cellStyle);

        // Add content to the cell with the specified font style
        $cell->addText($content, $fontStyle);
    }




    function addHtmlContentToPhpWord(Cell $section, $htmlContent)
    {
        if (!$htmlContent) {
            throw new \InvalidArgumentException('HTML content is null or empty.');
        }

        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $htmlContent, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // Recursively process nodes
        $processNode = function ($node, $phpWordSection) use (&$processNode) {
            foreach ($node->childNodes as $childNode) {
                if ($childNode instanceof \DOMText) {
                    $phpWordSection->addText($childNode->nodeValue);
                } elseif ($childNode instanceof \DOMElement) {
                    switch ($childNode->nodeName) {
                        case 'p':
                            $textRun = $phpWordSection->addTextRun();
                            $processNode($childNode, $textRun); // Process child nodes for the paragraph
                            $phpWordSection->addTextBreak(); // Add a paragraph break after the paragraph
                            break;
                        case 'b':
                        case 'strong':
                            $phpWordSection->addText($childNode->nodeValue, ['bold' => true]);
                            break;
                        case 'i':
                        case 'em':
                            $phpWordSection->addText($childNode->nodeValue, ['italic' => true]);
                            break;
                        case 'ul':
                            foreach ($childNode->childNodes as $listItem) {
                                if ($listItem instanceof \DOMElement && $listItem->nodeName === 'li') {
                                    $phpWordSection->addListItem($listItem->nodeValue, 0, null, 'ListBullet');
                                }
                            }
                            break;
                        case 'ol':
                            foreach ($childNode->childNodes as $listItem) {
                                if ($listItem instanceof \DOMElement && $listItem->nodeName === 'li') {
                                    $phpWordSection->addListItem($listItem->nodeValue, 0, null, 'ListNumber');
                                }
                            }
                            break;
                    }
                }
            }
        };

        // Process the HTML content directly as if it's the body node
        $domBody = new \DOMDocument();
        $domBody->loadHTML('<?xml encoding="utf-8" ?><body>' . $htmlContent . '</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $body = $domBody->getElementsByTagName('body')->item(0);

        if (!$body) {
            throw new \Exception('Failed to parse HTML content.');
        }

        $processNode($body, $section);
    }
}
