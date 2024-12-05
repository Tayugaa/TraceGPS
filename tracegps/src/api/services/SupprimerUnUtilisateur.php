<?php
// Projet TraceGPS - services web
// fichier : api/services/SupprimerUnParcours.php
// Dernière mise à jour : 05/12/2024 par dP

// Rôle : ce service permet à un utilisateur de supprimer un de ses parcours.
// Le service web doit recevoir 4 paramètres :
//     pseudo : le pseudo de l'utilisateur
//     mdp : le mot de passe hashé en sha1
//     idTrace : l'identifiant de la trace à supprimer
//     lang : le langage du flux de données retourné ("xml" ou "json")
// Le service retourne un flux de données XML ou JSON contenant un compte-rendu d'exécution.


$dao = new DAO();

// Récupération des données transmises
$pseudo = (empty($this->request['pseudo'])) ? "" : $this->request['pseudo'];
$mdpSha1 = (empty($this->request['mdp'])) ? "" : $this->request['mdp'];
$idTrace = (empty($this->request['idTrace'])) ? "" : $this->request['idTrace'];
$lang = (empty($this->request['lang'])) ? "xml" : $this->request['lang'];

// Vérification des données transmises
if ($pseudo == "" || $mdpSha1 == "" || $idTrace == "") {
    $msg = "Erreur : données incomplètes.";
    $code_reponse = 400;
} elseif ($dao->getNiveauConnexion($pseudo, $mdpSha1) == 0) {
    $msg = "Erreur : authentification incorrecte.";
    $code_reponse = 401;
} elseif (!$dao->getUneTrace($idTrace)) {
    $msg = "Erreur : parcours inexistant.";
    $code_reponse = 404;
} elseif (!$dao->estProprietaireDeTrace($pseudo, $idTrace)) {
    $msg = "Erreur : vous n'êtes pas le propriétaire de ce parcours.";
    $code_reponse = 403;
} else {
    // Tentative de suppression du parcours
    if ($dao->supprimerUneTrace($idTrace)) {
        $msg = "Parcours supprimé.";
        $code_reponse = 200;
    } else {
        $msg = "Erreur : problème lors de la suppression du parcours.";
        $code_reponse = 500;
    }
}

// Déconnexion
unset($dao);

// Formatage de la réponse
if ($lang == "json") {
    $content_type = "application/json; charset=utf-8";
    $donnees = json_encode(["data" => ["reponse" => $msg]], JSON_PRETTY_PRINT);
} else {
    $content_type = "application/xml; charset=utf-8";
    $donnees = creerFluxXML($msg);
}

// Envoi de la réponse HTTP
$this->envoyerReponse($code_reponse, $content_type, $donnees);
exit;

/**
 * Fonction de création de flux XML.
 */
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
?>
