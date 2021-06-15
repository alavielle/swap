<?php

require_once('includes/init.php');



if (!empty($_GET['id']) && is_numeric($_GET['id'])) {
    $annonces = sql("SELECT *,  date_format(date_enregistrement, '%d/%m/%Y') as date_annonceFR FROM annonce WHERE id_annonce = " . $_GET['id']);
    if ($annonces->rowCount() > 0) {
        $annonce = $annonces->fetch();
        $membres = sql("SELECT pseudo, telephone FROM membre WHERE id_membre = :id_membre", array(
            'id_membre' => $annonce['id_membre']
        ));
        $membre = $membres->fetch();
        $adresse = $annonce['adresse'] . ', ' . $annonce['cp'] . ' ' . $annonce['ville'];
    } else {
        add_flash('Annonce introuvable', 'warning');
        header('location:' .  URL);
        exit();
    }
} else {
    header('location:' . URL);
    exit();
}

$title = 'Annonce';
require_once('includes/header.php');

?>

<div class="container">
    <div class="row">
        <div class="col-6">
            <h1><?php echo $annonce['titre'] ?></h1>
        </div>
        <div class="col-6">
            <p class="text-end"><button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalContact" data-bs-whatever="<?php echo $membre['pseudo'] ?>">Contacter <?php echo $membre['pseudo'] ?></button></p>

            <div class="modal fade" id="modalContact" tabindex="-1" aria-labelledby="modalContactLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalContactLabel">Nouveau message</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form>
                                <div class="row mt-2"><div class="col-6 fst-italic">Par téléphone </div><div class="col-6 text-end"> <i class="fas fa-phone"></i><?php echo ' '. $membre['telephone'] ?></div>
                                </div>
                                <hr>
                                <div class="row mt-3 mb-3"><div class="col fst-italic">Par email via le formulaire ci-après </div></div>
                                <div class="mb-3">
                                    <label for="recipient-name" class="col-form-label">Objet</label>
                                    <input type="text" class="form-control" id="recipient-name">
                                </div>
                                <div class="mb-3">
                                    <label for="message-text" class="col-form-label">Message :</label>
                                    <textarea class="form-control" id="message-text" rows="7"></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                            <button type="button" class="btn btn-perso">Envoyer</button>
                        </div>
                    </div>
                </div>
            </div>


        </div>
        <hr>
    </div>


    <div class="row">
        <div class="col-lg-6">
            <img src="images/<?php echo $annonce['photo'] ?>" class="img-fluid" alt="<?php echo $annonce['titre'] ?>">
        </div>
        <div class="col">
            <h2 class="fs-5">Description</h2>
            <p>
                <?php echo $annonce['description_longue'] ?>
            </p>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-lg">
            <p><i class="far fa-calendar-alt"></i> Date de publication : <?php echo $annonce['date_annonceFR'] ?></p>
        </div>
        <div class="col-lg">
            <a class="text-secondary text-decoration-none" href="#"><i class="fas fa-user"></i> <?php echo $membre['pseudo'] ?></a>
        </div>
        <div class="col-lg">
            <p class="fw-bold"><i class="fas fa-tag"></i> <?php echo $annonce['prix'] ?> €</p>
        </div>
        <div class="col-lg">
            <p><i class="fas fa-map-marker-alt"></i> <?php echo $adresse ?></p>
        </div>
    </div>
    <div class="row">
        <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d20987.530104363675!2d2.2249472!3d48.88792709999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2sfr!4v1622990504081!5m2!1sen!2sfr" width="800" height="200" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
    </div>
    <div class="row">
        <h3>Autres annonces</h3>
        <hr>
    </div>
    <div class="row justify-content-between">
        <hr>
        <div class="col-6">
            <p><a href="" class="text-decoration-none">Déposer un commentaire ou une note</a></p>
        </div>
        <div class="col-6">
            <p class="text-end "><a href="<?php echo URL ?>" class="text-decoration-none">Retour vers les annonces</a></p>
        </div>
        <hr>
    </div>
</div>


<?php
require_once('includes/footer.php');
