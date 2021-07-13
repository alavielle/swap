<?php 

require_once('../includes/init.php');

if(isset($_POST)){

        
    $topActifs = sql("SELECT m.id_membre, m.nom, m.prenom, date_format(m.date_enregistrement, '%d/%m/%Y') as date_origine, COUNT(a.id_annonce) as nb_annonces FROM membre m
    INNER JOIN annonce a USING (id_membre)
    GROUP BY m.id_membre 
    ORDER BY COUNT(a.id_annonce) 
    DESC LIMIT 5"); 
    $topActif = $topActifs->fetchAll();     
    
    echo json_encode($topActif);

}
