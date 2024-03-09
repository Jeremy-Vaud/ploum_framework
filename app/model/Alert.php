<?php

namespace Model;

class Alert extends \App\EditArea {
    public function __construct() {
        $this->id = "alert";
        $this->fields = [
            "titre" => new \App\Field(["type" => "char", "length" => 100]),
            "img" => new \App\Image(["width" => 800, "maxSize" => 500000]),
            "richText" => new \App\Field(["type" => "richText", "length" => 100]),
            "pdf" => new \App\File(["type" => ["pdf"], "maxSize" => 500000, "public" => false]),
            "tag" => new \App\MultipleForeignKeys("Model\Tag", ["key" => "nom"]),
            "cle" => new \App\ForeignKey("Model\Tag", ["key" => "nom"]),
            "role" => new \App\Field(["type" => "select", "value" => "user", "choices" => ["user", "admin", "superAdmin"]])
        ];
        $this->adminPannel = [
            "title" => "Alert",
            "slug" => "alert",
            "icon" => "faComment",
            "order" => 4,
        ];
        parent::__construct();
    }
}
