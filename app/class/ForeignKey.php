<?php

namespace App;

/**
 * Représente une clée étrangère dans un champ d' une base de donnée
 * 
 * @author  Jérémy Vaud
 * @final
 */
final class ForeignKey extends Debug {
    // Traits
    use FieldTrait;
    use ForeignTrait;
    // Attributs
    protected ?string $table = null; // Nom de la table de la clée étrangère
    protected int $key = 0; // Id de l'objet
    protected ?object $value = null; // Objet
    /**
     * Constructeur
     *
     * @param  string $table Nom de la table de la clée étrangère
     * @param  array $params [column : string, admin : array]
     * @throws Exeption si la table n'existe pas
     * @return void
     */
    public function __construct(string $table, array $params = []) {
        // Vérifie que la class exist avant de construire
        try {
            // Input
            $this->input = "select";
            if (!class_exists($table)) {
                throw new \Exception("La class " .  htmlentities($table) . " n'existe pas");
            }
            // Column
            if (isset($params["column"])) {
                if (!is_string($params["column"])) {
                    throw new \Exception("Paramètre 'column' invalides");
                }
                $this->column = $params["column"];
            }
            // Admin
            if (isset($params["admin"])) {
                if (!$this->setAdmin($params["admin"])) {
                    throw new \Exception("Paramètres 'admin' invalides");
                }
            }
            // Table
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
    public function getKey(): int {
        return $this->key;
    }

    /**
     * Retourne l'objet vers lequel pointe la clée étrangère
     *
     * @return object
     */
    public function get(): object {
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
    public function getTypeForSql(): string {
        return "int NOT NULL";
    }

    /**
     * Attribuer une valeur à la clée étrangère
     *
     * @param  int $key Valeur de la clée
     * @return bool
     */
    public function set(int $key): bool {
        // Change value
        if ($key > 0) {
            $this->key = $key;
            return true;
        }
        return false;
    }
}
