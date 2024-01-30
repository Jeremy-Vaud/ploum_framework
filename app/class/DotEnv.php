<?php

namespace App;

/**
 * Classe pour charger les variables d'environnement
 * 
 * @author  Jérémy Vaud
 */
class DotEnv {

    /**
     * Charge les variables dans le tableau $_ENV depuis le fichier .env et charge les fichiers de paramètres du dossier app/settings/
     *
     * @return void
     */
    public static function load() {
        $missingFiles = "";
        if (!is_readable(__DIR__ . "/../.env")) {
            $missingFiles .= "<p>Fichier des variables d'environnement introuvable ou illisible.</p>";
        }
        if (!file_exists(__DIR__ . "/../settings/global.php")) {
            $missingFiles .= "<p>Fichier des paramètres globaux introuvable.</p>";
        }
        if (!file_exists(__DIR__ . "/../settings/routes.php")) {
            $missingFiles .= "<p>Fichier des paramètres du routeur introuvable.</p>";
        }
        if ($missingFiles === "") {
            self::loadEnvFile();
            require_once __DIR__ . "/../settings/global.php";
            require_once __DIR__ . "/../settings/routes.php";
        } else {
            echo "<h1>Fichier(s) de paramétrage manquant(s)</h1>";
            echo $missingFiles;
            die();
        }
    }
    
    /**
     * Charge les variables dans le tableau $_ENV depuis le fichier .env
     *
     * @return void
     */
    private static function loadEnvFile() {
        $lines = file(__DIR__ . "/../.env", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if (ctype_digit($value)) {
                $value = intval($value);
            } elseif ($value === "false") {
                $value = false;
            } elseif ($value === "true") {
                $value = true;
            } elseif ($value === "null") {
                $value = null;
            }
            $_ENV[$name] = $value;
        }
    }
}
