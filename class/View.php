<?php

namespace App;

use Exception;

/**
 * Classe View
 * 
 * @author  Jérémy Vaud
 * @final
 */
final class View extends Debug {
    // Attributs pour la balise head
    protected $title;
    protected $tag;
    protected $lang;
    protected $meta;
    protected $favicon;
    // Attributs pout le templates
    protected $base;
    protected $header;
    protected $footer;
    // Attribut du contenu
    protected $main = "";
    // Attributs pour les styles et scripts
    protected $scripts;
    protected $styles;
    // Attributs fragement 
    protected $fragment;
    // Racine du site
    protected $root = "/";

    /**
     * Contructeur
     *
     * @param  array $params Tous les paramètres sont optionnel :
     * 
     * String : title, tag, lang, meta, favicon, base, header, footer
     * 
     * Array : scripts, styles
     * 
     * @throws Exception Si attributs non valide
     * @return void
     */
    public function __construct(array $params = []) {
        include "settings/global.php";
        include "settings/routes.php";
        $this->title = isset($TITLE) ? $TITLE : "";
        $this->meta = isset($META) ? $META : "";
        $this->lang = isset($LANG) ? $LANG : "fr";
        $this->favicon = isset($FAVICON) ? $FAVICON : "";
        $this->base = isset($BASE) ? $BASE : "";
        $this->header = isset($HEADER) ? $HEADER : "";
        $this->footer = isset($FAVICON) ? $FOOTER : "";
        $this->scripts = isset($SCRIPTS) ? $SCRIPTS : [];
        $this->styles = isset($STYLES) ? $STYLES : [];
        if (isset($ROOT) && $ROOT !== "") {
            $this->root = "/" . htmlentities($ROOT) . "/";
        }
        try {
            foreach (["title", "tag", "lang", "meta", "favicon", "base", "header", "footer"] as $attr) {
                if (isset($params[$attr])) {
                    if (!is_string($params[$attr])) {
                        throw new \Exception("Le paramètre $attr n'est pas une chaine de caractère");
                    }
                    $this->$attr = $params[$attr];
                }
                if ($attr === "favicon" || $attr === "base" || $attr === "header" || $attr === "footer") {
                    if (!file_exists($this->$attr) && $this->$attr !== "") {
                        throw new \Exception("Le fichier " . htmlentities($this->$attr) . " est introuvable");
                    }
                }
            }
            foreach (["scripts", "styles"] as $attr) {
                if (isset($params[$attr])) {
                    if (!is_array($params[$attr])) {
                        throw new \Exception("Le paramètre $attr n'est pas un tableau");
                    }
                    foreach ($params[$attr] as $elt) {
                        if (!file_exists($elt)) {
                            throw new \Exception("Le fichier " . htmlentities($elt) . " est introuvable");
                        }
                        $this->$attr[] = (string)$elt;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->alertDebug($e);
        }
    }
    
    /**
     * Retourne la valeur d'un attribut
     *
     * @param  string $attr Nom de l'attribut
     * @return mixed Valeur de l'attribut si il existe sinon null
     */
    public function get(string $attr) {
        if(isset($this->$attr)) {
            return $this->$attr;
        }
        return null;
    }
    
    /**
     * Modifier la valeur d'un attribut ou ajouter des scripts et des styles à la vue
     *
     * @param  string $attr Nom de l'attribut
     * @param  mixed $val String ou array (uniquement pour les attributs scripts et styles)
     * @throws Exception Si les paramètres ne sont pas valide
     * @return void
     */
    public function set(string $attr, $val) {
        try {
            if($attr === "scripts" || $attr === "styles") {
                if(is_string($val)) {
                    if(!file_exists($val)){
                        throw new \Exception("Le fichier " . htmlentities($val) . " est introuvable");
                    }
                    $this->$attr[] = $val;
                }else if(is_array($val)) {
                    foreach($val as $string) {
                        if(!is_string($string)) {
                            throw new \Exception("Le nom du fichier " . htmlentities($string) . " n'est pas une chaine de caractère");
                        }
                        if(!file_exists($string)) {
                            throw new \Exception("Le fichier " . htmlentities($string) . " est introuvable");
                        }
                        $this->$attr[] = $string;
                    }
                } else {
                    throw new \Exception("Pour l'attribut $attr le paramètre \$val doit être un tableau ou une chaine de caractère");
                }
            } else if(isset($this->$attr)){
                if(!is_string($val)) {
                    throw new \Exception("Le nom du fichier de l'attribut $attr n'est pas une chaine de caractère");
                }
                $this->$attr = $val;
            } else {
                throw new \Exception("Le nom du paramètre " . htmlentities($attr) . " n'est pas valide");
            }
        } catch(Exception $e){
            $this->alertDebug($e);
        }
    }

    /**
     * Ajouter du contenu à l'attribut main
     *
     * @param  string $content Contenu à ajouter
     * @return void
     */
    public function addContent(string $content) {
        $this->main .= $content;
    }
    
    /**
     * Ajouter un template à l'attribut main
     *
     * @param  string $template Chemin du template
     * @throws Exception Si le fichier n'existe pas
     * @return void
     */
    public function addTemplate(string $template) {
        try{
            if(!file_exists($template)) {
                throw new \Exception("Le fichier " . htmlentities($template) . " est introuvable");
            }
            ob_start();
            include $template;
            $this->main .= ob_get_clean();
        } catch(\Exception $e) {
            $this->alertDebug($e);
        }
    }
    
    /**
     * Ajouter du contenu à l'attribut fragment
     *
     * @param  string $name Nom du fragment
     * @param  string $fragment Contenu du fragment
     * @return void
     */
    public function addFragment(string $name, string $fragment) {
        $this->fragment[$name] = $fragment;
    }
    
    /**
     * Affiche la vue
     * 
     * @return void
     */
    public function render() {
        $title = $this->title;
        if($this->tag !== ""){
            $title .= " - ".$this->tag;
        }
        $styles = $this->renderStyles();
        $scripts = $this->renderScripts();
        include $this->base;
    }
    
    /**
     * Créer la liste des fichiers de style
     *
     * @return string Liste des fichiers css avec leurs balises link
     */
    private function renderStyles() {
        $styles = "";
        foreach ($this->styles as $style) {
            $styles .= "<link href='" . $this->root . htmlentities($style) . "' rel='stylesheet'></link>";
        }
        return $styles;
    }
    
    /**
     * Créer la liste des fichiers js
     *
     * @return string Liste des fichiers js avec leurs balises script
     */
    private function renderScripts() {
        $scripts = "";
        foreach ($this->scripts as $script) {
            $scripts .= "<script type='text/javascript' src='" . $this->root . htmlentities($script) . "'></script>";
        }
        return $scripts;
    }
    
    /**
     * Retourne la racine
     *
     * @return string Racine
     */
    public function getRoot() {
        return $this->root;
    }
    
}
