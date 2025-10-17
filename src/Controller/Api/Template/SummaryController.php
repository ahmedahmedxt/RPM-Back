<?php

namespace App\Controller\Api\Template;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/summary')]
class SummaryController extends AbstractController
{
    #[Route('/extract', name: 'api_summary_extract', methods: ['POST'])]
    public function extractSummary(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $text = $data['text'] ?? '';
        $maxLength = $data['maxLength'] ?? 200;
        
        if (empty($text)) {
            return new JsonResponse(['error' => 'Text is required'], 400);
        }
        
        // Logique d'extraction simple
        $cleanText = strip_tags($text);
        $cleanText = preg_replace('/\s+/', ' ', $cleanText);
        $cleanText = trim($cleanText);
        
        if (strlen($cleanText) <= $maxLength) {
            $summary = $cleanText;
        } else {
            $truncated = substr($cleanText, 0, $maxLength);
            $lastSpace = strrpos($truncated, ' ');
            if ($lastSpace !== false) {
                $summary = substr($cleanText, 0, $lastSpace) . '...';
            } else {
                $summary = substr($cleanText, 0, $maxLength) . '...';
            }
        }
        
        // Extraction de mots-clés simple
        $words = preg_split('/\s+/', strtolower($cleanText));
        $stopWords = ['le', 'la', 'les', 'de', 'du', 'des', 'un', 'une', 'et', 'ou', 'mais', 'donc', 'or', 'ni', 'car'];
        $words = array_filter($words, function($word) use ($stopWords) {
            return strlen($word) > 2 && !in_array($word, $stopWords);
        });
        $keywords = array_slice(array_keys(array_count_values($words)), 0, 10);
        
        return new JsonResponse([
            'summary' => $summary,
            'keywords' => $keywords,
            'originalLength' => strlen($text),
            'summaryLength' => strlen($summary)
        ]);
    }

    #[Route('/generate-project', name: 'api_summary_generate_project', methods: ['POST'])]
    public function generateProjectSummary(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $summary = '';
        
        if (!empty($data['referenceTitre'])) {
            $summary .= $data['referenceTitre'];
        }
        
        if (!empty($data['client'])) {
            $summary .= ' - ' . $data['client'];
        }
        
        if (!empty($data['pays'])) {
            $summary .= ' (' . $data['pays'] . ')';
        }
        
        if (!empty($data['referenceAnneeAchevement'])) {
            $summary .= ' - ' . $data['referenceAnneeAchevement'];
        }
        
        $summary .= '.';
        
        return new JsonResponse(['summary' => $summary]);
    }
}