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

// Récupérer les catégories principales (les parents)
$sql = "SELECT DISTINCT nom_parent FROM HIERARCHIE_ALIMENT";
$stmt = $db->prepare($sql);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Catégories et Sous-Catégories</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
        <div class="return-button-container">
            <a href="../index.php" class="return-button">Retour à l'accueil</a>
        </div>

<h1>Choisissez une catégorie</h1>

<!-- Formulaire pour sélectionner une catégorie -->
<form id="category-form">
    <label for="categorie">Sélectionnez une catégorie:</label>
    <select name="categorie" id="categorie">
        <option value="">-- Choisir une catégorie --</option>
        <?php
        // Affichage des catégories dans une liste déroulante
        foreach ($categories as $category) {
            echo "<option value='" . $category['nom_parent'] . "'>" . $category['nom_parent'] . "</option>";
        }
        ?>
    </select>
</form>

<!-- Section pour afficher les sous-catégories -->
<div id="subcategories-section" style="display:none;">
    <h2>Sous-catégories :</h2>
    <form id="subcategory-form">
        <select name="sous_categorie" id="sous_categorie">
            <option value="">-- Choisir une sous-catégorie --</option>
        </select>
    </form>
</div>

<!-- Section pour afficher les sous-sous-catégories -->
<div id="subsubcategories-section" style="display:none;">
    <h2>Sous-sous-catégories :</h2>
    <select id="subsub_categorie">
        <option value="">-- Choisir une sous-sous-catégorie --</option>
    </select>
</div>

<!-- Section pour afficher les cocktails -->
<div id="cocktails-section" style="display:none;">
    <h2>Cocktails disponibles :</h2>
    <div id="cocktail-list"></div>
</div>

<script>
$(document).ready(function() {
    // Lorsqu'une catégorie est sélectionnée
    $('#categorie').change(function() {
        var category = $(this).val();
        if (category) {
            // Envoie AJAX pour récupérer les sous-catégories
            $.ajax({
                url: 'ajax_request.php',
                type: 'POST',
                data: { categorie: category },
                success: function(response) {
                    // Mettre à jour les sous-catégories dans le menu déroulant
                    $('#sous_categorie').html(response);
                    $('#subcategories-section').show();
                    $('#subsubcategories-section').hide(); // Cacher la sous-sous-catégorie si la catégorie change
                    $('#cocktails-section').hide(); // Cacher les cocktails
                }
            });
        } else {
            $('#subcategories-section').hide();
            $('#subsubcategories-section').hide();
            $('#cocktails-section').hide(); // Cacher les cocktails
        }
    });

    // Lorsqu'une sous-catégorie est sélectionnée
    $('#sous_categorie').change(function() {
        var sousCategory = $(this).val();
        if (sousCategory) {
            // Envoie AJAX pour récupérer les sous-sous-catégories
            $.ajax({
                url: 'ajax_request.php',
                type: 'POST',
                data: { sous_categorie: sousCategory },
                success: function(response) {
                    // Mettre à jour les sous-sous-catégories dans le menu déroulant
                    $('#subsub_categorie').html(response);
                    $('#subsubcategories-section').show();
                    $('#cocktails-section').hide(); // Cacher les cocktails
                }
            });
        } else {
            $('#subsubcategories-section').hide();
            $('#cocktails-section').hide(); // Cacher les cocktails
        }
    });

    // Lorsqu'une sous-sous-catégorie est sélectionnée
    $('#subsub_categorie').change(function() {
        var subSubCategory = $(this).val();
        if (subSubCategory) {
            // Envoie AJAX pour récupérer les cocktails
            $.ajax({
                url: 'ajax_request.php',
                type: 'POST',
                data: { subsub_categorie: subSubCategory },
                success: function(response) {
                    // Afficher les cocktails associés à la sous-sous-catégorie
                    $('#cocktail-list').html(response);
                    $('#cocktails-section').show();
                }
            });
        } else {
            $('#cocktails-section').hide(); // Cacher les cocktails
        }
    });
});
</script>

</body>
</html>
