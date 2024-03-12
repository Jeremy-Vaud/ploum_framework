<?php

namespace App;

/**
 * Représente une clée étrangère dans un champ d' une base de donnée
 * 
 * @author  Jérémy Vaud
 * @final
 */
final class ForeignKey extends Debug {
    // Attributs
    protected $table;
    protected $key = 0;
    protected $value = null;
    protected $admin = ["key" => "id", "admin" => []]; // Parmètres du panneau d'administration ("columns","insert","update")

    /**
     * Constructeur
     *
     * @param  string $table Nom de la table de la clée étrangère
     * @param  array $admin Liste des paramètres pour le panneau d'administration ("columns","insert","update")
     * @throws Exeption si la table n'existe pas
     * @return void
     */
    public function __construct(string $table, array $admin = []) {
        // Vérifie que la class exist avant de construire
        try {
            if (!class_exists($table)) {
                throw new \Exception("La class " .  htmlentities($table) . " n'existe pas");
            }
            if ($admin !== []) {
                if (isset($admin["key"])) {
                    $this->admin["key"] = $admin["key"];
                }
                if (isset($admin["admin"])) {
                    $this->admin["admin"] = $admin["admin"];
                }
            }
            $this->table = $table;
        } catch (\Exception $e) {
            $this->alertDebug($e);
        }
    }

    /**
     * Retourne la Valeur de la clée
     *
     * @return int la Valeur de la clée
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * Retourne l'objet vers lequel pointe la clée étrangère
     *
     * @return object
     */
    public function get() {
        // Renvoye value
        if ($this->value === null) {
            $this->value = new $this->table();
            $this->value->loadFromId($this->key);
        }
        return $this->value;
    }

    public function getTable() {
        return $this->table;
    }

    /**
     * Retourne le type de colone pour la structure de la BDD
     *
     * @return string type
     */
    public function getTypeForSql() {
        return "int NOT NULL";
    }

    /**
     * Attribuer une valeur à la clée étrangère
     *
     * @param  int $key Valeur de la clée
     * @return bool
     */
    public function set(int $key) {
        // Change value
        if ($key > 0) {
            $this->key = $key;
            return true;
        }
        return false;
    }

    /**
     * Retourne les paramètres du champ pour le panneau d'administration
     *
     * @param bool $table si true retourne aussi la valeur de l'attribut table
     * @return mixed Un tableau de paramètres ou false 
     */
    public function getAdmin(bool $table = true) {
        $return = ["type" => "select"];
        if ($table) {
            $return["table"] = $this->admin["admin"];
        }
        return $return;
    }

    public function getAdminKey() {
        return $this->admin["key"];
    }
}
