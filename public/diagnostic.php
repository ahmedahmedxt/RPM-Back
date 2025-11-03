<?php
echo '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>';
echo '<h1>🔍 Diagnostic complet</h1><pre>';

chdir(__DIR__ . '/..');
$php = '/opt/plesk/php/8.3/bin/php';

// 1. Vérifier que le contrôleur existe
echo "=== 1. VÉRIFICATION FICHIER ===\n";
$file = 'src/Controller/Api/AppelOffresController.php';
if (file_exists($file)) {
    echo "✅ $file existe\n";
    echo "Taille: " . filesize($file) . " bytes\n";
    echo "Modifié: " . date('Y-m-d H:i:s', filemtime($file)) . "\n";
    
    // Lire les annotations de route
    $content = file_get_contents($file);
    preg_match_all('/#\[Route\(\'([^\']+)\'.*methods.*\[\'([^\']+)\'\]\)\]/', $content, $matches);
    echo "\nRoutes trouvées dans le fichier:\n";
    for ($i = 0; $i < count($matches[0]); $i++) {
        echo "  {$matches[2][$i]} {$matches[1][$i]}\n";
    }
} else {
    echo "❌ Fichier manquant !\n";
}

// 2. Nettoyer le cache
echo "\n=== 2. NETTOYAGE CACHE ===\n";
shell_exec("rm -rf var/cache/* 2>&1");
echo shell_exec("$php bin/console cache:clear --env=prod --no-warmup 2>&1");

// 3. Warmup du cache
echo "\n=== 3. WARMUP CACHE ===\n";
echo shell_exec("$php bin/console cache:warmup --env=prod 2>&1");

// 4. Lister les routes
echo "\n=== 4. ROUTES SYMFONY ===\n";
$routes = shell_exec("$php bin/console debug:router 2>&1");
if (strpos($routes, 'api_appel_offres_create') !== false) {
    echo "✅ Route CREATE trouvée !\n";
} else {
    echo "❌ Route CREATE manquante !\n";
}
echo shell_exec("$php bin/console debug:router | grep 'appelOffres' 2>&1");

// 5. Vérifier les erreurs
echo "\n=== 5. ERREURS RÉCENTES ===\n";
if (file_exists('var/log/prod.log')) {
    echo shell_exec('tail -20 var/log/prod.log 2>&1');
}

echo '</pre><h2>✅ Diagnostic terminé</h2>';
echo '<p style="color:red;">SUPPRIMEZ CE FICHIER !</p></body></html>';
?>