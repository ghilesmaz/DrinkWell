<?php
session_start();

// on inclut le fichier donné, et le fichiers ayant les fonctions nécessaires
include "../Donnees/Donnees.inc.php";
include "../fonctions/fonctions.php";

// pour deboguer
echo "<pre>";
var_dump($_POST); 
echo "</pre>";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['email']) || !isset($_POST['password']) || !isset($_POST['nom']) || !isset($_POST['prenom'])) {
        echo "Erreur dans les valeurs de POST. Assurez-vous que tous les champs sont remplis.";
    } else {
        $db = connexion_database();
        $email = htmlspecialchars($_POST['email']);
        $mdp = htmlspecialchars($_POST['password']);
        $nom = htmlspecialchars($_POST['nom']);
        $prenom = htmlspecialchars($_POST['prenom']);
        echo "Nom : $nom<br>";
        echo "Prénom : $prenom<br>";
        echo "Email : $email<br>";
        echo "Mot de passe : $mdp<br>";

        // une requete pour vérifier si l'email existe déjà dans la base de données
        $sql = $db->prepare("SELECT * FROM UTILISATEUR WHERE mail LIKE :email");
        $sql->bindParam(':email', $email);

        try {
            $sql->execute();
        } catch (PDOException $exception) {
            echo "Erreur lors de la récupération de l'utilisateur $email";
        }
        $rep = $sql->fetch();

        // si l'utilisateur n'existe pas on crée un nouvel utilisateur
        if (!$rep) {
            $password_hash = password_hash($mdp, PASSWORD_DEFAULT);

            // requete d'insertion
            $sql = $db->prepare("INSERT INTO UTILISATEUR(nom, prenom, mail, mot_de_passe) VALUES (:nom, :prenom, :mail, :mot_de_passe)");
            $sql->bindParam(':nom', $nom);
            $sql->bindParam(':prenom', $prenom);
            $sql->bindParam(':mail', $email);
            $sql->bindParam(':mot_de_passe', $password_hash);

            try {
                $res = $sql->execute();
                if (!$res) {
                    echo "Erreur lors de l'insertion de l'utilisateur $email<br>";
                } else {
                    // si la creation de l'utisilateur a reussi, on l'ajoute dans la session
                    $_SESSION['est_connecte'] = "1";
                    $_SESSION['nom'] = $nom;
                    $_SESSION['prenom'] = $prenom;
                    $_SESSION['email'] = $email;

                    // redirection vers la page de connexion après avoir inscrit avec succès
                    echo "Utilisateur créé avec succès. Redirection vers l'accueil.<br>";
                    header('Location: ../connexion/index.php');
                    exit();
                }
            } catch (PDOException $exception) {
                echo "Erreur lors de l'insertion de l'utilisateur $email<br>";
            }
        } else {
            echo "Cet email est déjà utilisé. Veuillez en choisir un autre.<br>";
            header('Location: ../index.php');
            exit();
        }
    }
} else {
    echo "Erreur dans les valeurs de POST";
}
?>
