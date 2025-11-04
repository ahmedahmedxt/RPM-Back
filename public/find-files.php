<?php
header('Content-Type: text/html; charset=UTF-8');
echo '<h1>🔍 Recherche fichiers AppelOffre</h1><pre>';

chdir(__DIR__ . '/..');

// Fonction pour chercher récursivement
function findFiles($dir, $pattern) {
    $results = [];
    if (!is_dir($dir)) return $results;
    
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') continue;
        
        $path = $dir . '/' . $file;
        if (is_dir($path)) {
            $results = array_merge($results, findFiles($path, $pattern));
        } elseif (stripos($file, $pattern) !== false) {
            $results[] = $path;
        }
    }
    return $results;
}

// Chercher tous les fichiers contenant "AppelOffre"
echo "=== TOUS LES FICHIERS CONTENANT 'AppelOffre' ===\n\n";
$found = findFiles('src', 'AppelOffre');

foreach ($found as $file) {
    $size = filesize($file);
    $modified = date('Y-m-d H:i:s', filemtime($file));
    
    // Marquer les suspects
    $status = '';
    if (strpos($file, 'AppelOffres') !== false) {
        $status = ' ✅ BON (avec s)';
    } else {
        $status = ' ❌ ANCIEN (sans s) - À SUPPRIMER !';
    }
    
    echo "$file\n";
    echo "  Taille: $size bytes\n";
    echo "  Modifié: $modified\n";
    echo "  Statut: $status\n\n";
}

// Lister spécifiquement Entity/
echo "\n=== CONTENU EXACT DE src/Entity/ ===\n";
$entities = scandir('src/Entity');
foreach ($entities as $e) {
    if ($e != '.' && $e != '..' && !is_dir('src/Entity/' . $e)) {
        if (stripos($e, 'appeloffre') !== false) {
            echo "$e";
            if (strpos($e, 'AppelOffres') === false) {
                echo " ❌ À SUPPRIMER";
            }
            echo "\n";
        }
    }
}

echo '</pre><p style="color:red;">SUPPRIMEZ CE FICHIER APRÈS !</p>';
exit;
?>