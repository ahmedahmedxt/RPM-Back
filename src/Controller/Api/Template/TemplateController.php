<?php

namespace App\Controller\Api\Template;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/templates')]
class TemplateController extends AbstractController
{
	#[Route('/available-fields', name: 'api_templates_available_fields', methods: ['GET'])]
	public function getAvailableFields(): JsonResponse
	{
		$fields = [
			'referenceRef' => 'Référence',
			'referenceTitre' => 'Titre',
			'referenceLibelle' => 'Libellé',
			'referenceUrlFonctionnel' => 'URL Fonctionnel',
			'referenceDuree' => 'Durée',
			'referenceDateDemarrage' => 'Date de démarrage',
			'referenceDateAchevement' => 'Date d\'achèvement',
			'referenceAnneeAchevement' => 'Année d\'achèvement',
			'referenceDateReceptionProvisoire' => 'Date réception provisoire',
			'referenceDateReceptionDefinitive' => 'Date réception définitive',
			'referenceDureeGarantie' => 'Durée de garantie',
			'lieu' => 'Lieu',
			'pays' => 'Pays',
			'client' => 'Client',
			'categorie' => 'Catégorie',
			'devise' => 'Devise',
			'referenceCaracteristiques' => 'Caractéristiques',
			'referenceDescription' => 'Description',
			'referenceDescriptionServiceEffectivemenetRendus' => 'Services rendus',
			'technologies' => 'Technologies',
			'methodologies' => 'Méthodologies',
			'environnements' => 'Environnements de développement',
			'roles' => 'Rôles',
			'referenceBudget' => 'Budget',
			'referencePartBudgetGroupement' => 'Part budget groupement',
			'bailleurfonds' => 'Bailleurs de fonds',
			'referenceRemarque' => 'Remarques',
			'documents' => 'Documents associés'
		];

		return new JsonResponse(['fields' => $fields]);
	}

	#[Route('/system', name: 'api_templates_system', methods: ['GET'])]
	public function getSystemTemplates(): JsonResponse
	{
		$templates = [
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

		return new JsonResponse(['templates' => $templates]);
	}
}