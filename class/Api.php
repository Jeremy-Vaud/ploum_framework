<?php

namespace App;

/**
 * Api du panneau d'administration
 */
final class Api {
    private $action = null;
    private $object = null;
    private $adminMail = [];
    
    /**
     * __construct
     *
     * @param  mixed $adminMail Paramètres SMTP
     * @return void
     */
    public function __construct(array $adminMail) {
        $this->adminMail = $adminMail;
        session_start();
        if ($_SERVER['REQUEST_METHOD'] === "GET") {
            if (isset($_GET["isLog"])) {
                $this->action = "isLog";
            } else if (isset($_GET["logOut"])) {
                $this->action = "logOut";
            } else if (isset($_GET["table"])) {
                $this->action = "getTable";
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === "POST") {
            if (isset($_POST["action"])) {
                if ($_POST["action"] === "insert" || $_POST["action"] === "update" || $_POST["action"] === "delete") {
                    if (isset($_POST["table"]) && class_exists($_POST["table"])) {
                        $this->action = $_POST["action"];
                        $this->object = new $_POST["table"];
                    }
                } else if ($_POST["action"] === "logIn" || $_POST["action"] === "forgotPass") {
                    $this->action = $_POST["action"];
                }
            }
        }
    }
    
    /**
     * Exécute la fonction dont le nom est stocké dans l'attribut action
     *
     * @return void
     */
    public function run() {
        if ($this->action) {
            $action = $this->action;
            $this->$action();
        } else {
            echo json_encode(["warning" => "Une erreur est survenue"]);
            http_response_code(400);
        }
    }
    
    /**
     * Vérifie que la personne connecté a bien un compte administrateur
     *
     * @return bool
     */
    private function isAdmin() {
        if (isset($_SESSION["admin"]) && $_SESSION["admin"]) {
            return true;
        }
        return false;
    }
    
    /**
     * Vérifie que l'utilisateur est bien connecté avec un compte admin
     *
     * @return void
     */
    private function isLog() {
        if ($this->isAdmin()) {
            http_response_code(200);
        } else {
            session_destroy();
            http_response_code(401);
        }
    }
    
    /**
     * Détruit la session
     *
     * @return void
     */
    private function logOut() {
        session_destroy();
        http_response_code(200);
    }
    
    /**
     * Renvoi des données d'une tabbles de la BDD si l'utilisateur est bien connecté avec un compte admin
     *
     * @return void
     */
    private function getTable() {
        if ($this->isAdmin()) {
            $class = $_GET["table"];
            if (class_exists($class) && isset($_GET["id"])) {
                $object = new $class;
                if ($_GET["id"] === 'all') {
                    echo $object->listAllToJson();
                } else {
                    if ($object->loadFromId($_GET["id"])) {
                        echo $object->toJson();
                    } else {
                        http_response_code(404);
                    }
                }
            } else {
                http_response_code(400);
            }
        } else {
            http_response_code(401);
        }
    }
    
    /**
     * Insert une nouvelle ligne à la BDD si l'utilisateur est bien connecté avec un compte admin
     *
     * @return void
     */
    private function insert() {
        if ($this->isAdmin()) {
            $check = $this->object->checkData($_POST);
            $checkFiles = $this->object->checkFiles($_FILES);
            if ($check === [] && $checkFiles === []) {
                $this->object->setFromArray($_POST);
                if ($this->object->insert()) {
                    if ($_FILES !== []) {
                        $this->object->setFromArray($_FILES);
                        $this->object->update();
                    }
                    echo json_encode(["status" => "success", "data" => $this->object->toArray()]);
                } else {
                    http_response_code(400);
                }
            } else {
                echo json_encode(["status" => "invalid", "data" => array_merge($check, $checkFiles)]);
            }
        } else {
            http_response_code(401);
        }
    }
    
    /**
     * Mise à jour d'une ligne de la BDD si l'utilisateur est bien connecté avec un compte admin
     *
     * @return void
     */
    private function update() {
        if ($this->isAdmin()) {
            $this->object->loadFromId($_POST["id"]);
            $check = $this->object->checkData($_POST);
            $checkFiles = $this->object->checkFiles($_FILES);
            if ($check === [] && $checkFiles === []) {
                $this->object->setFromArray($_POST);
                $this->object->setFromArray($_FILES);
                if ($this->object->update()) {
                    echo json_encode(["status" => "success", "data" => $this->object->toArray()]);
                } else {
                    http_response_code(400);
                }
            } else {
                echo json_encode(["status" => "invalid", "data" => array_merge($check, $checkFiles)]);
            }
        } else {
            http_response_code(401);
        }
    }
    
    /**
     * Suprime une ligne de la BDD si l'utilisateur est bien connecté avec un compte admin
     *
     * @return void
     */
    private function delete() {
        if ($this->isAdmin()) {
            if ($this->object->loadFromId($_POST["id"])) {
                $this->object->delete();
                http_response_code(200);
            } else {
                http_response_code(404);
            }
        } else {
            http_response_code(401);
        }
    }
    
    /**
     * Se connecter si l'utilisateur est bien admin
     *
     * @return void
     */
    private function logIn() {
        try {
            if (!(isset($_POST["action"]) && isset($_POST["email"]) && isset($_POST["password"]))) {
                throw new \Exception("Une erreur est survenue");
            }
            $user = new User;
            if (!$user->connect($_POST["email"], $_POST["password"])) {
                throw new \Exception("Identifiants incorrect");
            }
            if (!$_SESSION["admin"]) {
                session_destroy();
                throw new \Exception("La connection néssecite un compte administrateur");
            } else {
                http_response_code(200);
                echo json_encode($_SESSION);
            }
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(["warning" => $e->getMessage()]);
        }
    }
    
    /**
     * Envoi d'un email de récupération de compte
     *
     * @return void
     */
    private function forgotPass() {
        try {
            if (!(isset($_POST["action"]) && isset($_POST["email"]))) {
                throw new \Exception("Une erreur est survenue");
            }
            $user = new User;
            if (!$user->userExist($_POST["email"], true)) {
                throw new \Exception("Adresse email inconnue");
            }
            if (!$user->createAdminRecoveryLink()) {
                throw new \Exception("Une erreur est survenue");
            }
            if (!$user->sendRecoveryLink($this->adminMail)) {
                throw new \Exception("Une erreur est survenue");
            }
            http_response_code(200);
            echo json_encode(["warning" => "Un email de récupération viens de vous être envoyé"]);
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(["warning" => $e->getMessage()]);
        }
    }
}
