<?php 

require_once('../includes/init.php');

if(isset($_POST)){


    if($_POST['id_membre'] > 0 && is_numeric($_POST['id_membre'])){ 
        
    $notes = sql("SELECT note.*, membre.pseudo, date_format(note.date_enregistrement, '%d/%m/%Y à %H:%i') as dateFr FROM note 
    INNER JOIN membre ON note.id_membre1 = membre.id_membre
    where id_membre2 = " .$_POST['id_membre']);
    
    $note = $notes->fetchAll();     
    
    echo json_encode($note);
    }
}
