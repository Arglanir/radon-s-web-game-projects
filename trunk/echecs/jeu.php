<html>
<head>
<title>Le jeu...</title>
</head>
<body>
<?
$noIdent = 1;
$msg = "";

if (isset($_POST["jou1"]) && isset($_POST["jou2"]) && isset($_POST["jou3"])) {
	$noIdent = 0;
	$couleur = 1;$jeJoue = 0;$ennemi = 0; $ami = 0;
	// vérification de l'identification
	require("config.inc");
	require("fonctions.inc");
	
	seconnecte();	
	
	//première connexion au jeu ?
	$d = !ereg("^[0-9]",$_POST["jou1"]);
	
	/* login existant ? */
	if ($d)
		$q = mysql_query ("SELECT nom, mdp, IdJoueur FROM Joueur WHERE nom=\"".$_POST["jou1"]."\";");
	else
		$q = mysql_query ("SELECT nom, mdp, IdJoueur FROM Joueur WHERE IdJoueur=".$_POST["jou1"].";");
	$noIdent = ($q?0:1);
	if ($noIdent) $msg .= "Mauvais login.<br>\n";
	else {
		$_POST["jou1"] = @mysql_result($q,0,"IdJoueur");
		/* mot de passe bon ? */
		$n = @mysql_result($q,0,"mdp");
		$noIdent = ($d? ($_POST["jou2"] == $n ? 0 : 1) : (crypte($n) == $_POST["jou2"] ? 0 : 1));
		if ($noIdent) $msg .= "Mauvais login.<br>\n";
		else {
			if ($d) $_POST["jou2"] = crypte($n);
			/* plateau existant ET joueur assermenté ? */
			$q = mysql_query ("SELECT IdP,blanc,noir,quiJoue FROM EPlateau WHERE IdP=".$_POST["jou3"].";");
			$noIdent = ($q?0:1);
			if ($noIdent) $msg .= "Pas de jeu correspondant.<br>\n";
			else {
				$noPart = !($_POST["jou1"] == @mysql_result($q,0,"blanc") || $_POST["jou1"] == @mysql_result($q,0,"noir"));
				if ($noPart) $msg .= "Non participant au jeu.<br>\n"; // mais il peut regarder le jeu
				else {
					$couleur = (($_POST["jou1"] == @mysql_result($q,0,"blanc"))?1:0);
					$jeJoue = ($couleur ==@mysql_result($q,0,"quiJoue"));
				}
				$ennemi = ($couleur?@mysql_result($q,0,"noir"):@mysql_result($q,0,"blanc"));
				$ami = (!$couleur?@mysql_result($q,0,"noir"):@mysql_result($q,0,"blanc"));
				$q = mysql_query ("SELECT nom FROM Joueur WHERE IdJoueur=".$ennemi.";");$ennemi = @mysql_result($q, 0);
				$q = mysql_query ("SELECT nom FROM Joueur WHERE IdJoueur=".$ami.";");$ami = @mysql_result($q, 0);

/**********gestion des déplacements *******/
/*
1) vérifier que c'est à soi de bouger et qu'il y a un mouvement demandé
2) vérifier que la pièce à bouger est à soi
3) vérifier que l'arrivée est valide (et mouvement spécial)
4) vérifier que la case d'arrivée n'est pas occupée ou par une pièce ennemie
5) vérifier que les cases entre le début et l'arrivée sont vides (sauf cavalier)
6) virer les pièces éventuelles de l'arrivée
7) effectuer mouvement spécial (roc, double déplacement du pion...)
*/
$x1 = @$_POST["x1"];$x2 = @$_POST["x2"];$y1 = @$_POST["y1"];$y2 = @$_POST["y2"];
if (($x1 != $x2 || $y1 != $y2) && $jeJoue && $x1 > 0 && $x2 > 0 && $y1 > 0
	&& $y2 > 0 && $x1 < 9 && $x2 < 9 && $y1 < 9 && $y2 < 9) { //1
	
$q = mysql_query ("SELECT IdPiece,couleur,type FROM EPiece WHERE ((x=$x1) AND ((y=$y1) AND (IdP=".$_POST["jou3"].")));");
$piece_depart 	= @mysql_result($q, 0, "IdPiece");
$piece_type 	= @mysql_result($q, 0, "type");
if ($piece_depart && @mysql_result($q, 0, "couleur") == $couleur) {//2

$q = mysql_query ("SELECT IdPiece,couleur FROM EPiece WHERE ((x=$x2) AND ((y=$y2) AND (IdP=".$_POST["jou3"].")));");
$piece_arrivee 	= @mysql_result($q, 0,"IdPiece");
$piece_arrivee_couleur = @mysql_result($q, 0, "couleur");

if ( 
	($piece_type == 1 &&
		(	(($couleur?-1:1)*($y2 - $y1)==2 && !$piece_arrivee && $x1 == $x2 && $y1==($couleur?7:2))
		|| 	(($couleur?-1:1)*($y2 - $y1)==1 && !$piece_arrivee && $x1 == $x2)
		||	(($couleur?-1:1)*($y2 - $y1)==1 && $piece_arrivee && abs($x1 - $x2) == 1)
		))
||	($piece_type == 2 && (abs($y2-$y1)==abs($x2-$x1)))
||	($piece_type == 3 && (abs($y2-$y1)*abs($x2-$x1)==2))
||	($piece_type == 5 && (($y2-$y1)*($x2-$x1) == 0))
||	($piece_type == 9 && (abs($y2-$y1)==abs($x2-$x1) || ($y2-$y1)*($x2-$x1) == 0))
||	($piece_type == 8 && (max(abs($y2-$y1),abs($x2-$x1))==1))

) {//3    manque le roc

if (!$piece_arrivee || $piece_arrivee_couleur != $couleur) {//4

$dxM = $x2 - $x1; $dyM = $y2 - $y1;//case d'arrivée
$dx = (($dxM>0)?1:(($dxM<0)?(-1):0));
$dy = (($dyM>0)?1:(($dyM<0)?(-1):0)); // direction du mouvement
$dxM = $dxM*$dx;$dyM = $dyM*$dy; // distance à la case d'arrivée

$q = mysql_query("SELECT COUNT(*) FROM EPiece WHERE ((IdP = ".$_POST["jou3"].") AND"
				."("
				."((x - $x1)*($dx) > 0) AND ((x - $x1)*($dx) < $dxM) AND "
				."((y - $y1)*($dy) > 0) AND ((y - $y1)*($dy) < $dyM) AND "
				."(($dy)*(x - $x1) = ($dx)*(y - $y1))"
				."))");

if (!$q || !@mysql_result($q, 0)) {// 5 

//$msg .= "($x1, $y1) par ($dx, $dy) vers ($x2, $y2) ($dxM, $dyM)<br>\n";

if ($piece_arrivee) { //6
	mysql_query ("UPDATE EPiece SET x=".$couleur.", y=0 WHERE IdPiece = $piece_arrivee;");
}// fin 6

// fait jouer !
mysql_query ("UPDATE EPiece SET x=$x2, y=$y2 WHERE IdPiece = $piece_depart;");
mysql_query ("UPDATE EPlateau SET quiJoue = ".(1-$couleur)." WHERE IdP = ".$_POST["jou3"].";"); $jeJoue = 0;
//envoieMail


} /* fin 5 */ else $msg.="Sauter au-dessus de ".@mysql_result($q, 0)." pièce(s) n'est pas autorisé.<br>\n";
} /* fin 4	*/ else $msg.="Case d'arrivée non vide<br>\n";
} /* fin 3*/ else $msg.="Mouvement non autorisé<br>\n";
} /* fin 2	*/ else $msg.="Cette pièce ne t'appartient pas.<br>\n";
}/* fin 1 */
/*  *********************************** */
echo $msg;

for($j=1;$j<9;$j++)
	for($i=1;$i<9;$i++){
		$q = mysql_query("SELECT couleur FROM ECase ".
			"WHERE ((x=$i) AND ((y=$j) AND (IdP=".$_POST["jou3"].")));");
		$tableau_couleur[$i][$j] = (@mysql_result($q,0)?"FFFFFF":"000000");
	}

for($j=1;$j<9;$j++)
	for($i=1;$i<9;$i++){
		$q = mysql_query("SELECT type, couleur FROM EPiece ".
			"WHERE ((x=$i) AND ((y=$j) AND (IdP=".$_POST["jou3"].")));");
		if ($q) {
			$c = @mysql_result($q,0,"couleur"); $p = @mysql_result($q,0,"type");
		}
		$tableau_piece[$i][$j] = ($q && $p ? $image_couleur[$c] . $image_piece[$p] : $image_vide );
	}

?>
<script language=Javascript>
<!--
<?
if (!$jeJoue) //raffraichir la page si ce n'est pas à soi de jouer
	echo "setTimeout(\"document.f.submit()\",30000);";
?>

var jeu=new Array();
<?
for($i=1;$i<9;$i++) {
	echo "\tjeu[$i]=new Array(\"".$image_vide."\",\"";
	for($j=1;$j<9;$j++) {
		echo $tableau_piece[$i][$j].($j<8?"\",\"":"\"");
	}	
	echo");\n";
}
?>
var fond=new Array();
<?
for($i=1;$i<9;$i++) {
	echo "\tfond[$i]=new Array(\"FFFFFF\",\"";
	for($j=1;$j<9;$j++) {
		echo $tableau_couleur[$i][$j].($j<8?"\",\"":"\"");
	}	
	echo");\n";
}
?>

function getcase(i,j){
<?
for($i=1;$i<9;$i++)
	for($j=1;$j<9;$j++)
		echo "\tif (i==$i && j==$j) return document.all.c$i$j;\n";
?>
}

function selcase(i,j) {
<?
	if (!$jeJoue) echo "\treturn false;\n";
?>
	x1 = parseInt(document.f.x1.value);
	y1 = parseInt(document.f.y1.value);
	
	if (x1 == 0 || (x1 == i && y1 == j)) {//première case ou déselection
		if (x1 > 0) {
			getcase(x1,y1).style.background = "#"+fond[x1][y1];
			document.f.JSmessage.value="Pas de case sélectionnée";
		} else  {
			getcase(i,j).style.background="blue";
			document.f.JSmessage.value="Case sélectionnée : "+i+", "+j;
		}
		document.f.x1.value=i-x1;document.f.y1.value=j-x1;
	} else {
		document.f.JSmessage.value="Vers la case "+i+", "+j;
		document.f.x2.value=i;document.f.y2.value=j;
		document.f.submit();
	}
		
	
	
}

-->
</script>
<center>
<?
//Pièces prises
$q = mysql_query ("SELECT type FROM EPiece WHERE (((y=0) AND (IdP=".$_POST["jou3"].")) AND (x=".(1-$couleur).")) ORDER BY type DESC;");
$k = 0;
while ($r = mysql_fetch_row($q)) {
	$k++;
	echo "<img src=\"".$dossier_image . $image_couleur[$couleur] . $image_piece[$r[0]] . $image_suffixe."\" >\n";
}
if ($k > 0) echo "<br>";
?>
<b><? echo $ennemi; ?></b>
<form name=f method=POST action="<? echo $_SERVER["SCRIPT_NAME"] ; ?>"><table style="background-color:#008800;">
<?
for($j=($couleur?1:8);($couleur?$j<9:$j>0); $j += 2*$couleur - 1){
	echo "<tr>";
	for($i=($couleur?1:8);($couleur?$i<9:$i>0);  $i += 2*$couleur - 1){
		echo "<td id=\"c$i$j\" width=40 height=40 style=\"background-color:#".
			$tableau_couleur[$i][$j].";\">";
		// dessiner les pièces
		$modifications = "border=no onclick=\"selcase(".$i.",".$j.")\"";
		echo "<img src=\"".$dossier_image . $tableau_piece[$i][$j] . $image_suffixe."\" ".$modifications.">";
		echo "</td>\n";
	}
	echo "</tr>\n";
}
?>
</table>
<input type=hidden name=jou1 value=<? echo $_POST["jou1"]; //IdJoueur ?>>
<input type=hidden name=jou2 value=<? echo $_POST["jou2"]; //mdp crypté ?>>
<input type=hidden name=jou3 value=<? echo $_POST["jou3"]; //IdP ?>>
<input type=text size=70 name=JSmessage style="border:0;text-align:center;" value="les messages s'affichent ici">
<input type=hidden name=x1 value=0><input type=hidden name=y1 value=0>
<input type=hidden name=x2 value=0><input type=hidden name=y2 value=0>
<br><input type=submit value="Raffraichir">


</form>
<?
if ($jeJoue) echo "C'est à toi de jouer.<br>";
?>
<b><? echo $ami; ?></b>
<?
//Pièces prises
$q = mysql_query ("SELECT type FROM EPiece WHERE (((y=0) AND (IdP=".$_POST["jou3"].")) AND (x=".($couleur).")) ORDER BY type DESC;");
$k = 0;
while ($r = mysql_fetch_row($q)) {
	if ($k++ == 0) echo "<br>";
	echo "<img src=\"".$dossier_image . $image_couleur[1-$couleur] . $image_piece[$r[0]] . $image_suffixe."\" >\n";
}
?>
</center>
<?
			}
		}
	}
}

if ($noIdent) {
	echo $msg;
	if (!isset($_POST["jou1"])) $_POST["jou1"] = "";
	if (!isset($_POST["jou2"])) $_POST["jou2"] = "";
	if (!isset($_POST["jou3"])) $_POST["jou3"] = "";
?>
<form method=POST action="<? echo $_SERVER["SCRIPT_NAME"] ; ?>">
<table><tr>
<td>Nom :</td><td><input type=text name=jou1 value="<? echo $_POST["jou1"]; //IdJoueur ou nom ?>"></td></tr>
<td>Mot de passe :</td><td><input type=password name=jou2 value="<? echo $_POST["jou2"]; //mdp non crypté ?>"></td></tr>
<td>Plateau :</td><td><input type=text name=jou3 value="<? echo $_POST["jou3"]; //IdP ?>"></td></tr>
</table>
<input type=submit value="Chercher la partie">
</form>
<?
}
?>
</body>
</html>