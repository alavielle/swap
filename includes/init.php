<?php

// fuseau horaire
date_default_timezone_set('Europe/Paris');

// langue locale - dépend du système d'exploitation
setlocale(LC_ALL, 'fr_FR.utf8', 'fra.utf8');

// Nom et ouverture de session
session_name('MYSWAP'); // nom par defaut : PHPSESSID
session_start();

global $subtitle;

// Connexion BDD en local
$pdo = new PDO(
    'mysql:host=localhost; charset=utf8;dbname=swap',
    'root',
    '',
    array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING, // On se met en ERRMODE_SILENT en mode production
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    )
);

// Connexion BDD en ligne
// $pdo = new PDO(
//     'mysql:host=cl1-sql11; charset=utf8;dbname=tfm80212',
//     'tfm80212',
//     'Gk6aUl8{e',
//     array(
//         PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING, // On se met en ERRMODE_SILENT en mode production
//         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
//     )
// );

// Inclusion des fonctions du site
require_once('functions.php');

// Constantes du site
// define('URL', '/');
define('URL', '/swap/');

