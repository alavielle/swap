<?php

require_once('includes/init.php');

if (!empty($_POST)) { // formulaire soumis
    $errors = 0;
    if (empty($_POST['email'])) {
        $errors++;
        add_flash('Merci de saisir votre adresse mail', 'danger');
    } else {
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors++;
            add_flash('Adresse email invalide', 'danger');
        } else {
            $_POST['email'] = trim($_POST['email']);
        }
    }
    if (empty($_POST['objet'])) {
        $errors++;
        add_flash('Merci de renseigner un objet', 'danger');
    } else {
        $_POST['objet'] = trim($_POST['objet']);
    }
    if (empty($_POST['message'])) {
        $errors++;
        add_flash('Merci de renseigner un message', 'danger');
    } else {
        $_POST['message'] = trim($_POST['message']);
    }

    $headers[] = 'MIME-VERSION:1.0';
    $headers[] = 'Content-type:text/html; charset=iso-8859-1';
    $headers[] = 'From: ' . $_POST['email'];

    $admins = sql("SELECT email FROM membre WHERE statut = 1");
    if ($admins->rowCount() > 0){
        while($admin = $admins->fetch()){
            mail($admin['email'], $_POST['objet'], $_POST['message'], implode(PHP_EOL, $headers));
        }
    }
    add_flash("Votre message a bien été envoyé.", 'info');
    header('location:'.URL.'index.php');
    exit();

}
$title = "Contact";
require_once('includes/header.php');

?>
<div class="row justify-content-center">
    <div class="col-xl-5 col-lg-6 col-sm-10 border border-dark p-5 rounded">
        <h1>Contactez-nous</h1>
        <hr class="mb-3">
        <form method="post">

                    <div class="mb-3">
                        <label for="email" class="form-label">Votre email</label>
                        <input type="text" id="email" name="email" class="form-control" value="">
                    </div>
                    <div class="mb-3">
                        <label for="objet" class="form-label">Objet</label>
                        <input type="text" id="objet" name="objet" class="form-control" value="">
                    </div>
                    <div class="mb-3">
                        <label for="nom" class="form-label">Message</label>
                        <textarea rows="10"  id="message" name="message" class="form-control" value=""></textarea>
                    </div>
                    
                <div class="d-grid col-md-4 mx-auto ">
                    <button type="submit" class="btn btn-perso mt-4">Envoyer</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
require_once('includes/footer.php');
