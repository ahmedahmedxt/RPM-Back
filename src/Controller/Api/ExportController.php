<?php

namespace App\Controller\Api;

use App\Repository\AppelOffresRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/export', name: 'api_export_')]
class ExportController extends AbstractController
{
    private AppelOffresRepository $appelOffresRepository;

    public function __construct(AppelOffresRepository $appelOffresRepository)
    {
        $this->appelOffresRepository = $appelOffresRepository;
    }

    /**
     * 📥 Export Excel d'un appel d'offre
     */
    #[Route('/excel/appeloffre/{id}', name: 'excel_appeloffre', methods: ['GET'])]
    public function exportAppelOffreToExcel(int $id): Response
    {
        $appelOffre = $this->appelOffresRepository->find($id);

        if (!$appelOffre) {
            return $this->json(['error' => 'Appel d\'offre non trouvé'], 404);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // En-têtes
        $sheet->setCellValue('A1', 'DÉTAILS APPEL D\'OFFRE');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->mergeCells('A1:B1');

        // Données
        $row = 3;
        $data = [
            ['Référence', $appelOffre->getAppelOffreDevis() ?? 'N/A'],
            ['Type', $appelOffre->getAppelOffresTypeId()?->getAppelOffresTypeLibelle() ?? 'N/A'],
            ['Objet', $appelOffre->getAppelOffresObjet() ?? 'N/A'],
            ['Organisme', $appelOffre->getAppelOffresOrganismeDemandeurId()?->getOrganismeDemandeurLibelle() ?? 'N/A'],
            ['Pays', $appelOffre->getAppelOffresPaysId()?->getPaysLibelle() ?? 'N/A'],
            ['Date limite', $appelOffre->getAppelOffresDateLimiteRemise()?->format('d/m/Y') ?? 'N/A'],
            ['Heure limite', $appelOffre->getAppelOffresHeureLimiteRemise() ?? 'N/A'],
            ['État', $appelOffre->getAppelOffresEtat() ?? 'N/A'],
            ['Participation', $appelOffre->getAppelOffresParticipation() ? 'Oui' : 'Non'],
            ['Date participation', $appelOffre->getAppelOffresDateParticipation()?->format('d/m/Y') ?? 'N/A'],
            ['Type participation', $appelOffre->getAppelOffresTypeParticipationId() ?? 'N/A'],
            ['Caution bancaire', $appelOffre->getAppelOffresCautionBancaire() ?? '0'],
            ['Moyen livraison', $appelOffre->getAppelOffresMoyenLivraisonId()?->getMoyenLivraisonLibelle() ?? 'N/A'],
            ['Cahier retiré', $appelOffre->getAppelOffresCCRetire() ? 'Oui' : 'Non'],
            ['Lien annonce', $appelOffre->getAppelOffresLienAnnonce() ?? 'N/A'],
            ['Classement', ($appelOffre->getAppelOffresResultatRang() ?? 'N/A') . ' / ' . ($appelOffre->getAppelOffresResultatRangTotal() ?? 'N/A')],
            ['Remarques', $appelOffre->getAppelOffresRemarque() ?? 'N/A'],
            ['Année', $appelOffre->getAppelOffreAnnee() ?? 'N/A'],
        ];

        foreach ($data as $rowData) {
            $sheet->setCellValue('A' . $row, $rowData[0]);
            $sheet->setCellValue('B' . $row, $rowData[1]);
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
        }

        // Auto-size colonnes
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);

        // Générer le fichier
        $writer = new Xlsx($spreadsheet);
        $filename = 'AO_' . ($appelOffre->getAppelOffreDevis() ?? $id) . '_' . date('Ymd_His') . '.xlsx';

        $response = new StreamedResponse(function() use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    /**
     * 📥 Export PDF d'un appel d'offre (placeholder)
     */
    #[Route('/pdf/appeloffre/{id}', name: 'pdf_appeloffre', methods: ['GET'])]
    public function exportAppelOffreToPdf(int $id): Response
    {
        $appelOffre = $this->appelOffresRepository->find($id);

        if (!$appelOffre) {
            return $this->json(['error' => 'Appel d\'offre non trouvé'], 404);
        }

        // TODO: Implémenter l'export PDF
        return $this->json(['message' => 'Export PDF à implémenter'], 501);
    }
}