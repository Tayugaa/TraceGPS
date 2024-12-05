<?php
// Projet TraceGPS
// fichier : modele/DAO.php   (DAO : Data Access Object)
// Rôle : fournit des méthodes d'accès à la bdd tracegps (projet TraceGPS) au moyen de l'objet PDO
// modifié par dP le 12/8/2021

// liste des méthodes déjà développées (dans l'ordre d'apparition dans le fichier) :

// __construct() : le constructeur crée la connexion $cnx à la base de données
// __destruct() : le destructeur ferme la connexion $cnx à la base de données
// getNiveauConnexion($login, $mdp) : fournit le niveau (0, 1 ou 2) d'un utilisateur identifié par $login et $mdp
// existePseudoUtilisateur($pseudo) : fournit true si le pseudo $pseudo existe dans la table tracegps_utilisateurs, false sinon
// getUnUtilisateur($login) : fournit un objet Utilisateur à partir de $login (son pseudo ou son adresse mail)
// getTousLesUtilisateurs() : fournit la collection de tous les utilisateurs (de niveau 1)
// creerUnUtilisateur($unUtilisateur) : enregistre l'utilisateur $unUtilisateur dans la bdd
// modifierMdpUtilisateur($login, $nouveauMdp) : enregistre le nouveau mot de passe $nouveauMdp de l'utilisateur $login daprès l'avoir hashé en SHA1
// supprimerUnUtilisateur($login) : supprime l'utilisateur $login (son pseudo ou son adresse mail) dans la bdd, ainsi que ses traces et ses autorisations
// envoyerMdp($login, $nouveauMdp) : envoie un mail à l'utilisateur $login avec son nouveau mot de passe $nouveauMdp

// liste des méthodes restant à développer :

// existeAdrMailUtilisateur($adrmail) : fournit true si l'adresse mail $adrMail existe dans la table tracegps_utilisateurs, false sinon
// getLesUtilisateursAutorises($idUtilisateur) : fournit la collection  des utilisateurs (de niveau 1) autorisés à suivre l'utilisateur $idUtilisateur
// getLesUtilisateursAutorisant($idUtilisateur) : fournit la collection  des utilisateurs (de niveau 1) autorisant l'utilisateur $idUtilisateur à voir leurs parcours
// autoriseAConsulter($idAutorisant, $idAutorise) : vérifie que l'utilisateur $idAutorisant) autorise l'utilisateur $idAutorise à consulter ses traces
// creerUneAutorisation($idAutorisant, $idAutorise) : enregistre l'autorisation ($idAutorisant, $idAutorise) dans la bdd
// supprimerUneAutorisation($idAutorisant, $idAutorise) : supprime l'autorisation ($idAutorisant, $idAutorise) dans la bdd
// getLesPointsDeTrace($idTrace) : fournit la collection des points de la trace $idTrace
// getUneTrace($idTrace) : fournit un objet Trace à partir de identifiant $idTrace
// getToutesLesTraces() : fournit la collection de toutes les traces
// getLesTraces($idUtilisateur) : fournit la collection des traces de l'utilisateur $idUtilisateur
// getLesTracesAutorisees($idUtilisateur) : fournit la collection des traces que l'utilisateur $idUtilisateur a le droit de consulter
// creerUneTrace(Trace $uneTrace) : enregistre la trace $uneTrace dans la bdd
// terminerUneTrace($idTrace) : enregistre la fin de la trace d'identifiant $idTrace dans la bdd ainsi que la date de fin
// supprimerUneTrace($idTrace) : supprime la trace d'identifiant $idTrace dans la bdd, ainsi que tous ses points
// creerUnPointDeTrace(PointDeTrace $unPointDeTrace) : enregistre le point $unPointDeTrace dans la bdd


// certaines méthodes nécessitent les classes suivantes :
use classes\Outils;
use classes\PointDeTrace;
use classes\Trace;
use classes\Utilisateur;

include_once ('Utilisateur.php');
include_once ('Trace.php');
include_once ('PointDeTrace.php');
include_once ('Point.php');
include_once ('Outils.php');

// inclusion des paramètres de l'application
include_once ('parametres.php');

