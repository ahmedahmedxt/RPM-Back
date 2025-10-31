<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\AppelOffres;
use App\Repository\AppelOffresRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use DateTime;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EtatController extends AbstractController
{
    #[Route('/api/etat/appel-offres/{id}', name: 'api_appel_offres_rapport', methods: ['GET'])]
    public function generatePdf(int $id, AppelOffresRepository $appelOffresRepository): Response
    {
        $appelOffres = $appelOffresRepository->find($id);

        if (!$appelOffres) {
            return new JsonResponse(['message' => 'Appel d\'offres non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dateCreationPDF = new DateTime();
        $currentDate = new \DateTime();
        $dateRemise = $appelOffres->getAppelOffreDateRemise();
        $dateRemisePassed = $dateRemise && $dateRemise < $currentDate ? 'Oui' : 'Non';
        $participation = $appelOffres->getAppelOffresParticipation() ? 'Oui' : 'Non';

        $typeLibelle = $appelOffres->getAppelOffresTypeId() 
            ? $appelOffres->getAppelOffresTypeId()->getAppelOffresTypeLibelle() 
            : '-';
        
        $moyenLibelle = $appelOffres->getAppelOffresMoyenLivraisonId() 
            ? $appelOffres->getAppelOffresMoyenLivraisonId()->getMoyenLivraisonLibelle() 
            : '-';
        
        $organismeLibelle = $appelOffres->getAppelOffresOrganismeDemandeurId() 
            ? $appelOffres->getAppelOffresOrganismeDemandeurId()->getOrganismeDemandeurLibelle() 
            : '-';
        
        $paysLibelle = $appelOffres->getAppelOffresPaysId() 
            ? $appelOffres->getAppelOffresPaysId()->getPaysLibelle() 
            : '-';

        $html = '
        <html>
            <head>
                <meta charset="UTF-8">
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 20px;
                    }
                    h1 {
                        color: #358DCC;
                        text-align: center;
                        margin-bottom: 20px;
                        border-bottom: 2px solid #333;
                        padding-bottom: 10px;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    th, td {
                        border: 1px solid #ddd;
                        padding: 8px;
                        text-align: left;
                    }
                    th {
                        background-color: #f2f2f2;
                    }
                </style>
            </head>
            <body>
            
            <p><strong>Date de création :</strong> ' . $dateCreationPDF->format('Y-m-d H:i:s') . '</p>
            <p><strong>Date de remise passée :</strong> ' . $dateRemisePassed .'</p>
            
    
                <h1>État de l\'appel d\'offres</h1>
                <table>
                   
                    <tr>
                        <th>Devis</th>
                        <td>' . ($appelOffres->getAppelOffreDevis() ?? '-') . '</td>
                    </tr>
                    <tr>
                        <th>Objet</th>
                        <td>' . ($appelOffres->getAppelOffresObjet() ?? '-') . '</td>
                    </tr>
                    <tr>
                        <th>Date de remise</th>
                        <td>' . ($appelOffres->getAppelOffreDateRemise() ? $appelOffres->getAppelOffreDateRemise()->format('Y-m-d') : '-') . '</td>
                    </tr>
                    <tr>
                        <th>Retiré</th>
                        <td>' . ($appelOffres->getAppelOffresCCRetire() ? 'Oui' : 'Non') . '</td>
                    </tr>
                    <tr>
                        <th>Participation</th>
                        <td>' . $participation . '</td>
                    </tr>
                    <tr>
                        <th>État</th>
                        <td>' . ($appelOffres->getAppelOffresEtat() ?? '-') . '</td>
                    </tr>
                    <tr>
                        <th>Type</th>
                        <td>' . $typeLibelle . '</td>
                    </tr>
                    <tr>
                        <th>Moyen de livraison</th>
                        <td>' . $moyenLibelle . '</td>
                    </tr>
                    <tr>
                        <th>Organisme demandeur</th>
                        <td>' . $organismeLibelle . '</td>
                    </tr>
                    <tr>
                        <th>Pays</th>
                        <td>' . $paysLibelle . '</td>
                    </tr>
                </table>
            </body>
        </html>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();

        return new Response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="appel_offres_' . $id . '.pdf"'
        ]);
    }

    #[Route('/api/export/excel/appeloffres/{id}', name: 'api_export_excel_appeloffres', methods: ['GET'])]
    public function exportToExcel(int $id, AppelOffresRepository $appelOffresRepository): Response
    {
        $appelOffres = $appelOffresRepository->find($id);

        if (!$appelOffres) {
            return new JsonResponse(['message' => 'Appel d\'offres non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'ÉTAT DE L\'APPEL D\'OFFRES');
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'Date de création :');
        $sheet->setCellValue('B2', (new DateTime())->format('Y-m-d H:i:s'));

        $row = 4;
        $data = [
            ['Champ', 'Valeur'],
            ['Devis', $appelOffres->getAppelOffreDevis() ?? '-'],
            ['Objet', $appelOffres->getAppelOffresObjet() ?? '-'],
            ['Date de remise', $appelOffres->getAppelOffreDateRemise() ? $appelOffres->getAppelOffreDateRemise()->format('Y-m-d') : '-'],
            ['Retiré', $appelOffres->getAppelOffresCCRetire() ? 'Oui' : 'Non'],
            ['Participation', $appelOffres->getAppelOffresParticipation() ? 'Oui' : 'Non'],
            ['État', $appelOffres->getAppelOffresEtat() ?? '-'],
            ['Type', $appelOffres->getAppelOffresTypeId() ? $appelOffres->getAppelOffresTypeId()->getAppelOffresTypeLibelle() : '-'],
            ['Moyen de livraison', $appelOffres->getAppelOffresMoyenLivraisonId() ? $appelOffres->getAppelOffresMoyenLivraisonId()->getMoyenLivraisonLibelle() : '-'],
            ['Organisme demandeur', $appelOffres->getAppelOffresOrganismeDemandeurId() ? $appelOffres->getAppelOffresOrganismeDemandeurId()->getOrganismeDemandeurLibelle() : '-'],
            ['Pays', $appelOffres->getAppelOffresPaysId() ? $appelOffres->getAppelOffresPaysId()->getPaysLibelle() : '-']
        ];

        foreach ($data as $rowIndex => $rowData) {
            $sheet->setCellValue('A' . ($row + $rowIndex), $rowData[0]);
            $sheet->setCellValue('B' . ($row + $rowIndex), $rowData[1]);
            
            if ($rowIndex === 0) {
                $sheet->getStyle('A' . ($row + $rowIndex) . ':B' . ($row + $rowIndex))
                    ->getFont()->setBold(true);
                $sheet->getStyle('A' . ($row + $rowIndex) . ':B' . ($row + $rowIndex))
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');
            }
        }

        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(50);

        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A4:B' . ($row + count($data) - 1))->applyFromArray($styleArray);

        $writer = new Xlsx($spreadsheet);
        
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return new Response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="appel_offres_' . $id . '_' . date('Y-m-d') . '.xlsx"'
        ]);
    }
}