<?php

require_once('../includes/init.php');

// Redirection si qq tente d'utiliser les url sans en avoir le droit
if (!isAdmin()) {
    header('location:' . URL . 'connexion.php');
    exit();
}

// Gestion de la suppression
if (isset($_GET['action']) && $_GET['action'] == 'delete') {

    if ($_GET['id'] == $_SESSION['membre']['id_membre']) {
        add_flash("Vous êtes administrateur, vous ne pouvez supprimer votre compte", 'danger');
    } else {
        $id_photos = sql('SELECT id_photo FROM annonce WHERE id_membre=:id', array(
            'id' => $_GET['id']
        ));
        while ($id_photo = $id_photos->fetch()){
            $photos = sql('SELECT * FROM photo WHERE id_photo = ' . $id_photo['id_photo']);
            $photo = $photos->fetch();
            foreach ($photo as $p) {
                suppPhotos($p);
            }
            sql("DELETE FROM photo WHERE id_photo = " . $photo['id_photo']);
        }
        sql('DELETE FROM annonce WHERE id_membre=:id', array(
            'id' => $_GET['id']
        ));
        sql('DELETE FROM membre WHERE id_membre=:id', array(
        'id' => $_GET['id']
        ));
    add_flash("le compte a été supprimé", 'info');
    header('location:' . $_SERVER['PHP_SELF']);
    exit();
    }
}

// Gestion de l'affichage '
if (isset($_GET['id_membre']) && isset($_GET['statut'])) {

    $selectMembres = sql('SELECT * FROM membre WHERE id_membre=:id_membre', array(
        'id_membre' => $_GET['id_membre']
    ));
    $selectMembre = $selectMembres->fetch();
    // Modification des droits, sauf ceux de l'admin
    if ($_GET['id_membre'] != $_SESSION['membre']['id_membre']) {

        if ($selectMembre['statut'] !== $_GET['statut']) {
            sql("UPDATE membre SET statut=:statut WHERE id_membre=:id_membre", array(
                'statut' => $_GET['statut'],
                'id_membre' => $_GET['id_membre']
            ));
            add_flash('Statut de l\'utilisateur mis à jour', 'warning');
        }
        // header('location:' . $_SERVER['PHP_SELF']);
        // exit();
    } else {
        add_flash('Impossible de modifier son propre statut', 'danger');
    }
} else {
    $selectMembres = sql('SELECT * FROM membre WHERE id_membre=:id', array(
        'id' => $_SESSION['membre']['id_membre']
    ));
    $selectMembre = $selectMembres->fetch();
}

$membres = sql("SELECT * , date_format(date_enregistrement, '%d/%m/%Y - %H:%i') as date_enrFR FROM membre ORDER BY pseudo");

$title = "Gestion des utilisateurs";
$subtitle = "Admin";
require_once('../includes/header.php');
?>
<div class="row">
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Pseudo</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Civilité</th>
                    <th>Statut</th>
                    <th>Date d'enregistrement</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if($membres->rowCount() > 0) :
                    while ($membre = $membres->fetch()) : ?>
                        <tr>
                            <form action="">
                                <td><?= $membre['id_membre'] ?></td>
                                <td><?= $membre['pseudo'] ?></td>
                                <td><?= $membre['nom'] ?></td>
                                <td><?= $membre['prenom'] ?></td>
                                <td><a href="mailto:<?= $membre['email'] ?>"><?= $membre['email'] ?></a></td>
                                <td><?= $membre['telephone'] ?></td>
                                <td><?= ($membre['civilite'] == 'm') ? 'Homme' : 'Femme' ?></td>
                                <td><?php if ($membre['id_membre'] != $_SESSION['membre']['id_membre']) : ?>
                                        <input type="hidden" name="id_membre" value="<?= $membre['id_membre'] ?>">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="statut" id="droits0<?= $membre['id_membre'] ?>" value="0" <?php if ($membre['statut'] == 0) echo 'checked' ?>>
                                            <label class="form-check-label" for="droits0<?= $membre['id_membre'] ?>">Membre</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="statut" id="droits1<?= $membre['id_membre'] ?>" value="1" <?php if ($membre['statut'] == 1) echo 'checked' ?>>
                                            <label class="form-check-label" for="droits2<?= $membre['id_membre'] ?>">Admin</label>
                                        </div>
                                    <?php else : ?>
                                        Administrateur
                                    <?php endif ?>
                                </td>
                                <td><?= $membre['date_enrFR'] ?></td>
                                <td class="text-center btn-actions">
                                    <a href="<?= URL . '?filtre=' .$membre['id_membre'] ?>" class="btn btn-outline-primary survol" id="survol_<?= $membre['id_membre'] ?>"><i class="fa fa-eye"></i></a>
                                    <button type="submit" class="btn btn-outline-secondary mt-1 mb-1"><i class="fa fa-edit"></i></button>
                                    <a href="?action=delete&id=<?= $membre['id_membre'] ?>" class="btn btn-outline-danger confirm" data-message="Etes-vous sûr(e) de vouloir supprimer ce membre ? Cette action est irreversible et supprimera toutes ses données (annonces et photos)."><i class="fa fa-trash"></i></a>                                   
                                </td>
                                <div class="modal fade" id="modalMembre<?= $membre['id_membre'] ?>" tabindex="-1"  aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title text-secondary" id="modalContactLabel">Au sujet du vendeur :</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                        <?php $notes = sql("SELECT note.*, membre.pseudo, date_format(note.date_enregistrement, '%d/%m/%Y à %H:%i') as dateFr FROM note 
                                        INNER JOIN membre ON note.id_membre1 = membre.id_membre
                                        where id_membre2 = " . $membre['id_membre']); 
                                        if( $notes->rowCount() > 0) :
                                            while ($note = $notes->fetch()) : ?>

                                                <div class="row mt-2">
                                                    <div class="col-6 couleur-perso fst-italic fs-6">Par <?= $note['pseudo'] ?> le <?= $note['dateFr'] ?></div>
                                                    <div class="col-6 text-end couleur-perso">
                                                        <?php for ($i = 0; $i < $note['note']; $i++) : ?>
                                                            <i class="fas fa-star"></i>
                                                        <?php endfor ?>
                                                        <?php for ($i = $note['note']; $i < 5; $i++) : ?>
                                                            <i class="far fa-star"></i>
                                                        <?php endfor ?>
                                                    </div>
                                                </div>
                                                <div >
                                                    <p> <?= $note['avis'] ?></p>
                                                </div>

                                            <?php endwhile ?>
                                        <?php endif ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </form>
                        </tr>
                    <?php endwhile ?>
                <?php endif ?>
            </tbody>

        </table>
    </div>
</div>
<div class="row mt-5">

    <?php require_once('../includes/infos_membre.php'); ?>
</div>

<?php
require_once('../includes/footer.php');
