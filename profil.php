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
                'id' => $_GET['id']
            ));

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

    $id_photos = sql('SELECT id_photo FROM annonce WHERE id_membre=:id', array(
        'id' => $_SESSION['membre']['id_membre']
    ));
    while ($id_photo = $id_photos->fetch()) {
        $photos = sql('SELECT * FROM photo WHERE id_photo = ' . $id_photo['id_photo']);
        $photo = $photos->fetch();
        foreach ($photo as $p) {
            suppPhotos($p);
        }
        sql("DELETE FROM photo WHERE id_photo = " . $photo['id_photo']);
    }
    sql('DELETE FROM annonce WHERE id_membre=:id', array(
        'id' => $_SESSION['membre']['id_membre']
    ));
    sql('DELETE FROM membre WHERE id_membre=:id', array(
        'id' => $_SESSION['membre']['id_membre']
    ));

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
            if (!password_verify($_POST['password'], $_SESSION['membre']['mdp'])) {
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

            sql("UPDATE membre SET mdp=:password  WHERE id_membre=:id_membre", array(
                'password' => $password_crypte,
                'id_membre' => $_SESSION['membre']['id_membre']
            ));
            $_SESSION['membre']['mdp']  =  $password_crypte;
            add_flash('Votre mot de passe a été mis à jour', 'warning');
        }
    }
}

$categories = sql("SELECT * FROM categorie ORDER BY titre");

