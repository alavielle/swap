<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SWAP | <?php echo $title ?? '' ?> <?php echo $subtitle ?></title>
    <!-- bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">

    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>

    <!-- Via Michelin-->
    <script src="https://secure-apijs.viamichelin.com/apijsv2/api/js?key=JSV2GP20210622171454935942936368$165380&lang=fra&protocol=https" type="text/javascript"> </script>
    <!-- font awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Data Tables -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.css">

    <!-- css principal -->
    <link rel="stylesheet" href="<?php echo URL ?>css/style.css">


</head>

<body>
    <header>

        <nav class="navbar navbar-expand-lg navbar-light fixed-top bg-perso">
            <div class="container-fluid">
                <a class="nav-link <?php if ($title == "Accueil") echo 'activePerso'; ?>" aria-current="page" href="<?php echo URL ?>"><i class="fas fa-home"></i> SWAP</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <ul class="navbar-nav me-auto mb-2 mb-md-0">
                        <li class="nav-item">
                            <a class="nav-link <?php if ($title == "Nous") echo 'active'; ?>" aria-current="page" href="<?php echo URL ?>nous.php">Qui sommes nous</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php if ($title == "Contact") echo 'active'; ?>" aria-current="page" href="<?php echo URL ?>contact.php">Contact</a>
                        </li>
                        <?php if (isAdmin()) : ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle <?php if ($subtitle == "Admin") echo 'active'; ?>" href="#" id="sousmenu" role='button' data-bs-toggle="dropdown">Back Office</a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="<?php echo URL ?>admin/gestion_annonces.php">Gestion des annonces</a></li>
                                    <li><a class="dropdown-item" href="<?php echo URL ?>admin/gestion_categories.php">Gestion des categories</a></li>
                                    <li><a class="dropdown-item" href="<?php echo URL ?>admin/gestion_membres.php">Gestion des membres</a></li>
                                    <li><a class="dropdown-item" href="<?php echo URL ?>admin/gestion_commentaires.php">Gestion des commentaires</a></li>
                                    <li><a class="dropdown-item" href="<?php echo URL ?>admin/gestion_notes.php">Gestion des notes</a></li>
                                    <li><a class="dropdown-item" href="<?php echo URL ?>admin/statistiques.php">Statistiques</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?php if ($subtitle == "Connect") echo 'activePerso'; ?>" href="#" id="sousmenu" role='button' data-bs-toggle="dropdown"><i class="fas fa-user"></i><?php echo !isConnected() ? ' Espace Membre' : ' ' . $_SESSION['membre']['pseudo'] ?></a>
                            <ul class="dropdown-menu">
                                <?php if (!isConnected()) : ?>
                                    <li><a class="dropdown-item" href="<?php echo URL ?>inscription.php">Inscription</a></li>
                                    <li><a class="dropdown-item" href="<?php echo URL ?>connexion.php">Connexion</a></li>
                                <?php else : ?>
                                    <li><a class="dropdown-item" href="<?php echo URL ?>depot_annonce.php">Déposer une annonce</a></li>
                                    <li><a class="dropdown-item" href="<?php echo URL ?>profil.php">Profil</a></li>
                                <?php endif ?>
                            </ul>
                        </li>
                        <?php if (isConnected()) : ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo URL ?>connexion.php?action=logout"><i class="fas fa-power-off"></i></a>
                            </li>
                        <?php endif ?>
                    </ul>
                    <?php if ($title == "Accueil") : ?>
                        <div class="d-flex">
                            <input class="form-control me-2" list="datalistOptions" id="myDataList" placeholder="Recherche ...">
                            <datalist id="datalistOptions">
                            </datalist>
                            <button class="btn btn-outline-light" type="submit" id="recherche">Rechercher</button>
                        </div>
                    <?php endif ?>
                </div>
            </div>
        </nav>



    </header>
    
    <main class="container-fluid my-5">

        <?php if (!empty(show_flash())) : ?>
            <div class="row justify-content-center">
                <div class="col">
                    <?php echo show_flash('reset'); ?>
                </div>
            </div>
        <?php endif; ?>