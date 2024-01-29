<?php

namespace Dev;

/**
 * Class permetant de générer le fichier 'adminSrc/data.json' qui sert à la génération du panneau d'administration
 * 
 * @author  Jérémy Vaud
 * @final
 */
final class AdminPannel {
    // Attributs
    protected $data = []; // Données du fichier 'adminSrc/data.json'

    /**
     * Initialistion
     *
     * @return void
     */
    public function __construct() {
        $GLOBALS["CLOUD"] ? $this->data["cloud"] = true :  $this->data["cloud"] = false;
        $this->findTables();
    }

    /**
     * Recherche dans les classes qui doivent aparaitre dans le panneau d'administration
     *
     * @return void
     */
    private function findTables() {
        $this->data["pages"] = [];
        foreach (scandir(__DIR__ . '/../../app/class') as $file) {
            if ($file !== "." && $file !== "..") {
                require_once __DIR__ . '/../../app/class/' . $file;
            }
        }
        foreach (scandir(__DIR__ . '/../../app/model') as $file) {
            if ($file !== "." && $file !== "..") {
                require_once __DIR__ . '/../../app/model/' . $file;
            }
        }
        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, "App\Table") || is_subclass_of($class, "App\EditArea")) {
                $obj = new $class;
                $data = $obj->getForAdminPannel();
                if ($data) {
                    $this->data["pages"][] = $data;
                }
            }
        }
    }

    /**
     * générer le fichier 'adminSrc/data.json'
     *
     * @return void
     */
    public function generate() {
        if ($file = fopen(__DIR__ . "/../adminSrc/data.json", "w")) {
            if (fwrite($file, json_encode($this->data))) {
                echo "Le fichier adminSrc/data.json a été modifié";
            } else {
                echo "Une erreur est survenue lors de l'écriture dans le fichier adminSrc/data.json";
            }
        } else {
            echo "Une erreur est survenue lors de l'ouverture du fichier adminSrc/data.json";
        }
    }
}