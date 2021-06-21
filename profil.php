<?php

require_once('includes/init.php');

if (!isConnected()) {
    header('location:' . URL . 'connexion.php');
    exit();
}
$chemin = $_SERVER['DOCUMENT_ROOT'] . URL . 'images/';


// Gestion de la suppression d'une annonce
if (isset($_GET['action']) && $_GET['action'] == 'deletea' && !empty($_GET['id']) && is_numeric($_GET['id'])) {
    
    $annonce = sql("SELECT * FROM annonce WHERE id_annonce = :id", array(
        'id' => $_GET['id']
    ));
    if ($annonce->rowCount() > 0) {
        $infos = $annonce->fetch();
        if (isAdmin() || $infos['id_membre'] == $_SESSION['membre']['id_membre']) {
            // Suppression des photos
            $titre = $infos['titre'];
            $id_photo = $infos['id_photo'];
            

            $photos = sql("SELECT * FROM photo WHERE id_photo = $id_photo");
            $photo = $photos->fetch();

            foreach ($photo as $p) {
                if (!empty($p) && file_exists($chemin . $p)) {
                    unlink($chemin . $p);
                }
            }

            // Suppression en bdd
            sql("DELETE FROM photo WHERE id_photo = :id", array(
                'id' => $id_photo
            ));
            sql('DELETE FROM annonce WHERE id_annonce=:id', array(
                'id' => $_GET['id']));

            add_flash("L'annonce $titre a été supprimée", 'warning');

        } else {
            add_flash("Il n'est pas possible de supprimer une annonce dont vous n'êtes pas l'auteur", 'danger');
            header('location:' . $_SERVER['PHP_SELF']);
            exit();
        }
    } else {
        add_flash('Annonce introuvable', 'warning');
    }
}

// Gestion de la suppression du compte
if (isset($_GET['action']) && $_GET['action'] == 'deletec') {

    sql('DELETE FROM membre WHERE id_membre=:id', array('id' => $_SESSION['membre']['id_membre']));
    add_flash("Votre compte a été supprimé. Merci d'avoir été avec nous. A bientôt", 'info');
    header('location:' . URL . 'connexion.php?action=logout');
    exit();
}

