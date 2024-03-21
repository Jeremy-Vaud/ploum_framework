<?php

namespace App;

/**
 * Représente une table de relation un-à-plusieurs de la BDD
 * 
 * @author  Jérémy Vaud
 * @final
 */
final class MultipleForeignKeys extends Debug {
    // Traits
    use FieldTrait;
    use ForeignTrait;
    // Attributs
    protected int $id = 0; // Id de l'objet auxquelle le champ appartiens
    protected ?string $table = null; // Nom de la classe a laquelle appartiens le champ
    protected ?string $foreignTable = null; // Nom de la classe étrangère
    protected ?string $tableName = null; // Nom de la table
    protected array $value = [];

    /**
     * Constructeur
     *
     * @param  string $foreignTable Nom de la classe étrangère
     * @param  array $params [column : string, admin : array]
     * @throws Exeption si la table n'existe pas
     * @return void
     */
    public function __construct(string $foreignTable, array $params = []) {
        try {
            // Input
            $this->input = "selectMulti";
            if (!class_exists($foreignTable)) {
                throw new \Exception("La class " .  htmlentities($foreignTable) . " n'existe pas");
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
            // ForeignTable
            $this->foreignTable = $foreignTable;
        } catch (\Exception $e) {
            $this->alertDebug($e);
        }
    }

    /**
     * Retourne la valeur de l'attribut value
     *
     * @return array value
     */
    public function get(): array {
        return $this->value;
    }

    /**
     * Retourne la valeur de l'attribut foreignTable
     *
     * @return string
     */
    public function getForeignTable(): string {
        return $this->foreignTable;
    }

    /**
     * Retourne la valeur de l'attribut tableName
     *
     * @return string
     */
    public function getTableName(): string {
        return $this->tableName;
    }

    /**
     * Attribuer une valeur à l'attribut $id
     *
     * @param  int $id
     * @return void
     */
    public function setId(int $id): void {
        $this->id = $id;
    }

    /**
     * Attribut les noms des attributs table et foreignTable
     *
     * @param  string $table
     * @throws Exeption si table n'existe pas
     * @return void
     */
    public function setTable(string $table): void {
        try {
            if (!class_exists($table)) {
                throw new \Exception("La class " .  htmlentities($table) . " n'existe pas");
            }
            $this->table = $table;
            $this->tableName = strtolower(explode("\\", $table)[1]) . "_" . strtolower(explode("\\", $this->foreignTable)[1]);
        } catch (\Exception $e) {
            $this->alertDebug($e);
        }
    }

    /**
     * Attribuer des valeurs à l'attribut $value depuis un tableau ou une chaine de charactères
     *
     * @param  mixed $list (array : [1,6,9], string : '1,6,9')
     * @return void
     */
    public function set(array | string $list): void {
        $this->value = [];
        if (is_array($list)) {
            foreach ($list as $elt) {
                $this->value[$elt] = null;
            }
        } else {
            $explode = explode(",", $list);
            foreach ($explode as $elt) {
                if (ctype_digit($elt)) {
                    $this->value[$elt] = null;
                }
            }
        }
    }

    /**
     * Charges les données depuis la BDD
     *
     * @throws Excepton Erreur sql 
     * @return void
     */
    public function load(): void {
        $table = $this->tableName;
        $explode = explode("_", $table);
        $firstTable = $explode[0];
        $secondTable = $explode[1];
        $sql = "SELECT `$secondTable`.* FROM `$table` LEFT JOIN `$secondTable` ON `$table`.`$secondTable` = `$secondTable`.`id` WHERE `$table`.`$firstTable` = :id";
        $param = ["id" => $this->id];
        try {
            if (!BDD::Execute($sql, $param)) {
                throw new \Exception("Erreur SQL ($sql)");
            }
            $results = BDD::FetchAll();
            $this->value = [];
            foreach ($results as $result) {
                $this->value[$result["id"]] = new $this->foreignTable;
                $this->value[$result["id"]]->setFromArray($result);
            }
        } catch (\Exception $e) {
            $this->alertDebug($e);
        }
    }

    /**
     * Insère les données dans la BDD
     *
     * @throws Excepton Erreur sql 
     * @return bool
     */
    public function insert(): bool {
        if ($this->value && $this->value !== []) {
            $explode = explode("_", $this->tableName);
            $sql = "INSERT INTO `" . $this->tableName . "` (`" . $explode[0] . "`,`" . $explode[1] . "`) VALUE ";
            $param = [":id" => $this->id];
            $i = 0;
            foreach (array_keys($this->value) as $foreignId) {
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
                return false;
            }
        }
        return true;
    }

    /**
     * Suprime toutes les données de la BDD
     *
     * @return bool
     */
    public function delete(): bool {
        $sql = "DELETE FROM `" . $this->tableName . "` WHERE `" . explode("\\", $this->table)[1] . "` = :id";
        $param = [":id" => $this->id];
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
     * Mise à jour des données de la BDD
     *
     * @return bool
     */
    public function update(): bool {
        try {
            if (!$this->delete()) {
                throw new \Exception("Erreur de supréssion ");
            }
            if (!$this->insert()) {
                throw new \Exception("Erreur de supréssion ");
            }
        } catch (\Exception $e) {
            $this->alertDebug($e);
            return false;
        }
        return true;
    }
}