$annonces = sql("SELECT id_annonce, titre, photo, date_format(date_enregistrement, '%d/%m/%Y') as date_annonceFR 
FROM annonce WHERE id_membre = " . $_SESSION['membre']['id_membre'] . "
ORDER BY date_enregistrement DESC");

$notes = sql("SELECT n.*, m.email, m.pseudo as acheteur, date_format(n.date_enregistrement, '%d/%m/%Y à %Hh%i') as date_enrFR 
FROM note n 
LEFT JOIN membre m ON n.id_membre1 = m.id_membre
WHERE n.id_membre2 = " . $_SESSION['membre']['id_membre'] . "
ORDER BY n.date_enregistrement DESC");

$membres = sql("SELECT * , date_format(date_enregistrement, '%d/%m/%Y - %H:%i') as date_enrFR FROM membre WHERE id_membre = " . $_SESSION['membre']['id_membre']);
$selectMembre = $membres->fetch();

$title = 'Profil';
$subtitle = "Connect";
require_once('includes/header.php');
?>

<div class="row">
    <div class="col-lg-4 col-xl-3">
        <div class="list-group" id="list-tab" role="tablist">
            <a class="list-group-item list-group-item-action active" id="profilAnnonce" data-bs-toggle="list" href="#showProfilAnnonce" role="tab">Vos annonces</a>
            <a class="list-group-item list-group-item-action" id="profilNote" data-bs-toggle="list" href="#showProfilNote" role="tab">Notes et avis</a>
            <a class="list-group-item list-group-item-action" id="infos" data-bs-toggle="list" href="#modifInfos" role="tab">Modifier vos informations</a>
            <a class="list-group-item list-group-item-action" id="pwd" data-bs-toggle="list" href="#modifPWD" role="tab">Modifier votre mot de passe</a>
        </div>
    </div>

    <div class="col-lg-8 col-xl-9">
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
            <div class="tab-pane fade show active" id="showProfilAnnonce" role="tabpanel">
                <h2>Vos annonces</h2>
                <hr class="mb-3">
                <?php if ($annonces->rowCount() > 0) : ?>
                    <table class="table cell-border table-hover" id="tableAnnonceProfil">
                        <thead class=" align-middle ">
                            <tr>
                                <th class="text-center">Date</th>
                                <th>Titre</th>
                                <!-- <th>Catégorie</th> -->
                                <th>Commentaires</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($annonce = $annonces->fetch()) : ?>
                                <tr>

                                    <td class="col-1"><?php echo $annonce['date_annonceFR'] ?></td>
                                    <td class="col-2">
                                        <div><?php echo $annonce['titre'] ?></div>
                                        <img src="<?php echo 'images/' . $annonce['photo'] ?>" alt="<?php echo $annonce['titre'] ?>" class="rounded me-2" width="148">
                                    <td class="col">
                                        <?php $commentaires = sql("SELECT c.*, m.pseudo, m.id_membre, date_format(c.date_enregistrement, '%d/%m/%Y à %Hh%i') as date_commFR FROM commentaire c INNER JOIN membre m ON m.id_membre = c.id_membre WHERE c.id_annonce = " . $annonce['id_annonce']); ?>
                                        <?php while ($commentaire = $commentaires->fetch()) : ?>
                                            <span id="auteur">
                                                <?php if ($commentaire['id_membre'] == $_SESSION['membre']['id_membre']) :
                                                    echo 'Vous avez répondu';
                                                else : echo 'Par ' . $commentaire['pseudo'] ?>
                                                <?php endif ?>
                                                , le <?php echo $commentaire['date_commFR'] ?> : </span>
                                            <pre><?php echo $commentaire['commentaire'] ?></pre><Br>
                                        <?php endwhile ?>
                                    </td>
                                    <td class="col-2 btn-actions text-center">
                                        <a href="<?php echo URL ?>annonce.php?id=<?php echo $annonce['id_annonce'] ?>" class="btn btn-outline-primary"><i class="fa fa-eye"></i></a>
                                        <a href="depot_annonce.php?action=edit&id=<?php echo $annonce['id_annonce'] ?>" class="btn btn-outline-secondary mt-1 mb-1"><i class="fa fa-edit"></i></a>
                                        <a href="?action=deletea&id=<?php echo $annonce['id_annonce'] ?>" class="btn btn-outline-danger confirm"><i class="fa fa-trash"></i></a>
                                        <p><a href="" data-index="<?= $annonce['id_annonce'] ?>" class="btn btn-outline-warning m-3 ouvertureModalReponse">Répondre</a></p>

                                        <div class="modal fade" id="modalReponse<?= $annonce['id_annonce'] ?>" aria-labelledby="modalReponseLabel" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title text-secondary">Répondre :</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row mb-3">
                                                            <div class="col">
                                                                <p class="fw-bold"><?php echo $annonce['titre'] ?></p>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <textarea class="form-control" rows="6" id="commentaire-text<?php echo $annonce['id_annonce'] ?>"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                        <button type="button" class="btn btn-perso reponse" data-index="<?= $annonce['id_annonce'] ?>">Répondre</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                            <?php endwhile ?>
                        </tbody>
                    </table>

                <?php else : ?>
                    <div class="mt-4 alert alert-warning">Vous n'avez pas d'annonce en ligne</div>
                <?php endif; ?>
            </div>
            <div class="tab-pane fade" id="showProfilNote" role="tabpanel">
                <h2>Notes et avis</h2>
                <hr class="mb-3">
                <?php if ($notes->rowCount() > 0) : ?>
                    <div class="row alert alert-warning m-4 px-5">Votre note moyenne est de 
                    <?php
                        $moyennes = sql("SELECT AVG(note) as 'moyenne' FROM note where id_membre2 = " . $_SESSION['membre']['id_membre']);
                        $moyenne = $moyennes->fetch(); ?>
                        <span  class="col "><?= round($moyenne['moyenne'], 2) ?> / 5</span>
                        <span class="col px-5 text-end"><?php $i = 1;
                        while ($i <= $moyenne['moyenne']) : ?>
                            <i class="fas fa-star"></i>
                        <?php
                            $i++;
                        endwhile;
                        if ($moyenne['moyenne'] > $i - 1) : ?>
                            <i class="fas fa-star-half-alt"></i>
                        <?php endif ?>
                        <?php for ($i = ceil($moyenne['moyenne']); $i < 5; $i++) : ?>
                            <i class="far fa-star"></i>
                        <?php endfor ?>
                        </span>
                        </div>
                        <hr class="mb-3">
                    <table class="table cell-border table-hover display" id="tableNoteProfil">
                        <colgroup>
                            <col width="20%">
                            <col width="15%">
                            <col width="45%">
                            <col width="15%">
                            <col width="5%">
                        </colgroup>
                        <thead class="align-middle ">
                            <tr class="text-center">
                                <th>Acheteur</th>
                                <th>Note</th>
                                <th>Avis</th>
                                <th>Date d'enregistrement</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($notes->rowCount() > 0) {
                                while ($note = $notes->fetch()) : ?>
                                    <tr>
                                        <td><?php echo $note['acheteur'] ?></td>
                                        <td class="col-1">
                                            <?php for ($i = 0; $i < $note['note']; $i++) : ?>
                                                <i class="fas fa-star"></i>
                                            <?php endfor ?>
                                            <?php for ($i = $note['note']; $i < 5; $i++) : ?>
                                                <i class="far fa-star"></i>
                                            <?php endfor ?>
                                        </td>
                                        <td>
                                            <?php
                                            $extrait = substr($note['avis'], 0, 200);
                                            echo (iconv_strlen($note['avis']) > 200) ? substr($extrait, 0, strrpos($extrait, ' ')) . ' &hellip;' : $extrait;
                                            ?>
                                        </td>
                                        <td><?php echo $note['date_enrFR'] ?></td>
                                        <td class="text-center btn-actions">
                                            <a href="" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalNote<?php echo $note['id_note'] ?>"><i class="fa fa-eye"></i></a>
                                        </td>
                                        <div class="modal fade" id="modalNote<?php echo $note['id_note'] ?>" tabindex="-1" aria-labelledby="modalNoteLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title text-secondary" id="modalContactLabel">Avis laissés par les acheteurs :</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row mt-2">
                                                            <div class="col-6 couleur-perso">Note attribuée par <?php echo $note['acheteur'] ?></div>
                                                            <div class="col-6 text-end couleur-perso">
                                                                <?php for ($i = 0; $i < $note['note']; $i++) { ?>
                                                                    <i class="fas fa-star"></i>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="note-text" class="col-form-label fst-italic">Message :</label>
                                                            <textarea class="form-control" id="avis" name="avis" rows="12"><?php echo $note['avis'] ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </tr>
                                <?php endwhile ?>
                            <?php }  ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="m-4 alert alert-warning">Vous n'avez pas de note</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>




<?php


require_once('includes/footer.php');
