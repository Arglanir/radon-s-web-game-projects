<?php
/*****************
crée des cases gif animées pour AOP2

	paramètres GET :
		c	couleur RRGGBB
		n	nombre de cellules
		h	chateau ? 1/0
		d	décor 0/1/2/3
		m	max atteint ? 1/0
		
		taille taille des atomes (carrés)
	images de 33x33
*******************/
include "GIFEncoder.class.php";

$couleur = (array_key_exists("c",$_GET)?$_GET["c"]:"0000FF");
$nombre = 0+(array_key_exists("n",$_GET)?$_GET["n"]:2);
$chateau = (array_key_exists("h",$_GET)?$_GET["h"]=="1"||$_GET["h"]=="true":false);
$dernier = (array_key_exists("r",$_GET)?$_GET["r"]=="1"||$_GET["r"]=="true":false);
$decor = 0+(array_key_exists("d",$_GET)?$_GET["d"]:0);
$maxatteint = (array_key_exists("m",$_GET)?$_GET["m"]=="1"||$_GET["m"]=="true":false);
$tailleattome = max(min(0+(array_key_exists("taille",$_GET)?$_GET["taille"]:3),5),1);

$rouge = hexdec(substr($couleur,0,2));
$vert = hexdec(substr($couleur,2,2));
$bleu = hexdec(substr($couleur,4,2));

$tailleimage = 0+(array_key_exists("tailleimage",$_GET)?$_GET["tailleimage"]:33);;

$imgs = array();
$t = array();

$imfond = imagecreatetruecolor($tailleimage, $tailleimage);

$coulcentre = array(0,0,0);$plateau = 0;//en pourcentage
switch ($decor){
	case 0:break;
	case 1 : $coulcentre = array(0,0,128);break;
	case 2 : $coulcentre = array(128,64,0);break;
	case 3 : $coulcentre = array(128,128,128);$plateau=50;break;
	/*case 1:  $bg = imagecolorallocate($imfond, 0, 0, 128);//glace
	imagefill($imfond, 0, 0, $bg);break;
	case 2:  $bg = imagecolorallocate($imfond, 128, 64, 0);//point chaud
	imagefill($imfond, 0, 0, $bg);break;
	case 3:  $bg = imagecolorallocate($imfond, 128, 128, 128);//obstacle
	imagefill($imfond, 0, 0, $bg);break;*/
}
if ($decor != 0){
	for ($ta=0; $ta<$tailleimage*(100-$plateau)/200; $ta++){
		$tabcoul = array();
		for ($i=0;$i<count($coulcentre);$i++) $tabcoul[$i] = floor($coulcentre[$i]*$ta*200/($tailleimage*(100-$plateau)));
		$coul = imagecolorallocate($imfond, $tabcoul[0], $tabcoul[1], $tabcoul[2]);
		imagefilledrectangle($imfond, $ta ,  $ta , $tailleimage-1-$ta ,  $tailleimage-1-$ta , $coul );
	}
}
//dessin des bords
$coulbord = array(128,128,128);
if ($dernier) $coulbord = array($rouge, $vert, $bleu);
$coulbord = imagecolorallocate($imfond, $coulbord[0], $coulbord[1], $coulbord[2]);
for ($i=0;$i<5;$i++){
	imagesetpixel($imfond, 0, $i, $coulbord);
	imagesetpixel($imfond, $tailleimage-1-$i, 0, $coulbord);
	imagesetpixel($imfond, $tailleimage-1, $tailleimage-1-$i, $coulbord);
	imagesetpixel($imfond, $i, $tailleimage-1, $coulbord);
}


