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
            "url" => new \App\Field(["type"=>"url"]),
            "date" => new \App\Field(["type"=>"date"]),
            "time" => new \App\Field(["type"=>"time"]),
            "datetime" => new \App\Field(["type"=>"dateTime"])
         ];
         $this->files = [
            "image" => new \App\File(["jpg","jpeg","png","webp"], 500000),
        ];
        $this->foreignKeys = [
            "Model\Tag" => []
        ];
        $this->adminPannel = [
            "title" => "Articles",
            "icon" => "faNewspaper",
            "order" => 2,
            "fields" => [
                "image" => [
                    "type" => "image",
                    "table" => ["insert","update"]
                ],
                "titre" => [
                    "type" => "text",
                    "table" => ["columns","insert","update"]
                ],
                "texte" => [
                    "type" => "textarea",
                    "table" => ["columns","insert","update"]
                ],
                "url" => [
                    "type" => "url",
                    "table" => ["columns","insert","update"]
                ],
                "tag" => [
                    "type" =>"selectMulti",
                    "table" => ["insert","update"],
                    "foreignTable" => "Model\\Tag",
                    "key" => "nom"
                ],
                "date" => [
                    "type" => "date",
                    "table" => ["columns","insert","update"]
                ]
            ],
        ];
    }
}