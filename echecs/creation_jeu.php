<html>
<head>
<title>Création d'un jeu</title>
</head>
<body>
<?php
if (isset($_POST["j1"]) && isset($_POST["j2"])) {

require("config.inc");
require("fonctions.inc");

seconnecte();

/* on récupère les ID des joueurs */
$q = mysql_query ("SELECT IdJoueur FROM Joueur WHERE (Nom = \"".$_POST["j1"]."\");");
$n1 = @mysql_result($q,0);
$q = mysql_query ("SELECT IdJoueur FROM Joueur WHERE (Nom = \"".$_POST["j2"]."\");");
$n2 = @mysql_result($q,0);
if (!$n1 || !$n2) die("Jeu impossible à créer, vérifier les autorisations...");

/* choix des couleurs */
if (mt_rand(1,2) == 1) {
$joueur_b = $n1;
$joueur_n = $n2;
} else {
$joueur_n = $n1;
$joueur_b = $n2;
}

/* création du plateau */
$n = 0;	$c = 0;
do {
	$m = cherche_numero_valide("EPlateau","IdP");
	$q = mysql_query("INSERT INTO EPlateau(IdP,blanc,noir,quiJoue) VALUES ("
				.$m.","
				.$joueur_b.","
				.$joueur_n.","
				.mt_rand(0,1).");");
	if (!$q) echo "Tentative n°".$n++." ($m) échouée : ".mysql_error()."<br>";
} while(!$q && $n < 10);
if (!$q) echo "Création du plateau de jeu échouée...<br>";
else echo "Création du plateau de jeu réussie !<br>";

/* création des cases */
$q = 1;
for ($i=1;$i<9 && $q;$i++)
	for ($j=1;$j<9 && $q;$j++) {
		$q = mysql_query("INSERT INTO ECase(x,y,couleur,IdP) VALUES ("
				.$i.","
				.$j.","
				.(($i+$j)%2).","
				.$m.");");
		if (!$q) echo "Création de la case $i, $j impossible.<br>".
					mysql_error()."<br>Abandon de la procédure.<br>";
	}
$q &= mysql_query("INSERT INTO ECase(x,y,couleur,IdP) VALUES (0,0,0,$m);");//pions pris par les noirs
$q &= mysql_query("INSERT INTO ECase(x,y,couleur,IdP) VALUES (1,0,0,$m);");//pions pris par les blancs
if (!$q) //destruction du plateau et des cases par cascade
{	mysql_query ("DELETE FROM EPlateau WHERE (IdP = $m);");
	die();
}
/* création des pièces */
$q = 1;
// 1 pion, 2 fou, 3 cavalier, 5 tour, 9 reine, 8 roi
for ($i=1;$i<9 && $q;$i++) {
  for ($j=1;$j<9 && $q;$j++) {
	$n = octet($plateau_standard,$i,$j);
	if (estpiece($n)) {
		$mc = cherche_numero_valide("EPiece","IdPiece");
		$q = mysql_query("INSERT INTO EPiece(type,couleur,IdPiece,x,y,IdP)".
					"VALUES (".typepiece($n).", ".couleur($n).", $mc, $i, $j, $m);");
		if (!$q) echo "Création de la pièce en ($i, $j) (".typepiece($n)." ".couleur($n).") impossible.<br>".
					mysql_error()."<br>Abandon de la procédure.<br>";
	}
  }
}
if (!$q) { //destruction du plateau et des cases par cascade
	mysql_query ("DELETE FROM EPlateau WHERE (IdP = $m);");
	die();
}
?>
Création du plateau <b><? echo $m; ?></b> réussie !<br>
(Noter ce numéro pour l'accès au jeu par <a href=jeu.php>la page prévue pour cela</a>)
<?
} else {
?>
<form method=POST action="<? echo $_SERVER["SCRIPT_NAME"] ; ?>">
Nom Joueur 1 : <input type=text name=j1><br>
Nom Joueur 2 : <input type=text name=j2><br>
<input type=submit name=envoi value="Créer !">
</form>
<?
}
?>
</body>
</html>