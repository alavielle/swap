<?php

require_once('includes/init.php');

// Gestion de la deconnexion
if(isset($_GET['action']) && $_GET['action'] == 'logout'){
    unset($_SESSION['membre']);
    add_flash('Vous etes déconnecté','warning');
    header('location:' . URL);
    exit();
}

if (isConnected()) {
    header('location:' . URL . 'profil.php');
    exit();
}

// Traitement de la connexion
if (!empty($_POST)) {
    $errors = 0;
    if (empty($_POST['pseudo'])) {
        $errors++;
        add_flash('Merci de saisir votre pseudo', 'danger');
    }
    if (empty($_POST['password'])) {
        $errors++;
        add_flash('Merci de saisir votre mot de passe', 'danger');
    }
    if ($errors == 0) {
        $user = getUserByPseudo($_POST['pseudo']);
        if ($user) {
            if (password_verify($_POST['password'], $user['mdp'])) {
                $_SESSION['membre'] = $user;
                add_flash('Connexion réussie', 'warning');
                header('location:' . URL);
                exit();
            } else {
                add_flash('Erreur sur les identifiants', 'danger');
            }
        } else {
            add_flash('Erreur sur les identifiants', 'danger');
        }
    }
}

$title = "Connexion";
$subtitle = "Connect";
require_once('includes/header.php');
?>
<div class="row justify-content-center">
    <div class="col-md-8 col-xl-4 border border-dark p-5 rounded">
        <h1>Connexion</h1>
        <hr class="mb-3">
        <form method="post">
            <div class="mb-3">
                <label for="pseudo" class="form-label">Pseudo</label>
                <input type="text" id="pseudo" name="pseudo" class="form-control" value="<?php echo $_POST['pseudo'] ?? '' ?>">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" id="password" name="password" class="form-control">
            </div>
            <div>
                <a href="<?php echo URL ?>reinitmdp.php">Oubli du mot de passe</a>
            </div>
            <div class="mb-3 text-end">
                <button type="submit" class="btn btn-perso">Se connecter</button>
            </div>
        </form>
    </div>
</div>
<div class="row">
    <div class="col text-center mt-4">
        <p>Pas encore de compte ? Vous pouvez en créer un en <a href="<?php echo URL ?>inscription.php">cliquant ici </a></p>
    </div>
</div>




<?php
require_once('includes/footer.php');
