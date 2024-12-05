<?php
// Projet TraceGPS - services web
// fichier : api/services/DemanderUneAutorisation.php
// Dernière mise à jour : 05/12/2024 par dP

// Rôle : Ce service permet à un utilisateur de demander une autorisation à un autre utilisateur.
// Le service web doit recevoir 5 paramètres :
//     pseudo : le pseudo de l'utilisateur qui demande l'autorisation
//     mdp : le mot de passe hashé en sha1 de l'utilisateur qui demande l'autorisation
//     pseudoDestinataire : le pseudo de l'utilisateur à qui on demande l'autorisation
//     texteMessage : le texte d'un message accompagnant la demande
//     nomPrenom : le nom et le prénom du demandeur
//     lang : le langage utilisé pour le flux de données ("xml" ou "json")
// Le service retourne un flux de données XML ou JSON contenant un compte-rendu d'exécution

use classes\Outils;


$dao = new DAO();

// Récupération des données transmises
$pseudo = (empty($this->request['pseudo'])) ? "" : $this->request['pseudo'];
$mdpSha1 = (empty($this->request['mdp'])) ? "" : $this->request['mdp'];
$pseudoDestinataire = (empty($this->request['pseudoDestinataire'])) ? "" : $this->request['pseudoDestinataire'];
$texteMessage = (empty($this->request['texteMessage'])) ? "" : $this->request['texteMessage'];
$nomPrenom = (empty($this->request['nomPrenom'])) ? "" : $this->request['nomPrenom'];
$lang = (empty($this->request['lang'])) ? "" : $this->request['lang'];

// "xml" par défaut si le paramètre lang est absent ou incorrect
if ($lang != "json") $lang = "xml";

// La méthode HTTP utilisée doit être GET
if ($this->getMethodeRequete() != "GET") {
    $msg = "Erreur : méthode HTTP incorrecte.";
    $code_reponse = 406;
} else {
    // Vérification de la complétude des données
    if ($pseudo == "" || $mdpSha1 == "" || $pseudoDestinataire == "" || $texteMessage == "" || $nomPrenom == "") {
        $msg = "Erreur : données incomplètes.";
        $code_reponse = 400;
    } else {
        // Vérification de l'authentification de l'utilisateur
        if ($dao->getNiveauConnexion($pseudo, $mdpSha1) != 1) { // Vérification du niveau d'accès
            $msg = "Erreur : authentification incorrecte.";
            $code_reponse = 401;
        } else {
            // Vérification de l'existence du pseudo destinataire
            $utilisateurDestinataire = $dao->getUnUtilisateur($pseudoDestinataire);
            if ($utilisateurDestinataire == null) {
                $msg = "Erreur : pseudo utilisateur inexistant.";
                $code_reponse = 400;
            } else {
                // Envoi du courriel au destinataire
                $adrMail = $utilisateurDestinataire->getAdrMail();
                $sujet = "Demande d'autorisation pour la localisation";
                $contenuMail = "Bonjour " . $pseudoDestinataire . ",\n\n" . $nomPrenom . " vous demande l'autorisation de le localiser.\n\nMessage :\n" . $texteMessage . "\n\nCliquez sur le lien ci-dessous pour accepter ou refuser la demande :\n\n";
                $contenuMail .= "http://localhost/ws-php-xxx/tracegps/api/ValiderDemandeAutorisation?pseudo=" . $pseudo . "&pseudoDestinataire=" . $pseudoDestinataire . "&action=accepter\n";
                $contenuMail .= "http://localhost/ws-php-xxx/tracegps/api/ValiderDemandeAutorisation?pseudo=" . $pseudo . "&pseudoDestinataire=" . $pseudoDestinataire . "&action=refuser";

                // Envoi du mail via la fonction d'envoi
                global $ADR_MAIL_EMETTEUR;
                $ok = Outils::envoyerMail($adrMail, $sujet, $contenuMail, $ADR_MAIL_EMETTEUR);

                if (!$ok) {
                    $msg = "Erreur : l'envoi du courriel de demande d'autorisation a rencontré un problème.";
                    $code_reponse = 500;
                } else {
                    $msg = $pseudoDestinataire . " va recevoir un courriel avec votre demande.";
                    $code_reponse = 200;
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
    $donnees = creerFluxXML($msg);
} else {
    $content_type = "application/json; charset=utf-8";
    $donnees = creerFluxJSON($msg);
}

// Envoi de la réponse HTTP
$this->envoyerReponse($code_reponse, $content_type, $donnees);
exit;

// ================================================================================================

// Création du flux XML en sortie
/**
 * @throws DOMException
 */
function creerFluxXML($msg)
{
    $doc = new DOMDocument();
    $doc->version = '1.0';
    $doc->encoding = 'UTF-8';

    $elt_commentaire = $doc->createComment('Service web DemanderUneAutorisation - BTS SIO - Lycée De La Salle - Rennes');
    $doc->appendChild($elt_commentaire);

    $elt_data = $doc->createElement('data');
    $doc->appendChild($elt_data);

    $elt_reponse = $doc->createElement('reponse', $msg);
    $elt_data->appendChild($elt_reponse);

    $doc->formatOutput = true;

    return $doc->saveXML();
}

// ================================================================================================

// Création du flux JSON en sortie
function creerFluxJSON($msg)
{
    $response = ["data" => ["reponse" => $msg]];
    return json_encode($response, JSON_PRETTY_PRINT);
}

// ================================================================================================
?>
