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
use classes\PointDeTrace;
use classes\Trace;

include_once ('../classes/DAO.php');
$dao = new DAO();


// test de la méthode getToutesLesTraces ----------------------------------------------------------
// modifié par dP le 14/8/2021
echo "<h3>Test de getToutesLesTraces : </h3>";
$lesTraces = $dao->getToutesLesTraces();
$nbReponses = sizeof($lesTraces);
echo "<p>Nombre de traces : " . $nbReponses . "</p>";
// affichage des traces
foreach ($lesTraces as $uneTrace)
{ echo ($uneTrace->toString());
    echo ('<br>');
}


// test de la méthode creerUnPointDeTrace ---------------------------------------------------------
// modifié par dP le 13/8/2021
echo "<h3>Test de creerUnPointDeTrace : </h3>";

// on affiche d'abord le nombre de points (5) de la trace 1
$lesPoints = $dao->getLesPointsDeTrace(1);
$nbPoints = sizeof($lesPoints);
echo "<p>Nombre de points de la trace 1 : " . $nbPoints . "</p>";

// on crée un sixième point et on l'ajoute à la trace 1
$unIdTrace = 1;
$unID = 6;
$uneLatitude = 48.20;
$uneLongitude = -1.55;
$uneAltitude = 50;
$uneDateHeure = date('Y-m-d H:i:s', time());
$unRythmeCardio = 80;
$unTempsCumule = 0;
$uneDistanceCumulee = 0;
$uneVitesse = 15;

$unPoint = new PointDeTrace($unIdTrace, $unID, $uneLatitude, $uneLongitude, $uneAltitude, $uneDateHeure,
    $unRythmeCardio, $unTempsCumule, $uneDistanceCumulee, $uneVitesse);
$ok = $dao->creerUnPointDeTrace($unPoint);

// on affiche à nouveau le nombre de points (6) de la trace 1
$lesPoints = $dao->getLesPointsDeTrace(1);
$nbPoints = sizeof($lesPoints);

// Suppression du point ajouté
$txt_req = "DELETE FROM tracegps_points WHERE idTrace = :idTrace AND id = :id";
$req = $dao->getConnexion()->prepare($txt_req);
$req->bindValue(":idTrace", $unIdTrace, PDO::PARAM_INT);
$req->bindValue(":id", $unID, PDO::PARAM_INT);
$ok = $req->execute();

if ($ok) {
    echo "<p>Le point ajouté a été supprimé avec succès.</p>";
} else {
    echo "<p>Échec lors de la suppression du point ajouté.</p>";
}

echo "<p>Nombre de points de la trace 1 : " . $nbPoints . "</p>";
echo ('<br>');


// test de la méthode creerUneTrace ----------------------------------------------------------
// modifié par dP le 14/8/2021
echo "<h3>Test de creerUneTrace : </h3>";
$trace1 = new Trace(0, "2017-12-18 14:00:00", "2017-12-18 14:10:00", true, 3);
$ok = $dao->creerUneTrace($trace1);
if ($ok) {
    echo "<p>Trace bien enregistrée !</p>";
    echo $trace1->toString();
}
else {
    echo "<p>Echec lors de l'enregistrement de la trace !</p>";
}




$trace2 = new Trace(0, date('Y-m-d H:i:s', time()), null, false, 3);
$ok = $dao->creerUneTrace($trace2);
if ($ok) {
echo "<p>Trace bien enregistrée !</p>";
    echo $trace2->toString();
}
else {
    echo "<p>Echec lors de l'enregistrement de la trace !</p>";
}


// ferme la connexion à MySQL :
unset($dao);
?>

</body>
</html>