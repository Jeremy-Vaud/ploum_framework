<?php

namespace App;

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
    // Attribut variables
    protected $variables = [];

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
        $this->title = isset($GLOBALS["TITLE"]) ? $GLOBALS["TITLE"] : "";
        $this->tag = isset($GLOBALS["TAG"]) ? $GLOBALS["TAG"] : null;
        $this->meta = isset($GLOBALS["META"]) ? $GLOBALS["META"] : "";
        $this->lang = isset($GLOBALS["LANG"]) ? $GLOBALS["LANG"] : "fr";
        $this->favicon = isset($GLOBALS["FAVICON"]) ? $GLOBALS["FAVICON"] : "";
        $this->base = isset($GLOBALS["BASE"]) ? __DIR__ . "/../view/" . $GLOBALS["BASE"] : null;
        $this->header = isset($GLOBALS["HEADER"]) ? __DIR__ . "/../view/" . $GLOBALS["HEADER"] : null;
        $this->footer = isset($GLOBALS["FOOTER"]) ? __DIR__ . "/../view/" . $GLOBALS["FOOTER"] : null;
        $this->scripts = isset($GLOBALS["SCRIPTS"]) ? $GLOBALS["SCRIPTS"] : [];
        $this->styles = isset($GLOBALS["STYLES"]) ? $GLOBALS["STYLES"] : [];
        try {
            foreach (["title", "tag", "lang", "meta", "favicon", "base", "header", "footer"] as $attr) {
                if (isset($params[$attr])) {
                    if (!is_string($params[$attr])) {
                        throw new \Exception("Le paramètre $attr n'est pas une chaine de caractère");
                    }
                    $this->$attr = $params[$attr];
                }
            }
            foreach(["base", "header", "footer"] as $attr) {
                if (isset($params[$attr])) {
                    $this->$attr = __DIR__ . "/../view/" . $params[$attr];
                }
                if ($this->$attr && !file_exists($this->$attr)) {
                    throw new \Exception("Le fichier $attr est introuvable dans le dossier view");
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
        if (isset($this->$attr)) {
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
            if ($attr === "scripts" || $attr === "styles") {
                if (is_string($val)) {
                    if (!file_exists($val)) {
                        throw new \Exception("Le fichier " . htmlentities($val) . " est introuvable");
                    }
                    $this->$attr[] = $val;
                } else if (is_array($val)) {
                    foreach ($val as $string) {
                        if (!is_string($string)) {
                            throw new \Exception("Le nom du fichier " . htmlentities($string) . " n'est pas une chaine de caractère");
                        }
                        if (!file_exists($string)) {
                            throw new \Exception("Le fichier " . htmlentities($string) . " est introuvable");
                        }
                        $this->$attr[] = $string;
                    }
                } else {
                    throw new \Exception("Pour l'attribut $attr le paramètre \$val doit être un tableau ou une chaine de caractère");
                }
            } else if (isset($this->$attr)) {
                if (!is_string($val)) {
                    throw new \Exception("Le nom du fichier de l'attribut $attr n'est pas une chaine de caractère");
                }
                $this->$attr = $val;
            } else {
                throw new \Exception("Le nom du paramètre " . htmlentities($attr) . " n'est pas valide");
            }
        } catch (\Exception $e) {
            $this->alertDebug($e);
        }
    }

    public function setVariables(array $var) {
        foreach ($var as $key => $val) {
            try {
                if($key === "title" || $key === "styles" || $key === "scripts") {
                    throw new \Exception("Le nom de variable $key n'est pas valide.");
                }
                $this->variables[$key] = $val;
            } catch (\Exception $e) {
                $this->alertDebug($e);
            }
        }
    }

    /**
     * Ajouter un template à l'attribut main
     *
     * @param  string $template Chemin du template
     * @throws Exception Si le fichier n'existe pas
     * @return void
     */
    public function addTemplate(string $template) {
        try {
            $path = __DIR__ . "/../view/" . $template;
            if (!file_exists($path)) {
                throw new \Exception("Le fichier " . htmlentities($template) . " est introuvable");
            }
            $this->main = $path;
        } catch (\Exception $e) {
            $this->alertDebug($e);
        }
    }

    /**
     * Affiche la vue
     * 
     * @return void
     */
    public function render() {
        $title = $this->title;
        if ($this->tag && $this->tag !== "") {
            $title .= " - " . $this->tag;
        }
        foreach ($this->variables as $key => $var) {
            ${$key} = $var;
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
            if($styles !== "") $styles .= "\t";
            $styles .= "<link href='" . htmlentities($style) . "' rel='stylesheet'/>\n";
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
            if($scripts !== "") $scripts .= "\t";
            $scripts .= "<script type='text/javascript' src='" . htmlentities($script) . "'></script>\n";
        }
        return $scripts;
    }
}
