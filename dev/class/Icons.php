<?php

namespace Dev;

/**
 * Classe permettant de générer le fichier dev/adminSrc/icons.js pour l'export des icônes du panneau d'administration
 * 
 * @author  Jérémy Vaud
 */
final class Icons {
    
    /**
     * Crée le fichier dev/adminSrs/icons.js
     *
     * @return void
     */
    public static function importIcons() {
        try {
            $file = fopen("dev/adminSrc/icons.js", "w");
            $text = self::ImportIconsText();
            if (!$text) {
                throw new \Exception("Aucune icône trouvée");
            }
            fwrite($file, $text);
            fclose($file);
            echo "Fichier icons.js crée";
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Liste les différents icones
     *
     * @return array
     */
    private static function getIcons() {
        $data = (new \App\AdminPannel)->get();
        $icons = [];
        foreach ($data["pages"] as $page) {
            if (isset($page["icon"])) {
                $icons[] = $page["icon"];
            }
        }

        return $icons;
    }

    /**
     * Crée le texte du fichier dev/adminSrs/icons.js
     *
     * @return string|false
     */
    private static function ImportIconsText() {
        $icons = self::getIcons();
        if ($icons !== []) {
            $string = "import { " . implode(", ", $icons) . " } from '@fortawesome/free-solid-svg-icons'\nexport default { ";
            foreach ($icons as $key => $icon) {
                if ($key !== 0) {
                    $string .= ", ";
                }
                $string .= "'$icon':$icon";
            }
            $string .= " }";
            return $string;
        }
        return false;
    }
}