// début de la classe DAO (Data Access Object)
class DAO
{
    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------- Membres privés de la classe ---------------------------------------
    // ------------------------------------------------------------------------------------------------------

    private $cnx;

    public function getConnexion(): PDO
    {
        return $this->cnx;
    }
    // la connexion à la base de données

    // ------------------------------------------------------------------------------------------------------
    // ---------------------------------- Constructeur et destructeur ---------------------------------------
    // ------------------------------------------------------------------------------------------------------
    public function __construct() {
        global $PARAM_HOTE, $PARAM_PORT, $PARAM_BDD, $PARAM_USER, $PARAM_PWD;
        try
        {	$this->cnx = new PDO ("mysql:host=" . $PARAM_HOTE . ";port=" . $PARAM_PORT . ";dbname=" . $PARAM_BDD,
            $PARAM_USER,
            $PARAM_PWD);
            return true;
        }
        catch (Exception $ex)
        {	echo ("Echec de la connexion a la base de donnees <br>");
            echo ("Erreur numero : " . $ex->getCode() . "<br />" . "Description : " . $ex->getMessage() . "<br>");
            echo ("PARAM_HOTE = " . $PARAM_HOTE);
            return false;
        }
    }

    public function __destruct() {
        // ferme la connexion à MySQL :
        unset($this->cnx);
    }

    // ------------------------------------------------------------------------------------------------------
    // -------------------------------------- Méthodes d'instances ------------------------------------------
    // ------------------------------------------------------------------------------------------------------

    // fournit le niveau (0, 1 ou 2) d'un utilisateur identifié par $pseudo et $mdpSha1
    // cette fonction renvoie un entier :
    //     0 : authentification incorrecte
    //     1 : authentification correcte d'un utilisateur (pratiquant ou personne autorisée)
    //     2 : authentification correcte d'un administrateur
    // modifié par dP le 11/1/2018
    public function getNiveauConnexion($pseudo, $mdpSha1) {
        // préparation de la requête de recherche
        $txt_req = "Select niveau from tracegps_utilisateurs";
        $txt_req .= " where pseudo = :pseudo";
        $txt_req .= " and mdpSha1 = :mdpSha1";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et de ses paramètres
        $req->bindValue("pseudo", $pseudo, PDO::PARAM_STR);
        $req->bindValue("mdpSha1", $mdpSha1, PDO::PARAM_STR);
        // extraction des données
        $req->execute();
        $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        // traitement de la réponse
        $reponse = 0;
        if ($uneLigne) {
            $reponse = $uneLigne->niveau;
        }
        // libère les ressources du jeu de données
        $req->closeCursor();
        // fourniture de la réponse
        return $reponse;
    }


    // fournit true si le pseudo $pseudo existe dans la table tracegps_utilisateurs, false sinon
    // modifié par dP le 27/12/2017
    public function existePseudoUtilisateur($pseudo): bool
    {
        // préparation de la requête de recherche
        $txt_req = "Select count(*) from tracegps_utilisateurs where pseudo = :pseudo";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et de ses paramètres
        $req->bindValue("pseudo", $pseudo, PDO::PARAM_STR);
        // exécution de la requête
        $req->execute();
        $nbReponses = $req->fetchColumn(0);
        // libère les ressources du jeu de données
        $req->closeCursor();

        // fourniture de la réponse
        if ($nbReponses == 0) {
            return false;
        }
        else {
            return true;
        }
    }


