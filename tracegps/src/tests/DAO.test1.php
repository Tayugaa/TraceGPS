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
<html lang="fr">
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




echo "<h3>Test de getLesTraces(idUtilisateur) : </h3>";
$lesTraces = $dao->getLesTraces(2);
$nbReponses = sizeof($lesTraces);
echo "<p>Nombre de traces de l'utilisateur 2 : " . $nbReponses . "</p>";
// affichage des traces
foreach ($lesTraces as $uneTrace)
{ echo ($uneTrace->toString());
    echo ('<br>');
}






// test de la méthode supprimerUneTrace -----------------------------------------------------------
// modifié par dP le 15/8/2021
echo "<h3>Test de supprimerUneTrace : </h3>";
$ok = $dao->supprimerUneTrace(20);
if ($ok) {
    echo "<p>Trace bien supprimée !</p>";
}
else {
    echo "<p>Echec lors de la suppression de la trace !</p>";
}


// test de la méthode getLesPointsDeTrace ---------------------------------------------------------
// modifié par dP le 13/8/2021
echo "<h3>Test de getLesPointsDeTrace : </h3>";
$lesPoints = $dao->getLesPointsDeTrace(1);
$nbPoints = sizeof($lesPoints);
echo "<p>Nombre de points de la trace 1 : " . $nbPoints . "</p>";
// affichage des points
foreach ($lesPoints as $unPoint)
{ echo ($unPoint->toString());
    echo ('<br>');
}

// test de la méthode getUneTrace -----------------------------------------------------------------
// modifié par dP le 14/8/2021
echo "<h3>Test de getUneTrace : </h3>";
$uneTrace = $dao->getUneTrace(2);
if ($uneTrace) {
    echo "<p>La trace 2 existe : <br>" . $uneTrace->toString() . "</p>";
}
else {
    echo "<p>La trace 2 n'existe pas !</p>";
}
$uneTrace = $dao->getUneTrace(100);
if ($uneTrace) {
    echo "<p>La trace 100 existe : <br>" . $uneTrace->toString() . "</p>";
}
else {
    echo "<p>La trace 100 n'existe pas !</p>";
}

// test des méthodes creerUnPointDeTrace et terminerUneTrace --------------------------------------
// modifié par dP le 15/8/2021
echo "<h3>Test de terminerUneTrace : </h3>";
// on choisit une trace non terminée
$unIdTrace = 3;
// on l'affiche
$laTrace = $dao->getUneTrace($unIdTrace);
echo "<h4>l'objet laTrace avant l'appel de la méthode terminerUneTrace : </h4>";
echo ($laTrace->toString());
echo ('<br>');
// on la termine
$dao->terminerUneTrace($unIdTrace);
// et on l'affiche à nouveau
$laTrace = $dao->getUneTrace($unIdTrace);
echo "<h4>l'objet laTrace après l'appel de la méthode terminerUneTrace : </h4>";
echo ($laTrace->toString());
echo ('<br>');


// ferme la connexion à MySQL :
unset($dao);
?>

</body>
</html>