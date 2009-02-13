<?php

include_once ("fonctions.inc");
include_once ("newjeux.php");

if (@md5($_GET["pw"]) != mdpadminmd5){
	header("HTTP/1.0 404 Not found",true,404);die();
	die("Vous n'êtes pas autorisés à venir ici.");
}
?>
<html>
<head>
<title>Page d'administration du jeu AOP2</title>
<script type="text/javascript" src="clientclasses.js" ></script>
<script type="text/javascript">
var bouh = location.search.substring(1,location.search.length).split(unescape("%26"));//on enlève le ? et on sépare avec les &
var tableauArguments = new Array();
for (i=0;i<bouh.length;i++){
   var temp = bouh[i].split("=");
    tableauArguments[temp[0]]=unescape(temp[1]);
}

function supprimerPartie(numero){
	var xhr = createXHR();
	var chaineDAppel = "serveur.php?a=s&pw="+tableauArguments["pw"]+"&p="+numero+"&j=admin&nocache=" + Math.random();
	xhr.onreadystatechange  = function(){ 
		if(xhr.readyState  == 4){
			if(xhr.status  == 200) {
				leXML = xhr.responseXML;
				yaErreur = leXML.getElementsByTagName("erreur").length;
				if (yaErreur){
					document.getElementById("comm").innerHTML = 
						"Erreur : "+leXML.getElementsByTagName("erreur").item(0).getAttribute('raison')+
						" lors de "+leXML.getElementsByTagName("erreur").item(0).getAttribute('origine')+".";
					window.setTimeout("window.location.reload()",5000);
				}
				else {
					document.getElementById("comm").innerHTML = leXML.getElementsByTagName("action").item(0).getAttribute('type')+" r&eacute;ussie. Rechargement de la page...";
					window.setTimeout("window.location.reload()",5000);
				}
			} else {
				document.getElementById("comm").innerHTML = "partie "+numero+" non supprimée";
			}
         }
    }; 
	document.getElementById("comm").innerHTML = "Attente du serveur...";
	
	xhr.open("GET", chaineDAppel, true); 
	xhr.send(null);
}
</script>
</head>
<body>
<a href="index.php">Retour à l'accueil</a>
<h3>Fichier XML :</h3>
<?php
include_once ("newjeux.php");

$lesParties = new PartiesEnCours();

$lesParties->afficherParties(true);

?>
<h3>Répertoire :</h3>
<?php
$dir = opendir('.');
echo "<br /><table>";
while (false !== ($file = readdir($dir))) {
	$num = array();
	if (!ereg("^xaop([0-9]{5,8})bacteries\.par$",$file,$num))
		continue;
	$numero = $num[1];
	echo "<tr><td>$numero</td><td>";
	echo date("d F Y H:i:s", fileatime($file));
	echo "</td><td>";
	echo "<input type=\"button\" value=\"Supprimer\" onclick=\"supprimerPartie('".$numero."');\" />";
	echo "</td>";
	echo "</tr>\n";
}
echo "</table>";
closedir($dir);
?>
<div id="comm"></div>
</body>
</html>