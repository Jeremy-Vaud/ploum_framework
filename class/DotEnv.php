<?php

namespace App;

/**
 * Classe pour charger les variables d'environnement
 * 
 * @author  Jérémy Vaud
 */
class DotEnv {

    /**
     * Charge les variables dans le tableau $_ENV depuis le fichier .env
     *
     * @return void
     */
    public static function load() {
        if ($_ENV === []) {
            try {
                if (!is_readable(".env")) {
                    throw new \Exception("Fichier des variables d'environnement introuvable ou illisible");
                }
                $lines = file(".env", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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
            } catch (\Exception $e) {
                echo "<br>";
                echo "Exeption reçue : ", $e->getMessage();
                echo "<br>";
                echo "Ligne ", $e->getLine(), " ", $e->getFile();
                die();
            }
        }
    }
}
