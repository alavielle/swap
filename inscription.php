<?php

require_once('includes/init.php');

// Si je suis connecté et que je tente de rentrer l'url de la page d'inscription, je suis redirigé vers ma page profil
if (isConnected()) {
    // avant la fonction header, aucun echo, aucune balise html
    header('location:' . URL . 'profil.php');
    exit(); // Stoppe le script php
}
// Traitement du formulaire
if (!empty($_POST)) { // formulaire soumis
    $errors = 0;

    if (empty(trim($_POST['pseudo']))) {
        $errors++;
        add_flash('Merci de choisir un pseudo', 'danger');
    } else if(iconv_strlen($_POST['pseudo']) < 3 || iconv_strlen($_POST['pseudo']) > 30){
        $errors++;
        add_flash('Le pseudo doit contenir au moins 3 caractères et moins de 30', 'danger');      
    } else {
        $pattern = '#^[a-z0-9\]\[_-]+$#i';
        if (!preg_match($pattern, $_POST['pseudo'])) {
            $errors++;
            add_flash('Le pseudo ne peut contenir que des lettres ou chiffres', 'danger');
        }
        $user = getUserByPseudo(trim($_POST['pseudo']));
        if ($user) {  // la valeur booléenne de qqchose de rempli est vrai
            $errors++;
            add_flash('Le pseudo choisi est indisponible, merci d\'en choisir un autre', 'warning');
        } else {
            $_POST['pseudo'] = trim($_POST['pseudo']);
        }
    }

    if (empty($_POST['password'])) {
        $errors++;
        add_flash('Merci de saisir un mot de passe', 'danger');
    } else {
        $pattern = '#^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])[\S]{8,20}$#';
        if (!preg_match($pattern, $_POST['password'])) {
            $errors++;
            add_flash('Le mot de passe doit être composé de 8 à 20 caractères, comprenant au moins une majuscule, une minuscule et un chiffre', 'danger');
        }
    }

    if (empty($_POST['confirmation'])) {
        $errors++;
        add_flash('Merci de confirmer votre mot de passe', 'danger');
    } else {
        if (!empty($_POST['password']) && ($_POST['confirmation'] !== $_POST['password'])) {
            $errors++;
            add_flash('La confirmation ne concorde pas avec le mot de passe', 'danger');
        }
    }

    if (empty($_POST['email'])) {
        $errors++;
        add_flash('Merci de saisir votre adresse mail', 'danger');
    } else {
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors++;
            add_flash('Adresse email invalide', 'danger');
        } else {
            $user = getUserByEmail(trim($_POST['email']));
            if ($user) {  // la valeur booléenne de qqchose de rempli est vrai
                $errors++;
                add_flash("L'adresse email choisie est indisponible, merci d'en choisir une autre", 'warning');
            } else {
                $_POST['email'] = trim($_POST['email']);
            }
        }
    }

    if (empty($_POST['nom'])) {
        $errors++;
        add_flash('Merci de renseigner votre nom', 'danger');
    } else {
        $_POST['nom'] = trim($_POST['nom']);
    }

    if (empty($_POST['prenom'])) {
        $errors++;
        add_flash('Merci de renseigner votre prénom', 'danger');
    } else {
        $_POST['prenom'] = trim($_POST['prenom']);
    }

    if (empty($_POST['telephone'])) {
        $errors++;
        add_flash('Merci de saisir un numéro de téléphone', 'danger');
    } else {
        $pattern = '#^[0-9]{2}[-/. ]?[0-9]{2}[-/. ]?[0-9]{2}[-/. ]?[0-9]{2}[-/. ]?[0-9]{2}?$#';
        if (!preg_match($pattern, $_POST['telephone'])) {
            $errors++;
            add_flash("Le format du numéro de téléphone n'est pas valide", 'danger');
        } else {
            $_POST['telephone'] = formatFrenchPhoneNumber($_POST['telephone']);
        }
    }

    if (empty($_POST['civilite'])) {
        $errors++;
        add_flash('Merci de sélectionner une civilité', 'danger');
    } 

    // A ce stade, si $error vaut tjs 0, tout est OK 
    if ($errors == 0) {
        sql("INSERT INTO membre VALUES (NULL, :pseudo, :password, :nom, :prenom, :telephone, :email, :civilite, 0, NOW(), NULL, NULL)", array(
            'pseudo' => trim($_POST['pseudo']),
            'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'nom' => trim($_POST['nom']),
            'prenom' => trim($_POST['prenom']),
            'telephone' =>  $_POST['telephone'],
            'email' => $_POST['email'],
            'civilite' =>  $_POST['civilite']
        ));
        add_flash('Inscription réussie, vous pouvez vous connecter', 'success');
        header('location:' . URL . 'connexion.php');
        exit();
    }
}


$title = "Inscription";
$subtitle = "Connect";
require_once('includes/header.php');

?>
<div class="row justify-content-center">
    <div class="col-xl-7 col-lg-8 col-sm-10 border border-dark p-5 rounded">
        <h1>Inscription</h1>
        <hr class="mb-3">
        <form method="post">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="pseudo" class="form-label">Pseudo*</label>
                        <input type="text" id="pseudo" name="pseudo" class="form-control" value="<?php echo $_POST['pseudo'] ?? '' ?>">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe*</label>
                        <input type="password" id="password" name="password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="confirmation" class="form-label">confirmation du mot de passe*</label>
                        <input type="password" id="confirmation" name="confirmation" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email*</label>
                        <input type="text" id="email" name="email" class="form-control" value="<?php echo $_POST['email'] ?? '' ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom*</label>
                        <input type="text" id="nom" name="nom" class="form-control" value="<?php echo $_POST['nom'] ?? '' ?>">
                    </div>
                    <div class="mb-3">
                        <label for="prenom" class="form-label">Prénom*</label>
                        <input type="text" id="prenom" name="prenom" class="form-control" value="<?php echo $_POST['prenom'] ?? '' ?>">
                    </div>
                    <div class="mb-3">
                        <label for="telephone" class="form-label">Téléphone*</label>
                        <input type="text" id="telephone" name="telephone" class="form-control" value="<?php echo  $_POST['telephone'] ?? '' ?>">
                    </div>
                    <div class="mb-3">
                        <label for="civilite" class="form-label">Civilité*</label>
                        <select id="civilite" name="civilite" class="form-select">
                            <option disabled <?php if (empty($_POST['civilite'])) echo 'selected' ?>>Choisir une civilité</option>
                            <option value="m" <?php if (!empty($_POST['civilite']) && $_POST['civilite'] == "m") echo 'selected' ?>>Homme</option>
                            <option value="f" <?php if (!empty($_POST['civilite']) && $_POST['civilite'] == "f") echo 'selected' ?>>Femme</option>
                        </select>
                    </div>
                </div>
                <div class="d-grid col-md-4 mx-auto ">
                    <button type="submit" class="btn btn-perso mt-4">S'inscrire</button>
                </div>
            </div>
            <p class="fst-italic fs-6">* champs obligatoires</p>
        </form>
        
    </div>
</div>
<div class="row">
    <div class="col text-center mt-4">
        <p>Déjà inscrit ? Vous pouvez vous connecter en <a href="<?php echo URL ?>connexion.php">cliquant ici </a></p>
    </div>
</div>

<?php
require_once('includes/footer.php');
