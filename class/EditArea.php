<?php

namespace App;

/**
 * Classe représentant une zone éditable
 * 
 * @author  Jérémy Vaud
 */
abstract class EditArea extends Debug {
    // Attributs
    protected string $id; // string entre 3 et 5 caractères
    protected array $fields = [];
    protected array | null $adminPannel = null;

    public function __construct() {
        try {
            $idLength = strlen($this->id);
            if ($idLength < 3 || $idLength > 5) {
                throw new \Exception("L'id doit comporter entre 3 et 5 caractères");
            }
            $this->setFilesPath();
        } catch (\Exception $e) {
            $this->alertDebug($e);
        }
    }

    /**
     * Retourne la valeur d'un champ
     *
     * @param  string $field Nom du champ
     * @return mixed Valeur du champ
     */
    public function get(string $field) {
        if ($field === "id") {
            return $this->id;
        } else if (isset($this->fields[$field])) {
            return $this->fields[$field]->get();
        }
        return null;
    }

    /**
     * Retourne la valeur d'un champ convertit en entités HTML
     *
     * @param  string $field Nom du champ
     * @return string
     */
    public function html(string $field) {
        if ($field === "id") {
            return htmlentities($this->id);
        } else if (isset($this->fields[$field])) {
            return $this->fields[$field]->html();
        }
        return "";
    }

    /**
     * Attribut une valeur à un champ
     *
     * @param  mixed $field Nom du champ
     * @param  mixed $val Valeur du champ
     * @param  bool $verif Si true vérifie que $val soit conforme
     * @throws Exception Si $val non conforme
     * @return void
     */
    public function set($field, $val, bool $verif = true) {
        try {
            if (isset($this->fields[$field])) {
                if (is_a($this->fields[$field], "App\File")) {
                    if (is_string($val)) {
                        $this->fields[$field]->set($val);
                    } else if (is_array($val)) {
                        $this->fields[$field]->save($val, false);
                    }
                } else if (!$this->fields[$field]->set($val, $verif)) {
                    throw new \Exception("La valeur du champs " . htmlentities($field) . " n'est pas valide");
                }
            }
        } catch (\Exception $e) {
            $this->alertDebug($e);
        }
    }

    /**
     * Attribuer des valeurs à des champs depuis un tableau
     *
     * @param  array $array [ Nom => Valeur ]
     * @return void
     */
    public function setFromArray(array $array) {
        foreach ($array as $key => $val) {
            $this->set($key, $val);
        }
    }

    /**
     * Enregistrer des fichiers depuis un tableau
     *
     * @param  mixed $array Tableau des fichier
     * @return void
     */
    public function saveFiles(array $array) {
        foreach ($array as $key => $val) {
            if (isset($this->fields[$key]) && is_a($this->fields[$key], "App\File")) {
                $this->fields[$key]->save($val);
            }
        }
        $this->upsert();
    }

    /**
     * Attribuer un chemin depuis leur nom à chaque élément de type file
     *
     * @return void
     */
    private function setFilesPath() {
        $class = substr(get_called_class(), strrpos(get_called_class(), '\\') + 1);
        foreach ($this->fields as $name => $val) {
            if (is_a($val, "App\File")) {
                $val->setPath("files/" . $class . "/" . $name . "/");
            }
        }
    }

    /**
     * Charger un objet depuis son id
     *
     * @param  int $id Id de l'objet
     * @throws Excepton Erreur sql 
     * @return bool
     */
    public function load() {
        $sql = "SELECT * FROM `edit_area` WHERE `id` = :id";
        $param = [":id" => $this->id];
        try {
            if (!BDD::Execute($sql, $param)) {
                throw new \Exception("Erreur SQL ($sql)");
            }
            $result = BDD::Fetch();
            if ($result) {
                $decode = json_decode($result["val"]);
                foreach ($decode as $field => $val) {
                    $this->set($field, $val, false);
                }
            }
        } catch (\Exception $e) {
            $this->alertDebug($e);
            return false;
        }
        return true;
    }
    
    /**
     * Mettre à jour ou insérer une ligne de la bdd
     *
     * @return bool
     */
    public function upsert() {
        $sql = "REPLACE INTO `edit_area` (`id`, `val`) VALUES (:id, :val)";
        $param = [":id" => $this->id, ":val" => $this->valuesToJSON()];
        try {
            if (!BDD::Execute($sql, $param)) {
                throw new \Exception("Erreur SQL ($sql)");
            }
        } catch (\Exception $e) {
            $this->alertDebug($e);
            return false;
        }
        return true;
    }
    
    /**
     * Renvoi toutes les valeurs des champs au format json
     *
     * @param  bool $filePath si true renvoi le chemin des fichier si false les noms
     * @return string
     */
    public function valuesToJSON(bool $filePath = false) {
        $values = [];
        foreach ($this->fields as $key => $field) {
            if(!$filePath && is_a($field, "App\File")) {
                $values[$key] = $field->getName();
            } else {
                $values[$key] = $field->get();
            }     
        }
        return json_encode($values);
    }

    /**
     * Vérifie que les valeurs de $array([champ => valeurs]) sont valide et retourne un tableau des erreurs
     *
     * @param  array $array [ champ => valeur ]
     * @return array $error [ Champs invalide => error ]
     */
    public function checkData(array $array) {
        $error = [];
        foreach ($array as $field => $val) {
            if (isset($this->fields[$field])) {
                if (get_class($this->fields[$field]) === "App\Field") {
                    $check = $this->fields[$field]->isValid($val, true);
                    if ($check !== true) {
                        $error[$field] = $check;
                    }
                } elseif (get_class($this->fields[$field]) === "App\ForeignKey") {
                    if (!(ctype_digit($val) || is_int($val))) {
                        $error[$field] = ("La valeur du champ n'est pas un entier");
                    }
                }
            }
        }
        return $error;
    }

    /**
     * Vérifie que les fichiers provenant d'un tableau sont conforme aux attributs $files
     *
     * @param  array $array (ex: $_FILES)
     * @return array Un tableau listant les erreurs [Nom de l'attributs => Message d'erreur]
     */
    public function checkFiles(array $array) {
        $error = [];
        foreach ($array as $file => $val) {
            if (isset($this->fields[$file]) && is_a($this->fields[$file], "App\File")) {
                $check = $this->fields[$file]->checkFile($val);
                if ($check !== true) {
                    $error[$file] = $check;
                }
            }
        }
        return $error;
    }

    /**
     * Récupère les différents types de champs pour la génération de la BDD
     *
     * @return array Tableau des champs (ex: email => varchar(254) NOT NULL)
     */
    public function getSqlColumns() {
        return ["edit_area" => ["id" => "char(5) NOT NULL", "val" => "text"]];
    }

    /**
     * Retourne tous les paramètres pour le panneau d'administration
     *
     * @return mixed Un tableau de paramètres ou false
     */
    public function getForAdminPannel() {
        if ($this->adminPannel) {
            $return = $this->adminPannel;
            $return["className"] = get_called_class();
            $return["fields"] = [];
            $return["type"] = "edit_area";
            foreach ($this->fields as $key => $field) {
                $return["fields"][$key] = $field->getAdmin();
            }
            return $return;
        }
        return false;
    }
}
