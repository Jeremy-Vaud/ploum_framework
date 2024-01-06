<?php
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
