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

$fichierDeBase = "aop.png";


$imB = imagecreatefrompng($fichierDeBase);

$imR = imagecreatetruecolor ( 33 , 33 );

//on cherche les coordonnées 
$src_x=0;
$src_y=0;

imagecopy ( $imR , $imB , 0 , 0 , $src_x , $src_y , 33 , 33 );
$blanc= imagecolorallocate ($imR, 255, 255, 255);
$violet= imagecolorallocate ($imR, 255, 0, 255);


	imagecolortransparent();
	
	header('Content-type: image/jpeg');
	imagepng($imR);
	imagedestroy($imR);
?>