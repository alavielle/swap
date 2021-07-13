<?php 

require_once('../includes/init.php');

if(isset($_POST)){

        
    $topAnnonces = sql("SELECT m.nom, m.prenom, a.titre, date_format(a.date_enregistrement, '%d/%m/%Y') as date_origine FROM annonce a
    INNER JOIN membre m USING (id_membre)
    ORDER BY a.date_enregistrement 
    LIMIT 5"); 
    $topAnnonce = $topAnnonces->fetchAll();     
    
    echo json_encode($topAnnonce);

}