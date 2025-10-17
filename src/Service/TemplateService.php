<?php

namespace App\Service;

use App\Entity\Reference;
use App\Entity\Template\ProjectTemplate;
use App\Repository\Template\ProjectTemplateRepository;

class TemplateService
{
    // Définition des champs disponibles pour le masquage
    public const AVAILABLE_FIELDS = [
        // Informations générales
        'referenceRef' => 'Référence',
        'referenceTitre' => 'Titre',
        'referenceLibelle' => 'Libellé',
        'referenceUrlFonctionnel' => 'URL Fonctionnel',
        
        // Informations temporelles
        'referenceDuree' => 'Durée',
        'referenceDateDemarrage' => 'Date de démarrage',
        'referenceDateAchevement' => 'Date d\'achèvement',
        'referenceAnneeAchevement' => 'Année d\'achèvement',
        'referenceDateReceptionProvisoire' => 'Date réception provisoire',
        'referenceDateReceptionDefinitive' => 'Date réception définitive',
        'referenceDureeGarantie' => 'Durée de garantie',
        
        // Informations géographiques et client
        'lieu' => 'Lieu',
        'pays' => 'Pays',
        'client' => 'Client',
        'categorie' => 'Catégorie',
        'devise' => 'Devise',
        
        // Informations techniques
        'referenceCaracteristiques' => 'Caractéristiques',
        'referenceDescription' => 'Description',
        'referenceDescriptionServiceEffectivemenetRendus' => 'Services rendus',
        'technologies' => 'Technologies',
        'methodologies' => 'Méthodologies',
        'environnements' => 'Environnements de développement',
        'roles' => 'Rôles',
        
        // Informations financières
        'referenceBudget' => 'Budget',
        'referencePartBudgetGroupement' => 'Part budget groupement',
        'bailleurfonds' => 'Bailleurs de fonds',
        
        // Informations additionnelles
        'referenceRemarque' => 'Remarques',
        'documents' => 'Documents associés'
    ];

    // Templates prédéfinis (système)
    public const SYSTEM_TEMPLATES = [
        'complet' => [
            'name' => 'Template Complet',
            'description' => 'Toutes les informations visibles',
            'hiddenFields' => []
        ],
        'basique' => [
            'name' => 'Template Basique',
            'description' => 'Informations essentielles uniquement',
            'hiddenFields' => [
                'referenceUrlFonctionnel',
                'referenceDateReceptionProvisoire',
                'referenceDateReceptionDefinitive',
                'referenceDureeGarantie',
                'referenceCaracteristiques',
                'referenceDescriptionServiceEffectivemenetRendus',
                'referencePartBudgetGroupement',
                'bailleurfonds',
                'referenceRemarque',
                'documents'
            ]
        ],
        'financier' => [
            'name' => 'Template Financier',
            'description' => 'Focus sur les aspects financiers',
            'hiddenFields' => [
                'referenceCaracteristiques',
                'technologies',
                'methodologies',
                'environnements',
                'roles',
                'referenceRemarque',
                'documents'
            ]
        ],
        'technique' => [
            'name' => 'Template Technique',
            'description' => 'Focus sur les aspects techniques',
            'hiddenFields' => [
                'referenceBudget',
                'referencePartBudgetGroupement',
                'bailleurfonds',
                'referenceRemarque'
            ]
        ],
        'client' => [
            'name' => 'Template Client',
            'description' => 'Version client sans informations sensibles',
            'hiddenFields' => [
                'referenceBudget',
                'referencePartBudgetGroupement',
                'bailleurfonds',
                'referenceRemarque',
                'client'
            ]
        ]
    ];

    private ProjectTemplateRepository $templateRepository;

    public function __construct(ProjectTemplateRepository $templateRepository)
    {
        $this->templateRepository = $templateRepository;
    }

    public function getAvailableFields(): array
    {
        return self::AVAILABLE_FIELDS;
    }

    public function getSystemTemplates(): array
    {
        return self::SYSTEM_TEMPLATES;
    }

    public function getUserTemplates(): array
    {
        $templates = $this->templateRepository->findUserTemplates();
        $result = [];
        
        foreach ($templates as $template) {
            $result[$template->getId()] = [
                'id' => $template->getId(),
                'name' => $template->getName(),
                'description' => $template->getDescription(),
                'hiddenFields' => $template->getHiddenFields()
            ];
        }
        
        return $result;
    }

    public function getAllTemplates(): array
    {
        return array_merge(
            $this->getSystemTemplates(),
            $this->getUserTemplates()
        );
    }

