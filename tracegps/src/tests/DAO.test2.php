<?php
// Projet TraceGPS
// fichier : modele/DAO.test1.php
// Rôle : test de la classe DAO.php
// Dernière mise à jour : xxxxxxxxxxxxxxxxx par xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

// Le code des tests restant à développer va être réparti entre les membres de l'équipe de développement.
// Afin de limiter les conflits avec GitHub, il est décidé d'attribuer un fichier de test à chaque développeur.
// Développeur 1 : fichier DAO.test1.php
// Développeur 2 : fichier DAO.test2.php
// Développeur 3 : fichier DAO.test3.php
// Développeur 4 : fichier DAO.test4.php

// Quelques conseils pour le travail collaboratif :
// avant d'attaquer un cycle de développement (début de séance, nouvelle méthode, ...), faites un Pull pour récupérer
// la dernière version du fichier.
// Après avoir testé et validé une méthode, faites un commit et un push pour transmettre cette version aux autres développeurs.
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Test de la classe DAO</title>
    <style type="text/css">body {font-family: Arial, Helvetica, sans-serif; font-size: small;}</style>
</head>
<body>

<?php
// connexion du serveur web à la base MySQL
include_once ('../classes/DAO.php');
$dao = new DAO();


// test de la méthode existeAdrMailUtilisateur ----------------------------------------------------
// modifié par dP le 12/8/2021
echo "<h3>Test de existeAdrMailUtilisateur : </h3>";
if ($dao->existeAdrMailUtilisateur("admin@gmail.com")) $existe = "oui"; else $existe = "non";
echo "<p>Existence de l'utilisateur 'admin@gmail.com' : <b>" . $existe . "</b><br>";
if ($dao->existeAdrMailUtilisateur("delasalle.sio.eleves@gmail.com")) $existe = "oui"; else $existe = "non";
echo "Existence de l'utilisateur 'delasalle.sio.eleves@gmail.com' : <b>" . $existe . "</b></br>";



// test de la méthode getLesUtilisateursAutorisant ------------------------------------------------
// modifié par dP le 13/8/2021
echo "<h3>Test de getLesUtilisateursAutorisant(idUtilisateur) : </h3>";
$lesUtilisateurs = $dao->getLesUtilisateursAutorisant(4);
$nbReponses = sizeof($lesUtilisateurs);
echo "<p>Nombre d'utilisateurs autorisant l'utilisateur 4 à voir leurs parcours : " . $nbReponses . "</p>";
// affichage des utilisateurs
foreach ($lesUtilisateurs as $unUtilisateur)
{ echo ($unUtilisateur->toString());
    echo ('<br>');
}



// test de la méthode getLesUtilisateursAutorises -------------------------------------------------
// modifié par dP le 13/8/2021
echo "<h3>Test de getLesUtilisateursAutorises(idUtilisateur) : </h3>";
$lesUtilisateurs = $dao->getLesUtilisateursAutorises(2);
$nbReponses = sizeof($lesUtilisateurs);
echo "<p>Nombre d'utilisateurs autorisés par l'utilisateur 2 : " . $nbReponses . "</p>";
// affichage des utilisateurs
foreach ($lesUtilisateurs as $unUtilisateur)
{ echo ($unUtilisateur->toString());
    echo ('<br>');
}



// test de la méthode getLesTracesAutorisees($idUtilisateur) --------------------------------------
// modifié par dP le 14/8/2021
echo "<h3>Test de getLesTracesAutorisees(idUtilisateur) : </h3>";
$lesTraces = $dao->getLesTracesAutorisees(2);
$nbReponses = sizeof($lesTraces);
echo "<p>Nombre de traces autorisées à l'utilisateur 2 : " . $nbReponses . "</p>";
// affichage des traces
foreach ($lesTraces as $uneTrace)
{ echo ($uneTrace->toString());
    echo ('<br>');
}
$lesTraces = $dao->getLesTracesAutorisees(3);
$nbReponses = sizeof($lesTraces);
echo "<p>Nombre de traces autorisées à l'utilisateur 3 : " . $nbReponses . "</p>";
// affichage des traces
foreach ($lesTraces as $uneTrace)
{ echo ($uneTrace->toString());
    echo ('<br>');
}
// ferme la connexion à MySQL :
unset($dao);
?>

</body>
</html>