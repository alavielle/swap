<?php

require_once('includes/init.php');

$title = "Recherche";
require_once('includes/header.php');
?>

<div class="container">
    <div class="row">
        <p class="text-center fst-italic mt-3"><span id="nb"><?php echo $nb_annonces ?></span> résultats</p>
    </div>
    <div class="row">
        <div class="col-lg-9 col-md-8">
            <div class="row ms-md-5">


                <div class="table-responsive-md">
                    <table class="table table-hover">
                        <hr class="mt-3">
                        <tbody id="tbody">
                            <?php while ($annonce = $annonces->fetch()) : ?>
                                <tr>
                                    <td class="col-md-3" id="tbodyPhoto">
                                        <div id="hauteur"><img src="<?php echo 'images/' . $annonce['photo'] ?>" alt="<?php echo $annonce['titre'] ?>" class="mx-auto d-block" width="100%" width="150"></div>
                                    </td>
                                    <td>
                                        <p><a href="annonce.php?id=<?php echo $annonce['id_annonce'] ?>" id="tbodyTitre" class="fw-bold text-decoration-none text-lien"><?php echo $annonce['titre'] ?></a></p>
                                        <p id="tbodyDesc">
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






<?php
require_once('includes/footer.php');
