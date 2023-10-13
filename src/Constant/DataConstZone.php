<?php

namespace App\Constant;

class DataConstZone
{

    public const CARACT_TAB= [
        ["name"=> "jardin", "displayName"=> "Jardin"],
        ["name"=> "parc", "displayName"=> "Parc"],
        ["name"=> "taillis", "displayName"=> "Taillis"],
        ["name"=> "bosquet", "displayName"=> "Bosquet"],
        ["name"=> "other", "displayName"=> "Autre, précisez :"]
    ];

    public const HOUPPIER_TAB = [
        ["name"=> "etale", "displayName"=> "Etalé"],
        ["name"=> "erige", "displayName"=> "Erigé"]
    ];

    public const ETAT_GENERAL_TAB= [
        ["name"=> "sain", "displayName"=> "Sain"],
        ["name"=> "regressif", "displayName"=> "Régressif"],
        ["name"=> "mort", "displayName"=> "Mort"],
        ["name"=> "exam-comple", "displayName"=> "Examen complémentaire requis"],
        ["name"=> "champignons-lignivores", "displayName"=> "Champignons lignivore, précisez :",],
        ["name"=> "parasite", "displayName"=> "Parasites et nuisibles :"],
        ["name"=> "comment", "displayName"=> "Commentaires :"]
    ];

    public const DOMAINE_TAB =[
        ["name"=> "espace-bublic", "displayName"=> "Espace public"],
        ["name"=> "propriete-privee", "displayName"=> "Propriété privée"]
    ];

    public const TRAVAUX_TAB =[
        ["name"=> "abattage-simple", "displayName"=> "Abattage simple"],
        ["name"=> "abattage-en-vue-de-remplacement", "displayName"=> "Abattage en vue de remplacement"],
        ["name"=> "aucun-travaux", "displayName"=> "Aucun travaux"],
        ["name"=> "nettoyage-sanitaire", "displayName"=> "Nettoyage sanitaire"],
        ["name"=> "mise-en-securite-des-branches-a-risque", "displayName"=> "Mise en sécurité des branches à risque"],
        ["name"=> "taille-de-cohabitation", "displayName"=> "Taille de cohabitation"],
        ["name"=> "preservation-de-niche-ecologique", "displayName"=> "Préservation de niche écologique"],
        ["name"=> "traitement-des-parasites-nuisibles", "displayName"=> "Traitement des parasites et nuisibles"],
        ["name"=> "examen-complementaire", "displayName"=> "Examen complémentaire"],
        ["name"=> "other", "displayName" => "Commentaires: ",],
        ["name"=> "soin-particulier-precisez", "displayName"=> "Soin particulier, précisez :"],
        ["name"=> "protection-particuliere-precisez", "displayName"=> "Protection particulière, précisez :"]
    ];
}