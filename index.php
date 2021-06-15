<?php

require_once('includes/init.php');

$title = "Accueil";
require_once('includes/header.php');

$categories = sql("SELECT * FROM categorie ORDER BY titre");
$membres = sql("SELECT m.id_membre, m.pseudo, a.id_annonce FROM membre m
            INNER JOIN annonce a WHERE a.id_membre = m.id_membre 
            GROUP BY m.id_membre ORDER BY m.pseudo");
$annonces = sql("SELECT a.*, m.pseudo FROM annonce a
            INNER JOIN membre m WHERE a.id_membre = m.id_membre
             ORDER BY date_enregistrement DESC");
$prix = sql("SELECT prix FROM annonce ORDER BY prix DESC LIMIT 1");
$prix_max = $prix->fetch()['prix'];
$prix = sql("SELECT prix FROM annonce ORDER BY prix LIMIT 1");
$prix_min = $prix->fetch()['prix'];
$nb_annonces = $annonces->rowCount();
?>

<div class="container">
    <div class="row">
        <div class="col-lg-3 col-md-4">
            <form action="" id="filtre">
                <label for="id_categorie" class="form-label fw-bold">Catégorie</label>
                <select id="id_categorie" name="id_categorie" class="form-select">
                    <option value="0" selected>Toutes les catégories</option>
                    <?php if ($categories->rowCount() > 0) : ?>
                        <?php while ($categorie = $categories->fetch()) : ?>
                            <option value="<?php echo $categorie['id_categorie'] ?>">
                                <?php echo $categorie['titre'] ?></option>
                        <?php endwhile ?>
                    <?php endif ?>
                </select>
                <label for="region" class="form-label fw-bold mt-3">Région</label>
                <select id="region" name="region" class="form-select">
                <option value="Toutes" selected>Toutes les régions</option>
                </select>
                <label for="id_membre" class="form-label fw-bold mt-3">Membre</label>
                <select id="id_membre" name="id_membre" class="form-select">
                    <option value="0" selected>Tous les membres</option>
                    <?php if ($membres->rowCount() > 0) : ?>
                        <?php while ($membre = $membres->fetch()) : ?>
                            <option value="<?php echo $membre['id_membre'] ?>">
                                <?php echo $membre['pseudo'] ?></option>
                        <?php endwhile ?>
                    <?php endif ?>
                </select>
                <label for="rangePrix" class="form-label fw-bold mt-3">Prix</label>
                <input type="range" class="form-range" min=<?php echo $prix_min ?> max=<?php echo $prix_max ?> id="rangePrix" name="rangePrix" value="<?php echo $prix_max ?>">
                <span id="prixMax" class="fst-italic">maximum : <?php echo number_format($prix_max, 0, ',', ' ') ?> € </span>
            </form>
            <p class="text-center fst-italic mt-3"><span id="nb"><?php echo $nb_annonces ?> résultat<?php echo ($nb_annonces == 1) ? ' ' : 's' ?></span> </p>

        </div>
        <div class="col-lg-9 col-md-8">
            <div class="row ms-md-5">
                <div class="col-lg-5">
                    <form action="">
                        <select id="tri" name="tri" class="form-select">
                            <option value="1">Trier par date (du plus récent au moins récent)</option>
                            <option value="2">Trier par date (du moins récent au plus récent)</option>
                            <option value="3">Trier par prix (du moins cher au plus cher)</option>
                            <option value="4">Trier par prix (du plus cher au moins cher)</option> 
                            <option value="5">Les meilleurs vendeurs en premier</option>
                        </select>
                    </form>

                </div>

                <div class="table-responsive-md">
                    <table class="table table-hover" >
                        <hr class="mt-3">
                        <tbody id="tbody">
                        <?php while ($annonce = $annonces->fetch()) : ?>
                            <tr>
                                <td class="col-md-3" id="tbodyPhoto"><div id="hauteur"><img src="<?php echo 'images/' . $annonce['photo'] ?>" alt="<?php echo $annonce['titre'] ?>" class="mx-auto d-block" width="100%" width="150"></div></td>
                                <td>
                                    <p ><a href="annonce.php?id=<?php echo $annonce['id_annonce'] ?>" id="tbodyTitre" class="fw-bold text-decoration-none text-lien"><?php echo $annonce['titre'] ?></a></p>
                                    <p id="tbodyDesc" >
                                    <?php
                                    $extrait = substr($annonce['description_longue'], 0, 200);
                                    echo (iconv_strlen($annonce['description_longue']) > 200) ? substr($extrait, 0, strrpos($extrait, ' ')) . ' &hellip;' : $extrait;
                                    ?>
                                    </p>
                                    <p id="tbodyPseudo" class="mt-3"><a href="#" class="text-decoration-none text-lien"><?php echo $annonce['pseudo'] ?></a></p>
                                </td>
                                <td id="tbodyPrix" class="fw-bold col-md-2 text-end"><?php echo number_format($annonce['prix'], 2, ',', ' ') ?> €</td>
                            </tr>
                        <?php endwhile ?>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>





</div>

</div>


</div>



<?php
require_once('includes/footer.php');
