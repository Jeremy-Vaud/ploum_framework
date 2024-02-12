<?php

namespace App;

/**
 * Api du panneau d'administration
 */
final class Api {
    private string | null $action = null; // Nom de la méthode à exécuter
    private string | null $role = null; // Role requis : null | "admin" | "superAdmin"
    private $object = null; // Objet sur lequel la méthode s'applique
    private array $files = ["add" => [], "del" => []]; // Liste des fichiers à ajouter ou à suprimer
    private array $methods = [ // Liste des différente méthodes utilisables triées par catégorie
        "TABLE" => [
            "getTable",
            "insert",
            "update",
            "delete",
            "download"
        ],
        "EDIT_AREA" => [
            "getEditArea",
            "upsert"
        ],
        "SESSION" => [
            "isLog",
            "logIn",
            "logOut",
            "isValidRecoveryLink",
            "forgotPass",
            "changePass"
        ],
        "USER" => [
            "updateUser",
            "updatePass"
        ],
        "CLOUD" => [
            "getDir",
            "createFolder",
            "deleteFiles",
            "uploadFiles",
            "moveFiles",
            "downloadFile",
            "renameFile",
            "getThumbmail"
        ]
    ];

    /**
     * Constructeur
     *
     * @return void
     */
    public function __construct() {
        session_start();
        if (isset($_POST["action"])) {
            if (isset($_POST["table"])) {
                is_subclass_of($_POST["table"], "App\Table") ? $this->setTableMethod() : null;
            } elseif (isset($_POST["edit_area"])) {
                is_subclass_of($_POST["edit_area"], "App\EditArea") ? $this->setEditAreaMethod() : null;
            } elseif (isset($_POST["method"])) {
                if ($_POST["method"] === "session") {
                    $this->setSessionMethod();
                } elseif ($_POST["method"] === "user") {
                    $this->setUserMethod();
                } elseif ($_POST["method"] === "cloud") {
                    $this->setCloudMethod();
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
        if ($this->checkRole()) {
            if ($this->action) {
                $action = $this->action;
                $this->$action();
            } else {
                http_response_code(400);
            }
        } else {
            http_response_code(401);
        }
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

    //--------------------------------------------------------------------------------------
    // Role
    //--------------------------------------------------------------------------------------

    /**
     * Vérifie le role de la personne connecté
     *
     * @return bool
     */
    private function checkRole() {
        if ($this->role === "admin") {
            return $this->isAdmin();
        }
        if ($this->role === "superAdmin") {
            return $this->isSuperAdmin();
        }
        return true;
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

    //--------------------------------------------------------------------------------------
    // Method table
    //--------------------------------------------------------------------------------------

    /**
     * Recherche la variable $_POST["action"] dans le tableau de l'attribut $methods["TABLE"]
     *
     * @return void
     */
    private function setTableMethod() {
        foreach ($this->methods["TABLE"] as $method) {
            if ($_POST["action"] === $method) {
                $this->action = $method;
                $this->object = new $_POST["table"];
                if (is_a($this->object, "App\User")) {
                    $this->role = "superAdmin";
                } else {
                    $this->role = "admin";
                }
                $this->sortFiles();
                break;
            }
        }
    }

    /**
     * Renvoi des données d'une tables de la BDD
     * @return void
     */
    private function getTable() {
        echo $this->object->listAllToJson();
    }

    /**
     * Insert une nouvelle ligne à la BDD
     *
     * @return void
     */
    private function insert() {
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
    }

    /**
     * Mise à jour d'une ligne de la BDD
     *
     * @return void
     */
    private function update() {
        if (isset($_POST["id"]) && $this->object->loadFromId($_POST["id"])) {
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
            http_response_code(400);
        }
    }

    /**
     * Suprime une ligne de la BDD
     *
     * @return void
     */
    private function delete() {
        if (isset($_POST["id"]) && $this->object->loadFromId($_POST["id"])) {
            $this->object->delete();
            http_response_code(200);
        } else {
            http_response_code(404);
        }
    }

    /**
     * Télécharger un fichier correspondant au champ d'une class table
     *
     * @return void
     */
    private function download() {
        if (isset($_POST["id"]) && isset($_POST["field"])) {
            $this->object->loadFromId($_POST["id"]);
            $file = $this->object->get($_POST["field"]);
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        } else {
            http_response_code(400);
        }
    }

    //--------------------------------------------------------------------------------------
    // Method edit area
    //--------------------------------------------------------------------------------------

    /**
     * Recherche la variable $_POST["action"] dans le tableau de l'attribut $methods["EDIT_AREA"]
     *
     * @return void
     */
    private function setEditAreaMethod() {
        foreach ($this->methods["EDIT_AREA"] as $method) {
            if ($_POST["action"] === $method) {
                $this->action = $_POST["action"];
                $this->object = new $_POST["edit_area"];
                $this->object->load();
                $this->role = "admin";
                $this->sortFiles();
                break;
            }
        }
    }

    /**
     * Renvoi des données d'une 'EditArea' si l'utilisateur est bien connecté avec un compte admin
     *
     * @return void
     */
    private function getEditArea() {
        $this->object->load();
        echo $this->object->valuesToJSON(true);
    }

    /**
     * Mise à jour ou création d'une ligne de la BDD si l'utilisateur est bien connecté avec un compte admin 
     *
     * @return void
     */
    private function upsert() {
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
    }

    //--------------------------------------------------------------------------------------
    // Methods session
    //--------------------------------------------------------------------------------------

    /**
     * Recherche la variable $_POST["action"] dans le tableau de l'attribut $methods["SESSION"]
     *
     * @return void
     */
    private function setSessionMethod() {
        foreach ($this->methods["SESSION"] as $method) {
            if ($_POST["action"] === $method) {
                $this->action = $method;
                break;
            }
        }
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
     * Se connecter si l'utilisateur est bien admin
     *
     * @return void
     */
    private function logIn() {
        try {
            if (!(isset($_POST["email"], $_POST["password"]))) {
                throw new \Exception("Une erreur est survenue");
            }
            $user = new User;
            if (!$user->connect($_POST["email"], $_POST["password"])) {
                throw new \Exception("Identifiants incorrect");
            }
            if (!$this->isAdmin()) {
                session_destroy();
                throw new \Exception("Identifiants incorrect");
            }
            echo json_encode($_SESSION);
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
        if (isset($_POST["code"])) {
            echo json_encode((new User)->isValidRecoveryLink($_POST["code"]));
        } else {
            echo json_encode(["isValid" => false, "msg" => "Url non valide"]);
        }
    }

    /**
     * Envoi d'un email de récupération de compte
     *
     * @return void
     */
    private function forgotPass() {
        try {
            if (!isset($_POST["email"])) {
                throw new \Exception("Une erreur est survenue");
            }
            $user = new User;
            if (!$user->userExist($_POST["email"], true)) {
                throw new \Exception("Adresse email inconnue");
            }
            if (!$user->createAdminRecoveryLink()) {
                throw new \Exception("Une erreur est survenue");
            }
            if (!$user->sendRecoveryLink()) {
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
     * Mise à jour du mot de passe depuis lien de récupération
     *
     * @return void
     */
    private function changePass() {
        if (isset($_POST["code"], $_POST["pass1"], $_POST["pass2"])) {
            echo json_encode((new User)->changePass($_POST["code"], $_POST["pass1"], $_POST["pass2"]));
        } else {
            echo json_encode(["isValid" => false, "msg" => "Une erreur est survenue"]);
        }
    }

    //--------------------------------------------------------------------------------------
    // Method user
    //--------------------------------------------------------------------------------------

    /**
     * Recherche la variable $_POST["action"] dans le tableau de l'attribut $methods["USER"]
     *
     * @return void
     */
    private function setUserMethod() {
        $this->role = "admin";
        foreach ($this->methods["USER"] as $method) {
            if ($_POST["action"] === $method) {
                $this->action = $_POST["action"];
                $this->object = new User;
                break;
            }
        }
    }

    /**
     * Mise à jour d'un compte utilisteur
     *
     * @return void
     */
    private function updateUser() {
        try {
            if (!(isset($_POST["id"], $_POST["nom"], $_POST["prenom"], $_POST["email"]) && $_SESSION["id"] === (int)$_POST["id"])) {
                throw new \Exception("Une erreur est survenue");
            }
            if (!$this->object->loadFromId($_POST["id"])) {
                throw new \Exception("Une erreur est survenue");
            }
            $data = ["nom" => $_POST["nom"], "prenom" => $_POST["prenom"], "email" => $_POST["email"]];
            $check = $this->object->checkData($data);
            if ($check === []) {
                $this->object->setFromArray($data);
                if (!$this->object->update()) {
                    throw new \Exception("Enregistrement échoué");
                }
                $this->object->updateSession();
                echo json_encode(["status" => "success", "session" => $_SESSION]);
            } else {
                echo json_encode(["status" => "invalid", "data" => $check]);
            }
        } catch (\Exception $e) {
            echo json_encode(["status" => "error", "msg" => $e->getMessage()]);
        }
    }

    /**
     * Mise à jour du mot de passe depuis "Mon compte"
     *
     * @return void
     */
    private function updatePass() {
        if (isset($_POST["pass"], $_POST["newPass1"], $_POST["newPass2"])) {
            echo json_encode($this->object->updatePass($_POST["pass"], $_POST["newPass1"], $_POST["newPass2"]));
        } else {
            echo json_encode(["status" => "invalid", "warning" => "Une erreur est survenue"]);
        }
    }

    //--------------------------------------------------------------------------------------
    // Method Cloud
    //--------------------------------------------------------------------------------------

    /**
     * Recherche la variable $_POST["action"] dans le tableau de l'attribut $methods["CLOUD"]
     *
     * @return void
     */
    private function setCloudMethod() {
        global $CLOUD;
        if ($CLOUD) {
            $this->role = "admin";
            foreach ($this->methods["CLOUD"] as $method) {
                if ($_POST["action"] === $method) {
                    $this->action = $_POST["action"];
                    break;
                }
            }
        }
    }

    /**
     * Liste les fichier d'un répertoire
     *
     * @return void
     */
    private function getDir() {
        if (isset($_POST["path"])) {
            $cloud = new Cloud($_POST["path"]);
            $folderChain = $cloud->getDir();
            echo json_encode($folderChain);
        }
    }

    /**
     * Crée un nouveau dossier
     *
     * @return void
     */
    private function createFolder() {
        if (isset($_POST["path"], $_POST["name"])) {
            $cloud = new Cloud($_POST["path"]);
            if ($cloud->createFolder($_POST["name"])) {
                http_response_code(200);
            }
        }
    }

    /**
     * Supprimer des fichiers ou des dossiers
     *
     * @return void
     */
    private function deleteFiles() {
        if (isset($_POST["path"], $_POST["files"])) {
            $cloud = new Cloud($_POST["path"]);
            $files = explode(",", $_POST["files"]);
            $cloud->deleteFiles($files);
        }
    }

    /**
     * Upload de plusieur fichiers
     *
     * @return void
     */
    private function uploadFiles() {
        if (isset($_POST["path"], $_FILES["files"])) {
            $cloud = new Cloud($_POST["path"]);
            $cloud->uploadFiles($_FILES["files"]);
        }
    }

    /**
     * Déplacer des fichiers ou des dossiers
     *
     * @return void
     */
    private function moveFiles() {
        if (isset($_POST["destination"], $_POST["files"])) {
            $cloud = new Cloud;
            $cloud->moveFiles($_POST["destination"], $_POST["files"]);
        }
    }

    /**
     * Télécharger des fichier ou des dossiers(zip)
     *
     * @return void
     */
    private function downLoadFile() {
        if (isset($_POST["file"])) {
            $cloud = new Cloud;
            $cloud->downloadFile($_POST["file"]);
        }
    }

    /**
     * Renomer un fichier ou un dossier
     *
     * @return void
     */
    private function renameFile() {
        if (isset($_POST["path"], $_POST["newName"], $_POST["oldName"])) {
            $cloud = new Cloud($_POST["path"]);
            $cloud->renameFile($_POST["newName"], $_POST["oldName"]);
        }
    }

    /**
     * Thumbmail d'un fichier
     *
     * @return void
     */
    private function getThumbmail() {
        if (isset($_POST["file"])) {
            $cloud = new Cloud;
            echo json_encode($cloud->getThumbmail($_POST["file"]));
        }
    }
}
