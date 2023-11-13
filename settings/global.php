<?php

// Afficher les messages d'erreur
$DEBUG = true;

// Redirection https
$HTTPS = false;

// Cloud
$CLOUD = true;

// Pour la balise head
$TITLE = "Ploum";
$LANG = "fr";
$META = "Description global";
$FAVICON = "img/favicon.ico";

//Fonts
$FONTS = "https://fonts.googleapis.com/css2?family=Langar&family=Niramit:wght@300;700&display=swap";

// Scripts et styles
$SCRIPTS = [];
$STYLES = [
    "fonts/font-face.css",
    "dist/style.css"
];

// Date
date_default_timezone_set('Europe/Paris');

// Templates
$BASE = "view/base.php";
$HEADER = "view/header.php";
$FOOTER = "view/footer.php";

// Email admin
$ADMIN_MAIL = [
    "Host" => "localhost",
    "SMTPAuth" => false,
    "Username" => null,
    "Password" => null,
    "SMTPSecure" => "",
    "Port" => 1025,
    "From" => ["admin@ploum.fr", "admin"]
];
