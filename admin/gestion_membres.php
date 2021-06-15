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
                <?php if($membres->rowCount() > 0){
                    while ($membre = $membres->fetch()) : ?>
                        <tr>
                            <form action="">
                                <td><?php echo $membre['id_membre'] ?></td>
                                <td><?php echo $membre['pseudo'] ?></td>
                                <td><?php echo $membre['nom'] ?></td>
                                <td><?php echo $membre['prenom'] ?></td>
                                <td><a href="mailto:<?php echo $membre['email'] ?>"><?php echo $membre['email'] ?></a></td>
                                <td><?php echo $membre['telephone'] ?></td>
                                <td><?php echo ($membre['civilite'] == 'm') ? 'Homme' : 'Femme' ?></td>
                                <td><?php if ($membre['id_membre'] != $_SESSION['membre']['id_membre']) : ?>
                                        <input type="hidden" name="id_membre" value="<?php echo $membre['id_membre'] ?>">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="statut" id="droits0<?php echo $membre['id_membre'] ?>" value="0" <?php if ($membre['statut'] == 0) echo 'checked' ?>>
                                            <label class="form-check-label" for="droits0<?php echo $membre['id_membre'] ?>">Membre</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="statut" id="droits1<?php echo $membre['id_membre'] ?>" value="1" <?php if ($membre['statut'] == 1) echo 'checked' ?>>
                                            <label class="form-check-label" for="droits2<?php echo $membre['id_membre'] ?>">Admin</label>
                                        </div>
                                    <?php else : ?>
                                        Administrateur
                                    <?php endif ?>
                                </td>
                                <td><?php echo $membre['date_enrFR'] ?></td>
                                <td class="btn-actions">
                                    <a href="?action=visu" class="btn btn-outline-primary"><i class="fa fa-eye"></i></a>
                                    <button type="submit" class="btn btn-outline-secondary mt-1 mb-1"><i class="fa fa-edit"></i></button>
                                    <a href="?action=delete&id=<?php echo $membre['id_membre'] ?>" class="btn btn-outline-danger confirm"><i class="fa fa-trash"></i></a>
                                </td>
                            </form>
                        </tr>
                    <?php endwhile ?>
                <?php } ?>
            </tbody>

        </table>
    </div>
</div>
<div class="row mt-5">

    <?php require_once('../includes/infos_membre.php'); ?>
</div>

<?php
require_once('../includes/footer.php');
