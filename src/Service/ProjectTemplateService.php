<?php

namespace App\Service;

use App\Entity\Template\ProjectTemplate;
use App\Repository\Template\ProjectTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProjectTemplateService
{
    private ProjectTemplateRepository $templateRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ProjectTemplateRepository $templateRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->templateRepository = $templateRepository;
        $this->entityManager = $entityManager;
    }

    public function createTemplate(array $data): ProjectTemplate
    {
        $template = new ProjectTemplate();
        $template->setName($data['name']);
        $template->setDescription($data['description'] ?? null);
        $template->setHiddenFields($data['hiddenFields'] ?? []);
        $template->setIsSystem($data['isSystem'] ?? false);

        $this->entityManager->persist($template);
        $this->entityManager->flush();

        return $template;
    }

    public function updateTemplate(int $id, array $data): ?ProjectTemplate
    {
        $template = $this->templateRepository->find($id);
        
        if (!$template) {
            return null;
        }

        if (isset($data['name'])) {
            $template->setName($data['name']);
        }
        
        if (isset($data['description'])) {
            $template->setDescription($data['description']);
        }
        
        if (isset($data['hiddenFields'])) {
            $template->setHiddenFields($data['hiddenFields']);
        }

        $template->setUpdatedAt(new \DateTime());
        
        $this->entityManager->flush();

        return $template;
    }

    public function deleteTemplate(int $id): bool
    {
        $template = $this->templateRepository->find($id);
        
        if (!$template) {
            return false;
        }

        // Ne pas permettre la suppression des templates système
        if ($template->isSystem()) {
            return false;
        }

        $this->entityManager->remove($template);
        $this->entityManager->flush();

        return true;
    }

    public function getTemplate(int $id): ?ProjectTemplate
    {
        return $this->templateRepository->find($id);
    }

    public function getTemplateByName(string $name): ?ProjectTemplate
    {
        return $this->templateRepository->findByName($name);
    }

    public function getAllTemplates(): array
    {
        return $this->templateRepository->findActiveTemplates();
    }

    public function getSystemTemplates(): array
    {
        return $this->templateRepository->findSystemTemplates();
    }

    public function getUserTemplates(): array
    {
        return $this->templateRepository->findUserTemplates();
    }

    public function duplicateTemplate(int $id, string $newName): ?ProjectTemplate
    {
        $originalTemplate = $this->templateRepository->find($id);
        
        if (!$originalTemplate) {
            return null;
        }

        $newTemplate = new ProjectTemplate();
        $newTemplate->setName($newName);
        $newTemplate->setDescription($originalTemplate->getDescription() . ' (Copie)');
        $newTemplate->setHiddenFields($originalTemplate->getHiddenFields());
        $newTemplate->setIsSystem(false); // Les copies sont toujours des templates utilisateur

        $this->entityManager->persist($newTemplate);
        $this->entityManager->flush();

        return $newTemplate;
    }

    public function validateTemplateData(array $data): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'Le nom du template est requis';
        } elseif (strlen($data['name']) > 100) {
            $errors[] = 'Le nom du template ne peut pas dépasser 100 caractères';
        }

        if (isset($data['description']) && strlen($data['description']) > 1000) {
            $errors[] = 'La description ne peut pas dépasser 1000 caractères';
        }

        if (isset($data['hiddenFields']) && !is_array($data['hiddenFields'])) {
            $errors[] = 'Les champs cachés doivent être un tableau';
        }

        return $errors;
    }

    public function getTemplateStatistics(): array
    {
        $allTemplates = $this->getAllTemplates();
        $systemTemplates = $this->getSystemTemplates();
        $userTemplates = $this->getUserTemplates();

        return [
            'total' => count($allTemplates),
            'system' => count($systemTemplates),
            'user' => count($userTemplates),
            'recent' => $this->getRecentTemplates(5)
        ];
    }

    private function getRecentTemplates(int $limit): array
    {
        return $this->templateRepository->createQueryBuilder('t')
            ->orderBy('t.updatedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}