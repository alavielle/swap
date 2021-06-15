<?php

require_once('../includes/init.php');

if(isset($_GET)){
    $motscles = sql("SELECT a.*, c.titre as categorie, c.motscles, m.pseudo FROM annonce a 
    INNER JOIN categorie c USING (id_categorie)
    INNER JOIN membre m USING (id_membre)
    WHERE a.titre LIKE '%$_GET[myInputValue]%' 
    OR a.description_courte LIKE '%$_GET[myInputValue]%'
    OR a.description_longue LIKE '%$_GET[myInputValue]%'
    OR c.titre LIKE '%$_GET[myInputValue]%'
    OR c.motscles LIKE '%$_GET[myInputValue]%'
    ");

    $suggestions = $motscles->fetchAll();

    echo json_encode($suggestions);
    
} 
