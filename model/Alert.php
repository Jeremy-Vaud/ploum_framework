<?php

namespace Model;

class Alert extends \App\EditArea {
    public function __construct() {
        $this->id = "alert";
        $this->fields = [
            "titre" => new \App\Field(["type" => "char", "length" => 100]),
            "img" => new \App\Image(["width" => 800, "maxSize" => 500000]),
            "richText" => new \App\Field(["type" => "richText", "length" => 100]),
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
