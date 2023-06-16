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
            "recoveryLink" => new Field(["type" => "url"]),
            "recoveryDate" => new Field(["type" => "dateTime"])
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
            $date = date("Y-m-d h:i:s");
            $this->set("recoveryLink", "https://" . $_SERVER["SERVER_NAME"] . "/admin/recovery/" . password_hash($date . $this->get("email"), PASSWORD_DEFAULT));
            $this->set("recoveryDate", $date);
            if ($this->update()) {
                return true;
            }
        }
        return false;
    }

    public function sendRecoveryLink() {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp.example.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'user@example.com';                     //SMTP username
            $mail->Password   = 'secret';                               //SMTP password
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('from@example.com', 'Mailer');
            $mail->addAddress('joe@example.net', 'Joe User');     //Add a recipient
            $mail->addAddress('ellen@example.com');               //Name is optional
            $mail->addReplyTo('info@example.com', 'Information');
            $mail->addCC('cc@example.com');
            $mail->addBCC('bcc@example.com');

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Here is the subject';
            $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            echo 'Message has been sent';
        } catch (\Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
