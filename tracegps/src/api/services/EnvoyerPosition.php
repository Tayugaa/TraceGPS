<?php
// Projet TraceGPS - services web
// fichier : api/services/EnvoyerPosition.php
// Dernière mise à jour : 05/12/2024 par dP

// Rôle : ce service permet à un utilisateur authentifié d'envoyer sa position.

use classes\Outils;

$dao = new DAO();

// Récupération des données transmises
$pseudo = $this->request['pseudo'] ?? "";
$mdp = $this->request['mdp'] ?? "";
$idTrace = $this->request['idTrace'] ?? "";
$dateHeure = $this->request['dateHeure'] ?? "";
$latitude = $this->request['latitude'] ?? "";
$longitude = $this->request['longitude'] ?? "";
$altitude = $this->request['altitude'] ?? "";
$rythmeCardio = $this->request['rythmeCardio'] ?? 0;
$lang = $this->request['lang'] ?? "xml";

// "xml" par défaut si le paramètre lang est absent ou incorrect
if ($lang != "json") $lang = "xml";

// La méthode HTTP utilisée doit être GET
if ($this->getMethodeRequete() != "GET") {
    $msg = "Erreur : méthode HTTP incorrecte.";
    $code_reponse = 406;
} else {
    // Vérification des paramètres
    if (empty($pseudo) || empty($mdp) || empty($idTrace) || empty($dateHeure) || empty($latitude) || empty($longitude) || empty($altitude)) {
        $msg = "Erreur : données incomplètes.";
        $code_reponse = 400;
    } else {
        // Authentification de l'utilisateur
        $utilisateur = $dao->getUnUtilisateur($pseudo);
        if ($utilisateur == null || $utilisateur->getMdp() != $mdp) {
            $msg = "Erreur : authentification incorrecte.";
            $code_reponse = 401;
        } else {
            // Vérification de l'existence et de la propriété de la trace
            $trace = $dao->getUneTrace($idTrace);
            if ($trace == null) {
                $msg = "Erreur : le numéro de trace n'existe pas.";
                $code_reponse = 400;
            } else if ($trace->getUtilisateur()->getPseudo() != $pseudo) {
                $msg = "Erreur : le numéro de trace ne correspond pas à cet utilisateur.";
                $code_reponse = 403;
            } else if ($trace->getTerminee()) {
                $msg = "Erreur : la trace est déjà terminée.";
                $code_reponse = 400;
            } else {
                // Enregistrement du point
                $idPoint = $dao->creerUnPoint($idTrace, $dateHeure, $latitude, $longitude, $altitude, $rythmeCardio);
                if ($idPoint == null) {
                    $msg = "Erreur : problème lors de l'enregistrement du point.";
                    $code_reponse = 500;
                } else {
                    $msg = "Point créé.";
                    $code_reponse = 200;
                    $donnees_retour = ["id" => $idPoint];
                }
            }
        }
    }
}

// Ferme la connexion à MySQL
unset($dao);

// Création du flux en sortie
if ($lang == "xml") {
    $content_type = "application/xml; charset=utf-8";
    $donnees = creerFluxXML($msg, $donnees_retour ?? []);
} else {
    $content_type = "application/json; charset=utf-8";
    $donnees = creerFluxJSON($msg, $donnees_retour ?? []);
}

// Envoi de la réponse HTTP
$this->envoyerReponse($code_reponse, $content_type, $donnees);
exit;

// ================================================================================================

// Création du flux XML en sortie
function creerFluxXML($msg, $donnees)
{
    $doc = new DOMDocument();
    $doc->version = '1.0';
    $doc->encoding = 'UTF-8';
    $elt_commentaire = $doc->createComment('Service web EnvoyerPosition - BTS SIO - Lycée De La Salle - Rennes');
    $doc->appendChild($elt_commentaire);
    $elt_data = $doc->createElement('data');
    $doc->appendChild($elt_data);
    $elt_reponse = $doc->createElement('reponse', $msg);
    $elt_data->appendChild($elt_reponse);
    if (!empty($donnees)) {
        $elt_donnees = $doc->createElement('donnees');
        foreach ($donnees as $key => $value) {
            $elt_donnee = $doc->createElement($key, $value);
            $elt_donnees->appendChild($elt_donnee);
        }
        $elt_data->appendChild($elt_donnees);
    }
    $doc->formatOutput = true;
    return $doc->saveXML();
}

// ================================================================================================

// Création du flux JSON en sortie
function creerFluxJSON($msg, $donnees)
{
    $elt_data = ["reponse" => $msg];
    if (!empty($donnees)) {
        $elt_data["donnees"] = $donnees;
    } else {
        $elt_data["donnees"] = [];
    }
    return json_encode(["data" => $elt_data], JSON_PRETTY_PRINT);
}

// ================================================================================================
?>
