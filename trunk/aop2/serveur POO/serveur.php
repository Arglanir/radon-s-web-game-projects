<?php

include_once ("classes.php");

$p = (array_key_exists("p",$_GET)?$_GET["p"]:"000001");
$joueurAppelant = (array_key_exists("j",$_GET)?$_GET["j"]:"1");
$action = (array_key_exists("a",$_GET)?$_GET["a"]:"g");

header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
define ("XMLHeader", "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>");

$fichierPartie = "xaop".$p."bacteries.par";

$partie = Partie::ouvrirXML($fichierPartie);
var_dump($partie);
echo "<br/><textarea cols=70>";
$partie->enregistrerXML();
echo "</textarea>";




?>