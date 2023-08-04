<?php

namespace App;

/**
 * Classe permettant de mettre à jour la structure de la BDD
 * 
 * @author  Jérémy Vaud
 */
final class Migration {

    protected $current = [];
    protected $tables = [];

    public function __construct() {
        $this->loadCurrent();
        $this->findTables();
    }

    /**
     * Recherche les différentes classes filles de la classe Table et leur type de champs pour la génération de la BDD
     *
     * @return void
     */
    private function findTables() {
        foreach (scandir('class') as $file) {
            if ($file !== "." && $file !== "..") {
                require_once "class/" . $file;
            }
        }
        foreach (scandir('model') as $file) {
            if ($file !== "." && $file !== "..") {
                require_once "model/" . $file;
            }
        }
        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, "App\Table")) {
                $obj = new $class;
                $tables = $obj->getSqlColumns();
                foreach ($tables as $key => $val) {
                    $this->tables[$key] = $val;
                }
            }
        }
    }

    /**
     * Charge la structure de la BDD actuelle
     *
     * @return void
     */
    private function loadCurrent() {
        BDD::Execute("SHOW TABLES", []);
        foreach (BDD::FetchAll() as $array) {
            $table = reset($array);
            $this->current[$table] = [];
            BDD::Execute("DESCRIBE " . $table);
            foreach (BDD::FetchAll() as $describe) {
                if ($describe["Field"] !== "id") {
                    $string = $describe["Type"];
                    if ($describe["Null"] === "NO") {
                        $string .= " NOT NULL";
                    }
                    if ($describe["Default"] !== null) {
                        $string .= " DEFAULT '" . $describe["Default"] . "'";
                    }
                    $this->current[$table][$describe["Field"]] = $string;
                }
            }
        }
    }

    /**
     * Met à jour la stucture de la BDD
     *
     * @return void
     */
    public function migrate() {
        $diff = false;
        foreach ($this->tables as $tableName => $tableField) {
            if (!isset($this->current[$tableName])) {
                $diff = true;
                $this->create($tableName, $tableField);
            } elseif (array_diff_assoc($this->current[$tableName],$tableField) !== []) {
                $diff = true;
                $this->alter($tableName, $tableField);
            }
        }
        foreach (array_keys($this->current) as $currentName) {
            if (!isset($this->tables[$currentName])) {
                $diff = true;
                $this->drop($currentName);
            }
        }
        if (!$diff) {
            echo "Aucune différence n'a été trouvé entre les classes et la base de donnée\n";
        }
    }

    /**
     * Création d'une nouvelles table dans la BDD
     *
     * @param  string $name Non de la table
     * @param  array $fields Tableau des différents champs de la table (ex: nom => varchar(100) NOT NULL)
     * @return void
     */
    private function create(string $name, array $fields) {
        $sql = "CREATE TABLE `$name` (`id` int NOT NULL AUTO_INCREMENT,";
        foreach ($fields as $nameField => $type) {
            $sql .= $nameField . " " . $type . ",";
        }
        $sql .= "PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8";
        if (BDD::Execute($sql)) {
            echo "La table $name a été crée\n";
        }
    }

    /**
     * Modifiction d'une table de la BDD
     *
     * @param  string $name Non de la table
     * @param  array $fields Tableau des différents champs de la table (ex: nom => varchar(100) NOT NULL)
     * @return void
     */
    private function alter(string $name, array $fields) {
        $sql = "ALTER TABLE `$name` ";
        foreach ($fields as $field => $type) {
            if (!isset($this->current[$name][$field])) {
                $sql .= "ADD `$field` $type,";
            } else if ($this->current[$name][$field] !== $type) {
                $sql .= "MODIFY `$field` $type,";
            }
        }
        foreach (array_keys($this->current[$name]) as $currentField) {
            if (!isset($fields[$currentField])) {
                $sql .= "DROP `$currentField`,";
            }
        }
        $sql = substr($sql, 0, -1);
        if (BDD::Execute($sql)) {
            echo "La table $name a été modifié\n";
        }
    }

    /**
     * Suppression d'une table de la BDD
     *
     * @param  string $name Non de la table
     * @return void
     */
    private function drop(string $name) {
        $sql = "DROP TABLE `$name`";
        if (BDD::Execute($sql)) {
            echo "La table $name a été suprimé\n";
        }
    }

    /**
     * Exporte la BDD dans un fichier sql
     *
     * @return void
     */
    public function export() {
        $string = "";
        foreach (array_keys($this->current) as $name) {
            $string .= "CREATE TABLE `$name` (`id` int NOT NULL AUTO_INCREMENT,";
            foreach ($this->current[$name] as $nameField => $type) {
                $string .= $nameField . " " . $type . ",";
            }
            $string .= "PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
            $sql = "SELECT * from `$name`";
            if (BDD::Execute($sql)) {
                if (BDD::RowCount() > 0) {
                    $result = BDD::FetchAll();
                    $string .= "INSERT INTO `$name` (";
                    foreach (array_keys($result[0]) as $key) {
                        $string .= "`$key`,";
                    }
                    $string = substr($string, 0, -1) . ")VALUES(";
                    foreach ($result as $line) {
                        foreach ($line as $val) {
                            if ($val) {
                                $string .= "'$val',";
                            } else {
                                $string .= "NULL,";
                            }
                        }
                        $string = substr($string, 0, -1) . "),(";
                    }
                    $string = substr($string, 0, -2) . ";";
                }
            }
        }
        if ($string !== "") {
            if (!is_dir("migrations")) {
                mkdir("migrations", 0777, true);
            }
            $path = "migrations/" . date("Y-m-d_H-i-s") . ".sql";
            $file = fopen($path, "w");
            if (fwrite($file, $string)) {
                echo "BDD exportée\n";
            } else {
                echo "Une erreur est survenu\n";
            }
            fclose($file);
        } else {
            echo "Aucunes données\n";
        }
    }
}
