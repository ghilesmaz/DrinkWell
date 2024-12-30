<?php
include('Donnees.inc.php');

$user = "root";
$password = "";
$server = "mysql:host=localhost;charset=utf8";

try {
    $db = new PDO($server, $user, $password);
    echo "Connexion réussie au serveur MySQL.<br>";

    $base = "DrinkWell";
    $sql_create_db = "CREATE DATABASE IF NOT EXISTS $base";
    $db->exec($sql_create_db);
    echo "Base de données '$base' créée ou déjà existante.<br>";

    $db->exec("USE $base");

    $sql_create_tables = "
    CREATE TABLE IF NOT EXISTS UTILISATEUR (
        id_utilisateur INT AUTO_INCREMENT,
        nom VARCHAR(100),
        prenom VARCHAR(100),
        username VARCHAR(100),
        mail VARCHAR(100) UNIQUE NOT NULL,
        mot_de_passe VARCHAR(100) NOT NULL,
        sexe VARCHAR(25),
        date_naissance DATE,
        adresse VARCHAR(255),
        code_postal VARCHAR(5),
        ville VARCHAR(100),
        tel VARCHAR(10),
        PRIMARY KEY(id_utilisateur)
    );

    CREATE TABLE IF NOT EXISTS PANIER (
        id_panier INT AUTO_INCREMENT,
        id_utilisateur INT,
        nom_cocktail VARCHAR(255),
        PRIMARY KEY(id_panier),
        FOREIGN KEY (id_utilisateur) REFERENCES UTILISATEUR(id_utilisateur) ON DELETE CASCADE,
        FOREIGN KEY (nom_cocktail) REFERENCES COCKTAIL(titre)
    );

    CREATE TABLE IF NOT EXISTS COCKTAIL (
        id_cocktail INT AUTO_INCREMENT PRIMARY KEY,
        titre VARCHAR(100) NOT NULL UNIQUE,
        ingredients TEXT NOT NULL,
        preparation TEXT NOT NULL
    );

    CREATE TABLE IF NOT EXISTS ALIMENT (
        nom_aliment VARCHAR(100) NOT NULL PRIMARY KEY
    );

    CREATE TABLE IF NOT EXISTS LIAISON (
        nom_cocktail VARCHAR(100),
        nom_aliment VARCHAR(100),
        PRIMARY KEY (nom_cocktail, nom_aliment),
        FOREIGN KEY (nom_cocktail) REFERENCES COCKTAIL(titre),
        FOREIGN KEY (nom_aliment) REFERENCES ALIMENT(nom_aliment)
    );

    CREATE TABLE IF NOT EXISTS HIERARCHIE_ALIMENT (
        nom_aliment VARCHAR(100),
        nom_parent VARCHAR(100),
        PRIMARY KEY (nom_aliment, nom_parent),
        FOREIGN KEY (nom_aliment) REFERENCES ALIMENT(nom_aliment),
        FOREIGN KEY (nom_parent) REFERENCES ALIMENT(nom_aliment)
    );";

    $db->exec($sql_create_tables);
    echo "Tables créées avec succès.<br>";

    $db->beginTransaction();

    foreach ($Hierarchie as $aliment => $details) {
        try {
            // Insertion de l'aliment
            $sql_aliment = $db->prepare("INSERT IGNORE INTO ALIMENT (nom_aliment) VALUES (:nom_aliment)");
            $sql_aliment->bindParam(':nom_aliment', $aliment);
            $sql_aliment->execute();

            // Insertion des parents dans la hiérarchie
            if (isset($details['super-categorie'])) {
                foreach ($details['super-categorie'] as $parent) {
                    $sql_hierarchie = $db->prepare("INSERT IGNORE INTO HIERARCHIE_ALIMENT (nom_aliment, nom_parent) VALUES (:nom_aliment, :nom_parent)");
                    $sql_hierarchie->bindParam(':nom_aliment', $aliment);
                    $sql_hierarchie->bindParam(':nom_parent', $parent);
                    $sql_hierarchie->execute();
                }
            }

            echo "Aliment '$aliment' et sa hiérarchie insérés avec succès.<br>";

        } catch (PDOException $exception) {
            echo "Erreur lors de l'ajout de l'aliment '$aliment' : " . $exception->getMessage() . "<br>";
            $db->rollBack();
            exit;
        }
    }

    foreach ($Recettes as $cocktail) {
        $titre = $cocktail['titre'];
        $ingredients = $cocktail['ingredients'];
        $preparation = $cocktail['preparation'];

        try {
            $Sql = $db->prepare("INSERT INTO COCKTAIL (titre, ingredients, preparation) VALUES (:titre, :ingredients, :preparation)");
            $Sql->bindParam(':titre', $titre);
            $Sql->bindParam(':ingredients', $ingredients);
            $Sql->bindParam(':preparation', $preparation);
            $Sql->execute();

            foreach ($cocktail['index'] as $aliment) {
                // Insertion de l'aliment
                $Sql_aliment = $db->prepare("INSERT IGNORE INTO ALIMENT (nom_aliment) VALUES (:nom_aliment)");
                $Sql_aliment->bindParam(':nom_aliment', $aliment);
                $Sql_aliment->execute();

                // Liaison entre le cocktail et l'aliment
                $Sql_liaison = $db->prepare("INSERT IGNORE INTO LIAISON (nom_cocktail, nom_aliment) VALUES (:nom_cocktail, :nom_aliment)");
                $Sql_liaison->bindParam(':nom_cocktail', $titre);
                $Sql_liaison->bindParam(':nom_aliment', $aliment);
                $Sql_liaison->execute();
            }

        } catch (PDOException $exception) {
            echo "Erreur lors de l'ajout du cocktail '$titre' : " . $exception->getMessage() . "<br>";
            $db->rollBack();
            exit;
        }
    }

    $db->commit();
    echo "Toutes les insertions ont été effectuées avec succès.<br>";

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "Erreur lors de l'insertion : " . $e->getMessage() . "<br>";
}

$db = null;
?>
