<?php

namespace App;

/**
 * Api du panneau d'administration
 */
final class Api {
    private string | null $action = null;
    private $object = null;
    private array $adminMail = [];
    private $files = ["add" => [], "del" => []];

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
            } else if (isset($_GET["isValidRecoveryLink"])) {
                $this->action = "isValidRecoveryLink";
            } else if (isset($_GET["edit_area"])) {
                $this->action = "getEditArea";
            }
        } elseif ($_SERVER['REQUEST_METHOD'] === "POST") {
            if (isset($_POST["action"])) {
                if ($_POST["action"] === "insert" || $_POST["action"] === "update" || $_POST["action"] === "delete") {
                    if (isset($_POST["table"]) && class_exists($_POST["table"])) {
                        $this->action = $_POST["action"];
                        $this->object = new $_POST["table"];
                        $this->sortFiles();
                    }
                } else if ($_POST["action"] === "logIn" || $_POST["action"] === "forgotPass" || $_POST["action"] === "changePass" || $_POST["action"] === "updateUser" || $_POST["action"] === "updatePass") {
                    $this->action = $_POST["action"];
                } else if ($_POST["action"] === "upsert") {
                    if (isset($_POST["edit_area"]) && class_exists($_POST["edit_area"])) {
                        $this->action = $_POST["action"];
                        $this->object = new $_POST["edit_area"];
                        $this->object->load();
                        $this->sortFiles();
                    }
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
        if (isset($_SESSION["role"]) && ($_SESSION["role"] === "admin" || $_SESSION["role"] === "superAdmin")) {
            return true;
        }
        return false;
    }

    /**
     * Vérifie que la personne connecté a bien un compte super administrateur
     *
     * @return bool
     */
    private function isSuperAdmin() {
        if (isset($_SESSION["role"]) && $_SESSION["role"] === "superAdmin") {
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
            echo json_encode($_SESSION);
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
     * Renvoi des données d'une tables de la BDD si l'utilisateur est bien connecté avec un compte admin
     *
     * @return void
     */
    private function getTable() {
        $class = $_GET["table"];
        if ($this->isAdmin() && $class !== "App\\User" || $this->isSuperAdmin()) {
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
     * Renvoi des données d'une 'EditArea' si l'utilisateur est bien connecté avec un compte admin
     *
     * @return void
     */
    private function getEditArea() {
        $class = $_GET["edit_area"];
        if ($this->isAdmin()) {
            if (class_exists($class)) {
                $object = new $class;
                $object->load();
                echo $object->valuesToJSON(true);
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
        if ($this->isAdmin() && $_POST["table"] !== "App\\User" || $this->isSuperAdmin()) {
            $check = $this->object->checkData($_POST);
            $checkFiles = $this->object->checkFiles($this->files["add"]);
            if ($check === [] && $checkFiles === []) {
                $this->object->setFromArray($_POST);
                if ($this->object->insert()) {
                    if ($_FILES !== []) {
                        $this->object->setFromArray($this->files["add"]);
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
        if ($this->isAdmin() && $_POST["table"] !== "App\\User" || $this->isSuperAdmin()) {
            $this->object->loadFromId($_POST["id"]);
            $check = $this->object->checkData($_POST);
            $checkFiles = $this->object->checkFiles($this->files["add"]);
            if ($check === [] && $checkFiles === []) {
                $this->object->setFromArray($_POST);
                $this->object->setFromArray($this->files["add"]);
                $this->object->deleteFiles($this->files["del"]);
                if ($this->object->update()) {
                    $response = ["status" => "success", "data" => $this->object->toArray(), "session" => null];
                    if ($_POST["table"] === "App\\User" && $_SESSION["id"] === (int)$_POST["id"]) {
                        $this->object->updateSession();
                        $response["session"] = $_SESSION;
                    }
                    echo json_encode($response);
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
     * Mise à jour d'un compte utilisteur
     *
     * @return void
     */
    private function updateUser() {
        if ($this->isAdmin() && $_SESSION["id"] === (int)$_POST["id"]) {
            $this->object = new User;
            $this->object->loadFromId($_POST["id"]);
            $data = ["nom" => $_POST["nom"], "prenom" => $_POST["prenom"], "email" => $_POST["email"]];
            $check = $this->object->checkData($data);
            if ($check === []) {
                $this->object->setFromArray($data);
                if ($this->object->update()) {
                    $this->object->updateSession();
                    echo json_encode(["status" => "success", "session" => $_SESSION]);
                } else {
                    http_response_code(400);
                }
            } else {
                echo json_encode(["status" => "invalid", "data" => $check]);
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
        if ($this->isAdmin() && $_POST["table"] !== "App\\User" || $this->isSuperAdmin()) {
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
     * Mise à jour ou création d'une ligne de la BDD si l'utilisateur est bien connecté avec un compte admin 
     *
     * @return void
     */
    private function upsert() {
        if ($this->isAdmin()) {
            $check = $this->object->checkData($_POST);
            $checkFiles = $this->object->checkFiles($this->files["add"]);
            if ($check === [] && $checkFiles === []) {
                $this->object->setFromArray($_POST);
                $this->object->setFromArray($this->files["add"]);
                $this->object->deleteFiles($this->files["del"]);
                if ($this->object->upsert()) {
                    $response = ["status" => "success", "session" => null];
                    echo json_encode($response);
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
            if (!$this->isAdmin()) {
                session_destroy();
                throw new \Exception("Identifiants incorrect");
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
            echo json_encode(["warning" => "Email de récupération envoyé"]);
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(["warning" => $e->getMessage()]);
        }
    }

    /**
     * Vérifie que le lien de rénitialistion de mot passe est valide
     *
     * @return void
     */
    private function isValidRecoveryLink() {
        echo json_encode((new User)->isValidRecoveryLink($_GET["isValidRecoveryLink"]));
    }

    /**
     * Mise à jour du mot de passe depuis lien de récupération
     *
     * @return void
     */
    private function changePass() {
        echo json_encode((new User)->changePass($_POST["code"], $_POST["pass1"], $_POST["pass2"]));
    }
    
    /**
     * Mise à jour du mot de passe depuis "Mon compte"
     *
     * @return void
     */
    private function updatePass() {
        echo json_encode((new User)->updatePass($_POST["pass"], $_POST["newPass1"], $_POST["newPass2"]));
    }
    
    /**
     * Trie les fichiers à supprimer et à ajouter
     *
     * @return void
     */
    private function sortFiles() {
        foreach ($_FILES as $key => $file) {
            if ($file["size"] !== 0) {
                $this->files["add"][$key] = $file;
            } else {
                $this->files["del"][] = $key;
            }
        }
    }
}
