<?php

namespace App;

/**
 * Représente un champ d' une base de donnée
 * 
 * Type possible: char, email, password, text, bool, dateTime, date, time, url
 * @author  Jérémy Vaud
 * @final
 */
final class Field extends Debug {
    // Attributs
    protected $type; // Type de champ
    protected $length = null; // Taille maximum pour les chaine de caractères
    protected $minLength = null; // Taille minimum pour les chaine de caractères
    protected $value = null; // Valeur du champ
    protected $unique = false; // Doit avoir une valeur unique (ex: email pour les utilisateur)

    /**
     * Vérifie les paramètres avant de construire
     *
     * @param  array $params [type(requis):string, length(optionnel): int, length(optionnel): int, minLength(optionnel): int, maxLength(optionnel): int, value(optionnel), unique(optionnel) : bool]
     * @throws Exeption Si les valeurs des paramètres ne sont pas conformes
     * @return void
     */
    public function __construct(array $params) {
        try {
            // Type
            if (!isset($params["type"])) {
                throw new \Exception("Le champ n'as pas de type définit");
            }
            if (!method_exists($this, "isValid" . ucfirst($params["type"]))) {
                throw new \Exception("Le type du champ n'est pas valide");
            }
            $this->type = $params["type"];
            // Length
            if (isset($params["length"])) {
                if (!is_int($params["length"]) || $params["length"] < 1) {
                    throw new \Exception("La taille du champ n'est pas valide");
                }
                $this->length = $params["length"];
            }
            if (isset($params["minLength"])) {
                if (!is_int($params["length"]) || $params["minLength"] < 0) {
                    throw new \Exception("La taille du minimum champ n'est pas valide");
                }
                $this->minLength = $params["minLength"];
            }
            if (isset($params["length"]) && isset($params["minLength"])) {
                if ($params["length"] < $params["minLength"]) {
                    throw new \Exception("La taille du max du champ est inférieure à la taille min");
                }
            }
            // Value
            if (isset($params["value"])) {
                $action = "isValid" . ucfirst($this->type);
                if (!$this->$action($params["value"])) {
                    throw new \Exception("La valeur du champ n'est pas valide");
                }
                $this->value = $params["value"];
            }
            // unique
            if (isset($params["unique"])) {
                if (!is_bool($params["unique"])) {
                    throw new \Exception("La valeur de unique n'est pas un booléen");
                }
                $this->unique = $params["unique"];
            }
        } catch (\Exception $e) {
            $this->alertDebug($e);
        }
    }

    /**
     * Retourne la valeur de l'attribut value
     *
     * @return mixed
     */
    public function get() {
        return $this->value;
    }

    /**
     * Retourne la valeur de l'attribut type
     *
     * @return string
     */
    public function getType() {
        return $this->type;
    }
    
    /**
     * Retourne le type de colone pour la structure de la BDD
     *
     * @return string type
     */
    public function getTypeForSql() {
        if($this->type === "int"){
            return "int(11) NOT NULL";
        }else if ($this->type === "char") {
            return "varchar(" . $this->length . ") NOT NULL";
        } else if ($this->type === "email") {
            return "varchar(254) NOT NULL";
        } else if ($this->type === "password") {
            return "varchar(80) NOT NULL";
        } else if ($this->type === "text" || $this->type === "url") {
            return "text NOT NULL";
        } else if ($this->type === "bool") {
            return "tinyint(1) NOT NULL";
        } else if ($this->type === "dateTime") {
            return "datetime NOT NULL DEFAULT '1970-01-01 00:00:00'";
        } else if ($this->type === "date") {
            return "date NOT NULL DEFAULT '1970-01-01'";
        } else if ($this->type === "time") {
            return "time NOT NULL DEFAULT '00:00:00'";
        }
    }

    /**
     * Retourne la valeur de l'attribut unique
     *
     * @return bool
     */
    public function isUnique() {
        return $this->unique;
    }

    /**
     * Attribuer une valeur à l'attribut $val (hash aussi les mots de passe)
     *
     * @param  mixed $val Valeur à attribuer
     * @param  bool $verif Si true vérifie la conformité du paramètre $val
     * @return bool Retourne false si $val n'est pas conforme
     */
    public function set($val, bool $verif = true) {
        // Change value
        if (!is_int($val) && ($this->type === "int")) {
            $val = (int)$val;
        }
        if ($this->type === "bool" && $val === "on") {
            $val = 1;
        }
        if ($verif) {
            if ($this->isValid($val)) {
                if ($this->type === "password") {
                    $this->value = password_hash($val, PASSWORD_DEFAULT);
                } else {
                    $this->value = $val;
                }
                return true;
            }
        } else {
            $this->value = $val;
            return true;
        }
        return false;
    }

