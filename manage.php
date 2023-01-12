<?php
/*
 * CLI
 */
require 'vendor/autoload.php';

if(isset($argv[1])) {
    switch ($argv[1]){

        case 'migrate':
            $migration = new App\Migration;
            $migration->migrate();
            break;
        
        default:
            echo "Commande inconnue";

    }
} else {
    echo "Aucune commande";
}