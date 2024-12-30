<?php
// Connexion à la base de données
try {
    $user = "root";
    $password = "";
    $dbName = "drinkwell";  
    $server = "mysql:host=localhost;dbname=$dbName;charset=utf8";  
    $db = new PDO($server, $user, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
    exit;
}

// Fonction pour récupérer les cocktails associés à une catégorie donnée
function getCocktails($categorie) {
    global $db;

    // Récupérer les cocktails associés à une catégorie donnée
    $sql = "
        SELECT c.titre, c.ingredients, c.preparation 
        FROM COCKTAIL c
        INNER JOIN LIAISON l ON c.titre = l.nom_cocktail
        INNER JOIN HIERARCHIE_ALIMENT h ON l.nom_aliment = h.nom_aliment
        WHERE h.nom_parent = :categorie
    ";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':categorie', $categorie);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour récupérer la catégorie parente d'une sous-catégorie
function getParentCategory($sous_categorie) {
    global $db;

    $sql = "SELECT nom_parent FROM HIERARCHIE_ALIMENT WHERE nom_aliment = :sous_categorie";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':sous_categorie', $sous_categorie);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result ? $result['nom_parent'] : null;
}

// Récupérer les sous-catégories en fonction de la catégorie ou sous-catégorie sélectionnée
if (isset($_POST['categorie'])) {
    // Récupérer les sous-catégories pour une catégorie donnée
    $sql = "SELECT nom_aliment FROM HIERARCHIE_ALIMENT WHERE nom_parent = :categorie";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':categorie', $_POST['categorie']);
    $stmt->execute();
    $subCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Afficher les sous-catégories et les cocktails associés à la catégorie
    foreach ($subCategories as $subCategory) {
        echo "<option value='" . $subCategory['nom_aliment'] . "'>" . $subCategory['nom_aliment'] . "</option>";
    }

    // Afficher les cocktails associés à la catégorie principale
    $cocktails = getCocktails($_POST['categorie']);
    if ($cocktails) {
        foreach ($cocktails as $cocktail) {
            echo "<div class='cocktail'>";
            echo "<h3>" . htmlspecialchars($cocktail['titre']) . "</h3>";
            echo "<p><strong>Ingrédients:</strong> " . htmlspecialchars($cocktail['ingredients']) . "</p>";
            echo "<p><strong>Préparation:</strong> " . htmlspecialchars($cocktail['preparation']) . "</p>";
            echo "</div>";
        }
    } else {
        echo "<p>Aucun cocktail disponible pour cette catégorie.</p>";
    }
} elseif (isset($_POST['sous_categorie'])) {
    // Récupérer la catégorie parente de la sous-catégorie
    $parentCategory = getParentCategory($_POST['sous_categorie']);

    // Récupérer les sous-sous-catégories pour une sous-catégorie donnée
    $sql = "SELECT nom_aliment FROM HIERARCHIE_ALIMENT WHERE nom_parent = :sous_categorie";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':sous_categorie', $_POST['sous_categorie']);
    $stmt->execute();
    $subSubCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Afficher les sous-sous-catégories et les cocktails associés à la sous-catégorie
    foreach ($subSubCategories as $subSubCategory) {
        echo "<option value='" . $subSubCategory['nom_aliment'] . "'>" . $subSubCategory['nom_aliment'] . "</option>";
    }

    // Afficher les cocktails associés à la sous-catégorie
    $cocktails = getCocktails($_POST['sous_categorie']);
    if ($cocktails) {
        foreach ($cocktails as $cocktail) {
            echo "<div class='cocktail'>";
            echo "<h3>" . htmlspecialchars($cocktail['titre']) . "</h3>";
            echo "<p><strong>Ingrédients:</strong> " . htmlspecialchars($cocktail['ingredients']) . "</p>";
            echo "<p><strong>Préparation:</strong> " . htmlspecialchars($cocktail['preparation']) . "</p>";
            echo "</div>";
        }
    } else {
        echo "<p>Aucun cocktail disponible pour cette sous-catégorie.</p>";
    }

    // Afficher les cocktails associés à la catégorie parente
    if ($parentCategory) {
        $cocktailsParent = getCocktails($parentCategory);
        if ($cocktailsParent) {
            echo "<h4>Cocktails de la catégorie parente : " . htmlspecialchars($parentCategory) . "</h4>";
            foreach ($cocktailsParent as $cocktail) {
                echo "<div class='cocktail'>";
                echo "<h3>" . htmlspecialchars($cocktail['titre']) . "</h3>";
                echo "<p><strong>Ingrédients:</strong> " . htmlspecialchars($cocktail['ingredients']) . "</p>";
                echo "<p><strong>Préparation:</strong> " . htmlspecialchars($cocktail['preparation']) . "</p>";
                echo "</div>";
            }
        }
    }
} elseif (isset($_POST['subsub_categorie'])) {
    // Récupérer la sous-catégorie parente de la sous-sous-catégorie
    $parentSubCategory = getParentCategory($_POST['subsub_categorie']);
    $parentCategory = getParentCategory($parentSubCategory);

    // Afficher les cocktails associés à la sous-sous-catégorie
    $cocktails = getCocktails($_POST['subsub_categorie']);
    if ($cocktails) {
        foreach ($cocktails as $cocktail) {
            echo "<div class='cocktail'>";
            echo "<h3>" . htmlspecialchars($cocktail['titre']) . "</h3>";
            echo "<p><strong>Ingrédients:</strong> " . htmlspecialchars($cocktail['ingredients']) . "</p>";
            echo "<p><strong>Préparation:</strong> " . htmlspecialchars($cocktail['preparation']) . "</p>";
            echo "</div>";
        }
    } else {
        echo "<p>Aucun cocktail disponible pour cette sous-sous-catégorie.</p>";
    }

    // Afficher les cocktails associés à la sous-catégorie parente
    if ($parentSubCategory) {
        $cocktailsParentSubCategory = getCocktails($parentSubCategory);
        if ($cocktailsParentSubCategory) {
            echo "<h4>Cocktails de la sous-catégorie parente : " . htmlspecialchars($parentSubCategory) . "</h4>";
            foreach ($cocktailsParentSubCategory as $cocktail) {
                echo "<div class='cocktail'>";
                echo "<h3>" . htmlspecialchars($cocktail['titre']) . "</h3>";
                echo "<p><strong>Ingrédients:</strong> " . htmlspecialchars($cocktail['ingredients']) . "</p>";
                echo "<p><strong>Préparation:</strong> " . htmlspecialchars($cocktail['preparation']) . "</p>";
                echo "</div>";
            }
        }
    }

    // Afficher les cocktails associés à la catégorie parente
    if ($parentCategory) {
        $cocktailsParentCategory = getCocktails($parentCategory);
        if ($cocktailsParentCategory) {
            echo "<h4>Cocktails de la catégorie parente : " . htmlspecialchars($parentCategory) . "</h4>";
            foreach ($cocktailsParentCategory as $cocktail) {
                echo "<div class='cocktail'>";
                echo "<h3>" . htmlspecialchars($cocktail['titre']) . "</h3>";
                echo "<p><strong>Ingrédients:</strong> " . htmlspecialchars($cocktail['ingredients']) . "</p>";
                echo "<p><strong>Préparation:</strong> " . htmlspecialchars($cocktail['preparation']) . "</p>";
                echo "</div>";
            }
        }
    }
}
?>
