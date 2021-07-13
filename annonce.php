<?php

require_once('includes/init.php');


if (!empty($_GET['id']) && is_numeric($_GET['id'])) {
    $annonces = sql("SELECT *, date_format(date_enregistrement, '%d/%m/%Y') as date_annonceFR FROM annonce WHERE id_annonce = " . $_GET['id']);
    if ($annonces->rowCount() > 0) {
        $annonce = $annonces->fetch();
        $membres = sql("SELECT pseudo, telephone, email FROM membre WHERE id_membre = " . $annonce['id_membre']);
        $membre = $membres->fetch();
        $adresse = $annonce['adresse'];
        $cp_ville = $annonce['cp'] . ' ' . $annonce['ville'];
        $photos = sql("SELECT photo1, photo2, photo3, photo4, photo5 FROM photo WHERE id_photo = " . $annonce['id_photo']);
        $photo = $photos->fetch();
        $notes = sql("SELECT AVG(note) as 'moyenne' FROM note where id_membre2 = " . $annonce['id_membre']);
        $note = $notes->fetch();

        $autresAnnonces = sql("SELECT id_annonce, photo FROM annonce WHERE id_categorie = " . $annonce['id_categorie'] . " AND id_annonce != " . $annonce['id_annonce']);
        $nbAutres = $autresAnnonces->rowCount();
        if ($nbAutres > 0) {
            $autreAnnonce = $autresAnnonces->fetchAll();
            shuffle_assoc($autreAnnonce);
        }
        $commentaires = sql("SELECT c.*, m.pseudo, m.id_membre, date_format(c.date_enregistrement, '%d/%m/%Y') as date_commFR FROM commentaire c INNER JOIN membre m ON m.id_membre = c.id_membre WHERE c.id_annonce = " . $annonce['id_annonce']);

        if (!empty($_POST)) { // formulaire soumis pour envoi mail
            $errors = 0;
            if (empty($_POST['objet'])) {
                $errors++;
                add_flash('Veuillez renseigner un objet', 'danger');
            } else {
                $_POST['objet'] = trim($_POST['objet']);
            }
            if (empty($_POST['message'])) {
                $errors++;
                add_flash('Veuillez renseigner un message', 'danger');
            } else {
                $_POST['message'] = trim($_POST['message']);
            }

            if ($errors == 0) {
                $headers[] = 'MIME-VERSION:1.0';
                $headers[] = 'Content-type:text/html; charset=iso-8859-1';
                $headers[] = 'From: ' . $_SESSION['membre']['email'];

                mail($membre['email'], $_POST['objet'], $_POST['message'], implode(PHP_EOL, $headers));

                add_flash("Votre message a bien été envoyé.", 'info');
                header('location:' . $_SERVER['PHP_SELF']);
                exit();
            }
        }
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
        <div class="col-8">
            <h1><?php echo $annonce['titre'] ?></h1>
        </div>
        <div class="col-4">
            <p class="text-end"><button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalContact" data-bs-whatever="<?= $membre['pseudo'] ?>">Contacter <?= $membre['pseudo'] ?></button></p>

            <div class="modal fade" id="modalContact" tabindex="-1" aria-labelledby="modalContactLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalContactLabel">Nouveau message</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST">
                            <div class="modal-body">
                                <div class="row mt-2">
                                    <div class="col-6 fst-italic">Par téléphone </div>
                                    <div class="col-6 text-end"> <i class="fas fa-phone"></i><?= ' ' . $membre['telephone'] ?></div>
                                </div>
                                <hr>
                                <div class="row mt-3 mb-3">
                                    <div class="col fst-italic">Par email via le formulaire ci-après </div>
                                </div>
                                <div class="mb-3">
                                    <label for="objet-text" class="col-form-label">Objet</label>
                                    <input type="text" class="form-control" name="objet" id="objet-text" value="Votre annonce <?= $annonce['titre'] ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="message-text" class="col-form-label">Message :</label>
                                    <textarea class="form-control" id="message-text" name="message" rows="7"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                <button type="submit" class="btn btn-perso" id="envoyer">Envoyer</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <hr>
    </div>


    <div class="row">
        <div class="col-lg-7">
            <div id="blocGalerie" class="d-flex justify-content-center border">
                <img src="images/<?php echo $annonce['photo'] ?>" id="grand" class="img-fluid" alt="<?php echo $annonce['titre'] ?>">
            </div>
            <div id="miniatures" class="mt-2">
                <?php foreach ($photo as $p) :;
                    if (!empty($p)) : ?>
                        <img src="images/<?= $p ?>" id="<?= $p ?>" alt="<?= $p ?>" class='miniature'>
                <?php endif;
                endforeach; ?>
            </div>
        </div>
        <div class="col-lg-5">
            <h2 class="fs-5">Description</h2>
            <div>
                <pre><?php echo $annonce['description_courte'] ?></pre>
                <br>
                <pre><?php echo $annonce['description_longue'] ?></pre>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-lg">
            <p><i class="far fa-calendar-alt"></i> Date de publication : <?= $annonce['date_annonceFR'] ?></p>
        </div>
        <div class="col-lg text-center" data-bs-toggle="modal" data-bs-target="#modalMembre">
            <a class="text-dark text-decoration-none avis" id ="avis_<?= $annonce['id_membre'] ?>" href=""><i class="fas fa-user"></i> <?= $membre['pseudo'] ?>
                <?php
                $i = 1;
                while ($i <= $note['moyenne']) : ?>
                    <i class="fas fa-star"></i>
                <?php
                    $i++;
                endwhile;
                if ($note['moyenne'] > $i - 1) : ?>
                    <i class="fas fa-star-half-alt"></i>
                <?php endif ?>
                <?php for ($i = ceil($note['moyenne']); $i < 5; $i++) : ?>
                    <i class="far fa-star"></i>
                <?php endfor ?></a>
        </div>
        <div class="col-lg text-center">
            <p class="fw-bold"><i class="fas fa-tag"></i> <?= number_format($annonce['prix'], 2, ',', ' ') ?>&nbsp;€</p>
        </div>
        <div class="col-lg text-end">
            <p><i class="fas fa-map-marker-alt"></i> <?= $cp_ville ?></p>
        </div>
    </div>

    <div class="modal fade" id="modalMembre<?= $annonce['id_membre'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-secondary" id="modalContactLabel">Au sujet du vendeur :</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php $notes = sql("SELECT note.*, membre.pseudo, date_format(note.date_enregistrement, '%d/%m/%Y à %H:%i') as dateFr FROM note 
                    INNER JOIN membre ON note.id_membre1 = membre.id_membre
                    where id_membre2 = " . $annonce['id_membre']);
                    if ($notes->rowCount() > 0) :
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
                            <div>
                                <p> <?= $note['avis'] ?></p>
                            </div>

                        <?php endwhile ?>
                    <?php else : ?>
                        <div class="col-6 couleur-perso fst-italic fs-6">Pas encore d'avis déposé...</div>
                    <?php endif ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

        <input id="geolocalisation" value="<?= $cp_ville ?>" class="hidden">
        <pre id="output"></pre>
        <div id="mapContainer" class="mapNormal"></div>

        <?php if (isset($autreAnnonce) && is_array($autreAnnonce)) : ?>
            <div class="row mt-3">
                <h4>Autres annonces dans la même catégorie</h4>
                <hr>
                <div class="row align-items-center mx-auto">
                    <?php
                    $i = 0;
                    foreach ($autreAnnonce as $a) { ?>
                        <div class="col-3 text-center">
                            <a href="annonce.php?id=<?php echo $a['id_annonce'] ?>"><img src="images/<?= $a['photo'] ?>" alt="<?= $a['photo'] ?>" class="miniature autreAnnonce img-thumbnail"></a>
                        </div>
                    <?php $i++;
                        if ($i == 4) break;
                    } ?>
                </div>
            </div>
            <hr>
        <?php endif ?>
        <div class="row mt-3 justify-content-between">
            <div class="col-6">
                <p><a href="<?php if (!isset($_SESSION['membre']['id_membre'])) echo "connexion.php" ?>" class="text-decoration-none" id="ouvertureModal" data-index="<?php if (isset($_SESSION['membre']['id_membre'])) echo $_SESSION['membre']['id_membre'] ?>">
                        <?php if (isset($_SESSION['membre']['id_membre'])) : ?> Déposer un commentaire ou une note
                        <?php else : ?> Connectez-vous pour déposer un commentaire ou une note
                        <?php endif ?> </a></p>
            </div>

            <div class="modal fade" id="modalNote" tabindex="-1" aria-labelledby="modalNoteLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title text-secondary" id="modalContactLabel">Concernant l'annonce :</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body" id="notation">
                            <div class="row mb-3">
                                <div class="col">
                                    <p class="fw-bold"><?php echo $annonce['titre'] ?></p><span class="fst-italic">Avez-vous un commentaire ou une question ? </span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <textarea class="form-control" id="commentaire-text" rows="6" name="commentaire-text"></textarea>
                            </div>
                            <hr>
                            <h5 class="modal-title text-secondary">Au sujet du vendeur :</h5>
                            <hr>
                            <div class="row mt-2">
                                <div class="col-6 couleur-perso">Note attribuée à <?php echo $membre['pseudo'] ?></div>
                                <div class="col-6 text-end couleur-perso noter"> <span class="blanc"><i class="far fa-star" id="star_0"></i></span><i class="far fa-star" id="star_1"></i><i class="far fa-star" id="star_2"></i><i class="far fa-star" id="star_3"></i><i class="far fa-star" id="star_4"></i><i class="far fa-star" id="star_5"></i></div>
                            </div>
                            <div class="mb-3">
                                <label for="note-text" class="col-form-label fst-italic">Laissez-lui un message :</label>
                                <textarea class="form-control" id="avis" name="avis" rows="4"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <span class="hidden" id="vendeur" data-index="<?php echo $annonce['id_membre'] ?>"></span>
                            <span class="hidden" id="id_annonce" data-index="<?php echo $annonce['id_annonce'] ?>"></span>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                            <button type="button" class="btn btn-perso" id="depot" name="depot">Déposer</button>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-6">
                <p class="text-end "><a href="<?php echo URL ?>" class="text-decoration-none">Retour vers les annonces</a></p>
            </div>
            <hr>
        </div>
        <?php if ($commentaires->rowcount() > 0) : ?>
            <div class="row">
                <h4>Commentaires</h4>
                <hr>
                <div class="row ">
                    <?php while ($commentaire = $commentaires->fetch()) : ?>
                        <div class="col-md-3 text-md-end">
                            <span id="auteur">
                                <?php if ($commentaire['id_membre'] == $annonce['id_membre']) :
                                    echo $commentaire['pseudo'] . ' a répondu';
                                else : echo 'Par ' . $commentaire['pseudo'] ?>
                                <?php endif ?>
                                , le <?= $commentaire['date_commFR'] ?> : 
                            </span>
                        </div>
                        <div class="col-md-9">
                            <pre><?= $commentaire['commentaire'] ?></pre><Br>
                        </div>
                    <?php endwhile ?>
                </div>
            </div>
        <?php endif ?>
    </div>


    <?php
    require_once('includes/footer.php');
