<?php
// Projet TraceGPS - services web
// fichier : api/services/DemanderMdp.php
// Dernière mise à jour : 05/12/2024 par dP

// Rôle : ce service permet à un utilisateur de demander un nouveau mot de passe s'il l'a oublié.
// Le service web doit recevoir 2 paramètres :
//     pseudo : le pseudo de l'utilisateur
//     lang : le langage du flux de données retourné ("xml" ou "json") ; "xml" par défaut si le paramètre est absent ou incorrect
// Le service retourne un flux de données XML ou JSON contenant un compte-rendu d'exécution

use classes\Outils;

$dao = new DAO();

// Récupération des données transmises
$pseudo = ( empty($this->request['pseudo'])) ? "" : $this->request['pseudo'];
$lang = ( empty($this->request['lang'])) ? "" : $this->request['lang'];

// "xml" par défaut si le paramètre lang est absent ou incorrect
if ($lang != "json") $lang = "xml";

// La méthode HTTP utilisée doit être GET
if ($this->getMethodeRequete() != "GET") {
    $msg = "Erreur : méthode HTTP incorrecte.";
    $code_reponse = 406;
} else {
    // Les paramètres doivent être présents
    if ($pseudo == "") {
        $msg = "Erreur : données incomplètes.";
        $code_reponse = 400;
    } else {
        // Vérification de l'existence du pseudo
        $unUtilisateur = $dao->getUnUtilisateur($pseudo);
        if ($unUtilisateur == null) {
            $msg = "Erreur : pseudo inexistant.";
            $code_reponse = 400;
        } else {
            // Génération d'un nouveau mot de passe
            $nouveauMdp = Outils::creerMdp();
            $mdpHash = sha1($nouveauMdp);

            // Mise à jour du mot de passe dans la base de données
            $ok = $dao->mettreAJourMotDePasse($pseudo, $mdpHash);
            if (!$ok) {
                $msg = "Erreur : problème lors de l'enregistrement du mot de passe.";
                $code_reponse = 500;
            } else {
                // Envoi du courriel avec le nouveau mot de passe
                $adrMail = $unUtilisateur->getAdrMail();
                $sujet = "Demande de nouveau mot de passe sur TraceGPS";
                $contenuMail = "Bonjour " . $pseudo . "\n\nVotre mot de passe a été réinitialisé. Voici votre nouveau mot de passe : " . $nouveauMdp . "\n\nCordialement,\nL'équipe TraceGPS";

                // Variable globale pour l'email de l'émetteur
                global $ADR_MAIL_EMETTEUR;

                $ok = Outils::envoyerMail($adrMail, $sujet, $contenuMail, $ADR_MAIL_EMETTEUR);
                if (!$ok) {
                    $msg = "Enregistrement effectué ; l'envoi du courriel de confirmation a rencontré un problème.";
                    $code_reponse = 500;
                } else {
                    $msg = "Vous allez recevoir un courriel avec votre nouveau mot de passe.";
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
function creerFluxXML($msg)
{
    $doc = new DOMDocument();
    $doc->version = '1.0';
    $doc->encoding = 'UTF-8';
    $elt_commentaire = $doc->createComment('Service web DemanderMdp - BTS SIO - Lycée De La Salle - Rennes');
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
    $elt_data = ["reponse" => $msg];
    $elt_racine = ["data" => $elt_data];
    return json_encode($elt_racine, JSON_PRETTY_PRINT);
}

// ================================================================================================
?>