    /**
     * Validtions de la valeur du champs
     *
     * @param  mixed $val Valeur à vérifier
     * @param  bool $returnMessage Si true retourne un message d'erreur si $val n'est pas conforme
     * 
     */
    public function isValid($val, bool $returnMessage = false) {
        $action = "isValid" . ucfirst($this->type);
        return $this->$action($val, $returnMessage);
    }

    /**
     * Validtions de la valeur du champs de type char
     *
     * @param  mixed $val Valeur à vérifier
     * @param  bool $returnMessage Si true retourne un message d'erreur si $val n'est pas conforme
     * @return bool True $val correct et $returnMessage = false
     */
    private function isValidChar($val, bool $returnMessage = false) {
        try {
            if (!is_string($val)) {
                throw new \Exception("La valeur du champ n'est pas une chaîne de caractères");
            }
            if (isset($this->length) && strlen($val) > $this->length) {
                throw new \Exception("La chaîne de caractères est trop longue");
            }
            if (isset($this->minLength) && strlen($val) < $this->minLength) {
                throw new \Exception("La chaîne de caractères est trop courte");
            }
        } catch (\Exception $e) {
            if ($returnMessage) {
                return $e->getMessage();
            } else {
                $this->alertDebug($e);
                return false;
            }
        }
        return true;
    }

