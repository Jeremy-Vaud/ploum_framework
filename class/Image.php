<?php

namespace App;

/**
 * Représente un champ d' une base de donnée de type Image(string en lien avec l'image)
 * Format d'image accepté .jpg",.png, .jpeg , .webp
 * L'image est sauvegardée au format webp
 * 
 * @author  Jérémy Vaud
 * @final
 */
final class Image extends File {
    protected $width = null;

    /**
     * Constructeur
     *
     * @param  array $params Tableau de paramètres : maxSize, admin
     * @return void
     */
    public function __construct(array $params) {
        // Constructeur
        try {
            // Type
            $this->type = ["jpg", "png", "jpeg", "webp"];
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
            // Width
            if (isset($params["width"])) {
                if (!is_int($params["width"]) || $params["width"] < 10) {
                    throw new \Exception("Paramètres width invalides");
                }
                $this->width = $params["width"];
            }
        } catch (\Exception $e) {
            $this->alertDebug($e);
        }
    }

    /**
     * Retourne les paramètres de l'image pour le panneau d'administration
     *
     * @return mixed Un tableau de paramètres
     */
    public function getAdmin() {
        return ["type" => "image", "table" => $this->admin];
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
        try {
            if (!$check) {
                if ($file['error'] !== 0) {
                    throw new \Exception("Une erreur est survenue");
                }
                $fileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
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
            $name = explode(".", $file["name"])[0] . ".webp";
            $target_file = $this->path . $name;
            $image = imagecreatefromstring(file_get_contents($file["tmp_name"]));
            if($this->width) {
                $image = imagescale($image, $this->width);
            }
            if (!imagewebp($image, $target_file)) {
                throw new \Exception("Une erreur est survenue pendant l'enregistrement de l'image");
            }
            $this->deleteFile();
            $this->name = $name;
        } catch (\Exception $e) {
            $this->alertDebug($e);
        }
    }

}
