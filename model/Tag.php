<?php 

/*
 * Example de class représentant un tag
 */

namespace Model;

class Tag extends \App\Table {
    public function __construct() {
        $this->fields = [
            "nom" => new \App\Field(["type"=>"char","length"=>100])
         ];
    }
}