    /**
     * Validtions de la valeur du champs de type email
     *
     * @param  string $val Valeur à vérifier
     * @param  bool $returnMessage Si true retourne un message d'erreur si $val n'est pas conforme
     * @return bool True $val correct et $returnMessage = false
     */
    private function isValidEmail(string $val, bool $returnMessage = false) {
        try {
            if (isset($this->length) && strlen($val) > $this->length) {
                throw new \Exception("La chaîne de caractères est trop longue");
            }
            if (!filter_var($val, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception("Email non valide");
            }
        } catch (\Exception $e) {
            if ($returnMessage) {
                return $e->getMessage();
            } else {
                $this->alertDebug($e);
                return false;
            }
        }
        return true;
    }

    /**
     * Validtions de la valeur du champs de type text
     *
     * @param  string $val Valeur à vérifier
     * @param  bool $returnMessage Si true retourne un message d'erreur si $val n'est pas conforme
     * @return bool True $val correct et $returnMessage = false
     */
    private function isValidText(string $val, bool $returnMessage = false) {
        try {
            if (isset($this->length) && strlen($val) > $this->length) {
                throw new \Exception("La chaîne de caractères est trop longue");
            }
            if (isset($this->minLength) && strlen($val) < $this->minLength) {
                throw new \Exception("La chaîne de caractères est trop courte");
            }
        } catch (\Exception $e) {
            if ($returnMessage) {
                return $e->getMessage();
            } else {
                $this->alertDebug($e);
                return false;
            }
        }
        return true;
    }

    /**
     * Validtions de la valeur du champs de type int
     *
     * @param  mixed $val Valeur à vérifier
     * @param  bool $returnMessage Si true retourne un message d'erreur si $val n'est pas conforme
     * @return bool True $val correct et $returnMessage = false
     */
    private function isValidInt($val, bool $returnMessage = false) {
        try {
            if (!(ctype_digit($val) || is_int($val))) {
                throw new \Exception("La valeur du champ n'est pas un entier");
            }
        } catch (\Exception $e) {
            if ($returnMessage) {
                return $e->getMessage();
            } else {
                $this->alertDebug($e);
                return false;
            }
        }
        return true;
    }

    /**
     * Validtions de la valeur du champs de type bool
     *
     * @param  mixed $val Valeur à vérifier
     * @param  bool $returnMessage Si true retourne un message d'erreur si $val n'est pas conforme
     * @return bool True $val correct et $returnMessage = false
     */
    private function isValidBool($val, bool $returnMessage = false) {
        try {
            if (!($val == 1 || $val == 0 || $val === true || $val === false || $val === "on")) {
                throw new \Exception("La valeur du champ n'est pas un booléen");
            }
        } catch (\Exception $e) {
            if ($returnMessage) {
                return $e->getMessage();
            } else {
                $this->alertDebug($e);
                return false;
            }
        }
        return true;
    }

    /**
     * Validtions de la valeur du champs de type password
     *
     * @param  string $val Valeur à vérifier
     * @param  bool $returnMessage Si true retourne un message d'erreur si $val n'est pas conforme
     * @return bool True $val correct et $returnMessage = false
     */
    private function isValidPassword(string $val, bool $returnMessage = false) {
        try {
            if (isset($this->length) && strlen($val) > $this->length) {
                throw new \Exception("Le mot de passe est trop long");
            }
            if (isset($this->minLength) && strlen($val) < $this->minLength) {
                throw new \Exception("Le mot de passe est trop court");
            }
        } catch (\Exception $e) {
            if ($returnMessage) {
                return $e->getMessage();
            } else {
                $this->alertDebug($e);
                return false;
            }
        }
        return true;
    }

    /**
     * Validtions de la valeur du champs de type dateTime
     *
     * @param  string $val Valeur à vérifier
     * @param  bool $returnMessage Si true retourne un message d'erreur si $val n'est pas conforme
     * @return bool True $val correct et $returnMessage = false
     */
    private function isValidDateTime(string $val, bool $returnMessage = false) {
        try {
            if (!preg_match("/^((((19|[2-9]\d)\d{2})\-(0[13578]|1[02])\-(0[1-9]|[12]\d|3[01]))|(((19|[2-9]\d)\d{2})\-(0[13456789]|1[012])\-(0[1-9]|[12]\d|30))|(((19|[2-9]\d)\d{2})\-02\-(0[1-9]|1\d|2[0-8]))|(((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))\-02\-29)) (([0-1][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?)$/", $val)) {
                throw new \Exception("DateTime non valide");
            }
        } catch (\Exception $e) {
            if ($returnMessage) {
                return $e->getMessage();
            } else {
                $this->alertDebug($e);
                return false;
            }
        }
        return true;
    }

    /**
     * Validtions de la valeur du champs de type date
     *
     * @param  string $val Valeur à vérifier
     * @param  bool $returnMessage Si true retourne un message d'erreur si $val n'est pas conforme
     * @return bool True $val correct et $returnMessage = false
     */
    private function isValidDate(string $val, bool $returnMessage = false) {
        try {
            if (!preg_match("/^((((19|[2-9]\d)\d{2})\-(0[13578]|1[02])\-(0[1-9]|[12]\d|3[01]))|(((19|[2-9]\d)\d{2})\-(0[13456789]|1[012])\-(0[1-9]|[12]\d|30))|(((19|[2-9]\d)\d{2})\-02\-(0[1-9]|1\d|2[0-8]))|(((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))\-02\-29))$/", $val)) {
                throw new \Exception("Date non valide");
            }
        } catch (\Exception $e) {
            if ($returnMessage) {
                return $e->getMessage();
            } else {
                $this->alertDebug($e);
                return false;
            }
        }
        return true;
    }

    /**
     * Validtions de la valeur du champs de type time
     *
     * @param  string $val Valeur à vérifier
     * @param  bool $returnMessage Si true retourne un message d'erreur si $val n'est pas conforme
     * @return bool True $val correct et $returnMessage = false
     */
    private function isValidTime(string $val, bool $returnMessage = false) {
        try {
            if (!preg_match("/^(([0-1][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?)$/", $val)) {
                throw new \Exception("Heure non valide");
            }
        } catch (\Exception $e) {
            if ($returnMessage) {
                return $e->getMessage();
            } else {
                $this->alertDebug($e);
                return false;
            }
        }
        return true;
    }

    /**
     * Validtions de la valeur du champs de type url
     *
     * @param  string $val Valeur à vérifier
     * @param  bool $returnMessage Si true retourne un message d'erreur si $val n'est pas conforme
     * @return bool True $val correct et $returnMessage = false
     */
    private function isValidUrl(string $val, bool $returnMessage = false) {
        try {
            if (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $val)) {
                throw new \Exception("Url non valide");
            }
        } catch (\Exception $e) {
            if ($returnMessage) {
                return $e->getMessage();
            } else {
                $this->alertDebug($e);
                return false;
            }
        }
        return true;
    }
}