    // fournit un objet Utilisateur à partir de son pseudo $pseudo
    // fournit la valeur null si le pseudo n'existe pas
    // modifié par dP le 9/1/2018
    public function getUnUtilisateur($pseudo) {
        // préparation de la requête de recherche
        $txt_req = "Select id, pseudo, mdpSha1, adrMail, numTel, niveau, dateCreation, nbTraces, dateDerniereTrace";
        $txt_req .= " from tracegps_vue_utilisateurs";
        $txt_req .= " where pseudo = :pseudo";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et de ses paramètres
        $req->bindValue("pseudo", $pseudo, PDO::PARAM_STR);
        // extraction des données
        $req->execute();
        $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        // libère les ressources du jeu de données
        $req->closeCursor();

        // traitement de la réponse
        if ( ! $uneLigne) {
            return null;
        }
        else {
            // création d'un objet Utilisateur
            $unId = $uneLigne->id !== null ? mb_convert_encoding($uneLigne->id, 'UTF-8', 'UTF-8') : "";
            $unPseudo = $uneLigne->pseudo !== null ? mb_convert_encoding($uneLigne->pseudo, 'UTF-8', 'UTF-8') : "";
            $unMdpSha1 = $uneLigne->mdpSha1 !== null ? mb_convert_encoding($uneLigne->mdpSha1, 'UTF-8', 'UTF-8') : "";
            $uneAdrMail = $uneLigne->adrMail !== null ? mb_convert_encoding($uneLigne->adrMail, 'UTF-8', 'UTF-8') : "";
            $unNumTel = $uneLigne->numTel !== null ? mb_convert_encoding($uneLigne->numTel, 'UTF-8', 'UTF-8') : "";
            $unNiveau = $uneLigne->niveau !== null ? mb_convert_encoding($uneLigne->niveau, 'UTF-8', 'UTF-8') : "";
            $uneDateCreation = $uneLigne->dateCreation !== null ? mb_convert_encoding($uneLigne->dateCreation, 'UTF-8', 'UTF-8') : "";
            $unNbTraces = $uneLigne->nbTraces !== null ? mb_convert_encoding($uneLigne->nbTraces, 'UTF-8', 'UTF-8') : "";
            $uneDateDerniereTrace = $uneLigne->dateDerniereTrace !== null ? mb_convert_encoding($uneLigne->dateDerniereTrace, 'UTF-8', 'UTF-8') : "";

            $unUtilisateur = new Utilisateur($unId, $unPseudo, $unMdpSha1, $uneAdrMail, $unNumTel, $unNiveau, $uneDateCreation, $unNbTraces, $uneDateDerniereTrace);
            return $unUtilisateur;
        }
    }


    // fournit la collection  de tous les utilisateurs (de niveau 1)
    // le résultat est fourni sous forme d'une collection d'objets Utilisateur
    // modifié par dP le 27/12/2017
    public function getTousLesUtilisateurs(): array
    {
        // préparation de la requête de recherche
        $txt_req = "Select id, pseudo, mdpSha1, adrMail, numTel, niveau, dateCreation, nbTraces, dateDerniereTrace";
        $txt_req .= " from tracegps_vue_utilisateurs";
        $txt_req .= " where niveau = 1";
        $txt_req .= " order by pseudo";

        $req = $this->cnx->prepare($txt_req);
        // extraction des données
        $req->execute();
        $uneLigne = $req->fetch(PDO::FETCH_OBJ);

        // construction d'une collection d'objets Utilisateur
        $lesUtilisateurs = array();
        // tant qu'une ligne est trouvée :
        while ($uneLigne) {
            // création d'un objet Utilisateur
            $unId = $uneLigne->id !== null ? mb_convert_encoding($uneLigne->id, 'UTF-8', 'UTF-8') : "";
            $unPseudo = $uneLigne->pseudo !== null ? mb_convert_encoding($uneLigne->pseudo, 'UTF-8', 'UTF-8') : "";
            $unMdpSha1 = $uneLigne->mdpSha1 !== null ? mb_convert_encoding($uneLigne->mdpSha1, 'UTF-8', 'UTF-8') : "";
            $uneAdrMail = $uneLigne->adrMail !== null ? mb_convert_encoding($uneLigne->adrMail, 'UTF-8', 'UTF-8') : "";
            $unNumTel = $uneLigne->numTel !== null ? mb_convert_encoding($uneLigne->numTel, 'UTF-8', 'UTF-8') : "";
            $unNiveau = $uneLigne->niveau !== null ? mb_convert_encoding($uneLigne->niveau, 'UTF-8', 'UTF-8') : "";
            $uneDateCreation = $uneLigne->dateCreation !== null ? mb_convert_encoding($uneLigne->dateCreation, 'UTF-8', 'UTF-8') : "";
            $unNbTraces = $uneLigne->nbTraces !== null ? mb_convert_encoding($uneLigne->nbTraces, 'UTF-8', 'UTF-8') : "";
            $uneDateDerniereTrace = $uneLigne->dateDerniereTrace !== null ? mb_convert_encoding($uneLigne->dateDerniereTrace, 'UTF-8', 'UTF-8') : "";

            $unUtilisateur = new Utilisateur($unId, $unPseudo, $unMdpSha1, $uneAdrMail, $unNumTel, $unNiveau, $uneDateCreation, $unNbTraces, $uneDateDerniereTrace);
            // ajout de l'utilisateur à la collection
            $lesUtilisateurs[] = $unUtilisateur;
            // extrait la ligne suivante
            $uneLigne = $req->fetch(PDO::FETCH_OBJ);
        }
        // libère les ressources du jeu de données
        $req->closeCursor();
        // fourniture de la collection
        return $lesUtilisateurs;
    }


