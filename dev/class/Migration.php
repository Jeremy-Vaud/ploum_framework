<?php

namespace Dev;

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
        $this->sortArrays();
    }

    /**
     * Recherche les différentes classes filles de la classe Table et leur type de champs pour la génération de la BDD
     *
     * @return void
     */
    private function findTables() {
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
        \App\BDD::Execute("SHOW TABLES", []);
        foreach (\App\BDD::FetchAll() as $array) {
            $table = reset($array);
            $this->current[$table] = [];
            \App\BDD::Execute("DESCRIBE " . $table);
            foreach (\App\BDD::FetchAll() as $describe) {
                $string = $describe["Type"];
                if ($describe["Null"] === "NO") {
                    $string .= " NOT NULL";
                }
                if ($describe["Default"] !== null) {
                    $string .= " DEFAULT '" . $describe["Default"] . "'";
                } else if ($describe["Extra"] === "auto_increment") {
                    $string .= " AUTO_INCREMENT";
                }
                $this->current[$table][$describe["Field"]] = $string;
            }
        }
    }
    
    /**
     * Trie les tableaux des attributs current et tables
     *
     * @return void
     */
    private function sortArrays() {
        foreach (array_keys($this->tables) as  $tableName) {
            $idVal = $this->tables[$tableName]["id"];
            unset($this->tables[$tableName]["id"]);
            ksort($this->tables[$tableName]);
            $this->tables[$tableName] = ["id" => $idVal] + $this->tables[$tableName];
        }
        foreach (array_keys($this->current) as  $tableName) {
            $idVal = $this->current[$tableName]["id"];
            unset($this->current[$tableName]["id"]);
            ksort($this->current[$tableName]);
            $this->current[$tableName] = ["id" => $idVal] + $this->current[$tableName];
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
            } else if ($this->current[$tableName] !== $tableField) {
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
        $sql = "CREATE TABLE `$name` (";
        foreach ($fields as $nameField => $type) {
            $sql .= $nameField . " " . $type . ",";
        }
        $sql .= "PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8";
        if (\App\BDD::Execute($sql)) {
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
        if (\App\BDD::Execute($sql)) {
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
        if (\App\BDD::Execute($sql)) {
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
            $string .= "CREATE TABLE `$name` (";
            foreach ($this->current[$name] as $nameField => $type) {
                $string .= $nameField . " " . $type . ",";
            }
            $string .= "PRIMARY KEY (`id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
            $sql = "SELECT * from `$name`";
            if (\App\BDD::Execute($sql)) {
                if (\App\BDD::RowCount() > 0) {
                    $result = \App\BDD::FetchAll();
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
            if (!is_dir("dev/migrations")) {
                mkdir("dev/migrations", 0777, true);
            }
            $path = "dev/migrations/" . date("Y-m-d_H-i-s") . ".sql";
            $file = fopen($path, "w");
            if (fwrite($file, $string)) {
                echo "BDD exportée dans le fichier $path\n";
            } else {
                echo "Une erreur est survenu\n";
            }
            fclose($file);
        } else {
            echo "Aucunes données\n";
        }
    }
}
