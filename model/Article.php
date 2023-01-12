<?php 

/*
 * Example de class représentant un article
 */

namespace Model;

class Article extends \App\Table {
    public function __construct() {
        $this->fields = [
            "titre" => new \App\Field(["type"=>"char","length"=>100]),
            "texte" => new \App\Field(["type"=>"text"]),
            "date" => new \App\Field(["type"=>"date"]),
            "url" => new \App\Field(["type"=>"url"]),
         ];
         $this->files = [
            "image" => new \App\File(["jpg","jpeg","png","webp"], 500000),
        ];
        $this->foreignKeys = [
            "Model\Tag" => []
        ];
    }
}