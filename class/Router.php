<?php

namespace App;

/**
 * Classe permettant de trouver le contrôleur correspondant à une url
 * 
 * @author  Jérémy Vaud
 */
class Router extends Debug{
    // Attributs
    protected $root;
    protected $home;
    protected $not_found;
    protected $routes;
    protected $url;
    protected $params = [];
    protected $controller;
    
    /**
     * Constructeur
     *
     * @return void
     * @throws Exeption Si le fichier de configuration des routes n'existe pas, si les varibles $ROOT, $HOME, $NOT_FOND, $ROUTES n'existent pas dans ce fichier ou si $_SERVER["REDIRECT_URL"] n'existe pas
     */
    public function __construct() {
        try {
            if(!file_exists("settings/routes.php")) {
                throw new \Exception("Le fichier settings/routes.php n'existe pas");
            }
            require "settings/routes.php";
            if(!isset($ROOT)) {
                throw new \Exception('La variable $ROOT du fichier settings/routes.php n\'existe pas');
            }
            $this->root = $ROOT;
            if(!isset($HOME)) {
                throw new \Exception('La variable $HOME du fichier settings/routes.php n\'existe pas');
            }
            $this->home = $HOME;
            if(!isset($NOT_FOUND)) {
                throw new \Exception('La variable $NOT_FOUND du fichier settings/routes.php n\'existe pas');
            }
            $this->not_found = $NOT_FOUND;
            if(!isset($ROUTES)) {
                throw new \Exception('La variable $ROUTES du fichier settings/routes.php n\'existe pas');
            }
            $this->routes = $ROUTES;
            if(!$_SERVER["REQUEST_URI"]) {
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
        $url = trim($url,"/");
        if($this->root !== ""){
            $url = substr(preg_replace("/^".$this->root."/","",$url),1);
        }
        $this->url = $url;
    }
            
    /**
     * Trouver le contrôleur correspondant à l'attribut $url et le passer à l'attribut $controller
     *
     * @return void
     */
    private function match() {
        if($this->url === "" || $this->url === false) {
            $this->controller = $this->home;
            return;
        }
        foreach($this->routes as $path => $controller) {
            $regex =  "#^".preg_replace('#:([\w]+)#', '([^/]+)', $path)."$#i";
            if(preg_match($regex, $this->url, $matches)) {
                $this->controller = $controller;
                array_shift($matches);
                if($matches !== []) {
                    $this->setParams($path, $matches);
                }
                return;
            }
        }
        $this->controller = $this->not_found;
    }
    
    /**
     * Passer un tableau contenant les valeurs des paramètres de l'url à l'attribut $params
     *
     * @param  string $path url relative
     * @param  array $matches Contient les valeurs à attribuer
     * @return void
     */
    private function setParams(string $path ,array $matches) {
        $arrayPath = explode("/", $path);
        $i = 0;
        foreach($arrayPath as $val) {
            if(preg_match('#:([\w]+)#', $val)) {
                $this->params[substr($val,1)] = $matches[$i];
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
            if(!file_exists("controller/".$this->controller)) {
                throw new \Exception("Fichier controller/".$this->controller." introuvable");
            }
        } catch(\Exception $e){
            $this->alertDebug($e);
            die();
        }
        return "controller/".$this->controller;
    }

    public function getParams() {
        return $this->params;
    }

}
