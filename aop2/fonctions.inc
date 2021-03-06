<?php
/*
Fichier: fonction.inc
Date: 27/01/2009
Auteur: Mika�l Mayer / C�dric Mayer
But: Fonctions communes � plusieurs fichiers
*/


$fichier_parties = "lespartiesencours.xml";

define ("mdpadminmd5", md5("meuhmeuh"));
define ("serveur_fichier","serveur.php");
define ("md5mdpIA",md5("jesuisuneiatresbelle"));
define ("XMLHeader", "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");

$color_array = 
array(
"Blanc" => "FEFEFE",
"Gris" => "A0A0A0",
"Bleu" => "4040FF",
"Bleu clair" => "8080FF",
"Cyan" => "00FFFF",
"Emeraude" => "00FF80",
"Vert" => "00FF00",
"Vert kaki" => "80FF00",
"Vert clair" => "80FF80",
"Jaune" => "FFFF00",
"Orange" => "FF8000",
"Rouge"  => "FF0000",
"Violet" => "FF00FF",
"Violet clair" => "FF80FF",
"Rose saumon" => "FF8080",
"Autre..." => ".....");

function entre($avant,$puis,$ensuite){//renvoie vrai si ils sont dans l'ordre
	return ($avant<=$puis) && ($puis<=$ensuite);
}

function mettreEntre($nombre,$base){//fonction permettant le modulo, met le nombre entre 0 et base-1 compris
	while($nombre < 0)
	$nombre+=$base;
	while($nombre >= $base)
	$nombre-=$base;
	return $nombre;
}

//fonctions de recherche dans un entier
function case2chateau($entier){//regarde si sur une case il y a un chateau
	return ($entier>=10000);
}
function case2cellules($entier){//retourne le nombre de cellules dans la case
	return ($entier-(floor($entier/100)*100));
}
function case2joueur($entier){//retourne le n� d'un joueur � qui est la case, m�me d�sert�e
	/*if (case2cellules($entier) == 0)
		return 0; //renvoie 0 si aucune cellule*/
	return floor(($entier-(case2chateau($entier)?10000:0))/100);
}

function distN0($a,$b,$multiplicateur=0.1){return max(abs($a),abs($b))+$multiplicateur*min(abs($b),abs($b));}

//renvoie une chaine contenant l'URL du client pour le joueur
function getUrlJoueur($numero_partie, $numero_joueur, $si_mot_de_passe=false, $mot_de_passe="") {
	$url = "index.php?comp=client&j=".$numero_joueur."&p=".$numero_partie;
	If($si_mot_de_passe) {
		$url = $url."&pw=".$mot_de_passe;
	}
	return $url;
}

function getNomFichier($i,$isxml=true) {//renvoie un nom de fichier
	return ($isxml?"x":"")."aop".$i."bacteries.par";
}

function getNumeroPartie() {//cr�e un indentifiant unique de partie
	$i = 0;
	$nomFichier = "";
	do {
		$i = rand(1000000, 9999999);
		$nomFichier = getNomFichier($i);
	} while(file_exists($nomFichier));
	return $i;
}


//fonctions de r�ponse XML
function envoyerReponse($reponse_corps_xml){
	header('Content-Type: text/xml');
	echo XMLHeader;
	echo "<reponse>\n".utf8_encode($reponse_corps_xml)."\n</reponse>";die();
}
function lancerErreur($raison_erreur,$ce_qui_etait_tente=false){
	envoyerReponse('<erreur raison="'.htmlentities(addslashes($raison_erreur)).'" origine="'.htmlentities(addslashes($ce_qui_etait_tente?$ce_qui_etait_tente:"")).'" />');
}



//fonction HTML generator
function addSelectOption($arrayOptions,$affichage=true) {
	$text = "";
	$idname = "";
	$options = "";
	$callback = "";
	$default = -1;
	$default_index = -1;
	$table = False;
	$color = False;
	$sortie_ecran = "";
	$saut_ligne = true;
	foreach($arrayOptions as $key => $value) {
		switch($key) {
		case "text": $text = $value; break;
		case "idname": $idname = $value; break;
		case "options": $options = $value; break;//tableau
		case "callback": $callback = $value; break;//fonction
		case "default": $default = $value; break;
		case "default_index": $default_index = $value; break;
		case "color": $color = $value; break;//booleen
		case "table": $table = $value; break;//booleen
		case "saut_ligne": $saut_ligne = $value; break;//booleen
		}
	}
	if($table) $sortie_ecran .=  "<tr><td style=\"text-align:right;\">";
	/*if($table) $sortie_ecran .= '<label style="text-align: right;" >'.$text.' : </label>';
	else $sortie_ecran .= '<label>'.$text.' : </label>';*/  $sortie_ecran .= ''.$text.' : ';
	if($table) $sortie_ecran .=  "</td><td>";
	$sortie_ecran .=  ($saut_ligne?"\n":"").'<select name="'.$idname.'" id="'.$idname.'" onChange="'.$callback.'">'." ";
	$i = 1;
	foreach($options as $value => $text) {
		if(isset($value)) {
			$sortie_ecran .= ($saut_ligne?"\n":"")."  <option";
			if(isset($value)) $sortie_ecran .= ' value="'.$text.'"';
			if($color) $sortie_ecran .= ' style="background-color:#'.$text.'"';
			if($value === $default or $text === $default or $i == $default_index) $sortie_ecran .= ' selected';
			$sortie_ecran .= ">".$value."</option> ";
		} else {
			$sortie_ecran .= ($saut_ligne?"\n":"")."  <option>".$text."</option> ";
		}
		$i += 1;
	}
	$sortie_ecran .= '</select>'.($saut_ligne?"\n":"");
	if($table) $sortie_ecran .= "</td></tr>";
	else $sortie_ecran .= '';
	if ($affichage)
		echo $sortie_ecran;
	else
		return $sortie_ecran;
}

function addCheckOption($arrayOptions,$affichage=true) {
	$text = "";
	$idname = "";
	$callback = "";
	$default = True;
	$sortie_ecran = "";
	foreach($arrayOptions as $key => $value) {
		switch($key) {
		case "text": $text = $value; break;
		case "idname": $idname = $value; break;
		case "callback": $callback = $value; break;
		case "default": $default = $value; break;
		}
	}
	$sortie_ecran .= $text.' : <input type=checkbox name="'.$idname.'" id="'.$idname.'" onChange="'.$callback.'"';
	if($default) {
		$sortie_ecran .= ' checked';
	}
	$sortie_ecran .= ' />'." ";
	if ($affichage)
		echo $sortie_ecran;
	else
		return $sortie_ecran;
}
?>