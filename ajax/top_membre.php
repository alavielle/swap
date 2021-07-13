<?php 

require_once('../includes/init.php');

if(isset($_POST)){


    // if($_POST['top'] == "note"){ 
        
    $topNotes = sql("SELECT m.id_membre, m.nom, m.prenom, AVG(n.note) as moyenne, COUNT(n.id_note) as nb_avis FROM membre m
    INNER JOIN note n WHERE n.id_membre2 = m.id_membre
    GROUP BY m.id_membre 
    ORDER BY AVG(note) 
    DESC LIMIT 5"); 
    $topNote = $topNotes->fetchAll();     
    
    echo json_encode($topNote);
    // }
}
