<?php
/***********
image.php : crée une image de case
	paramètres GET :
		c	couleur RRGGBB
		n	nombre de cellules
		h	chateau ? 1/0
		d	décor 0/1/2/3
		m	max atteint ? 1/0
		type	type de case (cellule, médiéval)

***************/
if (!array_key_exists("type",$_GET))
	$_GET["type"] = "cellule";

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


if ($_GET["type"] == "cellule") {
	$fichierDeBase = "aop.png";
} elseif ($_GET["type"] == "cool") {
	$fichierDeBase = "aopcool.png";
} else {
	$fichierDeBase = "aopmed.png";
}
$rouge = hexdec(substr($couleur,0,2));
$vert = hexdec(substr($couleur,2,2));
$bleu = hexdec(substr($couleur,4,2));

$imB = imagecreatefrompng($fichierDeBase);

$imR = imagecreate ( 33 , 33 );
$imR2 = imagecreate ( 33 , 33 );

//on cherche les coordonnées 

//création du fond
$src_x=34*($decor == 3?2:($decor == 2?1:($decor == 1?3:$decor)))+1;
$src_y=0;
imagecopy ( $imR , $imB , 0 , 0 , $src_x , $src_y , 33 , 33 );
/*
$violet = imagecolorat($imB,32,66);
$tabCouleur = imagecolorsforindex($imB,$violet);
/*	print_r( $tabCouleur );/**/

//création du dessus
if (!$chateau && $nombre>0){//pas de chateau
	$src_x=($nombre>5?34*min($nombre-6,1):($nombre-1)*34);
	$src_y=102+($nombre>5?34:0);
	imagecopy ( $imR2 , $imB , 0 , 0 , $src_x , $src_y , 33 , 33 );
	//$blanc = imagecolorallocate ($imR2, 255, 255, 255);
	$blanc = imagecolorat($imR2,0,0);
	//$violet = imagecolorallocate ($imR2, 255, 0, 255);
	$violet = imagecolorat($imR2,32,32);

	
	imagecolorset  ( $imR2  , $violet  , $rouge, $vert, $bleu);//$rouge, $vert, $bleu);
	
	imagecolortransparent($imR2, $blanc);
} else if ($chateau && $nombre>0) {//chateau
	$src_x=($nombre>5?34*min($nombre-6,4):($nombre-1)*34);
	$src_y=34+($nombre>5?34:0);
	imagecopy ( $imR2 , $imB , 0 , 0 , $src_x , $src_y , 33 , 33 );
	$blanc = imagecolorat($imR2,0,0);
	$violet = imagecolorat($imR2,32,32);

	imagecolorset  ( $imR2  , $violet  , $rouge, $vert, $bleu);
	imagecolortransparent($imR2, $blanc);


	if ($nombre > 10){//ajouter les petites images
		imagecopymerge($imR, $imR2, 0, 0, 0, 0, 33, 33, 100);
		$src_x=($nombre<14?68:($nombre<17?102:136));
		$src_y=136;
		imagecopy ( $imR2 , $imB , 0 , 0 , $src_x , $src_y , 33 , 33 );
		$blanc = imagecolorat($imR2,0,0);
		$violet = imagecolorat($imR2,32,32);

		imagecolorset  ( $imR2  , $violet  , $rouge, $vert, $bleu);
		imagecolortransparent($imR2, $blanc);

	}
}

//fusion
if ($nombre>0)	imagecopymerge($imR, $imR2, 0, 0, 0, 0, 33, 33, 100);

//dessin des bords
$coulbord = array(0,0,0);$tailleimage = 33;
if ($dernier) $coulbord = array($rouge, $vert, $bleu);
$coulbord = imagecolorallocate($imR, $coulbord[0], $coulbord[1], $coulbord[2]);
for ($i=0;$i<5;$i++){
	imagesetpixel($imR, $tailleimage-1, $i, $coulbord);
	imagesetpixel($imR, $tailleimage-1-$i, $tailleimage-1, $coulbord);
	imagesetpixel($imR, $tailleimage-1, $tailleimage-1-$i, $coulbord);
	imagesetpixel($imR, $i, $tailleimage-1, $coulbord);
}

header('Content-type: image/png');
imagepng($imR);
imagedestroy($imR);
imagedestroy($imR2);
imagedestroy($imB);die();


?>