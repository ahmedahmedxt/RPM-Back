<?php

namespace App\DTO;

class SummaryDTO
{
    private string $originalText;
    private string $summary;
    private array $keywords;
    private int $originalLength;
    private int $summaryLength;
    private int $compressionRatio;
    private array $metadata;

    public function __construct(
        string $originalText = '',
        string $summary = '',
        array $keywords = [],
        int $originalLength = 0,
        int $summaryLength = 0,
        array $metadata = []
    ) {
        $this->originalText = $originalText;
        $this->summary = $summary;
        $this->keywords = $keywords;
        $this->originalLength = $originalLength;
        $this->summaryLength = $summaryLength;
        $this->compressionRatio = $originalLength > 0 ? round(($summaryLength / $originalLength) * 100, 2) : 0;
        $this->metadata = $metadata;
    }

    public function getOriginalText(): string
    {
        return $this->originalText;
    }

    public function setOriginalText(string $originalText): self
    {
        $this->originalText = $originalText;
        return $this;
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function setSummary(string $summary): self
    {
        $this->summary = $summary;
        $this->summaryLength = strlen($summary);
        $this->updateCompressionRatio();
        return $this;
    }

    public function getKeywords(): array
    {
        return $this->keywords;
    }

    public function setKeywords(array $keywords): self
    {
        $this->keywords = $keywords;
        return $this;
    }

    public function addKeyword(string $keyword): self
    {
        if (!in_array($keyword, $this->keywords)) {
            $this->keywords[] = $keyword;
        }
        return $this;
    }

    public function getOriginalLength(): int
    {
        return $this->originalLength;
    }

    public function setOriginalLength(int $originalLength): self
    {
        $this->originalLength = $originalLength;
        $this->updateCompressionRatio();
        return $this;
    }

    public function getSummaryLength(): int
    {
        return $this->summaryLength;
    }

    public function setSummaryLength(int $summaryLength): self
    {
        $this->summaryLength = $summaryLength;
        $this->updateCompressionRatio();
        return $this;
    }

    public function getCompressionRatio(): float
    {
        return $this->compressionRatio;
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

    public function toArray(): array
    {
        return [
            'originalText' => $this->originalText,
            'summary' => $this->summary,
            'keywords' => $this->keywords,
            'originalLength' => $this->originalLength,
            'summaryLength' => $this->summaryLength,
            'compressionRatio' => $this->compressionRatio,
            'metadata' => $this->metadata
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    public function isValid(): bool
    {
        return !empty($this->summary) && $this->summaryLength > 0;
    }

    public function isEmpty(): bool
    {
        return empty($this->summary);
    }

    public function getWordCount(): int
    {
        return str_word_count($this->summary);
    }

    public function getKeywordCount(): int
    {
        return count($this->keywords);
    }

    public function getTopKeywords(int $limit = 5): array
    {
        return array_slice($this->keywords, 0, $limit);
    }

    public function hasKeyword(string $keyword): bool
    {
        return in_array(strtolower($keyword), array_map('strtolower', $this->keywords));
    }

    public function getCompressionEfficiency(): string
    {
        if ($this->compressionRatio <= 20) {
            return 'Excellent';
        } elseif ($this->compressionRatio <= 40) {
            return 'Bon';
        } elseif ($this->compressionRatio <= 60) {
            return 'Moyen';
        } else {
            return 'Faible';
        }
    }

    public function getReadingTime(): int
    {
        // Estimation : 200 mots par minute
        $wordsPerMinute = 200;
        $wordCount = $this->getWordCount();
        return max(1, round($wordCount / $wordsPerMinute));
    }

    private function updateCompressionRatio(): void
    {
        $this->compressionRatio = $this->originalLength > 0 
            ? round(($this->summaryLength / $this->originalLength) * 100, 2) 
            : 0;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['originalText'] ?? '',
            $data['summary'] ?? '',
            $data['keywords'] ?? [],
            $data['originalLength'] ?? 0,
            $data['summaryLength'] ?? 0,
            $data['metadata'] ?? []
        );
    }

    public static function createFromText(string $originalText, string $summary, array $keywords = []): self
    {
        return new self(
            $originalText,
            $summary,
            $keywords,
            strlen($originalText),
            strlen($summary)
        );
    }
}