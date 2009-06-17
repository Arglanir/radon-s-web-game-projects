<?php
/***********
status.php : cr�e une image montrant l'interrogation et le jeu
	param�tres GET :
		c	couleur RRGGBB
		s	statut 0-occup� � chercher 1-a jou�
		type	type de case (cellule, m�di�val)

***************/

if (!array_key_exists("type",$_GET))
	$_GET["type"] = "cellule";

/*if ($_GET["type"] == "atome") {
	include("animated/atomes.php");
	die();
}*/

$couleur = (array_key_exists("c",$_GET)?$_GET["c"]:"0000FF");
$statut = (array_key_exists("s",$_GET)?$_GET["s"]:0)+0;

if ($statut == 1)
	$fichierDeBase = "exclam.png";
else
	$fichierDeBase = "interro.png";
	
/*
if ($_GET["type"] == "cellule") {
	$fichierDeBase = "aop.png";
} elseif ($_GET["type"] == "cool") {
	$fichierDeBase = "aopcool.png";
} elseif ($_GET["type"] == "aop2") {
	$fichierDeBase = "aop2.png";
	$mettreBord = false;
} else {
	$fichierDeBase = "aopmed.png";
}*/
//d�coupage des couleur
$rouge = hexdec(substr($couleur,0,2));
$vert = hexdec(substr($couleur,2,2));
$bleu = hexdec(substr($couleur,4,2));

$imB = imagecreatefrompng($fichierDeBase);
$imR = imagecreate ( 75 , 75 );
imagecopy ( $imR , $imB , 0 , 0 , 0 , 0 , 75 , 75 );
//couleur � remplacer
$violet = imagecolorat($imR,37,72);
//remplacement de la couleur
imagecolorset  ( $imR  , $violet  , $rouge, $vert, $bleu);

header('Content-type: image/png');
imagepng($imR);
imagedestroy($imB);
imagedestroy($imR);
die();


?>