# Ploum_framework

Framework PHP pour la création de site web.

## Paramètres

### Globaux

Les paramètres globaux sont dans le fichier : *settings/global.php*

Variables du fichier : 

* $DEBUG = ( bool ) si true affiche les erreurs
* $TITLE = ( string ) 1ère partie du titre du document
* $LANG = ( string ) Attribut lang de la balise html
* $META = ( string ) Content de la balise meta description
* $FAVICON = ( string ) Chemin du fichier du favicon
* $SCRIPTS = ( array ) Liste des scripts qui se chargent sur toutes les pages
* $STYLES = ( array ) Liste des fichiers css qui se chargent sur toutes les pages
* $BASE = ( string ) Chemin du template de base de chaque page
* $HEADER = ( string ) Chemin du template général du header
* $FOOTER = ( string ) Chemin du template général du footer

### Routes

Dans le fichier : *settings/routes.php*

Ce fichier permet de définir différents controleurs en fonction de l'url demandé par l'utilisteur.

Variables du fichier : 

* $ROOT = ( string ) Dossier d'instalation du framework
* $HOME = ( string ) Chemin vers le controleur de la page d'accueil du site
* $NOT_FOND = ( string ) Chemin vers le controleur de la page 404
* $ROUTES = ( array ) Liste des différents controleurs en fonction de l'url

Chaque controleur doit être placé dans le dossier *controller*

Des paramètres peuvent être placé dans l'url afin d'être utilisé dans le code en utilisant le caractère *:*

exemple: *example/:param1/:param2*

## Classes

Les classes de base du framework sont dans le dossier class et utilisent le namespace *App*

Vos propres classes peuvent être placées dans le dossier model avec le namespace *Model*

### Table

Chaque classes héritant de classe *App\Table* représente une table de la base de donnée (des exemples sont présents dans le dossier *Model*).

Ces classes pouront utiliser les classes *App\Field* et *App\File* pour représenter des champs de la base de donnée et *App\ForeignKey.php* pour faire des liens entre différentes tables.

### View

La classe *App\View* permet d'afficher différents templates.

Cette classe peut être instanciée avec différents paramètres (voir le fichier *class/View.php*).

### ResponsiveImage

La classe *App\ResponsiveImage* permet de générer une balise image contenant plusieurs sources pour s'adapter à différentes tailles d'écrans (voir le fichier *class\ResponsiveImage.php*).

## Base de donnée

Le framework a été prévu pour utiliser MySQL.

La configuration de la base de donnée se fait dans le fichier *class/BDD.php*

Après avoir créé une base de donnée vous pouvez générer ou modifier automatiquement les tables de la base donnée avec la commande : *php manage.php migrate*

## Panneau d'administration

Un panneau d'administration du site généré automatiquement est en cours de dévellopement, il utilisera REACT.

## Librairies

TailwindCSS et PHPmailer sont installé de base.