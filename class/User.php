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
            "nom" => new Field(["type" => "char", "length" => 20, "minLength" => 3, "admin" => ["columns", "insert", "update"]]),
            "prenom" => new Field(["type" => "char", "length" => 20, "minLength" => 3, "admin" => ["columns", "insert", "update"]]),
            "email" => new Field(["type" => "email", "unique" => true, "admin" => ["columns", "insert", "update"]]),
            "password" => new Field(["type" => "password", "length" => 16, "minLength" => 4, "admin" => ["insert"]]),
            "role" => new Field(["type" => "select", "value" => "user", "choices" => ["user", "admin", "superAdmin"], "admin" => ["columns", "insert", "update"]]),
            "recoveryLink" => new Field(["type" => "url", "null" => true]),
            "recoveryDate" => new Field(["type" => "dateTime", "null" => true])
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
        if (!password_verify($password, $result["password"])) {
            return false;
        }
        foreach ($result as $field => $val) {
            $this->set($field, $val, false);
        }
        session_start();
        $this->updateSession();
        return true;
    }

    /**
     * Mis à jour des données stockées dans $_SESSION
     *
     * @return void
     */
    public function updateSession() {
        $_SESSION = ["id" => $this->get("id")];
        foreach ($this->fields as $field => $val) {
            if (!($field === "password" || $field === "recoveryDate" || $field === "recoveryLink")) {
                $_SESSION[$field] = $val->get();
            }
        }
    }

    /**
     * Vérifie qu'un compte exist depuis son email
     *
     * @param  mixed $email email de l'utilisateur
     * @param  mixed $admin si true l'utilisateur doit être administrateur
     * @return bool
     */
    public function userExist(string $email, bool $admin = false) {
        $sql = "SELECT * from `User` WHERE `email` = :email";
        $param = [":email" => $email];
        if (!BDD::Execute($sql, $param)) {
            return false;
        }
        $result = BDD::Fetch();
        if (!$result) {
            return false;
        }
        foreach ($result as $field => $val) {
            $this->set($field, $val, false);
        }
        $role = $this->get("role");
        if (!$admin || $admin && ($role === "admin" || $role === "superAdmin")) {
            return true;
        }
    }

    /**
     * Crée un lien de récupération de mot de passe pour un administrateur
     *
     * @return bool
     */
    public function createAdminRecoveryLink() {
        $role = $this->get("role");
        if ($role === "admin" || $role === "superAdmin") {
            $uniqueLink = false;
            while (!$uniqueLink) {
                $link = "https://" . $_SERVER["SERVER_NAME"] . "/admin/recovery?code=" . bin2hex(random_bytes(16));
                $uniqueLink = $this->checkRecoveryLink($link);
            }
            $date = date("Y-m-d H:i:s");
            $this->set("recoveryLink", $link);
            $this->set("recoveryDate", $date);
            if ($this->update()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verifie qu'un lien de récupération de mot de passe existe
     *
     * @param  mixed $link Lien de récupération de mot de passe
     * @return void
     */
    private function checkRecoveryLink(string $link) {
        $sql = "SELECT * FROM `user` WHERE `recoveryLink` = :link";
        $param = ["link" => $link];
        if (!BDD::Execute($sql, $param)) {
            return false;
        }
        if (BDD::RowCount() > 0) {
            return false;
        }
        return true;
    }

    /**
     * Charge un utilisateur depuis un lien de récupération de mot de passe
     *
     * @param  mixed $link Lien de récupération de mot de passe
     * @return bool
     */
    private function loadFromRecoveryLink(string $link) {
        $sql = "SELECT * FROM `user` WHERE `recoveryLink` = :link";
        $param = ["link" => $link];
        if (!BDD::Execute($sql, $param)) {
            return false;
        }
        $result = BDD::Fetch();
        if (!$result) {
            return false;
        }
        foreach ($result as $field => $val) {
            $this->set($field, $val, false);
        }
        return true;
    }

    /**
     * Verifie qu'un lien de récupération de mot de passe administrateur est valide
     *
     * @param  mixed $code Valeur du param code de l'url du lien
     * @return array Data pour le panneau d'admin
     */
    public function isValidRecoveryLink(string $code) {
        $return = ["isValid" => false, "msg" => ""];
        if ($this->loadFromRecoveryLink("https://" . $_SERVER["SERVER_NAME"] . "/admin/recovery?code=" . $code)) {
            $diff = time() - strtotime($this->get("recoveryDate"));
            if ($diff < 3600) {
                $return["isValid"] = true;
            } else {
                $return["msg"] = "Lien expiré";
            }
        } else {
            $return["msg"] = "Url non valide";
        }
        return $return;
    }

    /**
     * Modifie le mot de passe d'un admin
     *
     * @param  mixed $code Valeur du param code de l'url du lien de récupération
     * @param  mixed $pass1 Mot de passe
     * @param  mixed $pass2 Mot de passe
     * @return array Data pour le panneau d'admin
     */
    public function changePass(string $code, string $pass1, string $pass2) {
        $isValidRecoveryLink = $this->isValidRecoveryLink($code);
        if (!$isValidRecoveryLink["isValid"]) {
            return $isValidRecoveryLink;
        } else {
            $return = ["isValid" => true, "msg" => "Votre mot de passe à été mis à jour"];
            if ($pass1 === "") {
                $return["msg"] = "Veuillez remplir les champs";
            } else if ($pass1 !== $pass2) {
                $return["msg"] = "Les champs mot de passe ne sont pas identiques";
            } else {
                $check = $this->fields["password"]->isValid($pass1, true);
                if ($check !== true) {
                    $return["msg"] = $check;
                } else {
                    $this->set("password", $pass1);
                    $this->set("recoveryLink", null);
                    $this->set("recoveryDate", null);
                    if (!$this->update()) {
                        $return["msg"] = "Une erreur est survenue";
                    } else {
                        $return["isValid"] = false;
                    }
                }
            }
            return $return;
        }
    }

    /**
     * Envoi d'un lien de récupération de compte
     *
     * @param  mixed $SMTPParams paramètres SMTP
     * @return bool
     */
    public function sendRecoveryLink(array $SMTPParams) {
        $mail = new \PHPMailer\PHPMailer\PHPMailer();
        try {
            $mail->isSMTP();
            $mail->Host       = $SMTPParams["Host"];
            $mail->SMTPAuth   = $SMTPParams["SMTPAuth"];
            $mail->Username   = $SMTPParams["Username"];
            $mail->Password   = $SMTPParams["Password"];
            $mail->SMTPSecure = $SMTPParams["SMTPSecure"];
            $mail->Port       = $SMTPParams["Port"];

            $mail->setFrom($SMTPParams["From"][0], $SMTPParams["From"][1]);
            $mail->addAddress($this->get("email"), $this->get("nom") . " " . $this->get("prenom"));

            $mail->isHTML(true);
            $mail->Subject = "Mot de passe oublié";
            $mail->Body    = "<p>Pour réinitialiser votre mot passe cliqué <a href='" . htmlentities($this->get("recoveryLink")) . "'>ici</a>.</p>";
            $mail->AltBody = 'Pour réinitialiser votre mot passe suivez le lien ' . $this->get("recoveryLink");

            $mail->send();
            return true;
        } catch (\Exception $e) {
            //echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            return false;
        }
    }
}