if (!empty($_POST)) {

    // Formulaire données personnelles
    if (isset($_POST['update_perso'])) {

        $errors = 0;
        if (empty($_POST['pseudo'])) {
            $errors++;
            add_flash('Le pseudo ne peut pas etre vide', 'danger');
        } else {

            $user = getUserByPseudo($_POST['pseudo']);
            if ($user && $user['id_membre'] != $_SESSION['membre']['id_membre']) {
                $errors++;
                add_flash('Ce pseudo est indisponible', 'danger');
            }
        }

        if (empty($_POST['email'])) {
            $errors++;
            add_flash("L'email ne peut pas etre vide", 'danger');
        } else {
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                $errors++;
                add_flash("L'adresse mail est invalide", 'danger');
            } else {
                $user = getUserByEmail(trim($_POST['email']));
                if ($user &&  trim($_POST['email']) != $_SESSION['membre']['email']) {  // la valeur booléenne de qqchose de rempli est vrai
                    $errors++;
                    add_flash("L'adresse email choisie est indisponible, merci d'en choisir une autre", 'warning');
                } else {
                    $_POST['pseudo'] = trim($_POST['pseudo']);
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

        if ($errors == 0) {
            sql("UPDATE membre SET pseudo=:pseudo, email=:email, nom=:nom, prenom =:prenom, telephone=:telephone, civilite=:civilite WHERE id_membre=:id_membre", array(
                'pseudo' => $_POST['pseudo'],
                'email' => $_POST['email'],
                'nom' => $_POST['nom'],
                'prenom' => $_POST['prenom'],
                'telephone' => $_POST['telephone'],
                'civilite' => $_POST['civilite'],
                'id_membre' => $_SESSION['membre']['id_membre']
            ));
            $_SESSION['membre']['pseudo']  = $_POST['pseudo'];
            $_SESSION['membre']['email']  = $_POST['email'];
            $_SESSION['membre']['nom']  = $_POST['nom'];
            $_SESSION['membre']['prenom']  = $_POST['prenom'];
            $_SESSION['membre']['telephone']  = $_POST['telephone'];
            $_SESSION['membre']['civilite']  = $_POST['civilite'];
            add_flash('Vos informations ont été mises à jour', 'warning');
        }
    }

    // Formulaire mot de passe
    if (isset($_POST['update_password'])) {

        $errors = 0;


        if (empty($_POST['password'])) {
            $errors++;
            add_flash('Merci de saisir votre mot de passe actuel', 'danger');
        } else {
            if (!password_verify($_POST['password'], $_SESSION['membre']['password'])) {
                $errors++;
                add_flash('Mot de passe incorrect', 'danger');
            }
        }

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

            sql("UPDATE membre SET password=:password  WHERE id_membre=:id_membre", array(
                'password' => $password_crypte,
                'id_membre' => $_SESSION['membre']['id_membre']
            ));
            $_SESSION['membre']['password']  =  $password_crypte;
            add_flash('Votre mot de passe a été mise à jour', 'warning');
        }
    }
}

$categories = sql("SELECT * FROM categorie ORDER BY titre");

$annonces = sql("SELECT id_annonce, titre, photo, date_format(date_enregistrement, '%d/%m/%Y') as date_annonceFR 
FROM annonce WHERE id_membre = " . $_SESSION['membre']['id_membre'] . "
ORDER BY date_enregistrement DESC");

$notes = sql("SELECT note.*, date_format(date_enregistrement, '%d/%m/%Y') as date_avisFR 
FROM note WHERE id_membre2 = " . $_SESSION['membre']['id_membre'] . "
ORDER BY date_enregistrement DESC");

$membres = sql("SELECT * , date_format(date_enregistrement, '%d/%m/%Y - %H:%i') as date_enrFR FROM membre WHERE id_membre = " . $_SESSION['membre']['id_membre']);
$selectMembre = $membres->fetch();

$title = 'Profil';

require_once('includes/header.php');
?>

<div class="row">
    <div class="col-lg-4 col-xl-3">
        <div class="list-group" id="list-tab" role="tablist">
            <a class="list-group-item list-group-item-action active" id="profil" data-bs-toggle="list" href="#showProfil" role="tab">Votre Profil</a>
            <a class="list-group-item list-group-item-action" id="infos" data-bs-toggle="list" href="#modifInfos" role="tab">Modifier vos informations</a>
            <a class="list-group-item list-group-item-action" id="pwd" data-bs-toggle="list" href="#modifPWD" role="tab">Modifier votre mot de passe</a>
        </div>
    </div>

    <div class="col-lg">
        <div class="tab-content" id="nav-tabcontent">
            <div class="tab-pane fade" id="modifInfos" role="tabpanel">
                <div class="col-lg offset-xl-1 ">
                    <h2>Modifier vos infos personnelles</h2>
                    <hr class="mb-3">
                </div>
                <div class="row mt-5">

                    <?php require_once('includes/infos_membre.php'); ?>
                </div>

                <div class="row mt-5">
                    <div class="col-lg offset-xl-1 ">
                        <hr class="mb-3">
                        <div class="d-grid gap-2 d-md-flex">
                            <a href="?action=deletec" class="btn btn-outline-danger confirm" data-message="Etes-vous sûr(e) de vouloir  supprimer votre compte ? Cette action est irreversible et supprimera toutes vos données personnelles."><i class="fas fa-exclamation-triangle"></i> Supprimer mon compte</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="modifPWD" role="tabpanel">
                <div class="col-xl-6 col-lg-8 offset-lg-1">
                    <h2>Modifier votre mot de passe</h2>
                    <hr class="mb-3">

                    <form method="post">

                        <div class="mb-3">
                            <label for="password">Mot de passe actuel</label>
                            <input type="password" id="password" name="password" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="newpassword">Nouveau mot de passe</label>
                            <input type="password" id="newpassword" name="newpassword" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="confirmation">Confirmation</label>
                            <input type="password" id="confirmation" name="confirmation" class="form-control">
                        </div>
                        <button class="btn btn-perso" name="update_password">Mettre à jour</button>
                    </form>
                </div>
            </div>
            <div class="tab-pane fade show active" id="showProfil" role="tabpanel">
                <h2>Annonces en ligne</h2>
                <?php if ($annonces->rowCount() > 0) : ?>
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Titre</th>
                                <!-- <th>Catégorie</th> -->
                                <th>Commentaires</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($annonce = $annonces->fetch()) : ?>
                                <tr>
                                    <td class="col-1"><?php echo $annonce['date_annonceFR'] ?></td>
                                    <td class="col-2">
                                        <div><?php echo $annonce['titre'] ?></div>
                                        <img src="<?php echo 'images/'. $annonce['photo'] ?>" alt="<?php echo $annonce['titre'] ?>" class="rounded me-2" width="148">
                                        <!-- <td><?php echo $annonce['categorie'] ?></td> -->
                                    <td class="col">
                                        <?php $commentaires = sql("SELECT c.*, m.pseudo FROM commentaire c INNER JOIN membre m ON m.id_membre = c.id_membre WHERE c.id_annonce = " . $annonce['id_annonce']); ?>
                                        <?php while ($commentaire = $commentaires->fetch()) : ?>
                                            <span id="auteur"><?php echo $commentaire['pseudo'] ?> :</span>
                                            <?php echo $commentaire['commentaire'] ?><Br>
                                        <?php endwhile ?>
                                    </td>
                                    <td class="col-2 btn-actions">
                                        <a href="<?php echo URL ?>annonce.php?id=<?php echo $annonce['id_annonce'] ?>" class="btn btn-outline-primary"><i class="fa fa-eye"></i></a>
                                        <a href="depot_annonce.php?action=edit&id=<?php echo $annonce['id_annonce'] ?>" class="btn btn-outline-secondary mt-1 mb-1"><i class="fa fa-edit"></i></a>
                                        <a href="?action=deletea&id=<?php echo $annonce['id_annonce'] ?>" class="btn btn-outline-danger confirm"><i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>

                            <?php endwhile ?>
                        </tbody>
                    </table>


                <?php else : ?>
                    <div class="mt-4 alert alert-warning">Vous n'avez pas d'annonce en ligne</div>
                <?php endif; ?>

                <h2>Note et avis</h2>
                <?php if ($notes->rowCount() > 0) : ?>
                    <table class="table table-bordered table-hover">
                        <thead>

                        </thead>
                        <tbody>
                            <?php while ($note = $notes->fetch()) : ?>


                            <?php endwhile ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="mt-4 alert alert-warning">Vous n'avez pas de note</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>



<?php


require_once('includes/footer.php');
