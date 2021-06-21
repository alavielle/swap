<?php

require_once('../includes/init.php');

if (!empty($_SESSION['membre']['id_membre'])) {
    $OK = 1;
    if (!empty($_POST['note']) || !empty($_POST['avis'])) {
        $OK=2;
        sql("INSERT INTO note (id_membre1, id_membre2, note, avis, date_enregistrement) VALUES (:acheteur, :vendeur, :note, :avis, NOW())", array(
            'acheteur' => $_SESSION['membre']['id_membre'],
            'vendeur' => $_POST['vendeur'],
            'note' => $_POST['note'],
            'avis' => $_POST['avis']
        ));
    }
    if (!empty($_POST['commentaire']) && !empty($_POST['id_annonce']) && is_numeric($_POST['id_annonce'])) {
        $OK="3";        
        sql("INSERT INTO commentaire (id_membre, id_annonce, commentaire, date_enregistrement) VALUES
            (:acheteur, :id_annonce, :commentaire, NOW())", array(
            'acheteur' => $_SESSION['membre']['id_membre'],
            'id_annonce' => $_POST['id_annonce'],
            'commentaire' => $_POST['commentaire']
        ));
    }

    echo json_encode($OK);
}
