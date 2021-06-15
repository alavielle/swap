<?php

require_once('includes/init.php');
if(!empty($_POST)){
    if(!empty($_POST['email'])){
        if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
            $user = sql("SELECT * FROM membre WHERE email=:email", array(
                'email' => $_POST['email']
            ));
            if($user->rowCount() > 0){
                // génération du mail
                $infosuser = $user->fetch();
                $destinataire = $infosuser['email'];
                $expiration = time () + 30*60;
                //$token = str_repeat(uniqid(), 3);
                $token = uniqid() .  uniqid() .  uniqid();
                sql("UPDATE membre SET token=:token, expiration=:expiration WHERE id_membre=:id_membre", array(
                    'token' => $token,
                    'expiration' => $expiration,
                    'id_membre' => $infosuser['id_membre']
                ));
                $lien = $_SERVER['REQUEST_SCHEME']. '://' . $_SERVER['HTTP_HOST'] . URL . 'validreinit.php?email='.$infosuser['email'].'&token='.$token;
                $message = '<p>Bonjour ' . $infosuser['login'] . ',<br>Voici le lien à suivre pour réinitialiser votre mot de passe. Ce lien est valide 30 minutes.<br>
                <a href="'.$lien.'">'.$lien.'</a><br>
                A bientôt sur notre site</p>';
                $headers[] = 'MIME-VERSION:1.0';
                $headers[] = 'Content-type:text/html; charset=iso-8859-1';
                $headers[] = 'From: noreply@swap.fr';
                $sujet = 'SWAP - Demande de réinitialisation du mot de passe';
                mail($infosuser['email'], $sujet, $message, implode(PHP_EOL, $headers));
            }
            add_flash("Si cette adresse est trouvée dans notre base, un email vous permettant de réinitialiser votre mot de passe sera envoyé dans quelques instants", 'info');
            header('location:'.URL.'connexion.php');
            exit();
        }

    } else {
        add_flash('Merci de renseigner votre email', 'danger');
    }
}
$title = "Demande de réinitialisation du mot de passe";
require_once('includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-md-8 col-xl-4 border border-dark p-5 rounded">
        <h1>Oubli du mot de passe</h1>
        <hr class="mb-3">
        <form method="post">
            <div class="mb-3">
                <label for="email" class="form-label">Email du compte</label>
                <input type="email" id="email" name="email" class="form-control">
            </div>

            <button type="submit" class="btn btn-warning">Recevoir un lien de réinitialisation</button>
            <a href="<?php echo URL ?>connexion.php" class="btn btn-secondary">Retour</a>
        </form>
    </div>
</div>


<?php
require_once('includes/footer.php');
