<?php

namespace App;

/**
 * Classe pour créer une image responsive pour les fichier jpeg, png, et webp
 * 
 * @author  Jérémy Vaud
 * @final
 */
final class ResponsiveImage extends Debug {

    protected $path;
    protected $type;
    protected $srcset = [];
    protected $class;
    protected $alt;
    protected $size;
    
    /**
     * Constructeur
     *
     * @param  string $path chemin de l'image
     * @param  array $options (optionel) ["class" => string, "alt" => string]
     * @throws Exception Si le fichier n'existe pas ou si le format du fichier n'est pas pris en charge
     * @return void
     */
    public function __construct(string $path, array $options = []) {
        try {
            if (!file_exists($path)) {
                throw new \Exception("Fichier introuvable");
            }
            $this->type = exif_imagetype($path);
            if (!($this->type === 2 || $this->type === 3 || $this->type === 18)) {
                throw new \Exception("Le fichier n'est pas au format jpeg, png, ou webp");
            }
            $this->path = $path;
            isset($options["class"]) ? $this->class = htmlentities($options["class"]) : $this->class = "";
            isset($options["alt"]) ? $this->alt = htmlentities($options["alt"]) : $this->class = "";
            $this->size = getimagesize($path);
            $this->createImagesNames();
            $this->createImages();
        } catch (\Exception $e) {
            $this->alertDebug($e);
        }
    }
    
    /**
     * Crée les images au différentes dimention
     *
     * @return void
     */
    private function createImages() {
        if (!file_exists($this->srcset["lg"])) {
            if ($this->type === 2) {
                $image = imagecreatefromjpeg($this->path);
            } else if ($this->type === 3) {
                $image = imagecreatefrompng($this->path);
            }
            imagewebp($image, $this->srcset["lg"]);
        }
        if (!file_exists($this->srcset["md"])) {
            if (!isset($image)) {
                $image = imagecreatefromwebp($this->srcset["lg"]);
            }
            $imgResized = imagescale($image, 800);
            imagewebp($imgResized, $this->srcset["md"]);
        }
        if (!file_exists($this->srcset["sm"])) {
            if (!isset($image)) {
                $image = imagecreatefromwebp($this->srcset["lg"]);
            }
            $imgResized = imagescale($image, 400);
            imagewebp($imgResized, $this->srcset["sm"]);
        }
    }
    
    /**
     * Crée le nom de chaque fichier image aux différentes dimention
     *
     * @return void
     */
    private function createImagesNames() {
        $name = explode(".", $this->path)[0];
        $this->srcset["sm"] = $name . "-sm.webp";
        $this->srcset["md"] = $name . "-md.webp";
        $this->srcset["lg"] = $name . ".webp";
    }
    
    /**
     * Balise html de l'image
     *
     * @return void
     */
    public function display() {
?>
        <img srcset="<?= $this->srcset["sm"] ?> 400w,<?= $this->srcset["md"] ?> 800w,<?= $this->srcset["lg"] ?> 801w" src="<?= $this->srcset["lg"] ?>" sizes="100vw"  alt="<?= $this->alt ?>" class="<?= $this->class ?>" width="<?= $this->size[0] ?>" height="<?= $this->size[1] ?>"/>
<?php
    }
}