    // enregistre l'utilisateur $unUtilisateur dans la bdd
    // fournit true si l'enregistrement s'est bien effectué, false sinon
    // met à jour l'objet $unUtilisateur avec l'id (auto_increment) attribué par le SGBD
    // modifié par dP le 9/1/2018
    public function creerUnUtilisateur($unUtilisateur): bool
    {
        // on teste si l'utilisateur existe déjà
        if ($this->existePseudoUtilisateur($unUtilisateur->getPseudo())) return false;

        // préparation de la requête
        $txt_req1 = "insert into tracegps_utilisateurs (pseudo, mdpSha1, adrMail, numTel, niveau, dateCreation)";
        $txt_req1 .= " values (:pseudo, :mdpSha1, :adrMail, :numTel, :niveau, :dateCreation)";
        $req1 = $this->cnx->prepare($txt_req1);
        // liaison de la requête et de ses paramètres
        $req1->bindValue("pseudo", $unUtilisateur->getPseudo() !== null ? mb_convert_encoding($unUtilisateur->getPseudo(), 'UTF-8', 'UTF-8') : "", PDO::PARAM_STR);
        $req1->bindValue("mdpSha1", $unUtilisateur->getMdpsha1() !== null ? mb_convert_encoding(sha1($unUtilisateur->getMdpsha1()), 'UTF-8', 'UTF-8') : "", PDO::PARAM_STR);
        $req1->bindValue("adrMail", $unUtilisateur->getAdrmail() !== null ? mb_convert_encoding($unUtilisateur->getAdrmail(), 'UTF-8', 'UTF-8') : "", PDO::PARAM_STR);
        $req1->bindValue("numTel", $unUtilisateur->getNumTel() !== null ? mb_convert_encoding($unUtilisateur->getNumTel(), 'UTF-8', 'UTF-8') : "", PDO::PARAM_STR);
        $req1->bindValue("niveau", $unUtilisateur->getNiveau() !== null ? mb_convert_encoding($unUtilisateur->getNiveau(), 'UTF-8', 'UTF-8') : "", PDO::PARAM_INT);
        $req1->bindValue("dateCreation", $unUtilisateur->getDateCreation() !== null ? mb_convert_encoding($unUtilisateur->getDateCreation(), 'UTF-8', 'UTF-8') : "", PDO::PARAM_STR);
        // exécution de la requête
        $ok = $req1->execute();
        // sortir en cas d'échec
        if (!$ok) {
            return false;
        }

        // recherche de l'identifiant (auto_increment) qui a été attribué à la trace
        $unId = $this->cnx->lastInsertId();
        $unUtilisateur->setId($unId);
        return true;
    }


    // enregistre le nouveau mot de passe $nouveauMdp de l'utilisateur $pseudo daprès l'avoir hashé en SHA1
    // fournit true si la modification s'est bien effectuée, false sinon
    // modifié par dP le 9/1/2018
    public function modifierMdpUtilisateur($pseudo, $nouveauMdp): bool
    {
        // préparation de la requête
        $txt_req = "update tracegps_utilisateurs set mdpSha1 = :nouveauMdp";
        $txt_req .= " where pseudo = :pseudo";
        $req = $this->cnx->prepare($txt_req);
        // liaison de la requête et de ses paramètres
        $req->bindValue("nouveauMdp", sha1($nouveauMdp), PDO::PARAM_STR);
        $req->bindValue("pseudo", $pseudo, PDO::PARAM_STR);
        // exécution de la requête
        $ok = $req->execute();
        return $ok;
    }


