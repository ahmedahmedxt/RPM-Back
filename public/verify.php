<?php
header('Content-Type: text/html; charset=UTF-8');
echo '<h1>✅ Vérification finale</h1><pre>';
chdir(__DIR__ . '/..');
$php = '/opt/plesk/php/8.3/bin/php';

// 1. Vérifier que les ANCIENS fichiers n'existent PLUS
echo "=== 1. FICHIERS ANCIENS (doivent être supprimés) ===\n";
$oldFiles = [
    'src/Entity/AppelOffrePartenaire.php',
    'src/Controller/Api/AppelOffreTypeController.php',
    'src/Repository/AppelOffrePartenaireRepository.php',
    'src/Repository/AppelOffreRepository.php',
    'src/Repository/AppelOffreTypeRepository.php'
];

foreach ($oldFiles as $file) {
    if (file_exists($file)) {
        echo "❌ $file EXISTE ENCORE - SUPPRIMEZ-LE !\n";
    } else {
        echo "✅ $file supprimé\n";
    }
}

// 2. Vérifier que les NOUVEAUX fichiers existent
echo "\n=== 2. FICHIERS NOUVEAUX (doivent exister) ===\n";
$newFiles = [
    'src/Entity/AppelOffres.php',
    'src/Entity/AppelOffresPartenaire.php',
    'src/Controller/Api/AppelOffresController.php',
    'src/Repository/AppelOffresRepository.php'
];

foreach ($newFiles as $file) {
    if (file_exists($file)) {
        echo "✅ $file existe (" . filesize($file) . " bytes)\n";
    } else {
        echo "❌ $file MANQUANT !\n";
    }
}

// 3. Nettoyer le cache
echo "\n=== 3. NETTOYAGE CACHE ===\n";
shell_exec("rm -rf var/cache/*");
$cache = shell_exec("$php bin/console cache:clear --env=prod 2>&1");
if (stripos($cache, 'error') !== false || stripos($cache, 'cannot be found') !== false) {
    echo "❌ ERREUR lors du cache clear:\n";
    echo substr($cache, 0, 500);
} else {
    echo "✅ Cache nettoyé sans erreur\n";
}

// 4. Vérifier les routes
echo "\n=== 4. ROUTES CHARGÉES ===\n";
$routes = shell_exec("$php bin/console debug:router | grep 'api_appel_offres_' 2>&1");
$requiredRoutes = ['create', 'update', 'delete', 'index', 'show'];
foreach ($requiredRoutes as $route) {
    if (stripos($routes, $route) !== false) {
        echo "✅ Route $route trouvée\n";
    } else {
        echo "❌ Route $route MANQUANTE\n";
    }
}

echo "\n\nDétail:\n$routes";

echo '</pre><h2>✅ Vérification terminée</h2>';
echo '<p style="color:red;">SUPPRIMEZ CE FICHIER !</p>';
exit;
?>