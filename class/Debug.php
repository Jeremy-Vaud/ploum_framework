<?php

namespace App;

/**
 * Classe permettant d'afficher des messages d'erreur
 * 
 * @author  Jérémy Vaud
 * @abstract
 * 
 */
abstract class Debug {
    
    /**
     * Affiche le message, la ligne et le fichier d'une Exception
     *
     * @param  Exception $e 
     * @return void
     */
    public function alertDebug($e) {
        // Message d'erreur
        global $DEBUG;
        if ($DEBUG) {
            echo "<br>";
            echo "Exeption reçue : ", $e->getMessage();
            echo "<br>";
            echo "Ligne ", $e->getLine(), " ", $e->getFile();
            echo "<br><br>";
        }
    }

        
    /**
     * Print_r de l'objet dans une balise pre
     *
     * @return void
     */
    public function print() {
        // Affiche l'objet
        global $DEBUG;
        if ($DEBUG) {
            echo "<pre>";
            print_r($this);
            echo "</pre>";
        }      
    }
}
