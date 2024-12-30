<?php
session_start();
include_once('../fonctions/fonctions.php');
if (!isset($_SESSION['id_utilisateur'])) {
    echo "Erreur : utilisateur non identifié.";
    exit();
}

$db = connexion_database();
if (isset($_POST['id_panier'])) {
    $id_panier = $_POST['id_panier'];

    try {
        $sql = $db->prepare("DELETE FROM PANIER WHERE id_panier = :id_panier AND id_utilisateur = :id_utilisateur");
        $sql->execute([
            'id_panier' => $id_panier,
            'id_utilisateur' => $_SESSION['id_utilisateur']
        ]);

        header('Location: index.php');
        exit();
    } catch (PDOException $e) {
        echo "Erreur lors de la suppression de la recette : " . $e->getMessage();
    }
} else {
    echo "Erreur : ID du panier non fourni.";
}
?>
