<?php

require_once('../includes/init.php');

if(isset($_GET)){
    $photos = sql("SELECT * FROM photo 
    WHERE id_photo = " . $_GET['id_photo']);

    $photo = $photos->fetch();

    echo json_encode($photo);
    
} 