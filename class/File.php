<?php

namespace App;

/**
 * Représente un champ d' une base de donnée de type file(string en lien avec un fichier)
 * 
 * @author  Jérémy Vaud
 * @final
 */
final class File extends Debug {
    // Attributs
    protected $name;
    protected $path;
    protected $type;
    protected $maxSize;

    /**
     * Constructeur
     *
     * @param  array $type Tableau des extntion de fichier possible (exemple: 'jpg')
     * @param  int $maxSize Taille du fichier
     * @return void
     */
    public function __construct(array $type, int $maxSize = null) {
        // Constructeur
        $this->type = $type;
        $this->maxSize = $maxSize;
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
        if ($this->name !== null && $this->name !== "") {
            return $this->path . $this->name;
        }
        return null;
    }

    /**
     * Attribuer un nouveau chemin au fichier
     *
     * @param  string $path Chemin du fichier
     * @return void
     */
    public function setPath(string $path) {
        $this->path = $path;
    }

    /**
     * Attribuer nom au fichier
     *
     * @param  string $name Nom du fichier
     * @return void
     */
    public function set(string $name) {
        $this->name = $name;
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
            if (!$this->createDirectory()) {
                throw new \Exception("Une erreur est survenue lors de la création du répertoire");
            }
            if (!move_uploaded_file($file["tmp_name"], $target_file)) {
                throw new \Exception("Une erreur est survenue pendant l'enregistrement de l'image");
            }
            $this->deleteFile();
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
    private function createDirectory() {
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
    private function checkType(string $fileType) {
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
    private function checkSize(int $fileSize) {
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
        if(is_file($this->path.$this->name)) {
            unlink($this->path.$this->name);
        }
        $iterator = new \FilesystemIterator($this->path);
        if(!$iterator->valid()) {
            rmdir($this->path);
        }
    }
}
