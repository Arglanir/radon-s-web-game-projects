<?php
require("config.inc");

/* fonction pratiques et répétitives */
function numero() {
	$randval = mt_rand(1000000,9999999);
	return $randval;
}

function cherche_numero_valide($table,$colonne) {
	$q = mysql_query("SELECT MAX($colonne) FROM $table;");
	if (!$q) return false;
	$m = @mysql_result($q,0);
	if (!$m) return 1;
	$m = 1 + $m;
	return $m;
}

/* fonction pour rappatrier la pièce du plateau standard */
function octet($str,$i,$j) {return ord($str[8*$j-9+$i]);}
function estpiece($n) {return ($n <> ord(" ")); }
function couleur($n) {return (($n <= ord ('9'))?1:0);}
function typepiece($n) {return ($n - (couleur($n) ? ord('1') : ord('a')) + 1);}

function crypte($str) {
	return $str;
}

function seconnecte() {
	global $NOM_DB, $MYSQL_SERVER, $MYSQL_USER,$MYSQL_PSWD;
	$link = mysql_connect ($MYSQL_SERVER,$MYSQL_USER,$MYSQL_PSWD) or die ("Connexion impossible : ".mysql_error());
	if (!mysql_select_db($NOM_DB)) die ("Base de données perdue...");
}
?>