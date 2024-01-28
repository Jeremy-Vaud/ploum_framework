<?php
/*
 * CLI
 */
require __DIR__ ."/../app/vendor/autoload.php";
require __DIR__ ."/../app/settings/global.php";

App\DotEnv::load();

echo "\n";
if (isset($argv[1])) {
    switch ($argv[1]) {
        case 'help':
            echo "Liste des commandes:\n";
            echo "migrate : Génére ou modifie les tables de la BDD\n";
            echo "export-DB : Expote la Base de données\n";
            echo "create-superAdmin : Créer un compte superAdmin\n";
            echo "create-admin-pannel : Génére un fichier JSON pour la construction du panneau d'administration\n";
            echo "download-fonts : Télécharge des polices depuis Google Fonts\n";
            break;

        case 'migrate':
            $migration = new Dev\Migration;
            $migration->migrate();
            break;

        case 'export-DB':
            $migration = new Dev\Migration;
            $migration->export();
            break;

        case "create-superAdmin":
            $user = new App\User;
            $user->set("role", "superAdmin");
            $validEmail = false;
            $validPassword = false;
            foreach (["nom", "prenom", "email", "password"] as $key) {
                $valid = false;
                while (!$valid) {
                    echo "$key : ";
                    $handle = fopen("php://stdin", "r");
                    $val = trim(fgets($handle));
                    if ($user->checkData([$key => $val]) === []) {
                        $user->set($key, $val);
                        $valid = true;
                    } else {
                        echo "$key non valide\n";
                    }
                }
            }
            if ($user->insert()) {
                echo "Nouvel admin crée\n\n";
            } else {
                echo "Une erreur est survenue";
            }
            break;

        case 'create-admin-pannel':
            $adminPannel = new Dev\AdminPannel;
            $adminPannel->generate();
            break;

        case 'download-fonts':
            $gfd = new GFontsDownloader\GFontsDownloader\GFontsDownloader($FONTS, "public/assets/fonts");
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
