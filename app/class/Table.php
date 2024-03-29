<?php

namespace App;

/**
 * Classe représentant un table de la base de donnée
 * 
 * @author  Jérémy Vaud
 */
abstract class Table extends Debug {
    // Attributs
    protected int $id = 0;
    protected array $fields = [];
    protected array | null $adminPannel = null;

    public function __construct() {
        foreach (array_keys($this->fields) as $key) {
            if (get_class($this->fields[$key]) === "App\MultipleForeignKeys") {
                $this->fields[$key]->setTable(get_called_class());
            }
        }
    }

    /**
     * Retourne la valeur d'un champ de la table
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
     * Retourne la valeur d'un champ de la table convertit en entités HTML
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
     * Attribut une valeur à un champ de la table
     *
     * @param  mixed $field Nom du champ
     * @param  mixed $val Valeur du champ
     * @param  bool $verif Si true vérifie que $val soit conforme
     * @throws Exception Si $val non conforme
     * @return void
     */
    public function set($field, $val, bool $verif = true) {
        try {
            if ($field === "id") {
                $val = intval($val);
                if ($val >= 1) {
                    $this->id = $val;
                    $this->setFilesPath();
                } else {
                    throw new \Exception("La valeur de l'id n'est pas valide");
                }
            } else if (isset($this->fields[$field])) {
                if (is_a($this->fields[$field], "App\File")) {
                    if (is_string($val)) {
                        $this->fields[$field]->set($val);
                    } else if (is_array($val)) {
                        $this->fields[$field]->save($val, false);
                    }
                } else if (is_a($this->fields[$field], "App\MultipleForeignKeys")) {
                    $this->fields[$field]->set($val);
                } else if (!$this->fields[$field]->set($val, $verif)) {
                    throw new \Exception("La valeur du champs " . htmlentities($field) . " n'est pas valide");
                }
            }
        } catch (\Exception $e) {
            $this->alertDebug($e);
        }
    }

