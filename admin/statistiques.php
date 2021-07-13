<?php

require_once('../includes/init.php');

// Redirection si qq tente d'utiliser les url sans en avoir le droit
if (!isAdmin()) {
    header('location:' . URL . 'connexion.php');
    exit();
}







$title = "Statistiques";
$subtitle = "Admin";
require_once('../includes/header.php');
?>

<div class="row">
    <div class="col-lg-6 col-xl-4">
        <div class="list-group" id="list-tab" role="tablist">
            <a class="list-group-item list-group-item-action active" id="meilleuresNotes" data-bs-toggle="list" href="#showMeilleuresNotes" role="tab">Top 5 des membres les mieux notés</a>
            <a class="list-group-item list-group-item-action" id="plusActifs" data-bs-toggle="list" href="#showPlusActifs" role="tab">Top 5 des membres les plus actifs</a>
            <a class="list-group-item list-group-item-action" id="plusAnciens" data-bs-toggle="list" href="#showPlusAnciens" role="tab">Top 5 des annonces les plus anciennes</a>
            <a class="list-group-item list-group-item-action" id="meilleuresCategories" data-bs-toggle="list" href="#showMeilleuresCategories" role="tab">Top 5 des catégories contenant le plus d'annonces</a>
        </div>
    </div>

    <div class="col-lg-5 col-xl-7">
        <div class="tab-content" id="nav-tabcontent">
            <div class="tab-pane fade show active" id="showMeilleuresNotes" role="tabpanel">
                <div class="col-10 offset-1 ">
                    <h2 mt-3>Top 5 des membres les mieux notés</h2>
                    <hr class="mb-3">
                    <div id="topNotes">
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="showPlusActifs" role="tabpanel">
                <div class="col-10 offset-1 ">
                    <h2 mt-3>Top 5 des membres les plus actifs</h2>
                    <hr class="mb-3">
                    <div id="topActifs">
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="showPlusAnciens" role="tabpanel">
                <div class="col-10 offset-1 ">
                    <h2 mt-3>Top 5 des annonces les plus anciennes</h2>
                    <hr class="mb-3">
                    <div id="topAnciens">
                    </div>
                </div>
            </div>
            
            <div class="tab-pane fade" id="showMeilleuresCategories" role="tabpanel">
                <div class="col-10 offset-1 ">
                    <h2 mt-3>Top 5 des catégories contenant le plus d'annonces</h2>
                    <hr class="mb-3">
                    <div id="topCategories">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once('../includes/footer.php');
