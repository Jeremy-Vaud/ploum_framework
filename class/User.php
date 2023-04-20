<?php

namespace App;

/**
 * Utilisateur
 * 
 * @author  Jérémy Vaud
 */
class User extends Table {    

    /**
     * Constructeur
     *
     * @return void
     */
    public function __construct() {
        $this->fields = [
            "nom" => new Field(["type" => "char", "length" => 20, "minLength" => 3, "admin" => ["columns","insert","update"]]),
            "prenom" => new Field(["type" => "char", "length" => 20, "minLength" => 3, "admin" => ["columns","insert","update"]]),
            "email" => new Field(["type" => "email", "unique" => true, "admin" => ["columns","insert","update"]]),
            "password" => new Field(["type" => "password", "length" => 16, "minLength" => 4, "admin" => ["insert"]]),
            "admin" => new Field(["type" => "bool", "value" => 0, "admin" => ["columns","insert","update"]]),
        ];
        $this->adminPannel = [
            "title" => "Utilisateurs",
            "icon" => "faUsers",
            "order" => 1,
        ];
    }
    
    /**
     * Connection d'un utilisateur après vérification de son email et de son mot de passe
     *
     * @param  string $email
     * @param  string $password
     * @return bool
     */
    public function connect(string $email, string $password) {
        // Connection d'un utilisateur
        session_destroy();
        $sql = "SELECT * from `User` WHERE `email` = :email";
        $param = [":email" => $email];
        if (!BDD::Execute($sql, $param)) {
            return false;
        }
        $result = BDD::Fetch();
        if (!$result) {
            return false;
        }
        if(!password_verify($password, $result["password"])) {
            return false;
        }
        session_start();
        foreach ($result as $field => $val) {
            if($field !== "password") {
                $_SESSION[$field] = $val;
            }
        }
        return true;     
    }
}
