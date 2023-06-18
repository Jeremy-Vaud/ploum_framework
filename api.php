<?php
require 'settings/global.php';
require 'vendor/autoload.php';

$DEBUG = false;

session_start();
if (isset($_GET['isLog'])) {
    /*
     * Test si un admin est conecté
     */
    if (isset($_SESSION["admin"]) && $_SESSION["admin"]) {
        http_response_code(200);
    } else {
        session_destroy();
        http_response_code(401);
    }
} else if (isset($_GET['logOut'])) {
    /*
     * Déconnection
     */
    session_destroy();
    http_response_code(200);
} else if (isset($_SESSION["admin"]) && $_SESSION["admin"]) {
    /*
     * Traitement des requète si connecté
     */
    switch ($_SERVER['REQUEST_METHOD']) {

        case 'GET':
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
            break;

        case 'POST':
            $class = $_POST["table"];
            if (class_exists($class)) {
                $object = new $class;
                switch ($_POST["action"]) {

                    case 'insert':
                        $check = $object->checkData($_POST);
                        $checkFiles = $object->checkFiles($_FILES);
                        if ($check === [] && $checkFiles === []) {
                            $object->setFromArray($_POST);
                            if ($object->insert()) {
                                if ($_FILES !== []) {
                                    $object->setFromArray($_FILES);
                                    $object->update();
                                }
                                echo json_encode(["status" => "success", "data" => $object->toArray()]);
                            } else {
                                http_response_code(400);
                            }
                        } else {
                            echo json_encode(["status" => "invalid", "data" => array_merge($check, $checkFiles)]);
                        }
                        break;

                    case 'update':
                        $object->loadFromId($_POST["id"]);
                        $check = $object->checkData($_POST);
                        $checkFiles = $object->checkFiles($_FILES);
                        if ($check === [] && $checkFiles === []) {
                            $object->setFromArray($_POST);
                            $object->setFromArray($_FILES);
                            if ($object->update()) {
                                echo json_encode(["status" => "success", "data" => $object->toArray()]);
                            } else {
                                http_response_code(400);
                            }
                        } else {
                            echo json_encode(["status" => "invalid", "data" => array_merge($check, $checkFiles)]);
                        }
                        break;

                    case 'delete':
                        $object = new $class;
                        if ($object->loadFromId($_POST["id"])) {
                            $object->delete();
                            http_response_code(200);
                        } else {
                            http_response_code(404);
                        }
                        break;
                }
            } else {
                http_response_code(400);
            }
            break;
    }
} else {
    /*
     * Connection de l'utilisateur
     */
    if ($_SERVER['REQUEST_METHOD'] === "POST") {
        $data = json_decode(trim(file_get_contents("php://input")), true);
        try {
            if (isset($data["table"])) {
                throw new \Exception("Connection requise");
            }
            if (!(isset($data["action"]) && isset($data["email"]))) {
                throw new \Exception("Une erreur est survenue");
            }
            if ($data["action"] === "logIn" && isset($data["password"])) {
                $user = new App\User;
                if (!$user->connect($data["email"], $data["password"])) {
                    throw new \Exception("Identifiants incorrect");
                }
                if (!$_SESSION["admin"]) {
                    throw new \Exception("La connection néssecite un compte administrateur");
                } else {
                    http_response_code(200);
                    echo json_encode($_SESSION);
                }
            } elseif ($data["action"] === "forgotPass") {
                $user = new App\User;
                if (!$user->userExist($data["email"], true)) {
                    throw new \Exception("Adresse email inconnue");
                }
                if (!$user->createAdminRecoveryLink()) {
                    throw new \Exception("Une erreur est survenue");
                }
                if (!$user->sendRecoveryLink($adminMail)) {
                    throw new \Exception("Une erreur est survenue");
                }
                http_response_code(200);
                echo json_encode(["warning" => "Un email de récupération viens de vous être envoyé"]);
            } else {
                throw new \Exception("Une erreur est survenue");
            }
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(["warning" => $e->getMessage()]);
        }
    } else {
        http_response_code(401);
        echo json_encode(["warning" => "Une erreur est survenue"]);
    }
}