    // supprime l'utilisateur $pseudo dans la bdd, ainsi que ses traces et ses autorisations
    // fournit true si l'effacement s'est bien effectué, false sinon
    // modifié par dP le 9/1/2018
    public function supprimerUnUtilisateur($pseudo): bool
    {
        $unUtilisateur = $this->getUnUtilisateur($pseudo);
        if ($unUtilisateur == null) {
            return false;
        }
        else {
            $idUtilisateur = $unUtilisateur->getId();

            // suppression des traces de l'utilisateur (et des points correspondants)
            $lesTraces = $this->getLesTraces($idUtilisateur);
            if($lesTraces != null)
            {
                foreach ($lesTraces as $uneTrace) {
                    $this->supprimerUneTrace($uneTrace->getId());
                }
            }
            // préparation de la requête de suppression des autorisations
            $txt_req1 = "delete from tracegps_autorisations" ;
            $txt_req1 .= " where idAutorisant = :idUtilisateur or idAutorise = :idUtilisateur";
            $req1 = $this->cnx->prepare($txt_req1);
            // liaison de la requête et de ses paramètres
            $req1->bindValue("idUtilisateur", utf8_decode($idUtilisateur), PDO::PARAM_INT);
            // exécution de la requête
            $ok = $req1->execute();

            // préparation de la requête de suppression de l'utilisateur
            $txt_req2 = "delete from tracegps_utilisateurs" ;
            $txt_req2 .= " where pseudo = :pseudo";
            $req2 = $this->cnx->prepare($txt_req2);
            // liaison de la requête et de ses paramètres
            $req2->bindValue("pseudo", utf8_decode($pseudo), PDO::PARAM_STR);
            // exécution de la requête
            $ok = $req2->execute();
            return $ok;
        }
    }


    // envoie un mail à l'utilisateur $pseudo avec son nouveau mot de passe $nouveauMdp
    // retourne true si envoi correct, false en cas de problème d'envoi
    // modifié par dP le 9/1/2018
    public function envoyerMdp($pseudo, $nouveauMdp) {
        global $ADR_MAIL_EMETTEUR;
        // si le pseudo n'est pas dans la table tracegps_utilisateurs :
        if ( $this->existePseudoUtilisateur($pseudo) == false ) return false;

        // recherche de l'adresse mail
        $adrMail = $this->getUnUtilisateur($pseudo)->getAdrMail();

        // envoie un mail à l'utilisateur avec son nouveau mot de passe
        $sujet = "Modification de votre mot de passe d'accès au service TraceGPS";
        $message = "Cher(chère) " . $pseudo . "\n\n";
        $message .= "Votre mot de passe d'accès au service service TraceGPS a été modifié.\n\n";
        $message .= "Votre nouveau mot de passe est : " . $nouveauMdp ;
        $ok = Outils::envoyerMail ($adrMail, $sujet, $message, $ADR_MAIL_EMETTEUR);
        return $ok;
    }


    // Le code restant à développer va être réparti entre les membres de l'équipe de développement.
    // Afin de limiter les conflits avec GitHub, il est décidé d'attribuer une zone de ce fichier à chaque développeur.
    // Développeur 1 : lignes 350 à 549
    // Développeur 2 : lignes 550 à 749
    // Développeur 3 : lignes 750 à 949
    // Développeur 4 : lignes 950 à 1150

    // Quelques conseils pour le travail collaboratif :
    // avant d'attaquer un cycle de développement (début de séance, nouvelle méthode, ...), faites un Pull pour récupérer
    // la dernière version du fichier.
    // Après avoir testé et validé une méthode, faites un commit et un push pour transmettre cette version aux autres développeurs.





    // --------------------------------------------------------------------------------------
    // début de la zone attribuée au développeur 1 (alix) : lignes 350 à 549
    // --------------------------------------------------------------------------------------


    // Dans la classe Trace
    public function setId($id) {
        $this->id = $id;
    }


