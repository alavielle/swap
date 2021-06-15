<?php

require_once('../includes/init.php');

$motscles = sql("SELECT titre FROM annonce 
UNION SELECT description_courte FROM annonce 
UNION SELECT description_longue FROM annonce
UNION SELECT titre FROM categorie
UNION SELECT motscles FROM categorie
");

$suggestions = $motscles->fetchAll();

$reponses = array();
foreach ($suggestions as $suggestion) {
    if (strpos(strtoupper($suggestion['titre']), strtoupper($_GET['myInputValue']))) {
        $reponses[]['value'] = strtoupper($suggestion['titre']);
    }
}

echo json_encode($reponses);
