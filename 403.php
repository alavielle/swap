<?php

require_once('includes/init.php');

$title ="Erreur 404";
require_once('includes/header.php');
?>

<div class="row">
    <div class="col mt-5 text-center">
        <img src="<?php echo URL ?>images/site/403.png" class="img-fluid w-25 my-3">
        <p>
            Accès interdit...
        </p>
        <p>
            <a href="<?php echo URL ?>" class="btn btn-perso">Revenir à la page d'accueil</a>
        </p>
    </div>
</div>

<?php
require_once('includes/footer.php');