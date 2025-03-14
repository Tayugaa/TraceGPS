<?php
// Projet TraceGPS
// fichier : modele/PointDeTrace.test.php
// Rôle : test de la classe PointDeTrace.php
// Dernière mise à jour : 18/7/2021 par dPlanchet

// inclusion de la classe PointDeTrace
use classes\PointDeTrace;

include_once ('../classes/PointDeTrace.php');
?>
<!DOCTYPE html>
<html lang="">
<head>
	<meta charset="utf-8">
	<title>Test de la classe PointDeTrace</title>
	<style type="text/css">body {font-family: Arial, Helvetica, sans-serif; font-size: small;}</style>
</head>
<body>

<?php
echo "<h3>Test de la classe PointDeTrace</h3>";

// appel du constructeur et tests des accesseurs (get)
$unIdTrace = 1;
$unID = 1;
$uneLatitude = 48.5;
$uneLongitude = -1.6;
$uneAltitude = 50;
$uneDateHeure = date('Y-m-d H:i:s', time());		// l'heure courante
$unRythmeCardio = 80;
$unTempsCumule = 00;
$uneDistanceCumulee = 0;
$uneVitesse = 0;
$unPoint1 = new PointDeTrace($unIdTrace, $unID, $uneLatitude, $uneLongitude, $uneAltitude, $uneDateHeure, $unRythmeCardio, $unTempsCumule, $uneDistanceCumulee, $uneVitesse);

echo "<h4>objet unPoint1 : </h4>";
/**
 * @param PointDeTrace $unPoint1
 * @return void
 */
function extracted(PointDeTrace $unPoint1)
{
    echo("IdTrace : " . $unPoint1->getIdTrace() . "<br>");
    echo("Id : " . $unPoint1->getId() . "<br>");
    echo("latitude : " . $unPoint1->getLatitude() . "<br>");
    echo("longitude : " . $unPoint1->getLongitude() . "<br>");
    echo("altitude : " . $unPoint1->getAltitude() . "<br>");
    echo("Heure de passage : " . $unPoint1->getDateHeure() . "<br>");
    echo("Rythme cardiaque : " . $unPoint1->getRythmeCardio() . "<br>");
    echo("Temps cumule (s) : " . $unPoint1->getTempsCumule() . "<br>");
    echo("Temps cumule (hh:mm:ss) : " . $unPoint1->getTempsCumuleEnChaine() . "<br>");
    echo("Distance cumulée (Km) : " . $unPoint1->getDistanceCumulee() . "<br>");
    echo("Vitesse (Km/h) : " . $unPoint1->getVitesse() . "<br>");
    echo('<br>');
}

extracted($unPoint1);

// tests des mutateurs (set)
$unPoint2 = new PointDeTrace(0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
$unPoint2->setIdTrace(1);
$unPoint2->setId(2);
$unPoint2->setLatitude(48.51);
$unPoint2->setLongitude(-1.6);
$unPoint2->setAltitude(200);
$unPoint2->setDateHeure(date('Y-m-d H:i:s', time() + 220));		// l'heure courante + 220 sec
$unPoint2->setRythmeCardio(100);
$unPoint2->setTempsCumule(220);
$unPoint2->setDistanceCumulee(1.111);
$unPoint2->setVitesse(18.196);

echo "<h4>objet unPoint2 : </h4>";
extracted($unPoint2);

// test de la méthode toString
echo "<h4>méthode toString sur objet unPoint2 : </h4>";
echo ($unPoint2->toString());
echo ('<br>');


?>

</body>
</html>