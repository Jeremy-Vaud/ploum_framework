<?php
/*
 * CLI
 */
require 'vendor/autoload.php';

if(isset($argv[1])) {
    switch ($argv[1]){

        case 'help':
            echo "\n";
            echo "migrate : Génére ou modifie les tables de la BDD\n";
            echo "create-admin-pannel : Génére un fichier JSON pour la construction du panneau d'administration\n";
            echo "\n";
            break;

        case 'migrate':
            $migration = new App\Migration;
            $migration->migrate();
            break;

        case 'create-admin-pannel':
            $adminPannel = new App\AdminPannel;
            $adminPannel->generate();
            break;
        
        default:
            echo "Commande inconnue";

    }
} else {
    echo "Aucune commande";
}