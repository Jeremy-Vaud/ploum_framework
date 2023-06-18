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
            "admin" => new Field(["type" => "bool", "value" => 0, "admin" => ["columns", "insert", "update"]]),
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
        session_start();
        foreach ($result as $field => $val) {
            if ($field !== "password") {
                $_SESSION[$field] = $val;
            }
        }
        return true;
    }

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
        if ($admin && $this->get("admin") || !$admin) {
            return true;
        }
    }

    public function createAdminRecoveryLink() {
        if ($this->get("admin")) {
            $uniqueLink = false;
            while(!$uniqueLink) {
                $link = "https://" . $_SERVER["SERVER_NAME"] . "/admin/recovery?code=" . bin2hex(random_bytes(16));
                $uniqueLink = $this->checkRecoveryLink($link);
            }
            $date = date("Y-m-d h:i:s");
            $this->set("recoveryLink", $link);
            $this->set("recoveryDate", $date);
            if ($this->update()) {
                return true;
            }
        }
        return false;
    }

    private function checkRecoveryLink(string $link) {
        $sql = "SELECT * FROM `user` WHERE `recoveryLink` = :link";
        $param = ["link" => $link];
        if(!BDD::Execute($sql, $param)) {
            return false;
        }
        if(BDD::RowCount() > 0) {
            return false;
        }
        return true;
    }

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
            $mail->AltBody = 'Pour réinitialiser votre mot passe suivez le lien '. $this->get("recoveryLink");

            $mail->send();
            return true;
        } catch (\Exception $e) {
            //echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            return false;
        }
    }
}
