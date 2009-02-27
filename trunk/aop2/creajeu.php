<?php
/************************
Créateur de jeu appelé par un client
*************************/
include_once ("newjeux.php");

$jeu = new CreaJeu();
if (array_key_exists("enxml",$_GET))
	if ($_GET["enxml"]!="0"){
		$jeu->affichageInfosXML();
		die();
	}
$jeu->affichageLiensPartie();
//c'est bateau...
?>