<?php

namespace App;

class BDD {
    // Attributs
    protected static $DB = 'mysql:host=localhost;dbname=ploum;charset=UTF8;port=3306';
    protected static $USER = 'root';
    protected static $PASSWORD = '';
    protected static $OPTION = [\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING];
    protected static $PDO;
    protected static $REQ;
    
    /**
     * Connexion à la base de donnée
     *
     * @return void
     */
    public static function Connexion() {
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
