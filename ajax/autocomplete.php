<?php

require_once('../includes/init.php');

$motscles = sql("SELECT titre FROM annonce 
UNION SELECT description_courte FROM annonce 
UNION SELECT titre FROM categorie
UNION SELECT motscles FROM categorie
WHERE titre LIKE '%$_GET[myInputValue]%'
");

$suggestions = $motscles->fetchAll();

echo json_encode($suggestions);
