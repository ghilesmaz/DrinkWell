<?php
    session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DrinkWell - Recette</title>
    <link rel="stylesheet" href="styles.css"> 
    <?php
        include "../fonctions/fonctions.php";
        $db = connexion_database();
    ?>
</head>
<body>
    <header>
        <div class="container">
            <nav class="nav">
                <a href="../index.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">Accueil</a>
                <a href="../Cocktails/index.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && isset($_GET['nomCocktail'])) ? 'active' : ''; ?>">Cocktails</a>
                <a href="../mes_recettes_preferes/index.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && !isset($_GET['nomCocktail'])) ? 'active' : ''; ?>">Mes recettes préférées</a>
            </nav>
            <div class="header-right">
                <?php 
                    if (isset($_SESSION['est_connecte']) && $_SESSION['est_connecte']=="1") {
                        echo '<a href="../deconnexion/deconnexion.php" class="btn btn-logout">Déconnexion</a>';
                    } else {
                        echo '<a href="../connexion/index.php" class="btn btn-login">Connexion</a>
                              <a href="../creer_compte/index.php" class="btn btn-register">S\'inscrire</a>';
                    }
                ?>
            </div>
        </div>
    </header>
    <div class="container">
        <?php
            if (isset($_GET['nomCocktail']) && isset($_GET['path'])) {
                if ($_GET['nomCocktail'] != '' && $_GET['path'] != '') {
                    $nom_cocktail = htmlspecialchars($_GET['nomCocktail']);
                    $path = htmlspecialchars($_GET['path']);
                    $sql = $db->prepare("SELECT * FROM COCKTAIL WHERE titre LIKE :nom_cocktail");
                    $sql->bindParam(":nom_cocktail", $nom_cocktail);
                    try {
                        $sql->execute();
                    } catch (PDOException $exception) {
                        echo "Erreur lors de la récupération du cocktail $nom_cocktail";
                    }
                    $cocktail = $sql->fetch();
                    $nom = $cocktail['titre'];
                    $preparation = $cocktail['preparation'];
                    $ingredients = "";
                    foreach (explode('|', $cocktail['ingredients']) as $ingredient) {
                        $ingredients .= "<li>$ingredient</li>";
                    }

                    echo "
                    <div class=\"cocktail-details\">
                        <div class=\"card\">
                            <h2>Ingrédients</h2>
                            <ul>$ingredients</ul>
                        </div>

                        <div class=\"cocktail-image\">
                            <img src=\"$path\" alt=\"$nom\">
                        </div>

                        <div class=\"card\">
                            <h2>Préparation</h2>
                            <p>$preparation</p>
                        </div>
                    </div>
                    ";
                }
            } else {
                header("Location: ../index.php?erreur=cocktail a afficher absent");
            }
            
        ?>
    </div>
</body>
</html>
