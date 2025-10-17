<?php

namespace App\Service;

class SummaryExtractionService
{
    public function extractSummary(string $detailedDescription, int $maxLength = 200): string
    {
        // Nettoyer le texte HTML
        $cleanText = strip_tags($detailedDescription);
        
        // Supprimer les espaces multiples
        $cleanText = preg_replace('/\s+/', ' ', $cleanText);
        
        // Supprimer les caractères spéciaux en début/fin
        $cleanText = trim($cleanText);
        
        // Si le texte est déjà court, le retourner tel quel
        if (strlen($cleanText) <= $maxLength) {
            return $cleanText;
        }
        
        // Trouver le dernier point avant la limite
        $truncated = substr($cleanText, 0, $maxLength);
        $lastPeriod = strrpos($truncated, '.');
        
        if ($lastPeriod !== false && $lastPeriod > $maxLength * 0.7) {
            // Si on trouve un point dans les 70% de la limite, couper là
            return substr($cleanText, 0, $lastPeriod + 1);
        }
        
        // Sinon, trouver le dernier espace
        $lastSpace = strrpos($truncated, ' ');
        if ($lastSpace !== false) {
            return substr($cleanText, 0, $lastSpace) . '...';
        }
        
        // En dernier recours, couper brutalement
        return substr($cleanText, 0, $maxLength) . '...';
    }

    public function extractKeywords(string $text, int $maxKeywords = 10): array
    {
        // Nettoyer le texte
        $cleanText = strip_tags($text);
        $cleanText = strtolower($cleanText);
        
        // Supprimer la ponctuation
        $cleanText = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $cleanText);
        
        // Diviser en mots
        $words = preg_split('/\s+/', $cleanText);
        
        // Filtrer les mots vides (stop words en français)
        $stopWords = [
            'le', 'la', 'les', 'de', 'du', 'des', 'un', 'une', 'et', 'ou', 'mais', 'donc', 'or', 'ni', 'car',
            'à', 'avec', 'sans', 'pour', 'par', 'dans', 'sur', 'sous', 'vers', 'chez', 'entre', 'parmi',
            'ce', 'cette', 'ces', 'son', 'sa', 'ses', 'mon', 'ma', 'mes', 'ton', 'ta', 'tes', 'notre', 'nos',
            'votre', 'vos', 'leur', 'leurs', 'il', 'elle', 'ils', 'elles', 'nous', 'vous', 'je', 'tu', 'on',
            'est', 'sont', 'était', 'étaient', 'sera', 'seront', 'a', 'ont', 'avait', 'avaient', 'aura', 'auront',
            'être', 'avoir', 'faire', 'dire', 'aller', 'voir', 'savoir', 'pouvoir', 'falloir', 'vouloir',
            'que', 'qui', 'quoi', 'dont', 'où', 'quand', 'comment', 'pourquoi', 'si', 'comme', 'aussi',
            'très', 'plus', 'moins', 'bien', 'mal', 'toujours', 'jamais', 'souvent', 'parfois', 'encore',
            'déjà', 'maintenant', 'alors', 'donc', 'ainsi', 'puis', 'ensuite', 'après', 'avant', 'pendant'
        ];
        
        $words = array_filter($words, function($word) use ($stopWords) {
            return strlen($word) > 2 && !in_array($word, $stopWords);
        });
        
        // Compter les occurrences
        $wordCount = array_count_values($words);
        
        // Trier par fréquence
        arsort($wordCount);
        
        // Retourner les mots les plus fréquents
        return array_slice(array_keys($wordCount), 0, $maxKeywords);
    }

    public function generateProjectSummary(array $projectData): string
    {
        $summary = '';
        
        // Commencer par le titre si disponible
        if (!empty($projectData['referenceTitre'])) {
            $summary .= $projectData['referenceTitre'];
        }
        
        // Ajouter le client si disponible
        if (!empty($projectData['client'])) {
            $summary .= ' - ' . $projectData['client'];
        }
        
        // Ajouter le pays si disponible
        if (!empty($projectData['pays'])) {
            $summary .= ' (' . $projectData['pays'] . ')';
        }
        
        // Ajouter l'année si disponible
        if (!empty($projectData['referenceAnneeAchevement'])) {
            $summary .= ' - ' . $projectData['referenceAnneeAchevement'];
        }
        
        // Ajouter un point final
        $summary .= '.';
        
        return $summary;
    }
}