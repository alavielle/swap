<?php

require_once('includes/init.php');

$title = "Accueil";
require_once('includes/header.php');

$filtreMembre=0;
if (isset($_GET['filtre']) && is_numeric($_GET['filtre'])){
    $filtreMembre = $_GET['filtre'];
}


$categories = sql("SELECT * FROM categorie ORDER BY titre");
$membres = sql("SELECT m.id_membre, m.pseudo, a.id_annonce FROM membre m
            INNER JOIN annonce a WHERE a.id_membre = m.id_membre 
            GROUP BY m.id_membre ORDER BY m.pseudo");
$annonces = sql("SELECT a.*, m.pseudo FROM annonce a
            INNER JOIN membre m WHERE a.id_membre = m.id_membre
             ORDER BY a.date_enregistrement DESC");
$prix = sql("SELECT prix FROM annonce ORDER BY prix DESC LIMIT 1");
$prix_max = $prix->fetch()['prix'];
$prix = sql("SELECT prix FROM annonce ORDER BY prix LIMIT 1");
$prix_min = $prix->fetch()['prix'];
$nb_annonces = $annonces->rowCount();


?>

<!-- <div class="container"> -->
<div class="row">
    <div class="col-lg-2 col-md-3">
        <form action="" id="filtre" class="ps-3">
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
                <option value="0" <?php if(empty($filtreMembre)) echo 'selected' ?>>Tous les membres</option>
                <?php if ($membres->rowCount() > 0) : ?>
                    <?php while ($membre = $membres->fetch()) : ?>
                        <option value="<?php echo $membre['id_membre'] ?>"  <?php if($membre['id_membre'] == $filtreMembre) echo 'selected' ?>>
                        <?php echo $membre['pseudo'] ?></option>
                    <?php endwhile ?>
                <?php endif ?>
            </select>
            <label for="rangePrix" class="form-label fw-bold mt-3">Prix</label>
            <input type="range" class="form-range" min=<?php echo $prix_min ?> max=<?php echo $prix_max ?> id="rangePrix" name="rangePrix" value="<?php echo $prix_max ?>">
            <span id="prixMax" class="fst-italic">maximum : <?php echo number_format($prix_max, 0, ',', ' ') ?>&nbsp;€ </span>
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
                <table class="table table-hover">
                    <hr class="mt-3">
                    <tbody id="tbody">
                        <?php while ($annonce = $annonces->fetch()) : ?>
                            <tr>
                                <td class="col-md" id="tbodyPhoto">
                                    <div id="hauteur"><img src="<?php echo 'images/' . $annonce['photo'] ?>" alt="<?php echo $annonce['titre'] ?>" class="mx-auto d-block" height="100%"></div>
                                </td>
                                <td class="col-md">
                                    <div class="d-flex justify-content-between pb-2">
                                        <span class="d-inline"><a href="annonce.php?id=<?php echo $annonce['id_annonce'] ?>" id="tbodyTitre" class="fw-bold text-decoration-none text-lien"><?php echo $annonce['titre'] ?></a></span>
                                        <span class="fw-bold col-md-2 d-inline text-end"><?php echo number_format($annonce['prix'], 2, ',', '&nbsp;') ?>&nbsp;€</span>
                                    </div>
                                    <div class="d-flex">
                                        <p id="tbodyDesc"><?php echo $annonce['description_courte'] ?><br>
                                            <?php
                                            $extrait = substr($annonce['description_longue'], 0, 200);
                                            echo (iconv_strlen($annonce['description_longue']) > 200) ? substr($extrait, 0, strrpos($extrait, ' ')) . ' &hellip;' : $extrait;
                                            ?>
                                        </p>
                                    </div>
                                    <div class="d-flex align-items-end">
                                        <p id="tbodyPseudo" class="mt-3 "><a href="index.php?filtre=<?= $annonce['id_membre'] ?>" class="text-decoration-none text-lien"><?php echo $annonce['pseudo'] ?>
                                        <?php
                                        $notes = sql("SELECT AVG(note) as 'moyenne' FROM note where id_membre2 = " . $annonce['id_membre']);
                                        $note = $notes->fetch();
                                        $i = 1;
                                        while ($i <= $note['moyenne']) : ?>
                                            <i class="fas fa-star"></i>
                                        <?php
                                            $i++;
                                        endwhile;
                                        if ($note['moyenne'] > $i - 1) : ?>
                                            <i class="fas fa-star-half-alt"></i>
                                        <?php endif ?></a><span class="hidden" id="moyenne"><?= $note['moyenne'] ?></span></p>
                                    </div>
                                </td>

                            </tr>
                        <?php endwhile ?>
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</div>





<!-- </div> -->

</div>


</div>



<?php
require_once('includes/footer.php');