    public function getLesTraces($idUtilisateur): array {
        // préparation de la requête pour récupérer les traces de l'utilisateur
        $txt_req = "SELECT * FROM tracegps_traces WHERE idUtilisateur = :idUtilisateur ORDER BY dateDebut DESC";
        $req = $this->cnx->prepare($txt_req);
        $req->bindValue(":idUtilisateur", $idUtilisateur, PDO::PARAM_INT);
        $req->execute();

        $lesTraces = array();

        // boucle pour chaque trace trouvée
        while ($uneLigne = $req->fetch(PDO::FETCH_OBJ)) {
            $dateHeureFin = property_exists($uneLigne, 'dateHeureFin') ? $uneLigne->dateHeureFin : null;
            $uneTrace = new Trace(
                $uneLigne->id,
                $uneLigne->dateDebut,
                $uneLigne->terminee,
                $dateHeureFin,
                $uneLigne->idUtilisateur
            );

            // récupération et ajout des points pour chaque trace
            $lesPoints = $this->getLesPointsDeTrace($uneLigne->id);
            foreach ($lesPoints as $unPoint) {
                $uneTrace->ajouterPoint($unPoint);
            }

            // ajout de l'objet Trace (avec ses points) dans la collection
            $lesTraces[] = $uneTrace;
        }

        // libération des ressources
        $req->closeCursor();

        // retour de la collection de traces
        return $lesTraces;
    }

    public function supprimerUneTrace($idTrace): bool
    {
        $txt_req1 = "DELETE FROM tracegps_points WHERE idTrace = :idTrace";
        $req1 = $this->cnx->prepare($txt_req1);
        $req1->bindValue(":idTrace", $idTrace, PDO::PARAM_INT);
        $ok= $req1->execute();
        if (!$ok)
        {
            return $ok;
        }

        $txt_req2 = "DELETE FROM tracegps_traces WHERE id = :idTrace";
        $req2 = $this->cnx->prepare($txt_req2);
        $req2->bindValue(":idTrace", $idTrace, PDO::PARAM_INT);
        $req2->execute();
        $ok = $req2->execute();
        return $ok;
    }

    public function getLesPointsDeTrace($idTrace): array
    {
        $lesPoints = array();
        $txtReq = "SELECT * FROM tracegps_points WHERE idTrace = :idTrace ORDER BY id ASC;";
        $req = $this->cnx->prepare($txtReq);
        $req->bindValue(":idTrace", $idTrace, PDO::PARAM_INT);

        // Exécuter la requête
        $req->execute();

        // Boucler sur chaque ligne de résultat
        while ($uneLigne = $req->fetch(PDO::FETCH_ASSOC)) {
            // Créer un nouvel objet PointDeTrace avec les données récupérées
            $unPoint = new PointDeTrace(
                $uneLigne['idTrace'],
                $uneLigne['id'],
                $uneLigne['latitude'],
                $uneLigne['longitude'],
                $uneLigne['altitude'],
                $uneLigne['dateHeure'],
                $uneLigne['rythmeCardio'],
                0,0,0

            );

            // Ajouter l'objet PointDeTrace au tableau
            $lesPoints[] = $unPoint;
        }

        // Libérer les ressources associées au jeu de résultats
        $req->closeCursor();

        // Retourner la collection de points de trace
        return $lesPoints;
    }

    public function getUneTrace($idTrace) {
        // préparation de la requête de recherche pour la trace
        $txt_req = "SELECT * FROM tracegps_traces WHERE id = :idTrace";
        $req = $this->cnx->prepare($txt_req);
        $req->bindValue(":idTrace", $idTrace, PDO::PARAM_INT);
        $req->execute();
        $uneLigne = $req->fetch(PDO::FETCH_OBJ);

        // si aucune trace n'est trouvée, retourner null
        if (!$uneLigne) {
            return null;
        }

        // construction de l'objet Trace
        $dateHeureFin = property_exists($uneLigne, 'dateHeureFin') ? $uneLigne->dateHeureFin : null;
        $uneTrace = new Trace(
            $uneLigne->id,
            $uneLigne->dateDebut,
            $uneLigne->terminee,
            $dateHeureFin,
            $uneLigne->idUtilisateur
        );

        // utilisation de getLesPointsDeTrace pour ajouter les points à l'objet Trace
        $lesPoints = $this->getLesPointsDeTrace($idTrace);
        foreach ($lesPoints as $unPoint) {
            $uneTrace->ajouterPoint($unPoint);
        }

        // libère les ressources du jeu de données
        $req->closeCursor();

        // retourne l'objet Trace avec ses points
        return $uneTrace;
    }



