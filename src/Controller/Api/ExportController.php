<?php

namespace App\Controller\Api;

use App\Repository\AppelOffresRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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
     * 📥 Export Excel d'UN SEUL appel d'offre - SANS BIBLIOTHÈQUE
     */
    #[Route('/excel/appeloffre/{id}', name: 'excel_appeloffre', methods: ['GET'])]
    public function exportAppelOffreToExcel(int $id): Response
    {
        try {
            $appelOffre = $this->appelOffresRepository->find($id);

            if (!$appelOffre) {
                return $this->json(['error' => 'Appel d\'offre non trouvé'], 404);
            }

            // Fonction helper pour valeurs sécurisées
            $getVal = function($callback, $default = 'N/A') {
                try {
                    $result = $callback();
                    return $result !== null ? $result : $default;
                } catch (\Exception $e) {
                    return $default;
                }
            };

            // Créer le contenu Excel en XML (format compatible Excel)
            $excelContent = '<?xml version="1.0" encoding="UTF-8"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">
 <Styles>
  <Style ss:ID="header">
   <Font ss:Bold="1" ss:Size="14"/>
   <Interior ss:Color="#808000" ss:Pattern="Solid"/>
   <Font ss:Color="#FFFFFF"/>
  </Style>
  <Style ss:ID="label">
   <Font ss:Bold="1"/>
   <Interior ss:Color="#E0E0E0" ss:Pattern="Solid"/>
  </Style>
 </Styles>
 <Worksheet ss:Name="Appel Offre">
  <Table>
   <Column ss:Width="180"/>
   <Column ss:Width="350"/>
   <Row ss:StyleID="header">
    <Cell ss:MergeAcross="1">
     <Data ss:Type="String">DÉTAILS APPEL D\'OFFRE - ' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffreDevis())) . '</Data>
    </Cell>
   </Row>
   <Row/>
   <Row>
    <Cell ss:StyleID="label"><Data ss:Type="String">Référence</Data></Cell>
    <Cell><Data ss:Type="String">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffreDevis())) . '</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="label"><Data ss:Type="String">Type</Data></Cell>
    <Cell><Data ss:Type="String">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresTypeId()?->getAppelOffresTypeLibelle())) . '</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="label"><Data ss:Type="String">Objet</Data></Cell>
    <Cell><Data ss:Type="String">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresObjet())) . '</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="label"><Data ss:Type="String">Organisme Demandeur</Data></Cell>
    <Cell><Data ss:Type="String">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresOrganismeDemandeurId()?->getOrganismeDemandeurLibelle())) . '</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="label"><Data ss:Type="String">Pays</Data></Cell>
    <Cell><Data ss:Type="String">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresPaysId()?->getPaysLibelle())) . '</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="label"><Data ss:Type="String">Date Limite Remise</Data></Cell>
    <Cell><Data ss:Type="String">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresDateLimiteRemise()?->format('d/m/Y'))) . '</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="label"><Data ss:Type="String">Heure Limite</Data></Cell>
    <Cell><Data ss:Type="String">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresHeureLimiteRemise()?->format('H:i'))) . '</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="label"><Data ss:Type="String">État</Data></Cell>
    <Cell><Data ss:Type="String">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresEtat())) . '</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="label"><Data ss:Type="String">Participation</Data></Cell>
    <Cell><Data ss:Type="String">' . ($appelOffre->getAppelOffresParticipation() ? 'Oui' : 'Non') . '</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="label"><Data ss:Type="String">Date Participation</Data></Cell>
    <Cell><Data ss:Type="String">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresDateParticipation()?->format('d/m/Y'))) . '</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="label"><Data ss:Type="String">Type Participation</Data></Cell>
    <Cell><Data ss:Type="String">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresTypeParticipationId())) . '</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="label"><Data ss:Type="String">Caution Bancaire</Data></Cell>
    <Cell><Data ss:Type="String">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresCautionBancaire(), '0')) . '</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="label"><Data ss:Type="String">Moyen de Livraison</Data></Cell>
    <Cell><Data ss:Type="String">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresMoyenLivraisonId()?->getMoyenLivraisonLibelle())) . '</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="label"><Data ss:Type="String">Cahier des Charges Retiré</Data></Cell>
    <Cell><Data ss:Type="String">' . ($appelOffre->getAppelOffresCCRetire() ? 'Oui' : 'Non') . '</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="label"><Data ss:Type="String">Lien Annonce</Data></Cell>
    <Cell><Data ss:Type="String">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresLienAnnonce())) . '</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="label"><Data ss:Type="String">Classement</Data></Cell>
    <Cell><Data ss:Type="String">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresResultatRang()) . ' / ' . $getVal(fn() => $appelOffre->getAppelOffresResultatRangTotal())) . '</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="label"><Data ss:Type="String">Remarques</Data></Cell>
    <Cell><Data ss:Type="String">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresRemarque())) . '</Data></Cell>
   </Row>
   <Row>
    <Cell ss:StyleID="label"><Data ss:Type="String">Année</Data></Cell>
    <Cell><Data ss:Type="String">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffreAnnee())) . '</Data></Cell>
   </Row>
  </Table>
 </Worksheet>
