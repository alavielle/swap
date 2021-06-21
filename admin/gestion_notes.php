<?php

require_once('../includes/init.php');

// Redirection si qq tente d'utiliser les url sans en avoir le droit
if (!isAdmin()) {
    header('location:' . URL . 'connexion.php');
    exit();
}

// Suppression d'une note
if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id']) && is_numeric($_GET['id'])) {

    $note = sql("SELECT * FROM note WHERE id_note = :id", array(
        'id' => $_GET['id']
    ));
    if ($note->rowCount() > 0) {
        sql('DELETE FROM note WHERE id_note=:id', array(
            'id' => $_GET['id']
        ));
        add_flash("La note a été supprimée", 'warning');
    } else {
        add_flash('Note introuvable', 'warning');
    }
}



$notes = sql("SELECT n.*, m1.email as email1, m1.pseudo as acheteur, m2.email as email2, m2.pseudo as vendeur, date_format(n.date_enregistrement, '%d/%m/%Y à %H:%i') as date_enrFR FROM note n
        LEFT JOIN membre m1 ON n.id_membre1 = m1.id_membre
        LEFT JOIN membre m2 ON n.id_membre2 = m2.id_membre
        ORDER BY date_enregistrement DESC");

$title = "Gestion des notes";
require_once('../includes/header.php');
?>

<div class="row">
    <div class="table-responsive ">
        <table class="table table-bordered table-hover align-middle display" id="tablNnote">
            <thead class="align-middle ">
                <tr>
                    <th colspan="7" class="text-center pb-4">Gestion des notes</th>
                </tr>
                <tr class="text-center">
                    <th>#</th>
                    <th>Acheteur</th>
                    <th>Vendeur</th>
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

                            <td><?php echo $note['id_note'] ?></td>
                            <td><?php echo $note['acheteur'] ?></td>
                            <td><?php echo $note['vendeur'] ?></td>
                            <td class="col-1"><?php for ($i = 0; $i < $note['note']; $i++) { ?>
                                    <i class="fas fa-star"></i>
                                <?php } ?>
                                <!-- <i class="fas fa-star-half-alt"></i> -->
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
                                <a href="?action=delete&id=<?php echo $note['id_note'] ?>" class="btn btn-outline-danger confirm"><i class="fa fa-trash"></i></a>
                            </td>
                            <div class="modal fade" id="modalNote<?php echo $note['id_note'] ?>" tabindex="-1" aria-labelledby="modalNoteLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title text-secondary" id="modalContactLabel">Au sujet du vendeur :</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>

                                        <div class="modal-body">
                                            <div class="row mt-2">
                                                <div class="col-6 couleur-perso">Note attribuée par <?php echo $note['acheteur'] ?> à <?php echo $note['vendeur'] ?></div>
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
    </div>
</div>


<?php
require_once('../includes/footer.php');