    // --------------------------------------------------------------------------------------
    // début de la zone attribuée au développeur 2 (Steven) : lignes 550 à 749
    // --------------------------------------------------------------------------------------


    // --------------------------------------------------------------------------------------
    // début de la zone attribuée au développeur 3 (Raphael) : lignes 750 à 949
    // --------------------------------------------------------------------------------------

    // --------------------------------------------------------------------------------------
    // début de la zone attribuée au développeur 4 (Alban) : lignes 950 à 1150
    // --------------------------------------------------------------------------------------
    public function getToutesLesTraces() {
        // Préparation de la requête pour récupérer toutes les traces
        $txt_req = "SELECT * FROM tracegps_traces ORDER BY dateDebut DESC";
        $req = $this->cnx->prepare($txt_req);
        $req->execute();

        $lesTraces = [];
        while ($uneLigne = $req->fetch(PDO::FETCH_OBJ)) {
            // Construction de l'objet Trace
            $dateHeureFin = property_exists($uneLigne, 'dateFin') ? $uneLigne->dateFin : null;
            $uneTrace = new Trace(
                $uneLigne->id,
                $uneLigne->dateDebut,
                $dateHeureFin,
                $uneLigne->terminee,
                $uneLigne->idUtilisateur
            );

            // Utilisation de getLesPointsDeTrace pour obtenir les points associés à la trace
            $lesPoints = $this->getLesPointsDeTrace($uneTrace->getId());
            foreach ($lesPoints as $unPoint) {
                $uneTrace->ajouterPoint($unPoint);
            }

            // Ajout de la trace dans la collection
            $lesTraces[] = $uneTrace;
        }

        // Libération des ressources
        $req->closeCursor();

        // Retourne la collection de toutes les traces
        return $lesTraces;
    }

    // Enregistre le point $unPointDeTrace dans la base de données
// Retourne true si l'enregistrement s'est bien passé, false sinon
// Si le point est le premier d'une trace (id = 1), modifie la date de début de la trace avec la date du point
    public function creerUnPointDeTrace($unPointDeTrace): bool
    {
        // Préparation de la requête pour insérer un point
        $txt_req = "INSERT INTO tracegps_points (idTrace, id, latitude, longitude, altitude, dateHeure, rythmeCardio)
                VALUES (:idTrace, :id, :latitude, :longitude, :altitude, :dateHeure, :rythmeCardio)";
        $req = $this->cnx->prepare($txt_req);
        $req->bindValue("idTrace", $unPointDeTrace->getIdTrace(), PDO::PARAM_INT);
        $req->bindValue("id", $unPointDeTrace->getId(), PDO::PARAM_INT);
        $req->bindValue("latitude", $unPointDeTrace->getLatitude(), PDO::PARAM_STR);
        $req->bindValue("longitude", $unPointDeTrace->getLongitude(), PDO::PARAM_STR);
        $req->bindValue("altitude", $unPointDeTrace->getAltitude(), PDO::PARAM_STR);
        $req->bindValue("dateHeure", $unPointDeTrace->getDateHeure(), PDO::PARAM_STR);
        $req->bindValue("rythmeCardio", $unPointDeTrace->getRythmeCardio(), PDO::PARAM_INT);

        $ok = $req->execute();

        // Si le point est le premier (id = 1), mise à jour de la date de début de la trace
        if ($ok && $unPointDeTrace->getId() == 1) {
            $txt_req_update = "UPDATE tracegps_traces
                           SET dateDebut = :dateHeure
                           WHERE id = :idTrace";
            $req_update = $this->cnx->prepare($txt_req_update);
            $req_update->bindValue("dateHeure", $unPointDeTrace->getDateHeure(), PDO::PARAM_STR);
            $req_update->bindValue("idTrace", $unPointDeTrace->getIdTrace(), PDO::PARAM_INT);
            $ok = $req_update->execute();
        }

        $req->closeCursor();
        return $ok;
    }

