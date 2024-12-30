<?php
session_start();

// on verifie d'abord si l'utilisateur est connecte
if (!isset($_SESSION['email'])) {
    header('Location: ../connexion/index.php');  
    exit();
}

include '../fonctions/fonctions.php';  // on inclut la connexion à la base de données à l'aide du fichier bdd.php

if (isset($_GET['nomCocktail'])) {
    $nom_cocktail = htmlspecialchars($_GET['nomCocktail']);
    $db = connexion_database();

    // on recupere l'id de l'utilisateur qui est connecté
    $sql = $db->prepare("SELECT id_utilisateur FROM UTILISATEUR WHERE mail = :email");
    $sql->bindParam(":email", $_SESSION['email']);
    $sql->execute();
    $res = $sql->fetch();
    $id_user = $res['id_utilisateur'];

    $sql_check = $db->prepare("SELECT * FROM PANIER WHERE id_utilisateur = :id_utilisateur AND nom_cocktail = :nom_cocktail");
    $sql_check->bindParam(":id_utilisateur", $id_user);
    $sql_check->bindParam(":nom_cocktail", $nom_cocktail);
    $sql_check->execute();

    if ($sql_check->rowCount() == 0) {
        // on ajoute la recette dans le panier
        $sql_insert = $db->prepare("INSERT INTO PANIER (id_utilisateur, nom_cocktail) VALUES (:id_utilisateur, :nom_cocktail)");
        $sql_insert->bindParam(":id_utilisateur", $id_user);
        $sql_insert->bindParam(":nom_cocktail", $nom_cocktail);
        if ($sql_insert->execute()) {
            header('Location: ../mes_recettes_preferes/index.php');  // Rediriger vers la page des recettes préférées
        } else {
            echo "Erreur lors de l'ajout de la recette au panier.";
        }
    } else {
        echo "Cette recette est déjà dans vos préférées.";
    }
}
?>