    public function getTemplate(string $templateId): ?array
    {
        // Vérifier d'abord les templates système
        if (isset(self::SYSTEM_TEMPLATES[$templateId])) {
            return self::SYSTEM_TEMPLATES[$templateId];
        }
        
        // Puis les templates utilisateur
        $template = $this->templateRepository->find($templateId);
        if ($template) {
            return [
                'id' => $template->getId(),
                'name' => $template->getName(),
                'description' => $template->getDescription(),
                'hiddenFields' => $template->getHiddenFields()
            ];
        }
        
        return null;
    }

    public function filterReferenceData(Reference $reference, array $hiddenFields = []): array
    {
        $data = $this->serializeReference($reference);
        
        // Supprimer les champs cachés
        foreach ($hiddenFields as $field) {
            unset($data[$field]);
        }
        
        return $data;
    }

    private function serializeReference(Reference $reference): array
    {
        return [
            'referenceRef' => $reference->getReferenceRef(),
            'referenceTitre' => $reference->getReferenceTitre(),
            'referenceLibelle' => $reference->getReferenceLibelle(),
            'referenceUrlFonctionnel' => $reference->getReferenceUrlFonctionnel(),
            'referenceDuree' => $reference->getReferenceDuree(),
            'referenceDateDemarrage' => $reference->getReferenceDateDemarrage()?->format('Y-m-d'),
            'referenceDateAchevement' => $reference->getReferenceDateAchevement()?->format('Y-m-d'),
            'referenceAnneeAchevement' => $reference->getReferenceAnneeAchevement(),
            'referenceDateReceptionProvisoire' => $reference->getReferenceDateReceptionProvisoire()?->format('Y-m-d'),
            'referenceDateReceptionDefinitive' => $reference->getReferenceDateReceptionDefinitive()?->format('Y-m-d'),
            'referenceDureeGarantie' => $reference->getReferenceDureeGarantie(),
            'lieu' => $reference->getLieu()?->getLieuLibelle(),
            'pays' => $reference->getLieu()?->getPays()?->getPaysLibelle(),
            'client' => $reference->getClient()?->getClientRaisonSocial(),
            'categorie' => $reference->getCategorie()?->getCategorieLibelle(),
            'devise' => $reference->getDevises()?->getDevisesLibelle(),
            'referenceCaracteristiques' => $reference->getReferenceCaracteristiques(),
            'referenceDescription' => $reference->getReferenceDescription(),
            'referenceDescriptionServiceEffectivemenetRendus' => $reference->getReferenceDescriptionServiceEffectivemenetRendus(),
            'technologies' => $this->serializeTechnologies($reference),
            'methodologies' => $this->serializeMethodologies($reference),
            'environnements' => $this->serializeEnvironnements($reference),
            'roles' => $this->serializeRoles($reference),
            'referenceBudget' => $reference->getReferenceBudget(),
            'referencePartBudgetGroupement' => $reference->getReferencePartBudgetGroupement(),
            'bailleurfonds' => $this->serializeBailleurfonds($reference),
            'referenceRemarque' => $reference->getReferenceRemarque(),
            'documents' => $this->serializeDocuments($reference)
        ];
    }

    private function serializeTechnologies(Reference $reference): array
    {
        $technologies = [];
        foreach ($reference->getTechnologies() as $tech) {
            $technologies[] = $tech->getTechnologieLibelle();
        }
        return $technologies;
    }

    private function serializeMethodologies(Reference $reference): array
    {
        $methodologies = [];
        foreach ($reference->getMethodologies() as $method) {
            $methodologies[] = $method->getMethodologieLibelle();
        }
        return $methodologies;
    }

    private function serializeEnvironnements(Reference $reference): array
    {
        $environnements = [];
        foreach ($reference->getEnvironnementdeveloppements() as $env) {
            $environnements[] = $env->getEnvironnementDeveloppementLibelle();
        }
        return $environnements;
    }

    private function serializeRoles(Reference $reference): array
    {
        $roles = [];
        foreach ($reference->getRoles() as $role) {
            $roles[] = $role->getRoleLibelle();
        }
        return $roles;
    }

    private function serializeBailleurfonds(Reference $reference): array
    {
        $bailleurs = [];
        foreach ($reference->getBailleurfonds() as $bailleur) {
            $bailleurs[] = $bailleur->getBailleurFondLibelle();
        }
        return $bailleurs;
    }

    private function serializeDocuments(Reference $reference): array
    {
        // Cette méthode devrait être implémentée selon la structure des documents
        return [];
    }
}