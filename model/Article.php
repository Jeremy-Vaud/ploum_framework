<?php 

/*
 * Example de class représentant un article
 */

namespace Model;

class Article extends \App\Table {
    public function __construct() {
        $this->fields = [
            "titre" => new \App\Field(["type"=>"char","length"=>100, "admin" => ["columns","insert","update"]]),
            "texte" => new \App\Field(["type"=>"text", "admin" => ["columns","insert","update"]]),
            "url" => new \App\Field(["type"=>"url", "admin" => ["columns","insert","update"]]),
            "date" => new \App\Field(["type"=>"date", "admin" => ["columns","insert","update"]]),
            "time" => new \App\Field(["type"=>"time", "admin" => ["columns","insert","update"]]),
            "datetime" => new \App\Field(["type"=>"dateTime", "admin" => ["columns","insert","update"]]),
            "nombre" => new \App\Field(["type"=>"int", "admin" => ["columns","insert","update"]]),
            "cle" => new \App\ForeignKey("Model\Tag", ["key" => "nom", "pannel" => ["columns","insert","update"]])
         ];
         $this->files = [
            //"image" => new \App\File(["jpg","jpeg","png","webp"], 500000),
        ];
        $this->foreignKeys = [
            //"Model\Tag" => []
        ];
        $this->adminPannel = [
            "title" => "Articles",
            "icon" => "faNewspaper",
            "order" => 2,
        ];
    }
}