if (!$chateau){//atomes libres
	$nbframes = ($nombre>0?($tailleimage-$tailleattome)*2:1);
	$pos = array();//le tableau des positions
	$dir = array();//le tableau des directions

	for ($i = 0;$i<$nombre;$i++){
		$pos [$i] = array(rand(0,$tailleimage-$tailleattome),rand(0,$tailleimage-$tailleattome));
		switch ($decor){
			case 2:$dir [$i] = array(2*rand(1,1+$i)*(rand(0,1)*2-1),rand(1,1+$i)*(rand(0,1)*2-1));break;
			case 1:$dir [$i] = array((rand(0,2)-1),(rand(0,2)-1));
			if ($dir [$i][0] == 0 && $dir [$i][1] == 0) $dir [$i][0] = 1;
				break;
			default:$dir [$i] = array(rand(1,1+$i)*(rand(0,1)*2-1),rand(1,1+$i)*(rand(0,1)*2-1));break;
		}
	}
	
	for ($j = 0; $j < $nbframes; $j++){
		$im = imagecreatetruecolor($tailleimage, $tailleimage);
		imagecopy($im ,$imfond,0,0,0,0, $tailleimage, $tailleimage);
		$fond = imagecolorallocate($im, 0, 0, 0);
		$coul = imagecolorallocate($im, $rouge, $vert, $bleu);
		for ($i = 0; $i < $nombre; $i++){
			imagefilledrectangle($im, $pos[$i][0] ,  $pos[$i][1] , $pos[$i][0]+$tailleattome-1 ,  $pos[$i][1]+$tailleattome-1 , $coul );
			//imagesetpixel($im , $pos[$i][0] , $pos[$i][1] , $coul );
			$pos[$i][0] += $dir [$i][0];
			$pos[$i][1] += $dir [$i][1];
			//faire le reflet
			if ($pos[$i][0] < 0) {$pos[$i][0] = -$pos[$i][0];$dir [$i][0] = -$dir [$i][0];}
			if ($pos[$i][1] < 0) {$pos[$i][1] = -$pos[$i][1];$dir [$i][1] = -$dir [$i][1];}
			if ($pos[$i][0] > $tailleimage-$tailleattome) {$pos[$i][0] = 2*($tailleimage-$tailleattome)-$pos[$i][0];$dir [$i][0] = -$dir [$i][0];}
			if ($pos[$i][1] > $tailleimage-$tailleattome) {$pos[$i][1] = 2*($tailleimage-$tailleattome)-$pos[$i][1];$dir [$i][1] = -$dir [$i][1];}
		}
		if ($nombre > 0)
			dessineNumero($im,floor($tailleimage/2)+1,floor($tailleimage/2)+1,true,$coul,$fond,false,$nombre);
		ob_start();
		imagegif($im);
		$imgs[] = ob_get_clean();
		if ($decor==1) $t[] = 8;
		else $t[] = 5;
		imagedestroy($im);
	}

} else {//membrane activée : les atomes vont tourner dans un sens ou l'autre autour du centre de la case
	$nombre2 = ($nombre < 10 ? $nombre+1:$nombre);//combien à prendre en compte

	//paramètres
	$nbframesdelta = 4;
	$rayon = 10;//trouver le rayon en fonction du nombre d'atomes et la taille
	$circonference = $nombre2*$tailleattome;
	$rayon = $circonference/M_PI;
	
	//calculs
	$pos = array();//le tableau des positions
	$dir = array();//le tableau des directions
	$centre = (($tailleimage-$tailleattome)/2);
	//$tailleattome
	$nbframes = ($nombre < 10 ? $nbframesdelta*$nombre2:$nbframesdelta);
	$angle = 2*M_PI/$nombre2; //angle entre 2 atomes
	$delta = (rand(0,1)*2-1)*($angle/$nbframesdelta);
	$offset = $angle * rand(0,$nombre2);
	for ($j = 0; $j < $nbframes; $j++){
		$im = @imagecreatetruecolor($tailleimage, $tailleimage);
		imagecopy($im ,$imfond,0,0,0,0, $tailleimage, $tailleimage);
		$coul = imagecolorallocate($im, $rouge, $vert, $bleu);
		for ($i = 0;$i<$nombre;$i++){
			$angle2 = $i*$angle+$j*$delta+$offset;
			$pos[$i][0] = min(max(round($centre+$rayon*cos($angle2)),0),$tailleimage-$tailleattome);
			$pos[$i][1] = min(max(round($centre+$rayon*sin($angle2)),0),$tailleimage-$tailleattome);
			imagefilledrectangle($im, $pos[$i][0] ,  $pos[$i][1] , $pos[$i][0]+$tailleattome-1 ,  $pos[$i][1]+$tailleattome-1 , $coul );
		}
		ob_start();
		imagegif($im);
		$imgs[] = ob_get_clean();
		if ($decor==1) $t[] = 10;
		else $t[] = 5;
		imagedestroy($im);
	}
	
	
	
}
	imagedestroy($imfond);
/*
$string = "XoraX";

$txt = "";
$imgs = array();
$t = array();

foreach(str_split(" ".$string) as $c){
  $txt .= $c;
  $im = @imagecreatetruecolor(120, 20);
  $bg = imagecolorallocate($im, 255, 255, 255);
  imagefill($im, 0, 0, $bg);
  $textcolor = imagecolorallocate($im, 0, 0, 255);
  imagestring($im, 5, 40, 0, $txt, $textcolor);
  ob_start();
  imagegif($im);
  $imgs[] = ob_get_clean();
  $t[] = 10;
  imagedestroy($im);
}
*/
$gif = new GIFEncoder (
  $imgs,
  $t,
  0,
  2,
  0, 0, 0,
  "bin"
);

Header("Content-type:image/gif");
echo $gif->GetAnimation();

?>