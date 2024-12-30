<?php
session_start();

include "../fonctions/fonctions.php";

if (!isset($_POST['email']) || !isset($_POST['password'])) {
    header('Location: ./index.php?erreur_connexion=erreur lors de la transmission des donnees');
} else {
    $db = connexion_database();

    $email = htmlspecialchars($_POST['email']);
    $mdp = htmlspecialchars($_POST['password']);
    
    $sql = $db->prepare("SELECT * FROM UTILISATEUR WHERE mail LIKE :email");
    $sql->bindParam(':email', $email);
    try {
        $sql->execute();
    } catch (PDOException $exception) {
        echo "Erreur lors de la récupération de l'utilisateur $email";
    }
    
    $rep = $sql->fetch();

    if (!$rep) {
        header("Location: ./index.php?erreur_connexion=client non existant");
    } else {
        $mdp_hash = password_hash($mdp, PASSWORD_DEFAULT);
        if (password_verify($mdp, $rep['mot_de_passe'])) {
            $_SESSION['est_connecte'] = "1";
            $_SESSION['nom'] = $rep['nom'];
            $_SESSION['prenom'] = $rep['prenom'];
            $_SESSION['email'] = $rep['mail'];
            $_SESSION['id_utilisateur'] = $rep['id_utilisateur'];
            
            header("Location: ../index.php");
        } else {
            header("Location: ./index.php?erreur_connexion=erreur de mot de passe");
        }
    }
}
?>
