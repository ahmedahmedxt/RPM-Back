<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Projet;
use App\Repository\ProjetRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use DateTime;

class RapportController extends AbstractController
{
    private $projetRepository;

    public function __construct(ProjetRepository $projetRepository)
    {
        $this->projetRepository = $projetRepository;
    }

    #[Route('/image/{imageName}', name: 'serve_image', requirements: ['imageName' => '.+'])]
    public function serveImageAction(string $imageName): Response
    {
        $imagePath = $this->getParameter('kernel.project_dir') . '/public/images/' . $imageName;
   
        if (!file_exists($imagePath) || !is_readable($imagePath)) {
            throw $this->createNotFoundException('L\'image demandée n\'existe pas.');
        }
   
        return new Response(file_get_contents($imagePath), 200, [
            'Content-Type' => mime_content_type($imagePath),
        ]);
    }
   

    #[Route('/css/{cssName}', name: 'serve_css', requirements: ['cssName' => '.+'])]
    public function serveCssAction(string $cssName): Response
    {
        $cssPath = $this->getParameter('kernel.project_dir') . '/public/css/' . $cssName;

        if (!file_exists($cssPath) || !is_readable($cssPath)) {
            throw $this->createNotFoundException('Le fichier CSS demandé n\'existe pas.');
        }

        return new Response(file_get_contents($cssPath), 200, [
            'Content-Type' => 'text/css',
        ]);
    }

    #[Route('/rapport/{id}', name: 'app_rapport_id', requirements: ['id' => '\d+'])]
    public function index(Request $request, int $id): Response
    {
        $projet = $this->projetRepository->find($id);

        if (!$projet) {
            throw $this->createNotFoundException('Le projet avec l\'ID ' . $id . ' n\'existe pas.');
        }

        // Récupérer le lieu associé au projet
        $lieu = $projet->getLieu();

        // Récupérer le pays à partir du lieu
        $pays = $lieu ? $lieu->getPays() : '';

        $cssUrl = $this->generateUrl('serve_css', ['cssName' => 'pdf_styles.css'], UrlGeneratorInterface::ABSOLUTE_URL);
        $imageUrl = $this->generateUrl('serve_image', ['imageName' => 'xtensus-logo.png'], UrlGeneratorInterface::ABSOLUTE_URL);

        // Vérifier si la date de fin du projet est passée
        $dateFinProjet = $projet->getProjetDateAchevement();
        $dateActuelle = new DateTime();
        $projetTermine = $dateActuelle > $dateFinProjet;

        $dateCreationPDF = new DateTime();

        // Récupérer les catégories du projet
        $categories = [];
        foreach ($projet->getCategories() as $categorie) {
            $categories[] = [
                'id' => $categorie->getId(),
                'nom' => $categorie->getCategorieNom(), // Assurez-vous d'utiliser la bonne méthode pour récupérer le nom de la catégorie
            ];
        }

        // Générer le contenu HTML du rapport
        $html = '
        <html>
            <head>
                <meta charset="UTF-8">
                <link rel="stylesheet" href="' . $cssUrl . '">
                <style>
                    /* Vos styles CSS ici */
                    body {
                        font-family: Arial, sans-serif;
                        margin: 20px;
                    }
                   
                    h1 {
                        color:#358DCC;
                        text-align: center;
                        margin-bottom: 20px;
                        border-bottom: 2px solid #333;
                        padding-bottom: 10px;
                    }
                   
                    p {
                        font-size: 14px;
                        color: #555;
                        margin: 5px 0;
                    }
                   
                    .field {
                        border: 1px solid #ddd;
                        padding: 10px;
                        margin-bottom: 10px;
                        border-radius: 5px;
                        background-color: #f9f9f9;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                    }
                   
                    .field-label {
                        flex: 1;
                        font-weight: bold;
                    }
               
                    .field-value {
                        flex: 2;
                        text-align: center;
                        margin-top: -15px;
                    }
                    .field-value1 {
                        flex: 2;
                       
                        text-align: right;
                        margin-top: -15px;
                    }
               
                    .line {
                        border-bottom: 1px solid #ddd;
                        margin: 10px 0;
                    }
                   
                    /* Autres styles */
                </style>
            </head>
            <body>
                <p><strong>Date de création du rapport : </strong>' . $dateCreationPDF->format('Y-m-d H:i:s') . '</p>
                <p><strong>Status Projet : </strong>' . ($projetTermine ? 'terminé' : 'en cours') . '</p>
       
                <h1>Rapport du projet ' . $projet->getProjetLibelle() . '</h1>
               
                <div class="line"></div>
                <div class="field">
                    <div class="field-label">Référence du projet:</div>
                    <div class="field-value">' . $projet->getProjetReference() . '</div>
                </div>
               
                <div class="line"></div>
                <div class="field">
                    <div class="field-label">Client du projet:</div>
                    <div class="field-value">' . ($projet->getClient() ? $projet->getClient()->getPersonneContact() : '') . '</div>
                </div>
                <div class="line"></div>
                <div class="field">
                    <div class="field-label">Description du projet:</div>
                    <div class="field-value1">' . $projet->getProjetDescription() . '</div>
                </div>
               
                <div class="line"></div>
                <div class="field">
                    <div class="field-label">Date de démarrage:</div>
                    <div class="field-value">' . $projet->getProjetDateDemarrage()->format('Y-m-d') . '</div>
                </div>
                <div class="line"></div>
                <div class="field">
                    <div class="field-label">Date d\'achèvement:</div>
                    <div class="field-value">' . $projet->getProjetDateAchevement()->format('Y-m-d') . '</div>
                </div>
                <div class="line"></div>
                <div class="field">
                    <div class="field-label">URL fonctionnel:</div>
                    <div class="field-value">' . $projet->getProjetUrlFonctionnel() . '</div>
                </div>
                <div class="line"></div>
                <div class="field">
                    <div class="field-label">Description des services <br>effectivement rendus:</div>
                    <div class="field-value">' . $projet->getProjetDescriptionServiceEffectivementRendus() . '</div>
                </div>
                <div class="field">
                    <div class="field-label">Lieu du projet:</div>
                    <div class="field-value">' .  ($projet->getLieu() ? $projet->getLieu()->getLieuNom() : '') . '</div>
                </div>
                <div class="line"></div>
                <div class="field">
                    <div class="field-label">Pays du projet:</div>
                    <div class="field-value">' . ($pays ? $pays->getPaysNom() : '') . '</div>
                </div>';

        // Ajouter les catégories au rapport
        $categoriesString = '';
        foreach ($categories as $categorie) {
            $categoriesString .= $categorie['nom'] . ', ';
        }

        // Supprimer la virgule et l'espace en trop à la fin de la chaîne
        $categoriesString = rtrim($categoriesString, ', ');
        // Ajouter les catégories au rapport
        $html .= '<div class="field">
                    <div class="field-label">Catégories:</div>
                    <div class="field-value">' . $categoriesString . '</div>
                  </div>';

        // Terminer la construction du HTML
        $html .= '</body>
                    </html>';

        // Générer le PDF
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Récupérer le contenu du PDF et le retourner en réponse HTTP
        $output = $dompdf->output();

        return new Response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="projet_' . $id . '.pdf"'
        ]);
    }
}


