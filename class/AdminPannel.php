<?php

namespace App;

class AdminPannel {

    protected $data = [];

    private function findTables() {
        foreach (scandir('class') as $file) {
            if ($file !== "." && $file !== "..") {
                require_once "class/" . $file;
            }
        }
        foreach (scandir('model') as $file) {
            if ($file !== "." && $file !== "..") {
                require_once "model/" . $file;
            }
        }
        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, "App\Table")) {
                $obj = new $class;
                $data = $obj->getForAdminPannel();
                if ($data) {
                    $this->data[] = $data;
                }
            }
        }
    }

    public function generate() {
        $this->findTables();
        $file = fopen("adminSrc/data.json", "w");
        fwrite($file, json_encode($this->data));
        echo "Le fichier adminSrc/data.json a été crée";
    }
}