</Workbook>';

            $filename = 'AO_' . ($appelOffre->getAppelOffreDevis() ?? $id) . '_' . date('Ymd_His') . '.xls';

            $response = new Response($excelContent);
            $response->headers->set('Content-Type', 'application/vnd.ms-excel');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
            $response->headers->set('Cache-Control', 'max-age=0');
            $response->headers->set('Access-Control-Expose-Headers', 'Content-Disposition');

            return $response;

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur génération Excel',
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * 📥 Export de TOUS les appels d'offres en Excel
     */
    #[Route('/excel/all-appeloffres', name: 'excel_all', methods: ['GET'])]
    public function exportAllAppelOffres(): Response
    {
        try {
            $appelOffres = $this->appelOffresRepository->findAll();

            $excelContent = '<?xml version="1.0" encoding="UTF-8"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">
 <Styles>
  <Style ss:ID="header">
   <Font ss:Bold="1" ss:Size="12"/>
   <Interior ss:Color="#4472C4" ss:Pattern="Solid"/>
   <Font ss:Color="#FFFFFF"/>
  </Style>
 </Styles>
 <Worksheet ss:Name="Appels Offres">
  <Table>
   <Row ss:StyleID="header">
    <Cell><Data ss:Type="String">Référence</Data></Cell>
    <Cell><Data ss:Type="String">Type</Data></Cell>
    <Cell><Data ss:Type="String">Objet</Data></Cell>
    <Cell><Data ss:Type="String">Organisme</Data></Cell>
    <Cell><Data ss:Type="String">Pays</Data></Cell>
    <Cell><Data ss:Type="String">Date limite</Data></Cell>
    <Cell><Data ss:Type="String">État</Data></Cell>
    <Cell><Data ss:Type="String">Participation</Data></Cell>
    <Cell><Data ss:Type="String">Année</Data></Cell>
   </Row>';

            foreach ($appelOffres as $ao) {
                $excelContent .= '
   <Row>
    <Cell><Data ss:Type="String">' . htmlspecialchars($ao->getAppelOffreDevis() ?? 'N/A') . '</Data></Cell>
    <Cell><Data ss:Type="String">' . htmlspecialchars($ao->getAppelOffresTypeId()?->getAppelOffresTypeLibelle() ?? 'N/A') . '</Data></Cell>
    <Cell><Data ss:Type="String">' . htmlspecialchars($ao->getAppelOffresObjet() ?? 'N/A') . '</Data></Cell>
    <Cell><Data ss:Type="String">' . htmlspecialchars($ao->getAppelOffresOrganismeDemandeurId()?->getOrganismeDemandeurLibelle() ?? 'N/A') . '</Data></Cell>
    <Cell><Data ss:Type="String">' . htmlspecialchars($ao->getAppelOffresPaysId()?->getPaysLibelle() ?? 'N/A') . '</Data></Cell>
    <Cell><Data ss:Type="String">' . htmlspecialchars($ao->getAppelOffresDateLimiteRemise()?->format('d/m/Y') ?? 'N/A') . '</Data></Cell>
    <Cell><Data ss:Type="String">' . htmlspecialchars($ao->getAppelOffresEtat() ?? 'N/A') . '</Data></Cell>
    <Cell><Data ss:Type="String">' . ($ao->getAppelOffresParticipation() ? 'Oui' : 'Non') . '</Data></Cell>
    <Cell><Data ss:Type="String">' . htmlspecialchars($ao->getAppelOffreAnnee() ?? 'N/A') . '</Data></Cell>
   </Row>';
            }

            $excelContent .= '
  </Table>
 </Worksheet>
</Workbook>';

            $filename = 'AppelsOffres_' . date('Ymd_His') . '.xls';

            $response = new Response($excelContent);
            $response->headers->set('Content-Type', 'application/vnd.ms-excel');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
            $response->headers->set('Cache-Control', 'max-age=0');
            $response->headers->set('Access-Control-Expose-Headers', 'Content-Disposition');

            return $response;

        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 📥 Export PDF d'un appel d'offre (HTML imprimable)
     */
    #[Route('/pdf/appeloffre/{id}', name: 'pdf_appeloffre', methods: ['GET'])]
    public function exportAppelOffreToPdf(int $id): Response
    {
        try {
            $appelOffre = $this->appelOffresRepository->find($id);

            if (!$appelOffre) {
                return $this->json(['error' => 'Appel d\'offre non trouvé'], 404);
            }

            // Fonction helper
            $getVal = function($callback, $default = 'N/A') {
                try {
                    $result = $callback();
                    return $result !== null ? $result : $default;
                } catch (\Exception $e) {
                    return $default;
                }
            };

            $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Appel d\'Offre - ' . htmlspecialchars($appelOffre->getAppelOffreDevis() ?? $id) . '</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 30px; 
            color: #333;
        }
        h1 { 
            color: #808000; 
            border-bottom: 4px solid #808000; 
            padding-bottom: 10px;
            text-align: center;
        }
        .info-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        tr:nth-child(even) { 
            background: #f9f9f9; 
        }
        td { 
            padding: 12px; 
            border-bottom: 1px solid #ddd; 
        }
        .label { 
            font-weight: bold; 
            width: 35%; 
            background: #e9ecef;
            color: #495057;
        }
        .value {
            padding-left: 20px;
        }
        button { 
            background: #808000; 
            color: white; 
            padding: 12px 25px; 
            border: none; 
            cursor: pointer; 
            border-radius: 5px;
            font-size: 14px;
            margin-bottom: 20px;
        }
        button:hover {
            background: #606000;
        }
        @media print {
            button { display: none; }
        }
    </style>
</head>
<body>
    <button onclick="window.print()">
        🖨️ Imprimer / Enregistrer en PDF
    </button>
    
    <h1>DÉTAILS APPEL D\'OFFRE</h1>
    
    <div class="info-box">
        <strong>Référence :</strong> ' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffreDevis())) . '<br>
        <strong>Date d\'export :</strong> ' . date('d/m/Y H:i') . '
    </div>
    
    <table>
        <tr>
            <td class="label">Type</td>
            <td class="value">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresTypeId()?->getAppelOffresTypeLibelle())) . '</td>
        </tr>
        <tr>
            <td class="label">Objet</td>
            <td class="value">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresObjet())) . '</td>
        </tr>
        <tr>
            <td class="label">Organisme Demandeur</td>
            <td class="value">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresOrganismeDemandeurId()?->getOrganismeDemandeurLibelle())) . '</td>
        </tr>
        <tr>
            <td class="label">Pays</td>
            <td class="value">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresPaysId()?->getPaysLibelle())) . '</td>
        </tr>
        <tr>
            <td class="label">Date Limite Remise</td>
            <td class="value">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresDateLimiteRemise()?->format('d/m/Y'))) . '</td>
        </tr>
        <tr>
            <td class="label">Heure Limite</td>
            <td class="value">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresHeureLimiteRemise()?->format('H:i'))) . '</td>
        </tr>
        <tr>
            <td class="label">État</td>
            <td class="value"><strong style="color: #808000;">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresEtat())) . '</strong></td>
        </tr>
        <tr>
            <td class="label">Participation</td>
            <td class="value">' . ($appelOffre->getAppelOffresParticipation() ? 'Oui' : 'Non') . '</td>
        </tr>
        <tr>
            <td class="label">Date Participation</td>
            <td class="value">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresDateParticipation()?->format('d/m/Y'))) . '</td>
        </tr>
        <tr>
            <td class="label">Type Participation</td>
            <td class="value">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresTypeParticipationId())) . '</td>
        </tr>
        <tr>
            <td class="label">Caution Bancaire</td>
            <td class="value">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresCautionBancaire(), '0')) . '</td>
        </tr>
        <tr>
            <td class="label">Moyen de Livraison</td>
            <td class="value">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresMoyenLivraisonId()?->getMoyenLivraisonLibelle())) . '</td>
        </tr>
        <tr>
            <td class="label">Cahier des Charges Retiré</td>
            <td class="value">' . ($appelOffre->getAppelOffresCCRetire() ? 'Oui' : 'Non') . '</td>
        </tr>
        <tr>
            <td class="label">Lien Annonce</td>
            <td class="value">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresLienAnnonce())) . '</td>
        </tr>
        <tr>
            <td class="label">Classement</td>
            <td class="value">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresResultatRang()) . ' / ' . $getVal(fn() => $appelOffre->getAppelOffresResultatRangTotal())) . '</td>
        </tr>
        <tr>
            <td class="label">Remarques</td>
            <td class="value">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffresRemarque())) . '</td>
        </tr>
        <tr>
            <td class="label">Année</td>
            <td class="value">' . htmlspecialchars($getVal(fn() => $appelOffre->getAppelOffreAnnee())) . '</td>
        </tr>
    </table>
    
    <footer style="margin-top: 40px; text-align: center; color: #6c757d; font-size: 12px; border-top: 1px solid #ddd; padding-top: 20px;">
        Document généré le ' . date('d/m/Y à H:i') . '
    </footer>
</body>
</html>';

            $response = new Response($html);
            $response->headers->set('Content-Type', 'text/html; charset=UTF-8');

            return $response;

        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Erreur génération PDF',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}