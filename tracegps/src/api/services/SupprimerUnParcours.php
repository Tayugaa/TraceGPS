<?php
// Projet TraceGPS - services web
// fichier : api/services/SupprimerUnParcours.php
// Dernière mise à jour : 05/12/2024 par dP

$dao = new DAO();

// Récupération des données transmises via GET
$pseudo = isset($_GET['pseudo']) ? $_GET['pseudo'] : "";
$mdpSha1 = isset($_GET['mdp']) ? $_GET['mdp'] : "";
$idTrace = isset($_GET['idTrace']) ? $_GET['idTrace'] : "";
$lang = isset($_GET['lang']) ? $_GET['lang'] : "";

// Si le paramètre lang est absent ou incorrect, "xml" est utilisé par défaut
if ($lang != "json" && $lang != "xml") {
    $lang = "xml";
}

// Vérification de la méthode HTTP
if ($_SERVER['REQUEST_METHOD'] != "GET") {
    envoyerReponse("Erreur : données incomplètes.", 406, $lang);
}

if (empty($pseudo) || empty($mdpSha1) || empty($idTrace)) {
    envoyerReponse("Erreur : données incomplètes.", 400, $lang);
}

$niveauConnexion = $dao->getNiveauConnexion($pseudo, $mdpSha1);
if ($niveauConnexion == 0) {
    envoyerReponse("Erreur : authentification incorrecte.", 401, $lang);
}

$trace = $dao->getUneTrace($idTrace);
if (!$trace) {
    envoyerReponse("Erreur : parcours supprimé.", 404, $lang);
}

$utilisateur = $dao->getUnUtilisateur($pseudo);
if ($trace->getIdUtilisateur() != $utilisateur->getId()) {
    envoyerReponse("Erreur : vous n'êtes pas le propriétaire de ce parcours.", 403, $lang);
}

// Suppression du parcours
$dao->supprimerUneTrace($idTrace);
envoyerReponse("Parcours supprimé avec succès.", 200, $lang);

unset($dao);

function envoyerReponse($msg, $code_reponse, $lang)
{
    $content_type = ($lang == "xml") ? "application/xml; charset=utf-8" : "application/json; charset=utf-8";
    header("Content-Type: $content_type");
    http_response_code($code_reponse);

    if ($lang == "xml") {
        echo creerFluxXML($msg);
    } else {
        echo creerFluxJSON($msg);
    }
    exit;
}

function creerFluxXML($msg)
{
    $doc = new DOMDocument();
    $doc->version = '1.0';
    $doc->encoding = 'UTF-8';

    $elt_commentaire = $doc->createComment('Service web SupprimerUnParcours - BTS SIO - Lycée De La Salle - Rennes');
    $doc->appendChild($elt_commentaire);

    $elt_data = $doc->createElement('data');
    $doc->appendChild($elt_data);

    $elt_reponse = $doc->createElement('reponse', $msg);
    $elt_data->appendChild($elt_reponse);

    $doc->formatOutput = true;

    return $doc->saveXML();
}

function creerFluxJSON($msg)
{
    $elt_data = ["reponse" => $msg];
    $elt_racine = ["data" => $elt_data];
    return json_encode($elt_racine, JSON_PRETTY_PRINT);
}
?>
