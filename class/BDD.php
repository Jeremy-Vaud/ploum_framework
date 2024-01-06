<?php

namespace App;

class BDD {
    // Attributs
    protected static string $DB;
    protected static string $USER;
    protected static string $PASSWORD;
    protected static array $OPTION = [\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING];
    protected static $PDO;
    protected static $REQ;

    /**
     * Vérifie que les variables d'environnement nécessaire à la connexion à la base de la BDD sont présentes
     *
     * @throws Exeption Si certaines variables sont absentes
     * @return void
     */
    public static function __constructStatic() {
        try {
            $missingVar = "";
            foreach (["DB_HOST", "DB_NAME", "DB_PORT", "DB_USER", "DB_PASSWORD"] as $key) {
                if (!isset($_ENV[$key])) {
                    $missingVar .= "$key, ";
                }
            }
            if ($missingVar !== "") {
                $missingVar = substr($missingVar, 0, -2);
                throw new \Exception("Variables d'environnement manquantes : $missingVar");
            }
            self::$DB = "mysql:host=" . $_ENV["DB_HOST"] . ";dbname=" . $_ENV["DB_NAME"] . ";charset=UTF8;port=" . $_ENV["DB_PORT"];
            self::$USER = $_ENV["DB_USER"];
            self::$PASSWORD = $_ENV["DB_PASSWORD"];
        } catch (\Exception $e) {
            echo "<br>";
            echo "Exeption reçue : ", $e->getMessage();
            echo "<br>";
            echo "Ligne ", $e->getLine(), " ", $e->getFile();
            die();
        }
    }

    /**
     * Connexion à la base de donnée
     *
     * @return void
     */
    private static function Connexion() {
        try {
            self::$PDO = new \PDO(self::$DB, self::$USER, self::$PASSWORD, self::$OPTION);
        } catch (\PDOException $e) {
            print 'Erreur de connexion : ' . $e->getMessage();
            die();
        }
    }

    /**
     * Executer une requete sql
     *
     * @param  string $sql Requete sql
     * @param  array $param Paramètres de la requete
     * @return bool
     */
    public static function Execute(string $sql, array $param = []) {
        // Executer une requete sql
        if (!self::$PDO) {
            self::Connexion();
        }
        self::$REQ = self::$PDO->prepare($sql);
        if (self::$REQ->execute($param)) {
            return true;
        }
        return false;
    }

    /**
     * Recuperer une ligne de résultat d'une requete
     *
     * @return array
     */
    public static function Fetch() {
        return self::$REQ->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Recuperer le tableau des résultats d'une requete
     *
     * @return array
     */
    public static function FetchAll() {
        return self::$REQ->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Recuperer l' id de la derniere insertion
     *
     * @return int
     */
    public static function LastInsertId() {
        return self::$PDO->lastInsertId();
    }

    /**
     * Compter le nombre de ligne affecté par la requete
     *
     * @return int
     */
    public static function RowCount() {
        return self::$REQ->rowCount();
    }
}

BDD::__constructStatic();
