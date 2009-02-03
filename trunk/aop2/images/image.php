<?php
/***********
image.php : crée une image de case
	paramètres GET :
		c	couleur RRGGBB
		n	nombre de cellules
		h	chateau ? 1/0
		d	décor 0/1/2/3
		m	max atteint ? 1/0

***************/
if (!array_key_exists("type",$_GET))
	$_GET["type"] = "cellule";

$_GET["type"] = "atome";

if ($_GET["type"] == "atome") {
	include("animated/atomes.php");
	die();
}

$couleur = (array_key_exists("c",$_GET)?$_GET["c"]:"0000FF");
$nombre = 0+(array_key_exists("n",$_GET)?$_GET["n"]:2);
$chateau = (array_key_exists("h",$_GET)?$_GET["h"]=="1"||$_GET["h"]=="true":false);
$dernier = (array_key_exists("r",$_GET)?$_GET["r"]=="1"||$_GET["r"]=="true":false);
$decor = 0+(array_key_exists("d",$_GET)?$_GET["d"]:0);
$maxatteint = (array_key_exists("m",$_GET)?$_GET["m"]=="1"||$_GET["m"]=="true":false);
$tailleattome = max(min(0+(array_key_exists("taille",$_GET)?$_GET["taille"]:3),5),1);



$fichierDeBase = "aop.png";
$rouge = hexdec(substr($couleur,0,2));
$vert = hexdec(substr($couleur,2,2));
$bleu = hexdec(substr($couleur,4,2));

$imB = imagecreatefrompng($fichierDeBase);

$imR = imagecreatetruecolor ( 33 , 33 );

//on cherche les coordonnées 
$src_x=0;
$src_y=0;

imagecopy ( $imR , $imB , 0 , 0 , $src_x , $src_y , 33 , 33 );
$blanc= imagecolorallocate ($imR, 255, 255, 255);
$violet= imagecolorallocate ($imR, 255, 0, 255);

imagecolorset  ( $imR  , $violet  , $rouge, $vert, $bleu);

	imagecolortransparent();
	
	header('Content-type: image/png');
	imagepng($imR);
	imagedestroy($imR);
?>