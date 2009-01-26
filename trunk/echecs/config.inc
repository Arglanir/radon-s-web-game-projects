<?php
	$MYSQL_SERVER = "sql.free.fr";
	$MYSQL_USER = "radon222";
	$MYSQL_PSWD = "arglanir";
	$NOM_DB = $MYSQL_USER; // nom de la base de données
	$plateau_standard = /* /!\ il faut 64 caractères  	blanc : 	1 pion 2 fou 3 cavalier 5 tour 	9 reine 	8 roi
						espace : rien			noir : 	a pion b fou c cavalier e tour 	i reine 	h roi */
			"ecbihbce"
		.	"aaaaaaaa"
		.	"        "
		.	"        "
		.	"        "
		.	"        "
		.	"11111111"
		.	"53298235";
	
	// les images des pièces
	$dossier_image = "./piecesechecs/";
	$image_suffixe = ".gif";
	$image_couleur = array( 0 => "n", 1 => "b");
	$image_piece = array( 1 => "p", 2 => "f", 3 => "c", 5 => "t", 9 => "e", 8 => "r");
	$image_vide = "kk";
	//	$dossier_image . $image_couleur[$c] . $image_piece[$p] . $image_suffixe;
	
	mt_srand((double)microtime()*1000000);
?>