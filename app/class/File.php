<?php

namespace App;

/**
 * Représente un champ d' une base de donnée de type file(string en lien avec un fichier)
 * 
 * @author  Jérémy Vaud
 */
class File extends Debug {
    // Trait
    use FieldTrait;
    // Attributs
    protected ?string $name = null; // Nom du fichier
    protected ?string $path = null; // Chemin du fichier
    protected ?int $maxSize = null; // Taille maximum du fichier
    protected ?string $default = null; // Chemin du fichier par défaut
    protected bool $public = true; // Fichier public ou privé
    protected array $accept = []; // Tableau des extention de fichier possible (exemple: 'pdf')

    /**
     * Constructeur
     *
     * @param  array $params Tableau de paramètres : accept, maxSize, admin, default, public
     * @return void
     */
    public function __construct(array $params) {
        // Constructeur
        try {
            // Input
            $this->input = "file";
            // Accept
            if (!isset($params["accept"])) {
                throw new \Exception("Le fichier n'as pas de format définit");
            }
            if ($params["accept"] === [] || !is_array($params["accept"]) || !array_is_list($params["accept"])) {
                throw new \Exception("Le paramètre accept n'est pas valide");
            }
            $this->accept = $params["accept"];
            // Max size
            if (isset($params["maxSize"])) {
                if (!is_int($params["maxSize"]) || $params["maxSize"] < 1) {
                    throw new \Exception("La taille maximum du fichier n'est pas valide");
                }
                $this->maxSize = $params["maxSize"];
            }
            // Admin
            if (isset($params["admin"])) {
                if (!$this->setAdmin($params["admin"])) {
                    throw new \Exception("Paramètres 'admin' invalides");
                }
            }
            // Default
            if (isset($params["default"])) {
                $this->default = $params["default"];
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
     * @return ?string Nom du fichier
     */
    public function getName(): ?string {
        return $this->name;
    }

    /**
     * Retourne le chemin du fichier
     *
     * @return ?string Chemin du fichier
     */
    public function get(): ?string {
        $file = $this->path . str_replace("..", "", $this->name ?? "");
        if (file_exists($file)) {
            return $file;
        }
        return $this->default;
    }

    /**
     * Retourne le type de colone pour la structure de la BDD
     *
     * @return string
     */
    public function getTypeForSql(): string {
        return "text";
    }

    /**
     * Attribuer un nouveau chemin au fichier
     *
     * @param  string $path Chemin du fichier
     * @return void
     */
    public function setPath(string $path): void {
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
    public function set(string $name): void {
        $this->name = str_replace("..", "", $name);
    }

    /**
     * Sauvegarde le fichier après vérification
     *
     * @param  array $file Le fichier à passer depuis $_FILES
     * @param  bool $check Si true vérifie le fichier avant l'enregistrement
     * @return void
     */
    public function save(array $file, bool $check = true): void {
        // Sauvegarder le fichier
        $target_file = $this->path . $file["name"];
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        try {
            if (!$check) {
                if ($file['error'] !== 0) {
                    throw new \Exception("Une erreur est survenue");
                }
                if (!$this->isAccept($fileType)) {
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
     * @return bool|string True si pas d'erreur sinon un message d'erreur (string)
     */
    public function checkFile(array $file): bool|string {
        $target_file = $this->path . $file["name"];
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        try {
            if ($file['error'] !== 0) {
                throw new \Exception("Une erreur est survenue");
            }
            if (!$this->isAccept($fileType)) {
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
    protected function createDirectory(): bool {
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
    protected function isAccept(string $fileType): bool {
        // Vérifie l'extention
        $ok = false;
        foreach ($this->accept as $type) {
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
    protected function checkSize(int $fileSize): bool {
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
    public function deleteFile(): void {
        if ($this->name) {
            $name = str_replace("..", "", $this->name);
            if (is_file($this->path . $name)) {
                unlink($this->path . $name);
                rmdir($this->path);
            }
        }
    }
}
