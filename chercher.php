<?php
session_start();
include '../fonctions/fonctions.php';
$db = connexion_database();

if (isset($_POST['searchArea'])) {
    $searchArea = htmlspecialchars($_POST['searchArea']);
    $searchTerm = '%' . $searchArea . '%';

    // Filtrage des cocktails en fonction de ce qu'on cherche
    $sql = $db->prepare("SELECT * FROM COCKTAIL WHERE titre LIKE :search");
    $sql->bindParam(':search', $searchTerm);
} else {
    $sql = $db->prepare("SELECT * FROM COCKTAIL");
}

try {
    $sql->execute();
    $res = $sql->fetchAll();

    if ($res) {
        foreach ($res as $key) {
            $nom_cocktail = $key['titre'];
            $name_img = get_name($nom_cocktail);
            $path = "../Donnees/Photos/$name_img.jpg";
            if (!file_exists($path)) {
                $path = "../Donnees/Photos/cock.png";
            }

            // Affichage des cocktails
            echo "
            <div class=\"card col-12 col-lg-3 m-2 d-flex justify-content-center\">
                <a href=\"../recette/index.php?nomCocktail=$nom_cocktail&path=$path\" class=\"text-decoration-none\">
                    <img src=\"$path\" class=\"card-img-top rounded mx-auto d-block h-75\" alt=\"$nom_cocktail\">
                    <div class=\"card-body\">
                        <h5 class=\"card-text text-center text-black\">$nom_cocktail</h5>
                    </div>
                </a>
                <form action=\"#\" method=\"post\">
                    <input type=\"hidden\" name=\"id_cocktail\" value=\"{$key['id_cocktail']}\" />
                    <button type=\"submit\" name=\"Ajouter\" class=\"btn btn-outline-primary\">Ajouter aux préférées</button>
                </form>
            </div>
            ";
        }
    } else {
        echo "<p>Aucun cocktail trouvé.</p>";
    }
} catch (PDOException $exception) {
    echo "Erreur lors de la récupération des cocktails.";
}

// Gestion de l'ajout aux préférées
if (isset($_POST['Ajouter'])) {
    if (isset($_SESSION['est_connecte']) && $_SESSION['est_connecte'] == "1") {
        $id_cocktail = htmlspecialchars($_POST['id_cocktail']);
        $mail2 = $_SESSION['email'];

        $sql2 = $db->prepare("SELECT * FROM UTILISATEUR WHERE mail = :mail");
        $sql2->bindParam(':mail', $mail2);
        try {
            $sql2->execute();
            $res2 = $sql2->fetch();
            $id_utilisateur = $res2['id_utilisateur'];

            $sql3 = $db->prepare("SELECT * FROM COCKTAIL WHERE id_cocktail = :id_cocktail");
            $sql3->bindParam(':id_cocktail', $id_cocktail);
            $sql3->execute();
            $res3 = $sql3->fetch();
            $nom_cocktail_panier = $res3['titre'];

            $sql4 = $db->prepare("INSERT INTO PANIER (id_utilisateur, nom_cocktail) VALUES (:id_utilisateur, :nom_cocktail)");
            $sql4->bindParam(':id_utilisateur', $id_utilisateur);
            $sql4->bindParam(':nom_cocktail', $nom_cocktail_panier);
            if (!$sql4->execute()) {
                echo "Erreur lors de l'ajout du cocktail aux préférées.";
            }
        } catch (PDOException $exception) {
            echo "Erreur lors de l'ajout du cocktail aux préférées.";
        }
    } else {
        echo "Veuillez vous connecter pour ajouter aux préférées.";
    }
}
?>
