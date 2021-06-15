<?php

require_once('../includes/init.php');

// Redirection si qq tente d'utiliser les url sans en avoir le droit
if (!isAdmin()) {
    header('location:' . URL . 'connexion.php');
    exit();
}

$chemin = $_SERVER['DOCUMENT_ROOT'] . URL . 'images/';

// Suppression d'une annonce
if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id']) && is_numeric($_GET['id'])) {

    $annonce = sql("SELECT * FROM annonce WHERE id_annonce = :id", array(
        'id' => $_GET['id']
    ));
    if ($annonce->rowCount() > 0) {
        $infos = $annonce->fetch();

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
        add_flash('Annonce introuvable', 'warning');
    }
}


// Demande d'édition


$annonces = sql("SELECT a.*, m.pseudo, c.titre as categorie, date_format(a.date_enregistrement, '%d/%m/%Y à %H:%i') as date_enrFR FROM annonce a
INNER JOIN membre m USING (id_membre)
INNER JOIN categorie c USING (id_categorie)
ORDER BY date_enregistrement DESC");

$title = "Gestion des annonces";
require_once('../includes/header.php');
?>

<div class="row">
    <div class="table-responsive ">
        <table class="table table-bordered table-hover align-middle display" id="tableAnnonce">
            <thead class=" align-middle ">
                <tr><th colspan="14" class="text-center pb-4">Gestion des annonces</th></tr>
                <tr class="text-center">
                    <th>#</th>
                    <th>Titre</th>
                    <th>Description courte</th>
                    <th>Description longue</th>
                    <th>Prix</th>
                    <th class="col-3">Photo</th>
                    <th>Région</th>
                    <th>CP</th>
                    <th>Ville</th>
                    <th>Adresse</th>
                    <th>Pseudo</th>
                    <th>Catégorie</th>
                    <th>Date d'enregistrement</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if($annonces->rowCount() > 0){
                    while ($annonce = $annonces->fetch()) : ?>
                        <tr>
                            <!-- <form action=""> -->
                                <td><?php echo $annonce['id_annonce'] ?></td>
                                <td><?php echo $annonce['titre'] ?></td>
                                <td><?php echo $annonce['description_courte'] ?></td>
                                <td><?php
                                        $extrait = substr($annonce['description_longue'], 0, 50);
                                        echo (iconv_strlen($annonce['description_longue']) > 50) ? substr($extrait, 0, strrpos($extrait, ' ')) . ' &hellip;' : $extrait;
                                        ?></td>
                                <td><?php echo $annonce['prix'] ?></td>
                                <td><div id="hauteur" ><img src="../images/<?php echo $annonce['photo'] ?>" alt="<?php echo $annonce['titre'] ?>" class="mx-auto d-block" width="100%"></div><a href="" class="text-decoration-none"><p class="text-center">Voir les autres photos</p></a></td>
                                <td><?php echo $annonce['pays'] ?></td>
                                <td><?php echo $annonce['cp'] ?></td>
                                <td><?php echo $annonce['ville'] ?></td>
                                <td><?php echo $annonce['adresse'] ?></td>
                                <td><?php echo $annonce['pseudo'] ?></td>
                                <td><?php echo $annonce['categorie'] ?></td>
                                <td><?php echo $annonce['date_enrFR'] ?></td>
                                <td class="btn-actions">
                                    <a href="../annonce.php?id=<?php echo $annonce['id_annonce'] ?>" class="btn btn-outline-primary"><i class="fa fa-eye"></i></a>
                                    <a href="../depot_annonce.php?action=edit&id=<?php echo $annonce['id_annonce'] ?>" class="btn btn-outline-secondary mt-1 mb-1"><i class="fa fa-edit"></i></a>
                                    <a href="?action=delete&id=<?php echo $annonce['id_annonce'] ?>" class="btn btn-outline-danger confirm"><i class="fa fa-trash"></i></a>
                                </td>
                            <!-- </form> -->
                        </tr>
                    <?php endwhile ?>
                <?php }  ?>
            </tbody>

        </table>
    </div>
</div>


<?php
require_once('../includes/footer.php');
