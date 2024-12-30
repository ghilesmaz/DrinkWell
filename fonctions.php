<?php
    function connexion_database(){
        $user = "root"; 
        $password = ""; 
        $base = "DrinkWell";
        $server = "mysql:host=localhost;dbname=$base;charset=utf8";
        try
        {
            $db = new PDO($server, $user, $password);
        }
        catch (Exception $e)
        {
            die('Erreur : ' . $e->getMessage());
        }
        return $db;
    }

    function get_name($path){
        $path = str_replace(" ", "_", $path);
        $path = str_replace("'", "", $path);

        $path = str_replace("é", "e", $path);
        $path = str_replace("è", "e", $path);
        $path = str_replace("ê", "e", $path);
        $path = str_replace("ë", "e", $path);

        $path = str_replace("à", "a", $path);
        $path = str_replace("ä", "a", $path);
        $path = str_replace("à", "a", $path);
        $path = str_replace("ä", "a", $path);

        $path = str_replace("û", "u", $path);
        $path = str_replace("ü", "u", $path);
        $path = str_replace("ù", "u", $path);

        $path = str_replace("ï", "i", $path);
        $path = str_replace("î", "i", $path);

        $path = str_replace("ç", "c", $path);

        $path = str_replace("ñ", "n", $path);

        $path = str_replace("ô", "o", $path);
        $path = str_replace("ö", "o", $path);

        return $path;
    }


    
?>