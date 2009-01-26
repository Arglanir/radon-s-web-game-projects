<?
	imagecolortransparent()
	imagecopyresized
	
	header('Content-type: image/jpeg');
	imagepng();
	imagedestroy($im);
?>