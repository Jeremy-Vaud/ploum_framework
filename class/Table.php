<?php

namespace App;

/**
 * Classe représentant un table de la base de donnée
 * 
 * @author  Jérémy Vaud
 */
abstract class Table extends Debug {
    // Attributs
    protected $id = 0;
    protected $fields = [];
    protected $files = [];
    protected $foreignKeys = [];
    protected $adminPannel = null;

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
        } else if (isset($this->files[$field])) {
            return $this->files[$field]->get();
        }
        return null;
    }

    /**
     * Retourne un des tableaux d'objet contenu dans l'attribut foreignKeys après les avoir récupéré dans la base de donnée si néscessaire
     *
     * @param  string $class Nom de la classe à retouner
     * @return array Tableau d'objets de l'attribut foreignKeys avec l'index $class
     */
    public function getForeign(string $class) {
        if (isset($this->foreignKeys[$class])) {
            foreach ($this->foreignKeys[$class] as $key => $object) {
                if ($object->get('id') === 0) {
                    $object->loadFromId($key);
                }
            }
            return $this->foreignKeys[$class];
        }
        return null;
    }

    /**
     * Attribut une valeur à un champ se la table
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
                if (!$this->fields[$field]->set($val, $verif)) {
                    throw new \Exception("La valeur du champs " . htmlentities($field) . " n'est pas valide");
                }
            } else if (isset($this->files[$field]) && $val !== null) {
                if (is_string($val)) {
                    $this->files[$field]->set($val);
                } else if (is_array($val)) {
                    $this->files[$field]->save($val, false);
                }
            } else if (isset($this->foreignKeys[$field])) {
                $this->setForeign($field, $val);
            }
        } catch (\Exception $e) {
            $this->alertDebug($e);
        }
    }

    /**
     * Attribuer des id à l'attribut $foreignKeys
     *
     * @param  string $class Nom de la class
     * @param  string $keys liste d'id (ex: 1,2,3)
     * @return void
     */
    private function setForeign(string $class, string $keys) {
        if (isset($this->foreignKeys[$class])) {
            $this->foreignKeys[$class] = [];
            $arrayKeys = explode(",", $keys);
            foreach ($arrayKeys as $id) {
                if (ctype_digit($id)) {
                    $this->foreignKeys[$class][$id] = new $class;
                }
            }
        }
    }

    /**
     * Attribuer des valeurs à des champs de la table depuis un tableau
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
            if (isset($this->files[$key])) {
                $this->files[$key]->save($val);
            }
        }
        $this->update();
    }

    /**
     * Attribuer un chemin depuis leur nom à chaque élément de l'atribut $file
     *
     * @return void
     */
    private function setFilesPath() {
        $class = substr(get_called_class(), strrpos(get_called_class(), '\\') + 1);
        foreach ($this->files as $name => $file) {
            $file->setPath("files/" . $class . "/" . $this->id . "/" . $name . "/");
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
        $class = substr(get_called_class(), strrpos(get_called_class(), '\\') + 1);
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
     * Récupère les clés étrangère de l'attribut foreignKeys
     *
     * @throws Excepton Erreur sql 
     * @return void
     */
    private function loadForeignKeys() {
        $class = substr(get_called_class(), strrpos(get_called_class(), '\\') + 1);
        $param = [":id" => $this->id];
        foreach (array_keys($this->foreignKeys) as $key) {
            $foreignClass = substr($key, strrpos(get_called_class(), '\\') + 1);
            $table = $class . "_" . $foreignClass;
            $sql = "SELECT `$foreignClass` FROM `$table` WHERE `$class` = :id";
            try {
                if (!BDD::Execute($sql, $param)) {
                    throw new \Exception("Erreur SQL ($sql)");
                }
                $results = BDD::FetchAll();
                foreach ($results as $result) {
                    $this->foreignKeys[$key][$result[$foreignClass]] = new $key;
                }
            } catch (\Exception $e) {
                $this->alertDebug($e);
            }
        }
    }

    /**
     * Inserer une nouvelle ligne dans la base de donnée
     *
     * @throws Excepton Erreur sql 
     * @return bool
     */
    public function insert() {
        $class = substr(get_called_class(), strrpos(get_called_class(), '\\') + 1);
        $sql = "INSERT INTO `$class` SET ";
        $param = [];
        foreach ($this->fields as $field => $value) {
            $sql .= "`$field` = :$field,";
            if (get_class($value) === "App\Field") {
                $param[$field] = $value->get();
            } elseif (get_class($value) === "App\ForeignKey") {
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
            $this->insertForeignKeys();
        } catch (\Exception $e) {
            $this->alertDebug($e);
            return false;
        }
        return true;
    }

    /**
     * Insere toutes les clés étrangère de l'attribut foreignKeys dans la base de donnée
     *
     * @throws Excepton Erreur sql
     * @return void
     */
    private function insertForeignKeys() {
        $class = substr(get_called_class(), strrpos(get_called_class(), '\\') + 1);
        foreach (array_keys($this->foreignKeys) as $key) {
            if ($this->foreignKeys[$key] !== []) {
                $foreignClass = substr($key, strrpos(get_called_class(), '\\') + 1);
                $table = $class . "_" . $foreignClass;
                $sql = "INSERT INTO `$table` (`$class`,`$foreignClass`) VALUES ";
                $param = [":id" => $this->id];
                $i = 0;
                foreach (array_keys($this->foreignKeys[$key]) as $foreignId) {
                    $sql .= " (:id, :foreignId$i),";
                    $param[":foreignId$i"] = $foreignId;
                    $i++;
                }
                $sql = substr($sql, 0, -1);
                try {
                    if (!BDD::Execute($sql, $param)) {
                        throw new \Exception("Erreur SQL ($sql)");
                    }
                } catch (\Exception $e) {
                    $this->alertDebug($e);
                }
            }
        }
    }

    /**
     * Suprimer une ligne de la bdd
     *
     * @throws Excepton Erreur sql 
     * @return void
     */
    public function delete() {
        $class = substr(get_called_class(), strrpos(get_called_class(), '\\') + 1);
        $sql = "DELETE FROM `$class` WHERE `id` = :id";
        $param = [":id" => $this->id];
        try {
            if (!BDD::Execute($sql, $param)) {
                throw new \Exception("Erreur SQL ($sql)");
            }
            foreach ($this->files as $file) {
                $file->deleteFile();
            }
            if (is_dir("files/" . $class . "/" . $this->id)) {
                rmdir("files/" . $class . "/" . $this->id);
            }
        } catch (\Exception $e) {
            $this->alertDebug($e);
        }
    }

    /**
     * Suprime toutes les clés étrangère de l'attribut foreignKeys dans la base de donnée
     *
     * @throws Excepton Erreur sql
     * @return void
     */
    private function deleteFroreignKeys() {
        $class = substr(get_called_class(), strrpos(get_called_class(), '\\') + 1);
        foreach (array_keys($this->foreignKeys) as $key) {
            $foreignClass = substr($key, strrpos(get_called_class(), '\\') + 1);
            $table = $class . "_" . $foreignClass;
            $sql = "DELETE FROM `$table` WHERE `$class` = :id";
            $param = [":id" => $this->id];
            try {
                if (!BDD::Execute($sql, $param)) {
                    throw new \Exception("Erreur SQL ($sql)");
                }
            } catch (\Exception $e) {
                $this->alertDebug($e);
            }
        }
    }

    /**
     * Mettre à jour une ligne de la bdd
     *
     * @throws Excepton Erreur sql 
     * @return bool
     */
    public function update() {
        $class = substr(get_called_class(), strrpos(get_called_class(), '\\') + 1);
        $sql = "UPDATE `$class` SET ";
        $param = [":id" => $this->id];
        foreach ($this->fields as $field => $value) {
            $sql .= "`$field` = :$field,";
            if (get_class($value) === "App\Field") {
                $param[$field] = $value->get();
            } elseif (get_class($value) === "App\ForeignKey") {
                $param[$field] = $value->getKey();
            }
        }
        foreach ($this->files as $field => $value) {
            $sql .= "`$field` = :$field,";
            $param[$field] = $value->getName();
        }
        $sql = substr($sql, 0, -1) . " WHERE `id`=:id";
        try {
            if (!BDD::Execute($sql, $param)) {
                throw new \Exception("Erreur SQL ($sql)");
            }
            $this->deleteFroreignKeys();
            $this->insertForeignKeys();
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
        $class = substr(get_called_class(), strrpos(get_called_class(), '\\') + 1);
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
     * Vérifie dans la BDD que la valeur d'un champ n'existe pas déja
     *
     * @param  string $field Nom du champ
     * @param  mixed $value Valeur du champ
     * @throws Excepton Erreur sql
     * @return bool True si existe ou si erreur sinon false
     */
    private function alreadyExist(string $field, $value) {
        $class = substr(get_called_class(), strrpos(get_called_class(), '\\') + 1);
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
        $return = [];
        foreach ($this->listAll() as $elt) {
            $return[] = $elt->toArray();
        }
        return json_encode($return);
    }

    /**
     * Transformer tous les valeur des champs de la table sous forme d'un tableau à l'exeption des mots de passe
     *
     * @return array
     */
    public function toArray() {
        $array = ["id" => $this->id];
        foreach ($this->fields as $field => $value) {
            if (get_class($value) === "App\Field") {
                if ($value->getType() !== "password") {
                    $array[$field] = $value->get();
                }
            } elseif (get_class($value) === "App\ForeignKey") {
                $array[$field] = $value->get()->toArray();
            }
        }
        foreach ($this->files as $field => $value) {
            $array[$field] = $value->get();
        }
        foreach (array_keys($this->foreignKeys) as $key) {
            $array[$key] = [];
            $foreign = $this->getForeign($key);
            foreach ($foreign as $object) {
                $array[$key][] = $object->toArray();
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
            if (isset($this->files[$file])) {
                $check = $this->files[$file]->checkFile($val);
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
        $data = [$class => []];
        foreach ($this->fields as $name => $field) {
            $data[$class][$name] = $field->getTypeForSql();
        }
        foreach (array_keys($this->files) as $file) {
            $data[$class][strtolower($file)] = "text";
        }
        foreach (array_keys($this->foreignKeys) as $foreign) {
            $foreignClass = strtolower(substr($foreign, strrpos($foreign, '\\') + 1));
            $data[$class . "_" . $foreignClass] = [$class => "int(11) NOT NULL", $foreignClass => "int(11) NOT NULL"];
        }
        return $data;
    }

    public function getForAdminPannel() {
        $this->adminPannel["className"] = get_called_class();
        return $this->adminPannel;
    }
}
