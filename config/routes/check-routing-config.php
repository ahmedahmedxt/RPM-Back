<?php
echo '<h1>🔍 Configuration Routing</h1><pre>';
chdir(__DIR__ . '/..');
$php = '/opt/plesk/php/8.3/bin/php';

// 1. Vérifier routes.yaml
echo "=== config/routes.yaml ===\n";
if (file_exists('config/routes.yaml')) {
    echo file_get_contents('config/routes.yaml');
} else {
    echo "❌ Fichier manquant\n";
}

echo "\n\n=== config/routes/attributes.yaml ===\n";
if (file_exists('config/routes/attributes.yaml')) {
    echo file_get_contents('config/routes/attributes.yaml');
} else {
    echo "❌ Fichier manquant\n";
}

// 2. Vérifier toutes les routes (pas seulement grep)
echo "\n\n=== TOUTES LES ROUTES (filtré api_appel_offres) ===\n";
$allRoutes = shell_exec("$php bin/console debug:router 2>&1");
$lines = explode("\n", $allRoutes);
foreach ($lines as $line) {
    if (stripos($line, 'api_appel_offres') !== false) {
        echo $line . "\n";
    }
}

echo '</pre><p style="color:red;">SUPPRIMEZ!</p>';
?>