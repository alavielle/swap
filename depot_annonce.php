<?php

require_once('includes/init.php');

if (!isConnected()) {
    header('location:' . URL . 'connexion.php');
    exit();
}


$modif = false;
if (!empty($_GET)) {
    if ($_GET['action'] == "edit" && is_numeric($_GET['id'])) {
        $annonces = sql("SELECT * FROM annonce WHERE id_annonce = :id_annonce", array(
            'id_annonce' => $_GET['id']
        ));
        if ($annonces->rowCount() > 0) {
            $current = $annonces->fetch();
            $modif = true;
            $photos = sql("SELECT * FROM photo WHERE id_photo =:id_photo", array(
                'id_photo' => $current['id_photo']
            ));
            if ($photos->rowCount() > 0) {
                $photo = $photos->fetch();
            }
        }
    }

} else {

    $annonces = sql("SELECT adresse, cp, ville, pays FROM annonce WHERE id_membre = :id_membre ORDER BY date_enregistrement DESC LIMIT 1", array(
        'id_membre' => $_SESSION['membre']['id_membre']
    ));
    if ($annonces->rowCount() > 0) {
        $current = $annonces->fetch();
        add_flash("L'adresse de votre dernière annonce en ligne a été insérée automatiquement", 'warning');
    }
}


