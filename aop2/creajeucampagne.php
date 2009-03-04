<?php
	//crée une partie à partir d'une partie existante
/***
Paramètres GET:
	joueur //nom du joueur humain
	couleur //couleur du joueur humain
	c numéro de la campagne
	m numéro de la mission
	n niveau de difficulté
	
Renvoie les liens joueurs
*/
include_once("classes.php");
include_once("newjeux.php");
include_once("fonctions.inc");

if (!array_key_exists("c",$_GET)){lancerErreur("Campagne non renseignee","Recherche des parametres");}
if (!array_key_exists("m",$_GET)){lancerErreur("Mission non renseignee","Recherche des parametres");}
if (!array_key_exists("n",$_GET)){lancerErreur("Niveau non transmis","Recherche des parametres");}
if (!array_key_exists("joueur",$_GET)){lancerErreur("Nom du joueur non transmis","Recherche des parametres");}
if (!array_key_exists("couleur",$_GET)){lancerErreur("Couleur du joueur non transmise","Recherche des parametres");}

$fichierSource = "campagnes/xaop".$_GET["c"].$_GET["m"].".lvl";
if (!file_exists($fichierSource)){lancerErreur("Mission ".$_GET["c"].$_GET["m"]." inconnue","Recherche de la mission");}

$createur = new CreaJeu(false);
$createur->partie = Partie::ouvrirXML($fichierSource);

$createur->partie->joueur[1]->nom = $_GET["joueur"];
$createur->partie->joueur[1]->couleur = $_GET["couleur"];
//régler le niveau de difficulté : à faire

$createur->opt_partie_cachee = true;
$createur->enregistrerPartie();

if (array_key_exists("enxml",$_GET))
	if ($_GET["enxml"]!="0"){
		$createur->affichageInfosXML();
		die();
	}
$createur->affichageLiensPartie();


?>