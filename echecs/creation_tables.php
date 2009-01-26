<?php
	require("config.inc");

    $link = mysql_connect ($MYSQL_SERVER,$MYSQL_USER,$MYSQL_PSWD) or die ("Connexion impossible");
/*	if (mysql_select_db($NOM_DB)) {
		echo "Destruction de l'ancienne base de données echecs<br>\n";
		@mysql_drop_db ($NOM_DB);
	}
    if (@mysql_create_db ($NOM_DB)) {
        print ("Base de données echecs créée<br>\n");
    } else {
        printf ("Erreur lors de la création de la base: %s<br>\n", mysql_error());
    }
*/	mysql_select_db($NOM_DB) or die ("Selection de la BDD impossible");
	echo "Sélection de la BDD réussie<br>";
	
	/* CREATION DES RELATIONS */
	mysql_query("DROP TABLE Joueur;");
	$t = mysql_query ("CREATE TABLE Joueur ("
					."nom VARCHAR( 20 ) UNIQUE NOT NULL ,"
					."mdp VARCHAR( 20 ) NOT NULL ,"
					."email VARCHAR( 40 ) NOT NULL ,"
					."IdJoueur INT NOT NULL ,"
					."PRIMARY KEY ( IdJoueur ) );");
	if (!$t) echo "Erreur de création de table : ".mysql_error()."<br>\n";
	else echo "Création de Joueur réussie<br>\n";

	mysql_query("DROP TABLE EPlateau;");
	$t = mysql_query ("CREATE TABLE EPlateau ("
				."\nIdP INTEGER,"
				."\nblanc 	INTEGER,"
				."\nnoir 	INTEGER,"
				."\nquiJoue 	INTEGER,"	// noir : 0, blanc : 1
				."\nenvoieMail 	INTEGER,"	// 1 : oui 0 : non
				."\n PRIMARY KEY (IdP),"
				."\nFOREIGN KEY (blanc) REFERENCES Joueur(IdJoueur) ON DELETE SET NULL,"
				."\nFOREIGN KEY (noir) REFERENCES Joueur(IdJoueur) ON DELETE SET NULL);");
	if (!$t) echo "Erreur de création de table : ".mysql_error()."<br>\n";
	else echo "Création de EPlateau réussie<br>\n";
	
	mysql_query("DROP TABLE ECase;");
	$t = mysql_query ("CREATE TABLE ECase ("
				."\nx INTEGER,"
				."\ny INTEGER,"
				."\ncouleur INTEGER,"	// noir : 0, blanc : 1
				."\nIdP INTEGER,"
				."\nIdPiece INTEGER,"
				."\nPRIMARY KEY (x,y,IdP),"
				."\nFOREIGN KEY (IdPiece) REFERENCES EPiece(IdPiece) ON DELETE SET NULL,"
				."\nFOREIGN KEY (IdP) REFERENCES EPlateau(IdP) ON DELETE CASCADE);");
	if (!$t) echo "Erreur de création de table : ".mysql_error()."<br>\n";
	else  echo "Création de ECase réussie<br>\n";

	mysql_query("DROP TABLE EValeur;");
	$t = mysql_query ("CREATE TABLE EValeur ("
				."\ntype INTEGER PRIMARY KEY," // 1 pion, 2 fou, 3 cavalier, 5 tour, 9 reine, 8 roi
				."\nnom VARCHAR(10),"
				."\npoints INTEGER);");
	if (!$t) {echo "Erreur de création de table : ".mysql_error()."<br>\n";}
	else { echo "Création de EValeur réussie<br>\n";}

	mysql_query("DROP TABLE EPiece;");
	$t = mysql_query ("CREATE TABLE EPiece ("
				."\ntype INTEGER," // 1 pion, 2 fou, 3 cavalier, 5 tour, 9 reine, 8 roi
				."\ncouleur INTEGER," // noir : 0, blanc : 1
				."\nx INTEGER, y INTEGER,"
				."\nIdPiece INTEGER PRIMARY KEY,"
				."\nIdP INTEGER,"
				."\nFOREIGN KEY (IdP) REFERENCES EPlateau(IdP) ON DELETE CASCADE,"
				."\nFOREIGN KEY (x,y) REFERENCES ECase(x,y) ON DELETE CASCADE,"
				."\nFOREIGN KEY (type) REFERENCES EValeur(type) ON DELETE CASCADE);");
	if (!$t) echo "Erreur de création de table : ".mysql_error()."<br>\n";
	else  echo "Création de EPiece réussie<br>\n";
	
	/* INSERTION GENERIQUE DES RELATIONS */
	mysql_query ("INSERT INTO EValeur VALUES (1,\"pion\",1);");
	mysql_query ("INSERT INTO EValeur VALUES (2,\"fou\",3);");
	mysql_query ("INSERT INTO EValeur VALUES (3,\"cavalier\",3);");
	mysql_query ("INSERT INTO EValeur VALUES (5,\"tour\",5);");
	mysql_query ("INSERT INTO EValeur VALUES (9,\"reine\",9);");
	mysql_query ("INSERT INTO EValeur VALUES (8,\"roi\",1000);");

	echo "<br>Création terminée !";
?>

