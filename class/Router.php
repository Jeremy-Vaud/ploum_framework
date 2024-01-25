<?php

namespace App;

/**
 * Classe permettant de trouver le contrôleur correspondant à une url
 * 
 * @author  Jérémy Vaud
 */
class Router extends Debug {
    // Attributs
    protected $url;
    protected $params = [];
    protected $controller;

    /**
     * Constructeur
     *
     * @return void
     * @throws Exeption Si les varibles globales HOME, NOT_FOND, ROUTES n'existent pas ou si $_SERVER["REDIRECT_URL"] n'existe pas
     */
    public function __construct() {
        try {
            if (!isset($GLOBALS["HOME"])) {
                throw new \Exception('La variable globale HOME n\'existe pas');
            }
            if (!isset($GLOBALS["NOT_FOUND"])) {
                throw new \Exception('La variable globale NOT_FOUND n\'existe pas');
            }
            if (!isset($GLOBALS["ROUTES"])) {
                throw new \Exception('La variable globale ROUTES n\'existe pas');
            }
            if (!$_SERVER["REQUEST_URI"]) {
                throw new \Exception('La variable $_SERVER["REQUEST_URI"] n\'existe pas');
            }
            $this->setUrl($_SERVER["REQUEST_URI"]);
            $this->match();
        } catch (\Exception $e) {
            $this->alertDebug($e);
            die();
        }
    }



    /**
     * Attribuer une valeur à l'url
     *
     * @param  string $url Url relative
     * @return void
     */
    private function setUrl(string $url) {
        $url = explode("?", $url)[0];
        $url = trim($url, "/");
        $this->url = $url;
    }

    /**
     * Trouver le contrôleur correspondant à l'attribut $url et le passer à l'attribut $controller
     *
     * @return void
     */
    private function match() {
        if ($this->url === "" || $this->url === false) {
            $this->controller = __DIR__ . "/../controller/" . $GLOBALS["HOME"];
            return;
        }
        if (preg_match("#^admin(\/[\w]+)*$#i", $this->url)) {
            $this->controller = "admin/index.html";
            return;
        }
        foreach ($GLOBALS["ROUTES"] as $path => $controller) {
            $regex =  "#^" . preg_replace('#:([\w]+)#', '([^/]+)', $path) . "$#i";
            if (preg_match($regex, $this->url, $matches)) {
                $this->controller = __DIR__ . "/../controller/" . $controller;
                array_shift($matches);
                if ($matches !== []) {
                    $this->setParams($path, $matches);
                }
                return;
            }
        }
        $this->controller = __DIR__ . "/../controller/" . $GLOBALS["NOT_FOUND"];
    }

    /**
     * Passer un tableau contenant les valeurs des paramètres de l'url à l'attribut $params
     *
     * @param  string $path url relative
     * @param  array $matches Contient les valeurs à attribuer
     * @return void
     */
    private function setParams(string $path, array $matches) {
        $arrayPath = explode("/", $path);
        $i = 0;
        foreach ($arrayPath as $val) {
            if (preg_match('#:([\w]+)#', $val)) {
                $this->params[substr($val, 1)] = $matches[$i];
                $i++;
            }
        }
    }

    /**
     * Lire la valeur de l'attribut $controller 
     * 
     * @return string Chemin du contrôleur
     * @throws Exception Si le fichier du contrôleur n'existe pas
     */
    public function getController() {
        try {
            if (!file_exists($this->controller)) {
                throw new \Exception("Fichier " . $this->controller . " introuvable");
            }
        } catch (\Exception $e) {
            $this->alertDebug($e);
            die();
        }
        return $this->controller;
    }

    public function getParams() {
        return $this->params;
    }
}
