<?php

function sql(string $requete, array $params = array()): PDOStatement{

    global $pdo;
    $statement = $pdo->prepare($requete);
    if (!empty($params)) {
        foreach ($params as $key => $value) {
            $statement->bindValue(
                $key,
                htmlspecialchars($value),
                PDO::PARAM_STR
            );
        }
    }
    $statement->execute();
    return $statement;
}

// Fonctions utilisateur
function isConnected(){
    return isset($_SESSION['membre']);
}

function isAdmin(){
    return (isConnected() && $_SESSION['membre']['statut'] == 1);
}
function isMembre(){
    return (isConnected() && $_SESSION['membre']['statut'] == 0);
}

function getUserByPseudo(string $pseudo){
    $requete = sql("SELECT * FROM membre WHERE pseudo=:pseudo", array(
        'pseudo' => $pseudo
    ));
    if ($requete->rowCount() > 0) {
        return $requete->fetch();
    } else {
        return false;
    }
}

function getUserByEmail(string $email){
    $requete = sql("SELECT * FROM membre WHERE email=:email", array(
        'email' => $email
    ));
    if ($requete->rowCount() > 0) {
        return $requete->fetch();
    } else {
        return false;
    }
}

// fonction de chargement des photos
function upLoadPhotos($numPhoto, $prefix){
    $nomfichier = '';
    $image = "image" . $numPhoto;
    $data_img = "data_img" . $numPhoto;
    $nom_original = "nom_original" . $numPhoto;
    $chemin = $_SERVER['DOCUMENT_ROOT'] . URL . 'images/';

    // 1er cas $_FILES est dispo
    if (!empty($_FILES[$image]['name'])) {
        $nomfichier = $prefix . '_' . $_FILES[$image]['name'];
        move_uploaded_file($_FILES[$image]['tmp_name'], $chemin . $nomfichier);
    } 
    // 2ème cas, on utilise la memoire car $_FILES est perdu
    elseif (!empty($_POST[$data_img])) {
        $nomfichier = $prefix . '_' . $_POST[$nom_original];
        list(, $data) = explode(',', $_POST[$data_img]); // On récupère la variable $data qui est issue du tableau explode
        // ecriture du fichier 
        file_put_contents($chemin . $nomfichier, base64_decode($data));
    }
    return $nomfichier;
}

// fonction de suppression des photos
function suppPhotos($nomfichier){
    $chemin = $_SERVER['DOCUMENT_ROOT'] . URL . 'images/';
    if (!empty($nomfichier) && file_exists($chemin . $nomfichier)) {
        unlink($chemin . $nomfichier);
    }
}

// Formatage des numeros de tel
function formatFrenchPhoneNumber($phoneNumber, $international = false){
    //Supprimer tous les caractères qui ne sont pas des chiffres
    $phoneNumber = preg_replace('/[^0-9]+/', '', $phoneNumber);
    //Garder les 9 derniers chiffres
    $phoneNumber = substr($phoneNumber, -9);
    //On ajoute +33 si la variable $international vaut true et 0 dans tous les autres cas
    $motif = $international ? '+33 (\1) \2 \3 \4 \5' : '0\1 \2 \3 \4 \5';
    $phoneNumber = preg_replace('/(\d{1})(\d{2})(\d{2})(\d{2})(\d{2})/', $motif, $phoneNumber);
    
    return $phoneNumber;
    }

// fonction des messages
function add_flash(string $message, string $classe){
    if (!isset($_SESSION['messages'][$classe])) {
        $_SESSION['messages'][$classe] = array();
    }
    $_SESSION['messages'][$classe][] = $message;
}

function show_flash($option = null){
    $messages = '';
    if (isset($_SESSION['messages'])) {
        foreach (array_keys($_SESSION['messages']) as $keyname) {
            $messages .= '<div class="alert alert-' . $keyname . '">' . implode('<br>', $_SESSION['messages'][$keyname]) . '</div>'; // implode est équivalent au split
        }
    }
    if ($option == 'reset') {
        unset($_SESSION['messages']); // Je détruis les messages pour ne les afficher qu'1 seule fois
    }
    return $messages;
}

// Lister tous les pays européen pour la liste déroulante
function pays_europe(){
    $xml = simplexml_load_file('./docs/mondial.xml');
    $pays = array();
    foreach($xml->country as $country){
        $continent = explode(' ', $country->encompassed['continent']);
        if (in_array('europe', $continent)) {
            $pays[] = $country->name;
        }
    }
    sort($pays, SORT_STRING);
    return $pays;
}
