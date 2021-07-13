<?php

require_once('../includes/init.php');

// Redirection si qq tente d'utiliser les url sans en avoir le droit
if (!isAdmin()) {
    header('location:' . URL . 'connexion.php');
    exit();
}

// Suppression ($_Get)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id']) && is_numeric($_GET['id'])) {
    sql("DELETE FROM categorie WHERE id_categorie=:id", array(
        'id' => $_GET['id']
    ));
    add_flash('La catégorie a bien été supprimée', 'warning');
    header('location:' . $_SERVER['PHP_SELF']);
    exit();
}


// Traitement des formulaires

if (!empty($_POST)) {
    // Formulaire d'ajout soumis
    if (isset($_POST['add'])) {
        if (!empty(trim($_POST['categorie']))) {
            sql("INSERT INTO categorie VALUES(NULL, :titre, :motscles)", array(
                'titre' => $_POST['categorie'],
                'motscles' => $_POST['motscles']
            ));
            add_flash('La catégorie ' . $_POST['categorie'] . ' a été ajoutée', 'warning');
            header('location:' . $_SERVER['PHP_SELF']);
            exit();
        } else {
            add_flash('La catégorie ne doit pas être vide', 'danger');
        }
    }
    // Formulaire d'update soumis
    if (isset($_POST['update'])) {
        if (!empty(trim($_POST['categorie']))) {
            sql("UPDATE categorie SET titre=:nvtitre, motscles=:nvmotscles WHERE id_categorie=:id_categorie", array(
                'nvtitre' => $_POST['categorie'],
                'nvmotscles' => $_POST['motscles'],
                'id_categorie' => $_POST['id_categorie']
            ));
            add_flash('La catégorie a été mise à jour', 'warning');
            header('location:' . $_SERVER['PHP_SELF']);
            exit();
        } else {
            add_flash('La catégorie ne doit pas être vide', 'danger');
        }
    }
}


$categories = sql("SELECT * FROM categorie ORDER BY titre");


$title = "Gestion des catégories";
$subtitle = "Admin";
require_once('../includes/header.php');
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col">
            <h1>Catégories d'article</h1>
            <hr class="my-3">
            <form method="post" class="row">
                <div class="col-3">
                    <input type="text" id="categorie" name="categorie" class="form-control" placeholder="Catégorie à ajouter">
                </div>
                <div class="col-5">
                    <input type="text" id="motscles" name="motscles" class="form-control" placeholder="Mots Clés">
                </div>
                <div class="col-4">
                    <button type="submit" name="add" class="btn btn-perso">Ajouter</button>
                </div>
            </form>
            <?php if ($categories->rowCount() > 0) : ?>
                <h2>Liste des catégories</h2>
                <?php while ($categorie = $categories->fetch()) : ?>
                    <form method="post" class="row mb-3">
                        <input type="hidden" name="id_categorie" value="<?php echo $categorie['id_categorie'] ?>">
                        <div class="col-3 ">
                            <input type="text" id="categorie" name="categorie" class="form-control" value="<?php echo $categorie['titre'] ?>">
                        </div>
                        <div class="col-5 ">
                            <input type="text" id="motscles" name="motscles" class="form-control" value="<?php echo $categorie['motscles'] ?>">
                        </div>
                        <div class="col-4">
                            <button type="submit" name="update" class="btn btn-outline-secondary">
                                <i class="fa fa-edit"></i>
                            </button>
                            <a href="?action=delete&id=<?php echo $categorie['id_categorie'] ?>" class="btn btn-outline-danger confirm">
                                <i class="fa fa-trash"></i>
                            </a>
                        </div>
                    </form>
                <?php endwhile ?>
            <?php else : ?>
                <div class="mt-4 alert alert-warning">Il n'y a pas encore de catégorie</div>
            <?php endif ?>
        </div>
    </div>
</div>



<?php
require_once('../includes/footer.php');
