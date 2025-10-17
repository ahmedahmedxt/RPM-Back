<?php

namespace App\DTO;

class TemplateDTO
{
    private ?int $id;
    private string $name;
    private ?string $description;
    private array $hiddenFields;
    private bool $isSystem;
    private ?\DateTimeInterface $createdAt;
    private ?\DateTimeInterface $updatedAt;
    private array $metadata;
    private int $usageCount;
    private bool $isActive;

    public function __construct(
        ?int $id = null,
        string $name = '',
        ?string $description = null,
        array $hiddenFields = [],
        bool $isSystem = false,
        ?\DateTimeInterface $createdAt = null,
        ?\DateTimeInterface $updatedAt = null,
        array $metadata = [],
        int $usageCount = 0,
        bool $isActive = true
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->hiddenFields = $hiddenFields;
        $this->isSystem = $isSystem;
        $this->createdAt = $createdAt ?? new \DateTime();
        $this->updatedAt = $updatedAt ?? new \DateTime();
        $this->metadata = $metadata;
        $this->usageCount = $usageCount;
        $this->isActive = $isActive;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getHiddenFields(): array
    {
        return $this->hiddenFields;
    }

    public function setHiddenFields(array $hiddenFields): self
    {
        $this->hiddenFields = $hiddenFields;
        return $this;
    }

    public function addHiddenField(string $field): self
    {
        if (!in_array($field, $this->hiddenFields)) {
            $this->hiddenFields[] = $field;
        }
        return $this;
    }

    public function removeHiddenField(string $field): self
    {
        $key = array_search($field, $this->hiddenFields);
        if ($key !== false) {
            unset($this->hiddenFields[$key]);
            $this->hiddenFields = array_values($this->hiddenFields);
        }
        return $this;
    }

    public function hasHiddenField(string $field): bool
    {
        return in_array($field, $this->hiddenFields);
    }

    public function isSystem(): bool
    {
        return $this->isSystem;
    }

    public function setIsSystem(bool $isSystem): self
    {
        $this->isSystem = $isSystem;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function addMetadata(string $key, $value): self
    {
        $this->metadata[$key] = $value;
        return $this;
    }

    public function getMetadataValue(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }

    public function getUsageCount(): int
    {
        return $this->usageCount;
    }

    public function setUsageCount(int $usageCount): self
    {
        $this->usageCount = $usageCount;
        return $this;
    }

    public function incrementUsage(): self
    {
        $this->usageCount++;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getVisibleFieldsCount(): int
    {
        // Supposons qu'il y a un nombre total de champs disponibles
        $totalFields = 25; // À ajuster selon vos besoins
        return $totalFields - count($this->hiddenFields);
    }

    public function getHiddenFieldsCount(): int
    {
        return count($this->hiddenFields);
    }

    public function getVisibilityRatio(): float
    {
        $totalFields = 25; // À ajuster selon vos besoins
        return $totalFields > 0 ? round((($totalFields - count($this->hiddenFields)) / $totalFields) * 100, 2) : 0;
    }

    public function getVisibilityLevel(): string
    {
        $ratio = $this->getVisibilityRatio();
        
        if ($ratio >= 90) {
            return 'Complet';
        } elseif ($ratio >= 70) {
            return 'Détaillé';
        } elseif ($ratio >= 50) {
            return 'Modéré';
        } elseif ($ratio >= 30) {
            return 'Basique';
        } else {
            return 'Minimal';
        }
    }

    public function isCompatibleWith(array $availableFields): bool
    {
        foreach ($this->hiddenFields as $field) {
            if (!in_array($field, $availableFields)) {
                return false;
            }
        }
        return true;
    }

    public function getIncompatibleFields(array $availableFields): array
    {
        $incompatible = [];
        foreach ($this->hiddenFields as $field) {
            if (!in_array($field, $availableFields)) {
                $incompatible[] = $field;
            }
        }
        return $incompatible;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'hiddenFields' => $this->hiddenFields,
            'isSystem' => $this->isSystem,
            'createdAt' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'metadata' => $this->metadata,
            'usageCount' => $this->usageCount,
            'isActive' => $this->isActive,
            'visibleFieldsCount' => $this->getVisibleFieldsCount(),
            'hiddenFieldsCount' => $this->getHiddenFieldsCount(),
            'visibilityRatio' => $this->getVisibilityRatio(),
            'visibilityLevel' => $this->getVisibilityLevel()
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function isValid(): bool
    {
        return !empty($this->name) && 
               strlen($this->name) <= 100 &&
               (empty($this->description) || strlen($this->description) <= 1000);
    }

    public function isEmpty(): bool
    {
        return empty($this->name);
    }

    public function canBeDeleted(): bool
    {
        return !$this->isSystem && $this->usageCount === 0;
    }

    public function canBeModified(): bool
    {
        return !$this->isSystem || $this->isActive;
    }

    public function getDisplayName(): string
    {
        $suffix = $this->isSystem ? ' (Système)' : '';
        return $this->name . $suffix;
    }

    public function getShortDescription(int $maxLength = 100): string
    {
        if (empty($this->description)) {
            return '';
        }
        
        return strlen($this->description) <= $maxLength 
            ? $this->description 
            : substr($this->description, 0, $maxLength) . '...';
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['name'] ?? '',
            $data['description'] ?? null,
            $data['hiddenFields'] ?? [],
            $data['isSystem'] ?? false,
            isset($data['createdAt']) ? new \DateTime($data['createdAt']) : null,
            isset($data['updatedAt']) ? new \DateTime($data['updatedAt']) : null,
            $data['metadata'] ?? [],
            $data['usageCount'] ?? 0,
            $data['isActive'] ?? true
        );
    }

    public static function createSystemTemplate(string $name, array $hiddenFields = []): self
    {
        return new self(
            null,
            $name,
            'Template système prédéfini',
            $hiddenFields,
            true,
            new \DateTime(),
            new \DateTime(),
            ['type' => 'system'],
            0,
            true
        );
    }

    public static function createUserTemplate(string $name, string $description = '', array $hiddenFields = []): self
    {
        return new self(
            null,
            $name,
            $description,
            $hiddenFields,
            false,
            new \DateTime(),
            new \DateTime(),
            ['type' => 'user'],
            0,
            true
        );
    }

    public function clone(string $newName): self
    {
        return new self(
            null,
            $newName,
            $this->description . ' (Copie)',
            $this->hiddenFields,
            false, // Les clones sont toujours des templates utilisateur
            new \DateTime(),
            new \DateTime(),
            array_merge($this->metadata, ['cloned_from' => $this->id]),
            0,
            true
        );
    }
}