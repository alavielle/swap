<?php 

require_once('../includes/init.php');

if(isset($_POST)){


    if($_POST['id_membre'] > 0 && is_numeric($_POST['id_membre'])){ 
        
    $notes = sql("SELECT AVG(note) as 'moyenne' FROM note WHERE id_membre2 = " .$_POST['id_membre']); 
    $note = $notes->fetchAll();     
    
    echo json_encode($note);
    }
}
