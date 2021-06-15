<?php
$currentId = $_SESSION['membre']['id_membre'];
?>

<form method="post">

    <div class="row">
        <div class="col-xl-4 offset-xl-1 col-lg-5">
            <div class="mb-3">
                <label for="pseudo">Pseudo</label>
                <input type="text" id="pseudo" name="pseudo" class="form-control" value="<?php echo $selectMembre['pseudo'] ?>" <?php if ($selectMembre['id_membre'] !== $currentId) echo 'disabled' ?>>
            </div>
            <div class="mb-3">
                <label for="nom" class="form-label">Nom</label>
                <input type="text" id="nom" name="nom" class="form-control" value="<?php echo  $selectMembre['nom'] ?? '' ?>" <?php if ($selectMembre['id_membre'] !== $currentId) echo 'disabled' ?>>
            </div>
            <div class="mb-3">
                <label for="prenom" class="form-label">Prénom</label>
                <input type="text" id="prenom" name="prenom" class="form-control" value="<?php echo  $selectMembre['prenom'] ?? '' ?>" <?php if ($selectMembre['id_membre'] !== $currentId) echo 'disabled' ?>>
            </div>
        </div>
        <div class="col-xl-4 offset-xl-1 col-lg-5">
            <div class="mb-3">
                <label for="email">Email</label>
                <input type="text" id="email" name="email" class="form-control" value="<?php echo $selectMembre['email'] ?>" <?php if ($selectMembre['id_membre'] !== $currentId) echo 'disabled' ?>>
            </div>
            <div class="mb-3">
                <label for="telephone" class="form-label">Téléphone</label>
                <input type="text" id="telephone" name="telephone" class="form-control" value="<?php echo   formatFrenchPhoneNumber($selectMembre['telephone']) ?? '' ?>" <?php if ($selectMembre['id_membre'] !== $currentId) echo 'disabled' ?>>
            </div>
            <div class="mb-3">
                <div class="row">
                    <div class="col-6">
                        <label for="civilite" class="form-label">Civilité</label>
                        <select id="civilite" name="civilite" class="form-select" <?php if ($selectMembre['id_membre'] !== $currentId) echo 'disabled' ?>>
                            <option value="m" <?php if ($selectMembre['civilite'] == "m") echo 'selected' ?>>Homme</option>
                            <option value="f" <?php if ($selectMembre['civilite'] == "f") echo 'selected' ?>>Femme</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-5 <?php if($title !== 'Profil') echo 'hidden' ?>">
                <div class="mx-auto" style="width: 200px;">
                    <button class="btn btn-perso mb-3" name="update_perso">Mettre à jour</button>
                </div>
            </div>
    </div>
</form>