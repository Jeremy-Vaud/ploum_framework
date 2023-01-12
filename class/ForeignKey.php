<?php

namespace App;

/**
 * Représente une clée étrangère dans un champ d' une base de donnée
 * 
 * @author  Jérémy Vaud
 * @final
 */
final class ForeignKey extends Debug{
    // Attributs
    protected $table;
    protected $key;
    protected $value = null;
    
    /**
     * Constructeur
     *
     * @param  string $table Nom de la table de la clée étrangère
     * @param  int $key Valeur de la clée
     * @throws Exeption si la table n'existe pas
     * @return void
     */
    public function __construct(string $table, int $key = 0) {
        // Vérifie que la class exist avant de construire
        try {
            if (!class_exists($table)) {
                throw new \Exception("La class " .  htmlentities($table) ." n'existe pas");
            }
            $this->table = $table;
        } catch (\Exception $e) {
            $this->alertDebug($e);
        }
        if($key > 0) {
            $this->key = $key;
        }else {
            $this->key = 0;
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
        if($this->value === null) {        
            $this->value = new $this->table();
            $this->value->loadFromId($this->key);
        }
        return $this->value;
    }
    
    /**
     * Retourne le type de colone pour la structure de la BDD
     *
     * @return string type
     */
    public function getTypeForSql() {
        return "int(11) NOT NULL";
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
}