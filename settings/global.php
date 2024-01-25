<?php
// Cloud
global $CLOUD;
$CLOUD = true;

// Pour la balise head
global $TITLE, $TAG, $LANG, $META, $FAVICON;
$TITLE = "Ploum";
$TAG = null;
$LANG = "fr";
$META = "Description global";
$FAVICON = "img/favicon.ico";

//Fonts
$FONTS = "https://fonts.googleapis.com/css2?family=Langar&family=Niramit:wght@300;700&display=swap";

// Scripts et styles
global $SCRIPTS, $STYLES;
$SCRIPTS = [
    //"assets/js/main.js"
];
$STYLES = [
    "assets/fonts/font-face.css",
    "assets/css/style.css"
];

// Date
date_default_timezone_set('Europe/Paris');

// Templates (dans le dossier view)
global $BASE, $HEADER, $FOOTER;
$BASE = "base.php";
$HEADER = "header.php";
$FOOTER = "footer.php";
