<?php

namespace App;

/**
 * Class représentant le cloud
 * 
 * @author  Jérémy Vaud
 * @final
 */
final class Cloud {
    // Attributs
    protected $path = "cloud";
    protected $limitFileSize = 5000000;

    /**
     * Initialisation
     *
     * @param  string $path Chemin vers un sous dossier du dossier 'cloud' 
     * @return void
     */
    public function __construct(string $path = "") {
        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }
        $path = str_replace("..", "", $path);
        $path = trim($path, "/");
        if ($path !== "") {
            $this->path = "$this->path/$path";
        }
    }

    /**
     * Lister les fichiers et répertoires d'un dossier
     *
     * @return array Liste des fichiers et répertoires
     */
    public function getDir() {
        $files = [];
        if (is_dir($this->path)) {
            foreach (scandir($this->path) as $elt) {
                if (!($elt === "." || $elt === "..")) {
                    if (is_dir("$this->path/$elt")) {
                        $files[] = ["id" => "$this->path/$elt", "name" => $elt, "isDir" => true, "modDate" => filemtime("$this->path/$elt") * 1000];
                    } else {
                        $files[] = ["id" => "$this->path/$elt", "name" => $elt, "isDir" => false, "size" => filesize("$this->path/$elt"), "modDate" => filemtime("$this->path/$elt") * 1000];
                    }
                    if ($elt[0] === ".") {
                        $files[array_key_last($files)]["isHidden"] = true;
                    }
                }
            }
        }
        return $files;
    }
    
    /**
     * Return une image en base64
     *
     * @param  string $file le chemin du fichier
     * @return string
     */
    public function getThumbmail(string $file) {
        $res = null;
        $file = str_replace("..", "", $file);
        if (preg_match("/^cloud\/.+$/", $file)) {
            if (@exif_imagetype($file)) {
                $img = file_get_contents($file);
                $res = "data:image;base64," . base64_encode($img);
            }
        }
        return $res;
    }

    /**
     * Créer un nouveau dossier
     *
     * @param  string $name Nom du dossier
     * @return bool
     */
    public function createFolder(string $name) {
        if (preg_match('/^.{0,1}[0-9a-zàâçéèêëîïôûùüÿñæœ-]*$/i', $name)) {
            if (mkdir("$this->path/$name", 0777, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Supprimer un ou plusieurs fichiers ou dossiers
     *
     * @param  array $files Liste des éléments à supprimer
     * @return void
     */
    public function deleteFiles(array $files) {
        foreach ($files as $file) {
            $file = str_replace("..", "", $file);
            $file = str_replace("/", "", $file);
            if (is_dir("$this->path/$file")) {
                $this->removeDir($file, $this->path);
            } else if (file_exists("$this->path/$file")) {
                unlink("$this->path/$file");
            }
        }
    }

    /**
     * Supprimer un dossier
     *
     * @param  string $dir Nom du dossier
     * @param  mixed $path Chemin du dossier
     * @return void
     */
    private function removeDir(string $dir, string $path) {
        foreach (scandir("$path/$dir") as $elt) {
            if (!($elt === "." || $elt === "..")) {
                if (is_dir("$path/$dir/$elt")) {
                    $this->removeDir($elt, "$path/$dir");
                } else {
                    unlink("$path/$dir/$elt");
                }
            }
        }
        rmdir("$path/$dir");
    }

    /**
     * Enregistrer des fichiers
     *
     * @param  array $files Liste des fichiers
     * @return void
     */
    public function uploadFiles(array $files) {
        foreach ($files["name"] as $key => $name) {
            if ($files["error"][$key] == 0 && $files["size"][$key] < $this->limitFileSize) {
                move_uploaded_file($files["tmp_name"][$key], "$this->path/$name");
            }
        }
    }

    /**
     * Déplacer un ou plusieur fichiers ou dossiers
     *
     * @param  string $destination Chemin de destination
     * @param  array $files Liste des élément à déplacer
     * @return void
     */
    public function moveFiles(string $destination, array $files) {
        $destination = str_replace("..", "", $destination);
        if (preg_match("/^cloud\/.+$/", $destination)) {
            foreach ($files as $file) {
                $file = str_replace("..", "", $file);
                if (preg_match("/^cloud\/.+$/", $file)) {
                    rename($file, $destination . "/" . basename($file));
                }
            }
        }
    }

    /**
     * Envoi un fichier ou un dossier(zip) à l'utilisateur
     *
     * @param  string $file Elément à envoyer
     * @return void
     */
    public function downloadFile(string $file) {
        $file = str_replace("..", "", $file);
        if (preg_match("/^cloud\/.+$/", $file)) {
            if (is_dir($file)) {
                $zip = new \ZipArchive();
                $filename = basename($file) . ".zip";
                $zip->open($filename, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
                $listFiles = $this->listFiles($file);
                foreach ($listFiles as $elt) {
                    if ($elt["path"] === "") {
                        $zip->addFile("$file/" . $elt["file"], $elt["file"]);
                    } else {
                        $zip->addFile("$file/" . $elt["path"] . "/" . $elt["file"], $elt["path"] . "/" . $elt["file"]);
                    }
                }
                $zip->close();
                header('Content-Description: File Transfer');
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($filename));
                readfile($filename);
                unlink($filename);
                exit;
            } else if (file_exists($file)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($file) . '"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file));
                readfile($file);
                exit;
            }
        }
    }

    /**
     * Liste les fichier d'un dossier et de ses sous dossiers
     *
     * @param  mixed $dir Nom du dossier
     * @param  mixed $path Chemin du dossier
     * @return array $structure Liste de fichiers
     */
    private function listFiles($dir, $path = "") {
        $structure = [];
        foreach (scandir($dir) as $elt) {
            if (!($elt === "." || $elt === "..")) {
                if (is_dir("$dir/$elt")) {
                    $structure = array_merge($structure, $this->listFiles("$dir/$elt", $path === "" ? $elt : "$path/$elt"));
                } else {
                    $structure[] = ["file" => $elt, "path" => $path];
                }
            }
        }
        return $structure;
    }

    /**
     * Renomer un fichier ou un dossier
     *
     * @param  string $newName Nouveau nom
     * @param  string $oldName Ancien nom
     * @return void
     */
    public function renameFile(string $newName, string $oldName) {
        $newName = str_replace(["..", "/"], "", $newName);
        $oldName = str_replace(["..", "/"], "", $oldName);
        if (file_exists("$this->path/$oldName")) {
            rename("$this->path/$oldName", "$this->path/$newName");
        }
    }
}
