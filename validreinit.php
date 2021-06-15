<?php

require_once('includes/init.php');

if(isset($_GET['email']) && isset($_GET['token'])){
    $user = sql("SELECT * FROM membre WHERE email=:email AND token=:token AND expiration >=:expiration", array(
        'email' => $_GET['email'],
        'token' => $_GET['token'],
        'expiration' => time()
    )); 
    if($user->rowCount() > 0){
        $infosuser = $user->fetch();

    } else {
        add_flash('Lien déjà utilisé ou expiré', 'danger');
        header('location:'.URL.'connexion.php');
        exit();
    }
} else {
    header('location:' . URL . 'connexion.php');
    exit();
}

// Traitement du formulaire

if (!empty($_POST)) {
    if (isset($_POST['update_password'])) {

        $errors = 0;

        if (empty($_POST['newpassword'])) {
            $errors++;
            add_flash('Merci de saisir un nouveau mot de passe', 'danger');
        } else {
            $pattern = '#^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])[\S]{8,20}$#';
            if (!preg_match($pattern, $_POST['newpassword'])) {
                $errors++;
                add_flash('Le nouveau mot de passe doit être composé de 8 à 20 caractères comprenant au moins une minuscule, une majuscule et un chiffre', 'danger');
            }
        }


        if (empty($_POST['confirmation'])) {
            $errors++;
            add_flash('Merci de confirmer votre mot de passe', 'danger');
        } else {
            if (!empty($_POST['newpassword']) && ($_POST['confirmation'] !== $_POST['newpassword'])) {
                $errors++;
                add_flash('La confirmation ne concorde pas avec le mot de passe', 'danger');
            }
        }

        if ($errors == 0) {

            $password_crypte = password_hash($_POST['newpassword'], PASSWORD_DEFAULT);

            sql("UPDATE membre SET mdp=:password, token = NULL WHERE id_membre=:id_membre", array(
                'password' => $password_crypte,
                'id_membre' => $infosuser['id_membre']
            ));
            add_flash('Votre mot de passe a été réinitialisé, vous pouvez vous reconnecter', 'warning');
            header('location:' . URL . 'connexion.php');
            exit();
        }
    }
}
// Traitement du $_POST avec controles 
// mise à jour du mot de passe en version cryptée
// vider le champ token 

$title ="Réinitialisation du mot de passe";
require_once('includes/header.php');
?>

<!-- Formulaire avec les champs nouveau mot de passe et confirmation -->
<div class="row justify-content-center">
    <div class="col-md-8 col-xl-4 border border-dark p-5 rounded">
        <h1>Réinitialisation du mot de passe</h1>
        <hr class="mb-3">
        <form method="post">
            <div class="mb-3">
                <label for="newpassword" class="form-label">Mot de passe</label>
                <input type="password" id="newpassword" name="newpassword" class="form-control">
            </div>
            <div class="mb-3">
                <label for="confirmation" class="form-label">confirmation</label>
                <input type="password" id="confirmation" name="confirmation" class="form-control">
            </div>

            <div class="mb-3 text-end">
                <button type="submit" class="btn btn-secondary" name="update_password">Valider</button>
            </div>
        </form>
    </div>
</div>


<?php
require_once('includes/footer.php');