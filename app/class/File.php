<?php

namespace App;

/**
 * Représente un champ d' une base de donnée de type file(string en lien avec un fichier)
 * 
 * @author  Jérémy Vaud
 */
class File extends Debug {
    // Attributs
    protected $name; // Nom du fichier
    protected $path; // Chemin du fichier
    protected $type; // Tableau des extention de fichier possible (exemple: 'pdf')
    protected $maxSize = null; // Taille maximum du fichier
    protected $admin = []; // Parmètres du panneau d'administration ("columns","insert","update")
    protected $default = null; // Chemin du fichier par défaut
    protected $public = true; // Fichier public ou privé

    /**
     * Constructeur
     *
     * @param  array $params Tableau de paramètres : type, maxSize, admin, default, public
     * @return void
     */
    public function __construct(array $params) {
        // Constructeur
        try {
            // Type
            if (!isset($params["type"])) {
                throw new \Exception("Le fichier n'as pas de format définit");
            }
            if ($params["type"] === [] || !is_array($params["type"]) || !array_is_list($params["type"])) {
                throw new \Exception("Le paramètre type n'est pas valide");
            }
            $this->type = $params["type"];
            // Max size
            if (isset($params["maxSize"])) {
                if (!is_int($params["maxSize"]) || $params["maxSize"] < 1) {
                    throw new \Exception("La taille maximum du fichier n'est pas valide");
                }
                $this->maxSize = $params["maxSize"];
            }
            // Admin
            if (isset($params["admin"])) {
                foreach ($params["admin"] as $param) {
                    if ($param === "columns" || $param === "insert" || $param === "update") {
                        $this->admin[] = $param;
                    } else {
                        throw new \Exception("Paramètres admin invalides");
                    }
                }
            }
            // Default
            if (isset($params["default"])) {
                $this->default = $param["default"];
            }
            // Public
            if (isset($params["public"])) {
                if (is_bool($params["public"])) {
                    $this->public = $params["public"];
                } else {
                    throw new \Exception("Le paramètre public n'est pas un booléen");
                }
            }
        } catch (\Exception $e) {
            $this->alertDebug($e);
        }
    }

    /**
     * Retourne le nom du fichier
     *
     * @return string Nom du fichier
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Retourne le chemin du fichier
     *
     * @return string Chemin du fichier
     */
    public function get() {
        $file = $this->path . str_replace("..", "", $this->name ?? "");
        if (file_exists($file)) {
            return $file;
        }
        return $this->default;
    }

    /**
     * Retourne les paramètres du fichier pour le panneau d'administration
     *
     * @return mixed Un tableau de paramètres
     */
    public function getAdmin() {
        return ["type" => "file", "table" => $this->admin];
    }

    /**
     * Retourne le type de colone pour la structure de la BDD
     *
     * @return string
     */
    public function getTypeForSql() {
        return "text";
    }

    /**
     * Attribuer un nouveau chemin au fichier
     *
     * @param  string $path Chemin du fichier
     * @return void
     */
    public function setPath(string $path) {
        if ($this->public) {
            $this->path = "files/";
        } else {
            $this->path = __DIR__ . "/../files/";
        }
        $this->path .= str_replace("..", "", $path);
    }

    /**
     * Attribuer nom au fichier
     *
     * @param  string $name Nom du fichier
     * @return void
     */
    public function set(string $name) {
        $this->name = str_replace("..", "", $name);
    }

    /**
     * Sauvegarde le fichier après vérification
     *
     * @param  array $file Le fichier à passer depuis $_FILES
     * @param  bool $check Si true vérifie le fichier avant l'enregistrement
     * @return void
     */
    public function save(array $file, bool $check = true) {
        // Sauvegarder le fichier
        $target_file = $this->path . $file["name"];
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        try {
            if (!$check) {
                if ($file['error'] !== 0) {
                    throw new \Exception("Une erreur est survenue");
                }
                if (!$this->checkType($fileType)) {
                    throw new \Exception("Type de fichier non pris en charge");
                }
                if (!$this->checkSize($file["size"])) {
                    throw new \Exception("Fichier trop volumineux");
                }
            }
            $this->deleteFile();
            if (!$this->createDirectory()) {
                throw new \Exception("Une erreur est survenue lors de la création du répertoire");
            }
            if (!move_uploaded_file($file["tmp_name"], $target_file)) {
                throw new \Exception("Une erreur est survenue pendant l'enregistrement du fichier");
            }
            $this->name = $file["name"];
        } catch (\Exception $e) {
            $this->alertDebug($e);
        }
    }

    /**
     * Vérifie qu'un fichier est conforme aux paramètre
     *
     * @param  array $file (ex: $_FILES)
     * @return mixed True si pas d'erreur sinon un message d'erreur (string)
     */
    public function checkFile(array $file) {
        $target_file = $this->path . $file["name"];
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        try {
            if ($file['error'] !== 0) {
                throw new \Exception("Une erreur est survenue");
            }
            if (!$this->checkType($fileType)) {
                throw new \Exception("Type de fichier non pris en charge");
            }
            if (!$this->checkSize($file["size"])) {
                throw new \Exception("Fichier trop volumineux");
            }
        } catch (\Exception $e) {
            return $e->getmessage();
        }
        return true;
    }

    /**
     * Crée le repertoir du fichier si il n'existe pas
     *
     * @return bool
     */
    protected function createDirectory() {
        if (!is_dir($this->path)) {
            if (!mkdir($this->path, 0777, true)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Vérifie l'extention d'un fichier
     *
     * @param  string $fileType fichier à vérifier
     * @return bool
     */
    protected function checkType(string $fileType) {
        // Vérifie l'extention
        $ok = false;
        foreach ($this->type as $type) {
            if ($type === $fileType) {
                $ok = true;
            }
        }
        return $ok;
    }

    /**
     * Vérifie la taille du fichier
     *
     * @param  int $fileSize taille du fichier
     * @return bool
     */
    protected function checkSize(int $fileSize) {
        // Vérifie la taille du fichier
        if ($this->maxSize >= $fileSize) {
            return true;
        }
        return false;
    }

    /**
     * Suprimer le fichier du serveur
     *
     * @return void
     */
    public function deleteFile() {
        if ($this->name) {
            $name = str_replace("..", "", $this->name);
            if (is_file($this->path . $name)) {
                unlink($this->path . $name);
                rmdir($this->path);
            }
        }
    }
}
