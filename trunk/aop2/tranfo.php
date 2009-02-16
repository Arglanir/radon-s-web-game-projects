<?php
/*** transformation des parties textes en parties XML et test de classes.php ***/

include_once ("classes.php");

$dir = opendir('.');
echo "<br /><table>";
while (false !== ($file = readdir($dir))) {
	$num = array();
	if (!ereg("^aop([0-9]{5,8})bacteries\.par$",$file,$num))
		continue;
	$numero = $num[1];
	$fichier = $num[0];
	echo "<tr><td>";
	
	echo "Partie ".$num[1];
	$unePartie = Partie::fromText($fichier);
	$unePartie->enregistrerXML("x".$fichier);
	echo " transformée";

	echo "</td>";
	echo "</tr>\n";
}
echo "</table>";
closedir($dir);


?>