if (!empty($_POST)) {

    // Formulaire données personnelles
    if (isset($_POST['enregistrer']) || isset($_POST['maj'])) {

        $errors = 0;
        if (empty($_POST['titre'])) {
            $errors++;
            add_flash('Le titre ne peut pas etre vide', 'danger');
        }

        if (empty($_POST['descCourte'])) {
            $errors++;
            add_flash("La description courte ne peut pas etre vide", 'danger');
        }

        if (empty($_POST['descLongue'])) {
            $errors++;
            add_flash("La description longue ne peut pas etre vide", 'danger');
        }

        if (empty($_POST['prix'])) {
            $errors++;
            add_flash('Merci de renseigner le prix', 'danger');
        } else {
            if (!is_numeric($_POST['prix'])) {
                $errors++;
                add_flash('Format du prix invalide', 'danger');
            }
        }

        if (empty($_POST['id_categorie'])) {
            $errors++;
            add_flash('Merci de renseigner la catégorie', 'danger');
        }

        if (empty($_POST['adresse'])) {
            $errors++;
            add_flash("L'adresse de l'annonce ne peut être vide", 'danger');
        }

        if (empty($_POST['cp'])) {
            $errors++;
            add_flash('Le code postal ne peut être vide', 'danger');
        } else {
            if (!is_numeric($_POST['cp'])) {
                $errors++;
                add_flash('Format du code postal invalide', 'danger');
            } else {
                $_POST['cp'] = sprintf("%05d", $_POST['cp']);
            }
        }

        // controle de l'image
        if (empty($_FILES['image1']['name']) && empty($_POST['nom_original1']) && empty($photo['photo1'])) {
            $errors++;
            add_flash('Merci de choisir au moins une photo pour votre annonce', 'danger');
        }
        $ext_auto = ['image/jpeg', 'image/png'];
        if (!empty($_FILES['image1']['name']) && !in_array($_FILES['image1']['type'], $ext_auto)) {
            $errors++;
            add_flash('Format autorisé : JPEG et PNG', 'danger');
        }
        if (!empty($_FILES['image2']['name']) && !in_array($_FILES['image2']['type'], $ext_auto)) {
            $errors++;
            add_flash('Format autorisé : JPEG et PNG', 'danger');
        }
        if (!empty($_FILES['image3']['name']) && !in_array($_FILES['image3']['type'], $ext_auto)) {
            $errors++;
            add_flash('Format autorisé : JPEG et PNG', 'danger');
        }
        if (!empty($_FILES['image4']['name']) && !in_array($_FILES['image4']['type'], $ext_auto)) {
            $errors++;
            add_flash('Format autorisé : JPEG et PNG', 'danger');
        }
        if (!empty($_FILES['image5']['name']) && !in_array($_FILES['image5']['type'], $ext_auto)) {
            $errors++;
            add_flash('Format autorisé : JPEG et PNG', 'danger');
        }

        if ($errors == 0) {

            $nomfichier1 = '';
            $nomfichier2 = '';
            $nomfichier3 = '';
            $nomfichier4 = '';
            $nomfichier5 = '';

            if ($modif) {
                $prefix = $current['id_annonce'];

                if (!empty($_FILES['image1']['name'])) {
                    $nomfichier1 = upLoadPhotos(1, $prefix);
                    add_flash('photo1', 'warning');
                    if ($_FILES['image1']['name'] != $photo['photo1']) {
                        suppPhotos($photo['photo1']);
                    }
                } else {
                    $nomfichier1 = $photo['photo1'];
                }
                if (!empty($_FILES['image2']['name'])) {
                    $nomfichier2 = upLoadPhotos(2, $prefix);
                    if ($_FILES['image2']['name'] != $photo['photo2']) {
                        suppPhotos($photo['photo2']);
                    }
                } else {
                    $nomfichier2 = $photo['photo2'];
                }
                if (!empty($_FILES['image3']['name'])) {
                    $nomfichier3 = upLoadPhotos(3, $prefix);
                    if ($_FILES['image3']['name'] != $photo['photo3']) {
                        suppPhotos($photo['photo3']);
                    }
                } else {
                    $nomfichier3 = $photo['photo3'];
                }
                if (!empty($_FILES['image4']['name'])) {
                    $nomfichier4 = upLoadPhotos(4, $prefix);
                    if ($_FILES['image4']['name'] != $photo['photo4']) {
                        suppPhotos($photo['photo4']);
                    }
                } else {
                    $nomfichier4 = $photo['photo4'];
                }
                if (!empty($_FILES['image5']['name'])) {
                    $nomfichier5 = upLoadPhotos(5, $prefix);
                    if ($_FILES['image5']['name'] != $photo['photo5']) {
                        suppPhotos($photo['photo5']);
                    }
                } else {
                    $nomfichier5 = $photo['photo5'];
                }

                sql("UPDATE photo SET photo1=:photo1, photo2=:photo2, photo3=:photo3, photo4=:photo4, photo5=:photo5 WHERE id_photo=:id_photo", array(
                    'photo1' => $nomfichier1,
                    'photo2' => $nomfichier2,
                    'photo3' => $nomfichier3,
                    'photo4' => $nomfichier4,
                    'photo5' => $nomfichier5,
                    'id_photo' => $current['id_photo']
                ));

                sql("UPDATE annonce SET titre=:titre, description_courte=:descCourte, description_longue=:descLongue, prix=:prix, photo=:photo, pays=:pays, ville=:ville, adresse=:adresse, cp=:cp, id_categorie=:id_categorie WHERE id_annonce =:id_annonce ", array(
                    'titre' => $_POST['titre'],
                    'descCourte' => $_POST['descCourte'],
                    'descLongue' => $_POST['descLongue'],
                    'prix' => $_POST['prix'],
                    'photo' => $nomfichier1,
                    'adresse' => $_POST['adresse'],
                    'cp' => $_POST['cp'],
                    'ville' => $_POST['ville'],
                    'pays' => $_POST['pays'],
                    'id_categorie' =>  $_POST['id_categorie'],
                    'id_annonce' => $current['id_annonce']
                ));

                add_flash('Votre annonce a été modifiée', 'warning');
                header('location:' . URL);
                exit();
            }
            if (isset($_POST['enregistrer'])) {

                $lastAnnonce = sql("SELECT `id_annonce` FROM `annonce` ORDER BY id_annonce DESC LIMIT 1");
                $lastId_annonce = $lastAnnonce->fetch();
                $prefix = $lastId_annonce['id_annonce'] + 1;

                $nomfichier1 = upLoadPhotos(1, $prefix);
                $nomfichier2 = upLoadPhotos(2, $prefix);
                $nomfichier3 = upLoadPhotos(3, $prefix);
                $nomfichier4 = upLoadPhotos(4, $prefix);
                $nomfichier5 = upLoadPhotos(5, $prefix);

                sql("INSERT INTO photo VALUES (NULL, :photo1, :photo2, :photo3, :photo4, :photo5)", array(
                    'photo1' => $nomfichier1,
                    'photo2' => $nomfichier2,
                    'photo3' => $nomfichier3,
                    'photo4' => $nomfichier4,
                    'photo5' => $nomfichier5
                ));
                $lastId_photo = $pdo->lastInsertId();

                sql("INSERT INTO annonce (titre, description_courte, description_longue, prix, photo, pays, ville, adresse, cp, id_membre, id_categorie, id_photo, date_enregistrement) VALUES (:titre, :descCourte, :descLongue, :prix, :photo, :pays, :ville, :adresse, :cp,  :id_membre, :id_categorie, :id_photo, NOW())", array(
                    'titre' => $_POST['titre'],
                    'descCourte' => $_POST['descCourte'],
                    'descLongue' => $_POST['descLongue'],
                    'prix' => $_POST['prix'],
                    'photo' => $nomfichier1,
                    'adresse' => $_POST['adresse'],
                    'cp' => $_POST['cp'],
                    'ville' => $_POST['ville'],
                    'pays' => $_POST['pays'],
                    'id_membre' => $_SESSION['membre']['id_membre'],
                    'id_categorie' =>  $_POST['id_categorie'],
                    'id_photo' => $lastId_photo
                ));

                add_flash('Votre annonce a été enregistrée', 'warning');
                header('location:' . URL);
                exit();
            }
        }
    }
}


