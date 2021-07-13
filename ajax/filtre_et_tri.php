<?php 

require_once('../includes/init.php');

if(isset($_POST)){
    $filtreCategorie = '';
    $filtreMembre = '';
    $filtreRegion = '';
    $filtrePrix = '';
    $filtre = " ORDER BY date_enregistrement DESC";

    if($_POST['id_categorie'] > 0){ 
        $filtreCategorie = " AND a.id_categorie = " . $_POST['id_categorie'];
    }

    if($_POST['id_membre'] > 0){ 
        $filtreMembre = " AND a.id_membre = " . $_POST['id_membre'];
    }

    if($_POST['region'] != ""){ 
        $filtreRegion = " AND a.pays = '" . $_POST['region'] ."'";
        if($_POST['region'] == "Toutes")  $filtreRegion = "";
    }

    if($_POST['prix'] > 0){
        $filtrePrix = " AND a.prix <= " . $_POST['prix'];
    }

    if($_POST['id_tri'] > 0 && $_POST['id_tri'] < 5){ 
        if ($_POST['id_tri'] == 1 || $_POST['id_tri'] == 2){
           $champ = "date_enregistrement";
           $ordre = ($_POST['id_tri'] == 1) ? "DESC" : '';
       }
       if ($_POST['id_tri'] == 3 || $_POST['id_tri'] == 4){
           $champ = "prix";
           $ordre = ($_POST['id_tri'] == 4) ? "DESC" : '';
       }
       $filtre =  " ORDER BY " . $champ . " " . $ordre;
    }

    $annonces = sql("SELECT a.*, m.pseudo FROM annonce a
            INNER JOIN membre m WHERE a.id_membre = m.id_membre" 
            . $filtreCategorie 
            . $filtreMembre 
            . $filtreRegion 
            . $filtrePrix
            . $filtre 
         );
    $infos = $annonces->fetchAll();
    
    if ($_POST['id_tri'] == 5 ){
        foreach($infos as $key => $value){
            $notes = sql("SELECT AVG(note) as 'moyenne' FROM note WHERE id_membre2 = " .$infos[$key]['id_membre']); 
            $note = $notes->fetch();
            $infos[$key]['note'] =  floatval($note['moyenne']); 
        }
        $infosNote = array_column($infos, 'note');
        $infosMembre = array_column($infos, 'id_membre');
        array_multisort($infosNote, SORT_DESC, $infosMembre, SORT_ASC, $infos);          
    } 

    echo json_encode($infos);
   
}
