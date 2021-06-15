<?php

require_once('includes/init.php');

$title ="Erreur 404";
require_once('includes/header.php');
?>

<div class="row">
    <div class="col mt-5 text-center">
        <img src="<?php echo URL ?>images/404.png" class="img-fluid w-50 my-3">
        <p>
            Le contenu que vous esssayer d'atteindre n'existe pas ou a été supprimé
        </p>
        <p>
            <a href="<?php echo URL ?>" class="btn btn-perso">Revenir à la page d'accueil</a>
        </p>
    </div>
</div>

<?php
require_once('includes/footer.php');