<?php

require_once('../includes/init.php');

// Redirection si qq tente d'utiliser les url sans en avoir le droit
if (!isAdmin()) {
    header('location:' . URL . 'connexion.php');
    exit();
}

// Suppression d'un commentaire
if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id']) && is_numeric($_GET['id'])) {

    $commentaire = sql("SELECT * FROM commentaire WHERE id_commentaire = :id", array(
        'id' => $_GET['id']
    ));
    if ($commentaire->rowCount() > 0) {
        sql('DELETE FROM commentaire WHERE id_commentaire=:id', array(
            'id' => $_GET['id']
        ));
        add_flash("Le commentaire a été supprimée", 'warning');
    } else {
        add_flash('Commentaire introuvable', 'warning');
    }
}

$commentaires = sql("SELECT c.*, m.email, m.pseudo, a.id_annonce, a.titre, date_format(c.date_enregistrement, '%d/%m/%Y à %H:%i') as date_enrFR FROM commentaire c
                INNER JOIN membre m USING (id_membre)
                INNER JOIN annonce a USING(id_annonce)
                ORDER BY c.date_enregistrement DESC");

$title = "Gestion des commentaires";
$subtitle = "Admin";
require_once('../includes/header.php');
?>

<div class="row">
    <div class="table-responsive ">
        <table class="table cell-border table-hover align-middle display" id="tableCommentaire">
            <thead class=" align-middle ">
                <tr>
                    <th colspan="6" class="text-center pb-4">Gestion des commentaires</th>
                </tr>
                <tr class="text-center">
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
                            <td><?php echo $commentaire['pseudo'] ?></td>
                            <td><?php echo $commentaire['id_annonce'] . ' ' . $commentaire['titre'] ?></td>
                            <td><?php
                                $extrait = substr($commentaire['commentaire'], 0, 200);
                                echo (iconv_strlen($commentaire['commentaire']) > 200) ? substr($extrait, 0, strrpos($extrait, ' ')) . ' &hellip;' : $extrait;
                                ?>
                            </td>
                            <td><?php echo $commentaire['date_enrFR'] ?></td>
                            <td class="text-center btn-actions">
                                <a href="" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalComm<?php echo $commentaire['id_commentaire'] ?>"><i class="fa fa-eye"></i></a>
                                <a href="?action=delete&id=<?php echo $commentaire['id_commentaire'] ?>" class="btn btn-outline-danger confirm"><i class="fa fa-trash"></i></a>
                            </td>
                            <div class="modal fade" id="modalComm<?php echo $commentaire['id_commentaire'] ?>" tabindex="-1" aria-labelledby="modalCommLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title text-secondary" id="modalContactLabel">Concernant l'annonce :</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>

                                        <div class="modal-body">
                                            <div class="row mb-3">
                                                <div class="col ">
                                                    <p class="fw-bold"><?php echo $commentaire['titre'] ?></p><span class="fst-italic couleur-perso">Commentaire laissé par <?php echo $commentaire['pseudo'] ?></span>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <textarea class="form-control" id="commentaire-text" rows="12" name="commentaire-text"><?php echo $commentaire['commentaire'] ?></textarea>
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
    </div>
</div>


<?php
require_once('../includes/footer.php');
