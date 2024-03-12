<?php

namespace App;

/**
 * Class permetant de générer la structure du panneau d'administration
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
        usort($this->data["pages"], function ($a, $b) {
            return $a['order'] - $b['order'];
        });
    }
    
    /**
     * Retourne l'attribut "data"
     *
     * @return array
     */
    public function get() {
        return $this->data;
    }
}
