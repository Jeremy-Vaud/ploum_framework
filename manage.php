<?php
/*
 * CLI
 */
require 'vendor/autoload.php';
require "settings/global.php";

echo "\n";
if (isset($argv[1])) {
    switch ($argv[1]) {
        case 'help':
            echo "Liste des commandes:\n";
            echo "migrate : Génére ou modifie les tables de la BDD\n";
            echo "export-DB : Expote la Base de données";
            echo "create-superAdmin 'email' 'password': Créer un compte superAdmin\n";
            echo "create-admin-pannel : Génére un fichier JSON pour la construction du panneau d'administration\n";
            echo "download-fonts : Télécharge des polices depuis Google Fonts\n";
            break;

        case 'migrate':
            $migration = new App\Migration;
            $migration->migrate();
            break;

        case 'export-DB':
            $migration = new App\Migration;
            $migration->export();
            break;

        case "create-superAdmin":
            if ($argc !== 4) {
                echo "Paramètres de la commande invalide\n";
                echo "Commande pour créer un superAdmin : php manage.php create-superAdmin 'email' 'password'\n";
            } else {
                $user = new App\User;
                $check = $user->checkData(["email" => $argv[2], "password" => $argv[3]]);
                if ($check !== []) {
                    foreach ($check as $key => $error) {
                        echo "Erreur $key : $error\n";
                    }
                } else {
                    $user->set("nom", "admin");
                    $user->set("prenom", "admin");
                    $user->set("email", $argv[2]);
                    $user->set("password", $argv[3]);
                    $user->set("role", "superAdmin");
                    if ($user->insert()) {
                        echo "Nouvel admin crée\n\n";
                    } else {
                        echo "Une erreur est survenue";
                    }
                }
            }
            break;

        case 'create-admin-pannel':
            $adminPannel = new App\AdminPannel;
            $adminPannel->generate();
            break;

        case 'download-fonts':
            $gfd = new GFontsDownloader\GFontsDownloader\GFontsDownloader($FONTS);
            $gfd->download();
            break;

        default:
            echo "Commande inconnue\n";
            echo "Utilisez 'php manage.php help' pour plus d'informations\n";
    }
} else {
    echo "Aucune commande\n";
    echo "Utilisez 'php manage.php help' pour plus d'informations\n";
}
echo "\n";
