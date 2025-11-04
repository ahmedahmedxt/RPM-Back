<?php
chdir(__DIR__ . '/..');
$php = '/opt/plesk/php/8.3/bin/php';
header('Content-Type: text/plain');

echo "Nettoyage...\n";
shell_exec("rm -rf var/cache/*");
echo shell_exec("$php bin/console cache:clear --env=prod 2>&1");
echo "\n\nRoutes:\n";
echo shell_exec("$php bin/console debug:router | grep 'create' 2>&1");
?>