    // Enregistre la trace $uneTrace dans la table tracegps_traces
// Retourne true si l'enregistrement s'est bien passé, false sinon
    public function creerUneTrace($uneTrace) {
        // Préparation de la requête d'insertion
        $txt_req = "INSERT INTO tracegps_traces (dateDebut, dateFin, terminee, idUtilisateur)
                VALUES (:dateDebut, :dateFin, :terminee, :idUtilisateur)";
        $req = $this->cnx->prepare($txt_req);

        // Liaison des paramètres
        $req->bindValue(":dateDebut", $uneTrace->getDateHeureDebut(), PDO::PARAM_STR);
        if ($uneTrace->getDateHeureFin() === null) {
            $req->bindValue(":dateFin", null, PDO::PARAM_NULL);
        } else {
            $req->bindValue(":dateFin", $uneTrace->getDateHeureFin(), PDO::PARAM_STR);
        }
        $req->bindValue(":terminee", $uneTrace->getTerminee(), PDO::PARAM_BOOL);
        $req->bindValue(":idUtilisateur", $uneTrace->getIdUtilisateur(), PDO::PARAM_INT);

        // Exécution de la requête
        $ok = $req->execute();

        // Si l'insertion réussit, retourner l'ID généré
        if ($ok) {
            $id = $this->cnx->lastInsertId();
            $req->closeCursor();
            return $id; // Retourne l'ID généré
        }

        $req->closeCursor();
        return false; // Retourne false en cas d'échec
    }

    public function autoriseAConsulter($idAutorisant, $idAutorise): bool
    {

        $txt_req = "SELECT COUNT(*) FROM tracegps_autorisations WHERE idAutorisant = :idAutorisant AND idAutorise = :idAutorise";
        $req = $this->cnx->prepare($txt_req);
        $req->execute(['idAutorisant' => $idAutorisant, 'idAutorise' => $idAutorise]);
        $count = $req->fetchColumn();
        return $count > 0;
    }

    public function creerUneAutorisation($idAutorisant, $idAutorise): bool
    {
        try {
            // Vérifier si l'autorisation existe déjà
            $sql = "SELECT COUNT(*) FROM tracegps_autorisations 
                WHERE idAutorisant = :idAutorisant AND idAutorise = :idAutorise";
            $stmt = $this->cnx->prepare($sql);
            $stmt->execute(['idAutorisant' => $idAutorisant, 'idAutorise' => $idAutorise]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                // L'autorisation existe déjà, retournez false
                return false;
            }

            // Insérer une nouvelle autorisation
            $sql = "INSERT INTO tracegps_autorisations (idAutorisant, idAutorise) 
                VALUES (:idAutorisant, :idAutorise)";
            $stmt = $this->cnx->prepare($sql);
            $stmt->execute(['idAutorisant' => $idAutorisant, 'idAutorise' => $idAutorise]);

            return true; // L'insertion a réussi
        } catch (PDOException $e) {
            // Une erreur est survenue, retournez false
            return false;
        }
    }

    public function supprimerUneAutorisation($idAutorisant, $idAutorise): bool
    {
        try {
            // Supprimer l'autorisation correspondante
            $sql = "DELETE FROM tracegps_autorisations 
                WHERE idAutorisant = :idAutorisant AND idAutorise = :idAutorise";
            $stmt = $this->cnx->prepare($sql);
            $stmt->execute(['idAutorisant' => $idAutorisant, 'idAutorise' => $idAutorise]);

            // Vérifier si une ligne a été supprimée
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            // Retourner false en cas d'erreur
            return false;
        }
    }

    public function existeAdrMailUtilisateur($adrMail)
    {
    }

    public function mettreAJourMotDePasse($pseudo, string $mdpHash)
    {
    }

    public function getUtilisateursAutorises($pseudo)
    {
    }

    public function getLesUtilisateursQueJautorise($pseudo)
    {
    }

    public function estProprietaireDeTrace($pseudo, $idTrace)
    {
    }


} // fin de la classe DAO

// ATTENTION : on ne met pas de balise de fin de script pour ne pas prendre le risque
// d'enregistrer d'espaces après la balise de fin de script !!!!!!!!!!!!