    /**
     * Attribuer des valeurs à des champs de la table depuis un tableau
     *
     * @param  array $array [ Nom => Valeur ]
     * @param  string $for "insert" ou "update"
     * @return void
     */
    public function setFromArray(array $array, string|null $for = null) {
        foreach ($array as $key => $val) {
            if (is_null($for) || isset($this->fields[$key]) && ($for === "insert" && $this->fields[$key]->canInsert() || $for === "update" && $this->fields[$key]->canUpdate())) {
                $this->set($key, $val);
            }
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
        $this->update();
    }

    /**
     * Attribuer un chemin depuis leur nom à chaque élément de type file
     *
     * @return void
     */
    private function setFilesPath() {
        $class = strtolower(substr(get_called_class(), strrpos(get_called_class(), '\\') + 1));
        foreach ($this->fields as $name => $val) {
            if (is_a($val, "App\File")) {
                $val->setPath($class . "/" . $this->id . "/" . $name . "/");
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
    public function loadFromId(int $id) {
        $class = strtolower(substr(get_called_class(), strrpos(get_called_class(), '\\') + 1));
        $sql = "SELECT * FROM `$class` WHERE `id` = :id";
        $param = [":id" => $id];
        try {
            if (!BDD::Execute($sql, $param)) {
                throw new \Exception("Erreur SQL ($sql)");
            }
            $result = BDD::Fetch();
            if (!$result) {
                throw new \Exception("Aucun résultat trouvé dans la bdd ($sql)");
            }
            foreach ($result as $field => $val) {
                $this->set($field, $val, false);
            }
            $this->loadForeignKeys();
        } catch (\Exception $e) {
            $this->alertDebug($e);
            return false;
        }
        return true;
    }

    /**
     * Inserer une nouvelle ligne dans la base de donnée
     *
     * @throws Excepton Erreur sql 
     * @return bool
     */
    public function insert() {
        $class = strtolower(substr(get_called_class(), strrpos(get_called_class(), '\\') + 1));
        $sql = "INSERT INTO `$class` SET ";
        $param = [];
        foreach ($this->fields as $field => $value) {
            if (get_class($value) === "App\Field") {
                $sql .= "`$field` = :$field,";
                $param[$field] = $value->get();
            } elseif (get_class($value) === "App\ForeignKey") {
                $sql .= "`$field` = :$field,";
                $param[$field] = $value->getKey();
            }
        }
        $sql = substr($sql, 0, -1);
        try {
            if (!BDD::Execute($sql, $param)) {
                throw new \Exception("Erreur SQL ($sql)");
            }
            $this->id = BDD::LastInsertId();
            $this->setFilesPath();
            foreach ($this->fields as $key => $val) {
                if (get_class($val) === "App\MultipleForeignKeys") {
                    $this->fields[$key]->setId($this->id);
                    $this->fields[$key]->insert();
                }
            }
            $this->loadForeignKeys();
        } catch (\Exception $e) {
            $this->alertDebug($e);
            return false;
        }
        return true;
    }

    /**
     * Suprimer une ligne de la bdd
     *
     * @throws Excepton Erreur sql 
     * @return void
     */
    public function delete() {
        $class = strtolower(substr(get_called_class(), strrpos(get_called_class(), '\\') + 1));
        $sql = "DELETE FROM `$class` WHERE `id` = :id";
        $param = [":id" => $this->id];
        try {
            if (!BDD::Execute($sql, $param)) {
                throw new \Exception("Erreur SQL ($sql)");
            }
            foreach ($this->fields as $field) {
                if (is_a($field, "App\File")) {
                    $field->deleteFile();
                } elseif (is_a($field, "App\MultipleForeignKeys")) {
                    $field->delete();
                }
            }
            if (is_dir("files/$class/$this->id")) {
                rmdir("files/$class/$this->id");
            }
            if (is_dir(__DIR__ . "/../files/$class/$this->id")) {
                rmdir(__DIR__ . "/../files/$class/$this->id");
            }
        } catch (\Exception $e) {
            $this->alertDebug($e);
        }
    }

    /**
     * Mettre à jour une ligne de la bdd
     *
     * @throws Excepton Erreur sql 
     * @return bool
     */
    public function update() {
        $class = strtolower(substr(get_called_class(), strrpos(get_called_class(), '\\') + 1));
        $sql = "UPDATE `$class` SET ";
        $param = [":id" => $this->id];
        foreach ($this->fields as $field => $value) {
            if (get_class($value) === "App\Field") {
                $sql .= "`$field` = :$field,";
                $param[$field] = $value->get();
            } elseif (get_class($value) === "App\ForeignKey") {
                $sql .= "`$field` = :$field,";
                $param[$field] = $value->getKey();
            } elseif (is_a($value, "App\File")) {
                $sql .= "`$field` = :$field,";
                $param[$field] = $value->getName();
            }
        }
        $sql = substr($sql, 0, -1) . " WHERE `id`=:id";
        try {
            if (!BDD::Execute($sql, $param)) {
                throw new \Exception("Erreur SQL ($sql)");
            }
            foreach ($this->fields as $field) {
                if (is_a($field, "App\MultipleForeignKeys")) {
                    $field->update();
                }
            }
            $this->loadForeignKeys();
        } catch (\Exception $e) {
            $this->alertDebug($e);
            return false;
        }
        return true;
    }

    /**
     * Charger tous les lignes de la bdd
     *
     * @param string $orderBy Nom du champ pour classer les résultats
     * @param int $limit Nombre limit de lignes
     * @throws Excepton Erreur sql
     * @return array Liste d'objet
     */
    public function listAll(string $orderBy = "", int $limit = 0) {
        $class = strtolower(substr(get_called_class(), strrpos(get_called_class(), '\\') + 1));
        $sql = "SELECT * FROM `$class`";
        if (isset($this->fields[$orderBy])) {
            $sql .= " ORDER BY `$orderBy`";
        }
        if ($limit > 0) {
            $sql .= " LIMIT $limit";
        }
        $param = [];
        $return = [];
        try {
            if (!BDD::Execute($sql, $param)) {
                throw new \Exception("Erreur SQL ($sql)");
            }
            $result = BDD::FetchAll();
            foreach ($result as $line) {
                $class = get_called_class();
                $elt = new $class;
                foreach ($line as $field => $val) {
                    $elt->set($field, $val, false);
                }
                $elt->loadForeignKeys();
                $return[] = $elt;
            }
        } catch (\Exception $e) {
            $this->alertDebug($e);
        }
        return $return;
    }

    /**
     * Charger les objets des champs MutipleForeignKey
     *
     * @return void
     */
    public function loadForeignKeys() {
        foreach ($this->fields as $key => $val) {
            if (is_a($val, "App\MultipleForeignKeys")) {
                $this->fields[$key]->setId($this->id);
                $this->fields[$key]->load();
            }
        }
    }

    /**
     * Vérifie dans la BDD que la valeur d'un champ n'existe pas déja
     *
     * @param  string $field Nom du champ
     * @param  mixed $value Valeur du champ
     * @throws Excepton Erreur sql
     * @return bool True si existe ou si erreur sinon false
     */
    private function alreadyExist(string $field, $value) {
        $class = strtolower(substr(get_called_class(), strrpos(get_called_class(), '\\') + 1));
        $sql = "SELECT * FROM `$class` WHERE `$field` = :val AND `id` != :id";
        $param = [":val" => $value, ":id" => $this->id];
        try {
            if (!BDD::Execute($sql, $param)) {
                throw new \Exception("Erreur sql");
            }
        } catch (\Exception $e) {
            $this->alertDebug($e);
            return true;
        }
        $result = BDD::RowCount();
        if ($result > 0) {
            return true;
        }
        return false;
    }

    /**
     * Charger tous les objets de la BDD au format JSON
     *
     * @return string
     */
    public function listAllToJson() {
        $class = strtolower(substr(get_called_class(), strrpos(get_called_class(), '\\') + 1));
        $join = "";
        $columns = "$class.id";
        $data = [];
        $dataSelect = [];
        $requirePath = [];
        $splits = [];
        foreach ($this->fields as $field => $value) {
            if (is_a($value, "App\ForeignKey")) {
                $columns .= ", $class.$field";
                $table = strtolower(explode("\\", $value->getTable())[1]);
                $name = $value->getColumn();
                if (BDD::Execute("SELECT id AS value, $name AS name FROM $table", [])) {
                    $dataSelect[$field] = BDD::FetchAll();
                }
            } else if (is_a($value, "App\MultipleForeignKeys")) {
                $foreignTable = strtolower(explode("\\", $value->getForeignTable())[1]);
                $junctionTable = $value->getTableName();
                $columns .= ", GROUP_CONCAT($foreignTable.id) as $field";
                $join .= "LEFT JOIN $junctionTable ON $class.id = $junctionTable.$class LEFT JOIN $foreignTable ON $foreignTable.id = $junctionTable.$foreignTable";
                $name = $value->getColumn();
                $splits[] = $field;
                if (BDD::Execute("SELECT id AS value, $name AS name FROM $foreignTable", [])) {
                    $dataSelect[$field] = BDD::FetchAll();
                }
            } else if (is_a($value, "App\File")) {
                $columns .= ", $class.$field";
                $requirePath[] = $field;
            } else if (is_a($value, "App\Field")) {
                if ($value->getType() === "select") {
                    foreach ($value->getChoices() as $choice) {
                        $dataSelect[$field][] = ["value" => $choice, "name" => $choice];
                    }
                }
                if ($value->getType() !== "password") {
                    $columns .= ", $class.$field";
                }
            }
        }
        if (BDD::Execute("SELECT $columns FROM $class $join GROUP BY $class.id", [])) {
            $data = BDD::FetchAll();
        }
        foreach ($requirePath as $require) {
            foreach ($data as $key => $val) {
                $this->set("id", $val["id"]);
                $this->set($require, $val[$require]);
                $data[$key][$require] = $this->get($require);
            }
        }
        foreach ($splits as $split) {
            foreach ($data as $key => $val) {
                if (isset($data[$key][$split])) {
                    $data[$key][$split] = array_map("intval", explode(",", $data[$key][$split]));
                } else {
                    $data[$key][$split] = [];
                }
            }
        }
        return json_encode(["data" => $data, "dataSelect" => $dataSelect]);
    }

    /**
     * Transformer tous les valeur des champs de la table sous forme d'un tableau à l'exeption des mots de passe
     *
     * @return array
     */
    public function toArray() {
        $array = ["id" => $this->id];
        foreach ($this->fields as $field => $value) {
            if (get_class($value) === "App\ForeignKey") {
                $array[$field] = $value->getKey();
            } else if (is_a($value, "App\MultipleForeignKeys")) {
                $array[$field] = [];
                foreach ($value->get() as $object) {
                    $array[$field][] = $object->get("id");
                }
            } else if (get_class($value) === "App\Field") {
                if ($value->getType() !== "password") {
                    $array[$field] = $value->get();
                }
            } else {
                $array[$field] = $value->get();
            }
        }
        return $array;
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
                    if ($this->fields[$field]->isUnique()) {
                        if ($this->alreadyExist($field, $val)) {
                            $check = "Déjà existant";
                        } else {
                            $check = $this->fields[$field]->isValid($val, true);
                        }
                    } else {
                        $check = $this->fields[$field]->isValid($val, true);
                    }
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
     * Récupère les différents types de champs de la table pour la génération de la BDD
     *
     * @return array Tableau des champs (ex: email => varchar(254) NOT NULL)
     */
    public function getSqlColumns() {
        $class = strtolower(substr(get_called_class(), strrpos(get_called_class(), '\\') + 1));
        $data = [$class => ["id" => "int NOT NULL AUTO_INCREMENT"]];
        foreach ($this->fields as $name => $field) {
            if (is_a($field, "App\MultipleForeignKeys")) {
                $foreignClass = strtolower(substr($field->getForeignTable(), strrpos($field->getForeignTable(), '\\') + 1));
                $data[$class . "_" . $foreignClass] = ["id" => "int NOT NULL AUTO_INCREMENT", $class => "int NOT NULL", $foreignClass => "int NOT NULL"];
            } else {
                $data[$class][$name] = $field->getTypeForSql();
            }
        }
        return $data;
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
            $return["type"] = "table";
            foreach ($this->fields as $key => $field) {
                $params = $field->getAdmin();
                if ($params["table"] !== []) {
                    $return["fields"][$key] = $params;
                }
            }
            return $return;
        }
        return false;
    }

    /**
     * Suprimer des fichiers
     *
     * @param  mixed $list des champs files ou images a suprimer
     * @return void
     */
    public function deleteFiles(array $list) {
        foreach ($list as $key) {
            if (isset($this->fields[$key])) {
                $this->fields[$key]->deleteFile();
            }
        }
    }
}
