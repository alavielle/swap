<?php

require_once('includes/init.php');

$title = "Accueil";
require_once('includes/header.php');

// PAGINATION
// Nb d'annonces par page
if (isset($_POST['nbAnnoncesParPage'])) {
    $nbEltsParPage = (int) $_POST['nbAnnoncesParPage'];
    if ($nbEltsParPage < 5) $nbEltsParPage = 5;  // par défaut
} else {
    $nbEltsParPage = 5;    // par défaut
}

// N° de la page courante
if (isset($_GET['page'])) {
    $page = (int) $_GET['page'];
} else { // La variable n'existe pas, c'est la première fois qu'on charge la page
    $page = 1;  // On se met sur la page 1 (par défaut)
}

// On calcule le numéro du premier élément qu'on prend pour le LIMIT de MySQL 
$premierEltDeLaPage = ($page - 1) * $nbEltsParPage;

// nb total d'annonces
$annonces_totales = sql("SELECT * FROM annonce");
$nb_annonces = $annonces_totales->rowCount();
$nbPages = ceil($nb_annonces / $nbEltsParPage);



$filtreMembre = 0;
if (isset($_GET['filtre']) && is_numeric($_GET['filtre'])) {
    $filtreMembre = $_GET['filtre'];
}


$categories = sql("SELECT * FROM categorie ORDER BY titre");

$membres = sql("SELECT m.id_membre, m.pseudo, a.id_annonce FROM membre m
            INNER JOIN annonce a WHERE a.id_membre = m.id_membre 
            GROUP BY m.id_membre ORDER BY m.pseudo");

$annonces = sql("SELECT a.*, m.pseudo FROM annonce a
            INNER JOIN membre m WHERE a.id_membre = m.id_membre
             ORDER BY a.date_enregistrement DESC
             LIMIT " . $premierEltDeLaPage . "," . $nbEltsParPage);

$prix = sql("SELECT prix FROM annonce ORDER BY prix DESC LIMIT 1");
$prix_max = $prix->fetch()['prix'];
$prix = sql("SELECT prix FROM annonce ORDER BY prix LIMIT 1");
$prix_min = $prix->fetch()['prix'];

?>


<div class="row">
    <div class="col-lg-2 col-md-3">
        <form id="filtre" class="ps-3">
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
                <option value="0" <?php if (empty($filtreMembre)) echo 'selected' ?>>Tous les membres</option>
                <?php if ($membres->rowCount() > 0) : ?>
                    <?php while ($membre = $membres->fetch()) : ?>
                        <option value="<?php echo $membre['id_membre'] ?>" <?php if ($membre['id_membre'] == $filtreMembre) echo 'selected' ?>>
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
                                <td class="col-md-3" >
                                    <div class="hauteur"><img src="<?php echo 'images/' . $annonce['photo'] ?>" alt="<?php echo $annonce['titre'] ?>" class="mx-auto d-block" height="100%"></div>
                                </td>
                                <td class="col-md-9">
                                    <div class="d-flex justify-content-between pb-2">
                                        <span class="d-inline"><a href="annonce.php?id=<?php echo $annonce['id_annonce'] ?>" class="fw-bold text-decoration-none text-lien"><?php echo $annonce['titre'] ?></a></span>
                                        <span class="fw-bold col-md-2 d-inline text-end"><?php echo number_format($annonce['prix'], 2, ',', '&nbsp;') ?>&nbsp;€</span>
                                    </div>
                                    <div class="d-flex">
                                        <p><?php echo $annonce['description_courte'] ?><br>
                                            <?php
                                            $extrait = substr($annonce['description_longue'], 0, 200);
                                            echo (strlen($annonce['description_longue']) > 200) ? substr($extrait, 0, strrpos($extrait, ' ')) . ' &hellip;' : $extrait;
                                            ?>
                                        </p>
                                    </div>
                                    <div class="d-flex align-items-end" data-attr="<?= $annonce['id_membre'] ?>">
                                        <p class="text-lien mt-3"><?php echo $annonce['pseudo'] ?>
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
                                            <?php endif ?>
                                            <?php for ($i = ceil($note['moyenne']); $i < 5; $i++) : ?>
                                                <i class="far fa-star"></i>
                                            <?php endfor ?>
                                            <span class="hidden"><?= $note['moyenne'] ?></span>
                                        </p>
                                    </div>
                                </td>

                            </tr>
                        <?php endwhile ?>
                    </tbody>

                </table>
                <div class="row">
                    <form action="" method="post" class="col-lg-2 col-sm-3">
                        <select id="nbAnnoncesParPage" name="nbAnnoncesParPage" class="form-select form-select-sm" onchange='this.form.submit()'>
                            <option value="5" <?php if (!isset($_POST['nbAnnoncesParPage']) || ($_POST['nbAnnoncesParPage']== 5))  echo 'selected' ?>>5</option>
                            <option value="10" <?php if (isset($_POST['nbAnnoncesParPage']) && ($_POST['nbAnnoncesParPage']== 10))  echo 'selected' ?>>10</option>
                            <option value="20" <?php if (isset($_POST['nbAnnoncesParPage']) && ($_POST['nbAnnoncesParPage']== 20))  echo 'selected' ?>>20</option>
                        </select>
                    </form>
                    <div class="col-lg-3 col-sm-4"> <label for="id_nb" class="form-label">annonces par page</label> </div>
                    <div class="col">
                        <nav aria-label="Page navigation" id="zonePagination">
                            <span class="hidden" id="nbDePages" data-index="<?= $nbPages ?>"></span>
                            <ul class="pagination justify-content-end">
                                <span id="previous" class="<?php if ($page == 1) echo 'disabled' ?>">
                                    <li class="page-item <?php if ($page == 1) echo 'disabled' ?>">
                                        <a class="page-link" href="" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                </span>
                                <?php for ($i = 1; $i <= $nbPages; $i++) : ?>
                                    <span class="numeros" id="numero_<?= $i ?>">
                                        <li class="page-item <?php if ($page == $i) echo 'active' ?> <?php if ($page == $i) echo 'pageCourante' ?>" id="page_<?= $i ?>"><a class="page-link" href=""><?= $i ?></a></li>
                                    </span>
                                <?php endfor ?>

                                <span id="next" class="<?php if ($page == $nbPages) echo 'disabled' ?>">
                                    <li class="page-item <?php if ($page == $nbPages) echo 'disabled' ?>">
                                        <a class="page-link" href="" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </span>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once('includes/footer.php');
