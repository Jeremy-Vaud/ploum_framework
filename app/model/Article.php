<?php

/*
 * Example de class représentant un article
 */

namespace Model;

class Article extends \App\Table {
    public function __construct() {
        $this->fields = [
            "image" => new \App\Image(["width" => 800, "maxSize" => 500000, "default" => "img/favicon.ico", "admin" => ["insert", "update"]]),
            "pdf" => new \App\File(["accept" => ["pdf"], "maxSize" => 500000, "admin" => ["insert", "update"], "public" => false]),
            "titre" => new \App\Field(["type" => "char", "length" => 100, "admin" => ["columns", "insert", "update"]]),
            //"texte" => new \App\Field(["type"=>"text", "admin" => ["columns","insert","update"]]),
            //"url" => new \App\Field(["type"=>"url", "admin" => ["columns","insert","update"]]),
            //"date" => new \App\Field(["type"=>"date", "admin" => ["columns","insert","update"]]),
            //"time" => new \App\Field(["type"=>"time", "admin" => ["columns","insert","update"]]),
            //"datetime" => new \App\Field(["type"=>"dateTime", "admin" => ["columns","insert","update"]]),
            //"nombre" => new \App\Field(["type"=>"int", "admin" => ["columns","insert","update"]]),
            "cle" => new \App\ForeignKey("Model\Tag", ["column" => "nom", "admin" => ["columns", "insert", "update"]]),
            "tag" => new \App\MultipleForeignKeys("Model\Tag", ["column" => "nom", "admin" => ["insert", "update"]]),
            "checkbox" => new \App\Field(["type"=>"bool", "admin" => ["columns","insert","update"]]),
            "richText" => new \App\Field(["type" => "richText", "length" => 100, "admin" => ["insert", "update"]]),
            "role" => new \App\Field(["type" => "select", "value" => "user", "choices" => ["user", "admin", "superAdmin"], "admin" => ["columns","insert","update"]])
        ];
        $this->adminPannel = [
            "title" => "Articles",
            "slug" => "articles",
            "icon" => "faNewspaper",
            "order" => 2,
        ];
        parent::__construct();
    }
}
