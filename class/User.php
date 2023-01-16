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
            "nom" => new Field(["type" => "char", "length" => 20, "minLength" => 3]),
            "prenom" => new Field(["type" => "char", "length" => 20, "minLength" => 3]),
            "email" => new Field(["type" => "email", "unique" => true]),
            "password" => new Field(["type" => "password", "length" => 16, "minLength" => 4]),
            "admin" => new Field(["type" => "bool", "value" => 0]),
        ];
        $this->adminPannel = [
            "title" => "Utilisateurs",
            "icon" => "faUsers",
            "order" => 1,
            "fields" => [
                "nom" => [
                    "type" => "text",
                    "table" => ["colums","insert","update"]
                ],
                "prenom" => [
                    "type" => "text",
                    "table" => ["colums","insert","update"]
                ],
                "email" => [
                    "type" => "email",
                    "table" => ["colums","insert","update"]
                ],
                "password" => [
                    "type" => "password",
                    "table" => ["insert"]
                ],
                "admin" => [
                    "type" => "checkbox",
                    "table" => ["colums","insert","update"]
                ],
            ],
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
