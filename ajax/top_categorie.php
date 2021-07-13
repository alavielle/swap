<?php 

require_once('../includes/init.php');

if(isset($_POST)){

        
    $topCategories = sql("SELECT c.titre, COUNT(a.id_annonce) as nb_annonces FROM categorie c
    INNER JOIN annonce a USING (id_categorie)
    GROUP BY c.id_categorie 
    ORDER BY COUNT(a.id_annonce) 
    DESC LIMIT 5"); 
    $topCategorie = $topCategories->fetchAll();     
    
    echo json_encode($topCategorie);

}