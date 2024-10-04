<?php
namespace classes;

use Exception;

class Point
{
    protected $latitude;
    protected $longitude;
    protected $altitude;

    //Getters et Setters//
    public function __construct($latitude, $longitude, $altitude){
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->altitude = $altitude;
    }

    //Latitude
    public function getLatitude(){
        return $this->latitude;
    }
    public function setLatitude($unelatitude)
    {
        $this->latitude = $unelatitude;
    }

    //Longitude
    public function getLongitude()
    {
        return $this->longitude;
    }
    public function setLongitude($uneLongitude)
    {
        $this->longitude = $uneLongitude;
    }

    //Altitude
    public function getAltitude()
    {
        return $this->altitude;
    }
    public function setAltitude($uneAltitude)
    {
        $this->altitude = $uneAltitude;
    }


    //MÉTHODES D'INSTANCES//

    public function toString(): string
    {
        $msg = "latitude : " . $this->latitude . "<br>";
        $msg .= "longitude : " . $this->longitude . "<br>";
        $msg .= "altitude : " . $this->altitude . "<br>";
        return $msg;
    }


    //MÉTHODES STATIQUES//
    private static function getDistanceBetween($latitude1, $longitude1, $latitude2, $longitude2)
    {
        // Si les deux points sont identiques, la distance est de 0
        if (abs($latitude1 - $latitude2) < 0.000001 && abs($longitude1 - $longitude2) < 0.000001) return 0;

        try {
            $earthRadiusKm = 6371.0; // Rayon moyen de la Terre en kilomètres

            // Conversion des degrés en radians
            $lat1Rad = deg2rad($latitude1);
            $lat2Rad = deg2rad($latitude2);
            $deltaLatRad = deg2rad($latitude2 - $latitude1);
            $deltaLonRad = deg2rad($longitude2 - $longitude1);

            // Formule de Haversine
            $a = sin($deltaLatRad / 2) * sin($deltaLatRad / 2) +
                cos($lat1Rad) * cos($lat2Rad) *
                sin($deltaLonRad / 2) * sin($deltaLonRad / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

            // Distance en kilomètres
            return $earthRadiusKm * $c;
        } catch (Exception $ex) {
            return 0; // Retourne 0 en cas d'erreur
        }
    }

// Méthode statique publique
// calcule la distance (en Km) entre 2 points géographiques passés en paramètres :
// point1 : le premier point
// point2 : le second point
// fournit : la distance (en Km) entre les 2 points
    public static function getDistance(Point $point1, Point $point2)
    {
        // Extraire les coordonnées des deux points
        $latitude1 = $point1->getLatitude();
        $longitude1 = $point1->getLongitude();
        $latitude2 = $point2->getLatitude();
        $longitude2 = $point2->getLongitude();

        // Appeler la méthode privée pour calculer la distance
        return self::getDistanceBetween($latitude1, $longitude1, $latitude2, $longitude2);
    }



}