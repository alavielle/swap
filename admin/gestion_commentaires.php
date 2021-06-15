<?php

require_once('../includes/init.php');

// Redirection si qq tente d'utiliser les url sans en avoir le droit
if (!isAdmin()) {
    header('location:' . URL . 'connexion.php');
    exit();
}

$commentaires = sql("SELECT c.*, m.email, m.pseudo, a.id_annonce, a.titre, date_format(c.date_enregistrement, '%d/%m/%Y à %H:%i') as date_enrFR FROM commentaire c
                INNER JOIN membre m USING (id_membre)
                INNER JOIN annonce a USING(id_annonce)
                ORDER BY c.date_enregistrement DESC");

$title = "Gestion des commentaires";
require_once('../includes/header.php');
?>

<div class="row">
    <div class="table-responsive ">
        <table class="table table-bordered table-hover align-middle display" id="tableCommentaire">
            <thead class=" align-middle ">
                <tr>
                    <th colspan="6" class="text-center pb-4">Gestion des commentaires</th>
                </tr>
                <tr class="text-center">
                    <th>#</th>
                    <th>Membre</th>
                    <th>Annonce</th>
                    <th>Commentaire</th>
                    <th>Date d'enregistrement</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($commentaires->rowCount() > 0) {
                    while ($commentaire = $commentaires->fetch()) : ?>
                        <tr>

                            <td><?php echo $commentaire['id_commentaire'] ?></td>
                            <td><?php echo $commentaire['pseudo'] ?></td>
                            <td><?php echo $commentaire['id_annonce'] . ' ' . $commentaire['titre'] ?></td>
                            <td><?php echo $commentaire['commentaire'] ?></td>
                            <td><?php echo $commentaire['date_enrFR'] ?></td>
                            <td class="btn-actions">
                                <a href="../commentaire.php?id=<?php echo $commentaire['id_commentaire'] ?>" class="btn btn-outline-primary"><i class="fa fa-eye"></i></a>
                                <a href="../depot_commentaire.php?action=edit&id=<?php echo $commentaire['id_commentaire'] ?>" class="btn btn-outline-secondary mt-1 mb-1"><i class="fa fa-edit"></i></a>
                                <a href="?action=delete&id=<?php echo $commentaire['id_commentaire'] ?>" class="btn btn-outline-danger confirm"><i class="fa fa-trash"></i></a>
                            </td>

                        </tr>
                    <?php endwhile ?>
                <?php }  ?>
            </tbody>

        </table>
    </div>
</div>


<?php
require_once('../includes/footer.php');
