<?php
// Projet TraceGPS - services web
// fichier : api/services/GetLesUtilisateursQueJautorise.php
// Dernière mise à jour : 05/12/2024 par dP

$dao = new DAO();

// Initialisation de la variable $data
$data = [];

// Récupération des données transmises
$pseudo = (empty($this->request['pseudo'])) ? "" : $this->request['pseudo'];
$mdpSha1 = (empty($this->request['mdp'])) ? "" : $this->request['mdp'];
$lang = (empty($this->request['lang'])) ? "" : $this->request['lang'];

// "xml" par défaut si le paramètre lang est absent ou incorrect
if ($lang != "json") $lang = "xml";

// La méthode HTTP utilisée doit être GET
if ($this->getMethodeRequete() != "GET") {
    $msg = "Erreur : méthode HTTP incorrecte.";
    $code_reponse = 406;
} else {
    // Les paramètres doivent être présents
    if ($pseudo == "" || $mdpSha1 == "") {
        $msg = "Erreur : données incomplètes.";
        $code_reponse = 400;
    } else {
        // Vérification de l'authentification de l'utilisateur
        if ($dao->getNiveauConnexion($pseudo, $mdpSha1) != 1) {
            $msg = "Erreur : authentification incorrecte.";
            $code_reponse = 401;
        } else {
            // Récupération des utilisateurs autorisés
            $utilisateursAutorises = $dao->getLesUtilisateursQueJautorise($pseudo);

            if (empty($utilisateursAutorises)) {
                // Aucun utilisateur autorisé
                $msg = "Aucune autorisation accordée par " . $pseudo . ".";
                $code_reponse = 200;
            } else {
                // Utilisateurs autorisés trouvés
                $msg = count($utilisateursAutorises) . " autorisation(s) accordée(s) par " . $pseudo . ".";
                $data = $utilisateursAutorises;
                $code_reponse = 200;
            }
        }
    }
}

// Ferme la connexion à MySQL
unset($dao);

// Création du flux en sortie
if ($lang == "xml") {
    $content_type = "application/xml; charset=utf-8";
    $donnees = creerFluxXML($msg, $data);
} else {
    $content_type = "application/json; charset=utf-8";
    $donnees = creerFluxJSON($msg, $data);
}

// Envoi de la réponse HTTP
$this->envoyerReponse($code_reponse, $content_type, $donnees);
exit;

// ================================================================================================

// Création du flux XML en sortie
function creerFluxXML($msg, $data)
{
    $doc = new DOMDocument();
    $doc->version = '1.0';
    $doc->encoding = 'UTF-8';

    $elt_commentaire = $doc->createComment('Service web GetLesUtilisateursQueJautorise - BTS SIO - Lycée De La Salle - Rennes');
    $doc->appendChild($elt_commentaire);

    $elt_data = $doc->createElement('data');
    $doc->appendChild($elt_data);

    $elt_reponse = $doc->createElement('reponse', $msg);
    $elt_data->appendChild($elt_reponse);

    if (!empty($data)) {
        $elt_donnees = $doc->createElement('donnees');
        $elt_data->appendChild($elt_donnees);

        $elt_lesUtilisateurs = $doc->createElement('lesUtilisateurs');
        $elt_donnees->appendChild($elt_lesUtilisateurs);

        foreach ($data as $utilisateur) {
            $elt_utilisateur = $doc->createElement('utilisateur');
            $elt_lesUtilisateurs->appendChild($elt_utilisateur);

            $elt_utilisateur->appendChild($doc->createElement('id', $utilisateur['id']));
            $elt_utilisateur->appendChild($doc->createElement('pseudo', $utilisateur['pseudo']));
            $elt_utilisateur->appendChild($doc->createElement('adrMail', $utilisateur['adrMail']));
            $elt_utilisateur->appendChild($doc->createElement('numTel', $utilisateur['numTel']));
            $elt_utilisateur->appendChild($doc->createElement('niveau', $utilisateur['niveau']));
            $elt_utilisateur->appendChild($doc->createElement('dateCreation', $utilisateur['dateCreation']));
            $elt_utilisateur->appendChild($doc->createElement('nbTraces', $utilisateur['nbTraces'] ?? 0));

            if (isset($utilisateur['dateDerniereTrace'])) {
                $elt_utilisateur->appendChild($doc->createElement('dateDerniereTrace', $utilisateur['dateDerniereTrace']));
            }
        }
    }

    $doc->formatOutput = true;
    return $doc->saveXML();
}

// ================================================================================================

// Création du flux JSON en sortie
function creerFluxJSON($msg, $data)
{
    $response = ["data" => ["reponse" => $msg]];

    if (!empty($data)) {
        $utilisateurs = [];
        foreach ($data as $utilisateur) {
            $utilisateurs[] = [
                "id" => $utilisateur['id'],
                "pseudo" => $utilisateur['pseudo'],
                "adrMail" => $utilisateur['adrMail'],
                "numTel" => $utilisateur['numTel'],
                "niveau" => $utilisateur['niveau'],
                "dateCreation" => $utilisateur['dateCreation'],
                "nbTraces" => $utilisateur['nbTraces'] ?? 0,
                "dateDerniereTrace" => $utilisateur['dateDerniereTrace'] ?? null
            ];
        }
        $response['data']['donnees'] = ["lesUtilisateurs" => $utilisateurs];
    }

    return json_encode($response, JSON_PRETTY_PRINT);
}
?>
