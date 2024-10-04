<?php
// Importation du fichier `Outils.php`
namespace classes;
include_once('Outils.php');

// Assurez-vous que le `namespace` correspond à celui de `Outils`

class Utilisateur extends Outils
{
    // Les propriétés privées de la classe Utilisateur
    private $id;
    private $pseudo;
    private $mdpSha1;
    private $adrMail;
    private $numTel;
    private $niveau;
    private $dateCreation;
    private $nbTraces;
    private $dateDerniereTrace;

    // Constructeur
    public function __construct($unId, $unPseudo, $unMdpSha1, $uneAdrMail, $unNumTel, $unNiveau, $uneDateCreation, $unNbTraces, $uneDateDerniereTrace)
    {
        $this->id = $unId;
        $this->pseudo = $unPseudo;
        $this->mdpSha1 = $unMdpSha1;
        $this->adrMail = $uneAdrMail;

        // Formate le numéro de téléphone avec la méthode statique d'Outils
        $this->numTel = parent::corrigerTelephone($unNumTel);

        $this->niveau = $unNiveau;
        $this->dateCreation = $uneDateCreation;
        $this->nbTraces = $unNbTraces;
        $this->dateDerniereTrace = $uneDateDerniereTrace;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }

    public function getPseudo()
    {
        return $this->pseudo;
    }

    public function getMdpSha1()
    {
        return $this->mdpSha1;
    }

    public function getAdrMail()
    {
        return $this->adrMail;
    }

    public function getNumTel()
    {
        return $this->numTel;
    }

    public function getNiveau()
    {
        return $this->niveau;
    }

    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    public function getNbTraces()
    {
        return $this->nbTraces;
    }

    public function getDateDerniereTrace()
    {
        return $this->dateDerniereTrace;
    }

    // Setters
    public function setId($unId)
    {
        $this->id = $unId;
    }

    public function setPseudo($unPseudo)
    {
        $this->pseudo = $unPseudo;
    }

    public function setMdpSha1($unMdpSha1)
    {
        $this->mdpSha1 = $unMdpSha1;
    }

    public function setAdrMail($uneAdrMail)
    {
        $this->adrMail = $uneAdrMail;
    }

    public function setNumTel($unNumTel)
    {
        // Formate le numéro de téléphone avec la méthode statique d'Outils
        $this->numTel = parent::corrigerTelephone($unNumTel);
    }

    public function setNiveau($unNiveau)
    {
        $this->niveau = $unNiveau;
    }

    public function setDateCreation($uneDateCreation)
    {
        $this->dateCreation = $uneDateCreation;
    }

    public function setNbTraces($unNbTraces)
    {
        $this->nbTraces = $unNbTraces;
    }

    public function setDateDerniereTrace($uneDateDerniereTrace)
    {
        $this->dateDerniereTrace = $uneDateDerniereTrace;
    }


    //MÉTHODES D'INSTANCES//

    public function toString(): string
    {
        $msg = 'id : ' . $this->id . '<br>';
        $msg .= 'pseudo : ' . $this->pseudo . '<br>';
        $msg .= 'mdpSha1 : ' . $this->mdpSha1 . '<br>';
        $msg .= 'adrMail : ' . $this->adrMail . '<br>';
        $msg .= 'numTel : ' . $this->numTel . '<br>';
        $msg .= 'niveau : ' . $this->niveau . '<br>';
        $msg .= 'dateCreation : ' . $this->dateCreation . '<br>';
        $msg .= 'nbTraces : ' . $this->nbTraces . '<br>';
        $msg .= 'dateDerniereTrace : ' . $this->dateDerniereTrace . '<br>';
        return $msg;
    }
}
