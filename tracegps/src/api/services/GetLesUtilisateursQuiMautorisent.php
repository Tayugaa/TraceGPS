<?php
// Projet TraceGPS - services web
// fichier : api/services/GetLesUtilisateursQuiMautorisent.php
// Dernière mise à jour : 12/12/2024 par dP

// Rôle : Ce service permet à un utilisateur d'obtenir la liste des utilisateurs qui l'autorisent à consulter leurs parcours.
// Le service web doit recevoir 3 paramètres :
//     pseudo : le pseudo de l'utilisateur
//     mdp : le mot de passe de l'utilisateur hashé en sha1
//     lang : le langage du flux de données retourné ("xml" ou "json") ; "xml" par défaut si le paramètre est absent ou incorrect
// Le service retourne un flux de données XML ou JSON contenant un compte-rendu d'exécution

// Connexion à la base de données
$dao = new DAO();

// Récupération des paramètres depuis la requête HTTP
$pseudo = (empty($this->request['pseudo'])) ? "" : $this->request['pseudo'];
$mdpSha1 = (empty($this->request['mdp'])) ? "" : $this->request['mdp'];
$lang = (empty($this->request['lang'])) ? "xml" : $this->request['lang'];

// Vérification des paramètres
if ($pseudo == "" || $mdpSha1 == "") {
    $msg = "Erreur : données incomplètes.";
    $code_reponse = 400;
    $lesUtilisateurs = [];
} else {
    // Authentification de l'utilisateur
    $niveauConnexion = $dao->getNiveauConnexion($pseudo, $mdpSha1);

    if ($niveauConnexion == 0) {
        $msg = "Erreur : authentification incorrecte.";
        $code_reponse = 401;
        $lesUtilisateurs = [];
    } else {
        // Récupération des utilisateurs qui autorisent cet utilisateur
        $utilisateur = $dao -> getUnUtilisateur($pseudo);
        $IdUtilisateur = $utilisateur -> getId();
        $lesUtilisateurs = $dao->getLesUtilisateursAutorisant($IdUtilisateur);

        // Assurez-vous que $lesUtilisateurs est un tableau
        if ($lesUtilisateurs === null) {
            $lesUtilisateurs = [];
        }

        $nb = count($lesUtilisateurs);
        if ($nb == 0) {
            $msg = "Aucune autorisation accordée à $pseudo.";
            $code_reponse = 200;
        } else {
            $msg = "$nb autorisation(s) accordée(s) à $pseudo.";
            $code_reponse = 200;
        }
    }
}

// Génération de la réponse dans le format demandé
if ($lang == "xml") {
    $content_type = "application/xml; charset=utf-8";
    $donnees = creerFluxXML($msg, $lesUtilisateurs);
} else {
    $content_type = "application/json; charset=utf-8";
    $donnees = creerFluxJSON($msg, $lesUtilisateurs);
}

// Envoi de la réponse HTTP
$this->envoyerReponse($code_reponse, $content_type, $donnees);

// Fermeture de la connexion
unset($dao);

// Fonction pour créer un flux XML
function creerFluxXML($msg, $lesUtilisateurs) {
    $doc = new DOMDocument();
    $doc->version = '1.0';
    $doc->encoding = 'UTF-8';

    $elt_data = $doc->createElement('data');
    $doc->appendChild($elt_data);

    $elt_reponse = $doc->createElement('reponse', $msg);
    $elt_data->appendChild($elt_reponse);

    if (count($lesUtilisateurs) > 0) {
        $elt_donnees = $doc->createElement('donnees');
        $elt_data->appendChild($elt_donnees);

        $elt_lesUtilisateurs = $doc->createElement('lesUtilisateurs');
        $elt_donnees->appendChild($elt_lesUtilisateurs);

        foreach ($lesUtilisateurs as $unUtilisateur) {
            $elt_utilisateur = $doc->createElement('utilisateur');
            $elt_lesUtilisateurs->appendChild($elt_utilisateur);

            $elt_utilisateur->appendChild($doc->createElement('id', $unUtilisateur->getId()));
            $elt_utilisateur->appendChild($doc->createElement('pseudo', $unUtilisateur->getPseudo()));
            $elt_utilisateur->appendChild($doc->createElement('adrMail', $unUtilisateur->getAdrMail()));
            $elt_utilisateur->appendChild($doc->createElement('numTel', $unUtilisateur->getNumTel()));
            $elt_utilisateur->appendChild($doc->createElement('niveau', $unUtilisateur->getNiveau()));
            $elt_utilisateur->appendChild($doc->createElement('dateCreation', $unUtilisateur->getDateCreation()));
            $elt_utilisateur->appendChild($doc->createElement('nbTraces', $unUtilisateur->getNbTraces()));

            if ($unUtilisateur->getNbTraces() > 0) {
                $elt_utilisateur->appendChild($doc->createElement('dateDerniereTrace', $unUtilisateur->getDateDerniereTrace()));
            }
        }
    }

    $doc->formatOutput = true;
    return $doc->saveXML();
}

// Fonction pour créer un flux JSON
function creerFluxJSON($msg, $lesUtilisateurs) {
    if (count($lesUtilisateurs) == 0) {
        $elt_data = ["reponse" => $msg];
    } else {
        $lesObjetsDuTableau = array();
        foreach ($lesUtilisateurs as $unUtilisateur) {
            $lesObjetsDuTableau[] = [
                "id" => $unUtilisateur->getId(),
                "pseudo" => $unUtilisateur->getPseudo(),
                "adrMail" => $unUtilisateur->getAdrMail(),
                "numTel" => $unUtilisateur->getNumTel(),
                "niveau" => $unUtilisateur->getNiveau(),
                "dateCreation" => $unUtilisateur->getDateCreation(),
                "nbTraces" => $unUtilisateur->getNbTraces(),
                "dateDerniereTrace" => $unUtilisateur->getDateDerniereTrace()
            ];
        }
        $elt_data = ["reponse" => $msg, "donnees" => ["lesUtilisateurs" => $lesObjetsDuTableau]];
    }
    return json_encode(["data" => $elt_data], JSON_PRETTY_PRINT);
}
?>