$title = 'Déposer une annonce';
require_once('includes/header.php');

$categories = sql("SELECT * FROM categorie ORDER BY titre");
?>

<div class="row">
    <div class="col mx-5">
        <h1><?php echo ($modif) ? 'Modifier votre ' : 'Déposer une ' ?>annonce</h1>
    </div>
    <form method="post" enctype="multipart/form-data">
        <div class="row mx-5">
            <div class="col-lg-5">
                <div class="mb-3">
                    <label for="titre" class="form-label">Titre</label>
                    <input type="text" id="titre" name="titre" class="form-control" value="<?php echo $_POST['titre'] ?? $current['titre'] ?? '' ?>" placeholder="Titre de l'annonce">
                </div>
                <div class="mb-3">
                    <label for="descCourte" class="form-label">Description courte</label>
                    <textarea id="descCourte" name="descCourte" class="form-control" rows="2" placeholder="Description courte de votre annonce"><?php echo $_POST['descCourte'] ?? $current['description_courte'] ?? '' ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="descLongue" class="form-label">Description longue</label>
                    <textarea id="descLongue" name="descLongue" class="form-control" rows="4" placeholder="Description longue de votre annonce"><?php echo $_POST['descLongue'] ?? $current['description_longue'] ?? '' ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="prix" class="form-label">Prix</label>
                    <input type="text" id="prix" name="prix" class="form-control" value="<?php echo $_POST['prix'] ?? $current['prix'] ?? '' ?>" placeholder="Prix figurant dans l'annonce">
                </div>
                <div class="mb-3">
                    <label for="id_categorie" class="form-label">Catégorie</label>
                    <select id="id_categorie" name="id_categorie" class="form-select">
                        <option disabled selected>Choisir une catégorie</option>
                        <?php if ($categories->rowCount() > 0) : ?>
                            <?php while ($categorie = $categories->fetch()) : ?>
                                <option value="<?php echo $categorie['id_categorie'] ?>" <?php if ((!empty($_POST['id_categorie']) && $_POST['id_categorie'] == $categorie['id_categorie']) || (!empty($current['id_categorie']) && $current['id_categorie'] == $categorie['id_categorie'])) echo 'selected'; ?>>
                                    <?php echo $categorie['titre'] ?></option>
                            <?php endwhile ?>
                        <?php else : ?>
                            <option disabled selected>Pas de catégorie</option>
                        <?php endif ?>
                    </select>
                </div>
            </div>
            <div class="col-lg-7">
                <div><label for="photo" class="form-label">Photos</label></div>
                <div class="mb-3">
                    <input type="file" id="header1" name='image1' class="hidden form-control" accept="image/jpeg, image/png">
                    <label for="header1" id="preview1" class="position-relative">
                        <img src="<?php echo (!empty($_POST['data_img1'])) ? $_POST['data_img1'] : ((isset($photo['photo1'])) ? URL . 'images/' . $photo['photo1'] : 'https://via.placeholder.com/100?text=Photo1') ?>" alt="preview1" class="rounded" width="148">
                        <span <?php if(!$modif) echo 'hidden'?> class="pe-1 text-secondary position-absolute top-0 end-0" id="supprPhoto1"><i class="far fa-window-close"></i></span>
                    </label>
                    <input type="hidden" id="nom_original1" name="nom_original1" value="<?php echo $_POST['nom_original'] ?? '' ?>">
                    <input type="hidden" id="data_img1" name="data_img1" value="<?php echo $_POST['data_img1'] ?? '' ?>">
                    <input type="file" id="header2" name='image2' class="hidden form-control" accept="image/jpeg, image/png">
                    <label for="header2" id="preview2" class="position-relative">
                        <img src="<?php echo (!empty($_POST['data_img2'])) ? $_POST['data_img2'] : ((!empty($photo['photo2'])) ? URL . 'images/' . $photo['photo2'] : 'https://via.placeholder.com/100?text=Photo2') ?>" alt="preview" class="rounded " width="148">
                        <span <?php if(!$modif) echo 'hidden'?> class="pe-1 text-secondary position-absolute top-0 end-0"><i class="far fa-window-close"></i></span>
                    </label>
                    <input type="hidden" id="nom_original2" name="nom_original2" value="<?php echo $_POST['nom_original2'] ?? '' ?>">
                    <input type="hidden" id="data_img2" name="data_img2" value="<?php echo $_POST['data_img2'] ?? '' ?>">

                    <input type="file" id="header3" name='image3' class="hidden form-control" accept="image/jpeg, image/png">
                    <label for="header3" id="preview3" class="position-relative">
                        <img src="<?php echo (!empty($_POST['data_img3'])) ? $_POST['data_img3'] : ((!empty($photo['photo3'])) ? URL . 'images/' . $photo['photo3'] : 'https://via.placeholder.com/100?text=Photo3') ?>" alt="preview" class="rounded " width="148">
                        <span <?php if(!$modif) echo 'hidden'?> class="pe-1 text-secondary position-absolute top-0 end-0"><i class="far fa-window-close"></i></span>
                    </label>
                    <input type="hidden" id="nom_original3" name="nom_original3" value="<?php echo $_POST['nom_original3'] ?? '' ?>">
                    <input type="hidden" id="data_img3" name="data_img3" value="<?php echo $_POST['data_img3'] ?? '' ?>">

                    <input type="file" id="header4" name='image4' class="hidden form-control" accept="image/jpeg, image/png">
                    <label for="header4" id="preview4" class="position-relative">
                        <img src="<?php echo (!empty($_POST['data_img4'])) ? $_POST['data_img4'] : ((!empty($photo['photo4'])) ? URL . 'images/' . $photo['photo4'] : 'https://via.placeholder.com/100?text=Photo4') ?>" alt="preview" class="rounded " width="148">
                    <span <?php if(!$modif) echo 'hidden'?> class="pe-1 text-secondary position-absolute top-0 end-0"><i class="far fa-window-close"></i></span>
                    </label>
                    <input type="hidden" id="nom_original4" name="nom_original4" value="<?php echo $_POST['nom_original4'] ?? '' ?>">
                    <input type="hidden" id="data_img4" name="data_img4" value="<?php echo $_POST['data_img4'] ?? '' ?>">

                    <input type="file" id="header5" name='image5' class="hidden form-control" accept="image/jpeg, image/png">
                        <label for="header5" id="preview5" class="position-relative">
                        <img src="<?php echo (!empty($_POST['data_img5'])) ? $_POST['data_img5'] : ((!empty($photo['photo5'])) ? URL . 'images/' . $photo['photo5'] : 'https://via.placeholder.com/100?text=Photo5') ?>" alt="preview" class="rounded" width="148">
                        <span <?php if(!$modif) echo 'hidden'?> class="pe-1 text-secondary position-absolute top-0 end-0"><i class="far fa-window-close"></i></a>
                    </label>
                    <input type="hidden" id="nom_original5" name="nom_original5" value="<?php echo $_POST['nom_original5'] ?? '' ?>">
                    <input type="hidden" id="data_img5" name="data_img5" value="<?php echo $_POST['data_img5'] ?? '' ?>">
                </div>
                <div class="mb-3">
                    <label for="adresse" class="form-label">Adresse</label>
                    <textarea id="adresse" name="adresse" class="form-control" rows="2" placeholder="Adresse figurant dans l'annonce"><?php echo (isset($current['adresse'])) ? $current['adresse'] : '' ?></textarea>
                </div>
                <div class="row">
                    <div class="col-xl-2 col-lg-3 col-4">
                        <label for="cp" class="form-label">Code Postal </label>
                        <input type="text" maxlength="5" id="cp" name="cp" class="form-control" value="<?php echo (isset($current['cp'])) ? $current['cp'] : '' ?>" placeholder="Code postal">
                    </div>
                    <div class="col">
                        <label for="ville" class="form-label">Ville</label>
                        <select id="ville" name="ville" class="form-select">
                            <option selected><?php echo (isset($current['ville'])) ? $current['ville'] : '' ?></option>
                        </select>
                    </div>
                    <div class="col">
                        <label for="pays" class="form-label">Région*</label>
                        <select id="pays" name="pays" class="form-control">
                            <option selected><?php echo (isset($current['pays'])) ? $current['pays'] : '' ?></option>
                        </select>
                        <span class="fst-italic">* France uniquement</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mx-5">
            <div class="col mx-auto">
                <button class="btn btn-perso mt-3" name="<?php echo ($modif) ? "maj" : "enregistrer" ?>"><?php if ($modif) : ?>Mettre à jour<?php else : ?>Enregistrer<?php endif ?></button>
            </div>
        </div>
    </form>
</div>

<?php
require_once('includes/footer.php');
