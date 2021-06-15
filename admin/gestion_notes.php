<?php

require_once('../includes/init.php');

// Redirection si qq tente d'utiliser les url sans en avoir le droit
if (!isAdmin()) {
    header('location:' . URL . 'connexion.php');
    exit();
}

$notes = sql("SELECT n.*, m1.email as email1, m1.pseudo as acheteur, m2.email as email2, m2.pseudo as vendeur, date_format(n.date_enregistrement, '%d/%m/%Y à %H:%i') as date_enrFR FROM note n
        LEFT JOIN membre m1 ON n.id_membre1 = m1.id_membre
        LEFT JOIN membre m2 ON n.id_membre2 = m2.id_membre
        ORDER BY date_enregistrement DESC");

$title ="Gestion des notes";
require_once('../includes/header.php');
?>

<div class="row">
    <div class="table-responsive ">
        <table class="table table-bordered table-hover align-middle display" id="tablNnote">
            <thead class=" align-middle ">
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
                            <td><?php echo $note['note'] ?><i class="fas fa-star"></i><i class="far fa-star"></i><i class="fas fa-star-half-alt"></i></td>
                            <td><?php echo $note['avis'] ?>
                            
                            </td>
                            <td><?php echo $note['date_enrFR'] ?></td>
                            <td class="btn-actions">
                                <a href="../note.php?id=<?php echo $note['id_note'] ?>" class="btn btn-outline-primary"><i class="fa fa-eye"></i></a>
                                <a href="../depot_note.php?action=edit&id=<?php echo $note['id_note'] ?>" class="btn btn-outline-secondary mt-1 mb-1"><i class="fa fa-edit"></i></a>
                                <a href="?action=delete&id=<?php echo $note['id_note'] ?>" class="btn btn-outline-danger confirm"><i class="fa fa-trash"></i></a>
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