<?php

namespace App;

/**
 * Attribut et méthodes commune aux class Field, File, ForeignKey, MultileForeignKeys
 * 
 * @author  Jérémy Vaud
 */
trait FieldTrait {
    protected ?string $input = null; // Type d'input à utiliser pour le panneau d'administration
    protected array $admin = []; // Parmètres du panneau d'administration ("columns","insert","update")

    /**
     * Retourne les paramètres du champ pour le panneau d'administration
     *
     * @param bool $table si true retourne aussi la valeur de l'attribut table
     * @return array Un tableau de paramètres
     */
    public function getAdmin(bool $table = true): array {
        if (is_null($this->input) && method_exists($this, "setInput")) {
            $this->setInput();
        }
        $return = ["type" => $this->input];
        if ($table) {
            $return["table"] = $this->admin;
        }
        return $return;
    }

    /**
     * Setter de l'attribut admin
     *
     * @param  array $admin Parmètres du panneau d'administration ("columns","insert","update")
     * @return bool
     */
    protected function setAdmin(array $admin): bool {
        foreach ($admin as $param) {
            if ($param === "columns" || $param === "insert" || $param === "update") {
                $this->admin[] = $param;
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * Vérifie que le champs peut être renseigner depuis le panneau d'administration pour un insert dans la BDD
     *
     * @return bool
     */
    public function canInsert(): bool {
        if (in_array("insert", $this->admin)) {
            return true;
        }
        return false;
    }

    /**
     * Vérifie que le champs peut être renseigner depuis le panneau d'administration pour un update dans la BDD
     *
     * @return bool
     */
    public function canUpdate(): bool {
        if (in_array("update", $this->admin)) {
            return true;
        }
        return false;
    }
}
