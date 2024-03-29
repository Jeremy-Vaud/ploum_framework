<?php

/*
 * Example de class représentant un tag
 */

namespace Model;

class Tag extends \App\Table {
    public function __construct() {
        $this->fields = [
            "nom" => new \App\Field(["type" => "char", "length" => 100, "admin" => ["columns", "insert", "update"]])
        ];
        $this->adminPannel = [
            "title" => "Tags",
            "slug" => "tags",
            "icon" => "faTags",
            "order" => 3
        ];
    }
}
