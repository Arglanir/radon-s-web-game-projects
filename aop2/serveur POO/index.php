<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<?php
/*
Fichier: index.php
Date: 27/01/2009
Auteur: Mika�l Mayer / C�dric Mayer
But: Accueil du jeu, montre les parties en cours et a le formulaire pour la cr�ation d'une partie
*/
include("fonctions.inc");

$max_joueurs = 9;
$array_count = array();

for($i=1; $i<=$max_joueurs; $i++) {
	$array_count[$i] = $i;
}



$game_name = "Age Of Paramecia II";
?>

<html>
<head>
<title><?php echo $game_name;?></title>
<script type="text/javascript" src="clientclasses.js" ></script>
<script language="javascript">
function nomIA(){//g�n�re un nom d'IA
	var syllabes = new Array("kel","gal","mot","juh","syd","fek","p�d","van","xor","bel","jol");
	var suffixe = "ia";
	var nbSyl = 1+Math.floor(2*Math.random());
	var nom = "";
	for (var i=0;i<nbSyl;i++)
		nom+=syllabes[Math.floor(syllabes.length*Math.random())];
	nom += suffixe;
	nom = nom.substr(0,1).toUpperCase() + nom.substr(1,nom.length-1);
	return nom;
}

function bodyOnLoad() {
	document.cre.nbJoueurs.value = 2;
	updateNumberPlayers();
	cacherTout();
	for(var i = 1; i <= <?php echo $max_joueurs.""; ?>; i++) {
		changecolor(i);
	}
}

function updateNumberPlayers() {
	for(var i = 1; i <= <?php echo $max_joueurs.""; ?>; i++) {
		if(i <= document.cre.nbJoueurs.value ) {
			document.getElementById("divname"+i).style.display = "block";
			//document.getElementById("no"+i).name = ;
		} else {
			document.getElementById("divname"+i).style.display = "none";
		}
	}
}

function updateMotDePasse(n) {
	checked = document.getElementById("si_mdp"+n).checked;
	document.getElementById("divmdp"+n).style.display = (checked ? "inline": "none");
	document.getElementById("divias"+n).style.display = (checked ? "none" : "inline" );
}
function updateIA(n) {
	checked = document.getElementById("is_ia"+n).checked;
	document.getElementById("no"+n).value = nomIA();
	document.getElementById("divia"+n).style.display = (checked ? "inline": "none");
	document.getElementById("divmdps"+n).style.display = (checked ?"none" : "inline" );
}

function changecolor(n) {
	var color = document.getElementById("couleur"+n).value;
	if(color.length==6) {
		document.getElementById("no"+n).style.backgroundColor = "#"+color;
	}
}

function cacherTout(){
	document.getElementById("creation").style.display = "none";
	document.getElementById("regles").style.display = "none";
	document.getElementById("parties").style.display = "none";
}
function changerAffichage(quoi,comment){
	if (document.getElementById(quoi).style.display == "none")
	document.getElementById(quoi).style.display = (comment?comment:"block");
	else
	document.getElementById(quoi).style.display = "none";
}


var partiesExistantes = new Array();
function chargerPartiesEnCours(){
	communiqueGET("lespartiesencours.xml",function(Rxml){
		var listeParties = Rxml.getElementsByTagName("partie");
		var chaineAAfficher = "<b>"+listeParties.length+" parties en cours"+(listeParties.length?" :":"")+"</b><br />";
		var partiesCachees = 0;
		for (var i = 0; i < listeParties.length ; i++){
			var partie = listeParties[i];
			var numeroP = partie.getAttribute('numero');
			var nbjoueurs = parseInt(partie.getAttribute('nbJoueurs'));
			var nExistaitPas = (partiesExistantes[numeroP] != nbjoueurs);
			partiesExistantes[numeroP] = nbjoueurs;
			var cachee = parseInt(partie.getAttribute('cachee'));
			if (cachee && !nExistaitPas) {
				partiesCachees++;
				continue;
			}
			chaineAAfficher += (nExistaitPas?"<b>&#8594;":"")+"Partie n�"+numeroP+" : "; //nbjoueurs+";"+partiesExistantes[numeroP]+";"+
			var listeJoueurs = partie.getElementsByTagName("joueur");
			for (var j=0;j < listeJoueurs.length;j++){
				var joueur = listeJoueurs[j];
				var nom = joueur.getAttribute('nom');
				var numeroJ = joueur.getAttribute('numero');
				var couleur = joueur.getAttribute('couleur');
				var lien = "client.html?j="+numeroJ+"&p="+numeroP;
				chaineAAfficher += '<a style="color:black;background-color:#'+couleur+';" href="'+lien+'">'+nom+'</a> ';
			}
			chaineAAfficher += '<span id="action-'+numeroP+'"><input type="button" value="Entrer" onclick="sajouter2(\''+numeroP+'\');" /></span>'+(nExistaitPas?"</b>":"")+'<br/>';
		}
		if (partiesCachees)
			chaineAAfficher += partiesCachees+" autres parties cach�es."
		document.getElementById("parties2").innerHTML = chaineAAfficher;
	});
}
function sajouter2(numeroPartie){
	chaineaafficher = "<form action=\"<?php echo serveur_fichier; ?>?a=autrejoueur&p="+numeroPartie+"\" method='POST' target='framecreation'>";
	chaineaafficher += "&nbsp;&nbsp;&#9495;&nbsp;<input type=\"hidden\" name=\"p\" value=\""+numeroPartie+"\" />";
	chaineaafficher += "<input style=\"vertical-align:bottom;\" type=\"text\" name=\"nom\" value=\"Votre nom\" onfocus=\"if (this.value=='Votre nom') this.value='';\" />";
	chaineaafficher += "<?php echo addslashes(addSelectOption(
array("text" => " Couleur",
	"idname" => "couleur",
	"options" => $GLOBALS["color_array"],
	"callback" => "",//"changecolor(".$i.")",
	"default_index" => 0,
	"color" => True
),false)); ?>";
	chaineaafficher += "<input style=\"vertical-align:bottom;\" type=\"submit\" value=\"OK\" /></form>";
	document.getElementById("action-"+numeroPartie).innerHTML = chaineaafficher;
}
</script>
</head>
<body onload="bodyOnLoad();chargerPartiesEnCours();">
<h1><?php echo $game_name; ?></h1>
<i>Jeu d�velopp� par C�dric, Mika�l et Erwin Mayer</i>
<br />
<h2><a href="" onclick="changerAffichage('regles');return false;" style="color:black;text-decoration:none;">&gt; R�gles</a></h2>
<div id="regles" style="display:none;">
<h3>Introduction</h3>
<?php echo $game_name;?> est un jeu hautement instable o� vous devez lutter pour la survie de votre colonie de cellules sans cesse grandissante. C'est la dure loi de l'�volution : seuls les plus forts gagneront cette course pour la Vie !

<h3>Objectif</h3>
Votre colonie doit finir seule sur le plateau de jeu.

<h3>D&eacute;roulement</h3>
Chaque joueur � son tour ajoute une cellule � lui dans une case qui lui appartient ou qui lui est proche<sup><a href="" onclick="changerAffichage('option4expl','inline');return false;" style="text-decoration:none;">&gt;</a><span id="option4expl" style="display:none;">Options changeables dans "Ajout diagonal"</span></sup>. Puis cette action peut g�n�rer des r�actions en chaine selon la r�gle suivante : si le nombre C de cellules dans une case est sup�rieur ou �gal au nombre N de cases autour de cette case (situ�es en croix), N cellules de cette case vont aller chacune dans une case diff�rente autour (�v�nement nomm� ci-apr�s "explosion"), et cela jusqu'� ce que le jeu redevienne stable<sup><a href="" onclick="changerAffichage('option7expl','inline');return false;" style="text-decoration:none;">&gt;</a><span id="option7expl" style="display:none;">Option changeable dans "Profondeur de jeu"</span></sup>.<br />
Si lors d'une explosion, une de vos cellules arrive dans une case contr�l�e par un autre joueur, les cellules de cette case deviennent les v�tres.
<h4><img style="vertical-align:bottom;" src="images/image.php?n=10&d=0&h=1&type=atome" />
<img style="vertical-align:bottom;" src="images/image.php?n=10&d=0&h=1&type=cellule" />
<img style="vertical-align:bottom;" src="images/image.php?n=10&d=0&h=1&type=mediev" /> Les membranes ou chateaux<sup><a href="" onclick="changerAffichage('option7expl','inline');return false;" style="text-decoration:none;">&gt;</a><span id="option7expl" style="display:none;">Option changeable dans "Chateaux"</span></sup></h4>
L'exception � cette r�gle d'explosion est lorsque vous tentez de cr�er quelque chose de plus solidaire avec vos cellules. Pour cela, changez le mode d'addition en mode de cr�ation de chateau lors de votre tour de jeu. La cellule que vous cr�erez sera la messag�re et organisera le d�but de cette membrane avec les autres cellules pr�sentes dans la case. Toute cellule ensuite ajout�e par vous normalement ou par explosion viendra grandir et solidifier l'ensemble.<br />
Si la membrane est attaqu�e (par explosion d'un autre joueur), et que les cellules la composant sont trop peu nombreuses (nombre inf�rieur ou �gal � 9), elles se d�solidarisent et de plus appartiennent au joueur attaquant. Attention aux r�actions en chaine ! Par contre, si elles sont fortes (au moins 10), alors le joueur attaquant perd une cellule et vous aussi, sans que le reste soit affect�.<br />
Pour d�solidariser par vous-m�me une de vos membranes, il suffit de vous remettre dans le mode de cr�ation/destruction de membrane, et d'envoyer une cellule faire le travail. Attention aux r�actions en chaine !

<h3>Effet du d�cor</h3>
Il y a 4 types de terrain :
<dl>
<dt><img style="vertical-align:bottom;" src="images/image.php?n=0&d=0&type=atome" />
<img style="vertical-align:bottom;" src="images/image.php?n=0&d=0&type=cellule" />
<img style="vertical-align:bottom;" src="images/image.php?n=0&d=0&type=mediev" /> Stable </dt>
<dd>Terrain de base du jeu. Une membrane ne peut �tre construite que sur ce type de terrain.</dd>

<dt><img style="vertical-align:bottom;" src="images/image.php?n=0&d=1&type=atome" />
<img style="vertical-align:bottom;" src="images/image.php?n=0&d=1&type=cellule" />
<img style="vertical-align:bottom;" src="images/image.php?n=0&d=1&type=mediev" /> Glace </dt>
<dd>Un endroit plus froid est moins propice au d�veloppement de la vie. Vous ne pouvez pas y envoyer de cellule si elle y sera seule, et ni si ensuite elle doit repartir de suite (explosion juste apr�s). Dans ces cas, la seule mani�re de conqu�rir une telle case sera par les explosions des cellules d'� c�t� (au moins 2 explosions, car les cellules ont tendance � mourrir en arrivant sur un endroit froid).</dd>
	
	<dt><img style="vertical-align:bottom;" src="images/image.php?n=0&d=2&type=atome" />
	<img style="vertical-align:bottom;" src="images/image.php?n=0&d=2&type=cellule" />
	<img style="vertical-align:bottom;" src="images/image.php?n=0&d=2&type=mediev" /> Point chaud </dt>
	<dd>Un endroit plus chaud est meilleur pour le d�veloppement des cellules. Lorsqu'une cellule arrive sur une telle case, elle se d�double (une fois par tour de joueur).</dd>

<dt><img style="vertical-align:bottom;" src="images/image.php?n=0&d=3&type=atome" />
<img style="vertical-align:bottom;" src="images/image.php?n=0&d=3&type=cellule" />
<img style="vertical-align:bottom;" src="images/image.php?n=0&d=3&type=mediev" /> Obstacle </dt>
<dd>Les cellules ne peuvent s'y d�velopper. Ce sera donc une cellule de moins dans la limite de population sans explosion des cases d'�-c�t�.</dd>


</dl>

</div><script type="text/javascript">var debut=true;</script>
<h2><a href="" onclick="changerAffichage('creation');return false;" style="color:black;text-decoration:none;">&gt; Cr&eacute;ation d'une partie</a></h2>
<iframe style="display:none;" id="framecreation" name="framecreation" src="" height="90" width="200" FRAMEBORDER=0 scrolling="no" onload="if (!debut) {document.getElementById('statuscreation').style.display='none';document.getElementById('parties').style.display='block';chargerPartiesEnCours();} else debut=false;"></iframe>
<div id="statuscreation" style="display:none;">Cr�ation de la partie en cours...</div>
<div id="creation" style="display:none;">
<form action="creajeu.php" method="POST" name="cre" target="framecreation" onsubmit="document.getElementById('statuscreation').style.display='block';changerAffichage('creation');">
<?php
addSelectOption(
array("text" => "Nombre de joueurs",
	"idname" => "nbJoueurs",
	"options" => $array_count,
	"callback" => "updateNumberPlayers()"
));

foreach($array_count as $i) {
echo "<div id=divname".$i.">\n";
echo "<table border=1><tr><td>";
echo 'Nom : <input type=text id="no'.$i.'" name="nomJoueur'.$i.'" value="Joueur'.$i.'" onfocus="if (this.value.indexOf(\'Joueur\') != -1) this.value=\'\';" style="background-color:#0000FF"><br/>';
echo '<div id="divias'.$i.'" style="display:inline">Intelligence artificielle : <input type="checkbox" name="is_ia'.$i.'" id="is_ia'.$i.'"  onchange="updateIA('.$i.')" />';
echo '<div id="divia'.$i.'" style="display:none">Niveau : <select type=text id="nivia'.$i.'" name="nivia'.$i.'"><option value=0 selected>0</option><option value=1>1</option><option value=2>2</option></select></div></div>';
echo '<div id="divmdps'.$i.'" style="display:inline"> Mot de passe : <input type="checkbox" name="si_mdp'.$i.'" id="si_mdp'.$i.'"  onchange="updateMotDePasse('.$i.')" />';
echo '<div id="divmdp'.$i.'" style="display:none"><input type=text id="mdp'.$i.'" name="mdp'.$i.'" value="" /></div></div>';
echo "<br/>";
addSelectOption(
array("text" => " Couleur",
	"idname" => "couleur".$i,
	"options" => $color_array,
	"callback" => "changecolor(".$i.")",
	"default_index" => $i,
	"color" => True
));
echo "</td></tr></table>";
echo "</div>\n";
}
?>
<h3>Options </h3>
<?php
// Script to list the files named aopMMMMMM.lvl
/*if ($handle = opendir('.')) {
	while (false !== ($file = readdir($handle))) {
		if ($file != "." && $file != "..") {
			echo "$file\n";
		}
	}
	closedir($handle);
}*/
?>
<table>
<tr><td style="text-align:right;">
Taille : </td><td>
<input type=text id="x" name="x" value="6" style="width:30px">
x
<input type=text id="y" name="y" value="6" style="width:30px">
</tr>

<?php
//addCheckOption(
//array("text" => "Ch�teaux actifs",
//      "idname" => "opt_chateaux_actifs",
//      "default" => False));
addSelectOption(
array("text" => "Ch�teaux",
	"idname" => "opt_chateaux_actifs",
	"options" => array("Activ�s" => 1,
						"Non activ�s" => 0),
	"default" => 1,
	"table"=>true
));

addSelectOption(
array("text" => "Type bordure",
	"idname" => "opt_type_bords",
	"options" => array("Bloqu�s" => 1,
						"Non bloquants" => 0,
						"Monde torrique" => 2),
	"default" => 2,
	"table"=>true
));
addSelectOption(
array("text" => "Ajout diagonale ",
	"idname" => "opt_ajout_diagonale",
	"options" => array("On peut cliquer en diagonale" => 1,
						"Uniquement sur les c�t�s du carr�" => 0),
	"default" => 1,
	"table"=>true
));
addSelectOption(
array("text" => "Explosions ",
	"idname" => "opt_explosion_joueur",
	"options" => array("Seulement pour le joueur en cours" => 1,
						"Tous les joueurs sont affect�s" => 0),
	"default" => 1,
	"table"=>true
));
addSelectOption(
array("text" => "Visibilit� de la partie ",
	"idname" => "opt_partie_cachee",
	"options" => array("Cach�e" => 1,
						"Visible" => 0),
	"callback" => "if (this.value=='1') document.cre.opt_attente_joueurs.value='0';",
	"default" => 0,
	"table"=>true
));
addSelectOption(
array("text" => "Decor",
	"idname" => "opt_avec_decor",
	"options" => array("Seulement stable" => 0,
						"Parsem�" => 1,
						"Parsem�" => 2,
						"Dense" => 3),
	"default_index" => 0,
	"table"=>true
));
addSelectOption(
array("text" => "Attente d'autres joueurs",
	"idname" => "opt_attente_joueurs",
	"callback" => "if (this.value=='1') document.cre.opt_partie_cachee.value='0';",
	"options" => array("non" => 0,
						"oui" => 1),
	"default_index" => 0,
	"table"=>true
));
?>
<tr><td style="text-align:right;">
Profondeur de jeu : </td><td><input type=text id="opt_profondeur_jeu" name="opt_profondeur_jeu" value="100" style="width:35px" />
</td></tr>
<tr><td style="text-align:center;" colspan=2>
<input type="submit" name="Envoi" value="Cr�er une partie !" title="Clique ici pour cr�er la partie avec les options actuelles" /> 
</td></tr>
</table>


</form>
</div>

<h2><a href="" onclick="chargerPartiesEnCours();changerAffichage('parties');return false;" style="color:black;text-decoration:none;">&gt; Parties en cours</a></h2>
<div id="parties" style="display:none;">
<?php
//include_once ("newjeux.php");
//$lesParties = new PartiesEnCours();
//$lesParties->afficherParties(false);
?>
<div id="parties2"></div><div id="comm"></div>
<form method="GET" action="jeu.html"><h3>Aller dans une partie non affich�e</h3>
Num&eacute;ro partie : <input type="text" name="p" value="0000000" onfocus="if (this.value='0000000') this.value='';" /><br/>
Num&eacute;ro du joueur : <select name="j"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option></select><br/>
<input type="submit" value="Chercher la partie" title="clique ici" />
</form>

<a href="" onclick="changerAffichage('admini');return false;" style="color:black;text-decoration:none;">&gt; Administration</a><form style="display:none;" id='admini' action="admin.php" method="GET"><input type="text" name="pw" /><input type="submit" value="Aller � l'administration" /></form>
</div>
<div id="bas"><small>&copy; C&eacute;dric & Mika&euml;l Mayer 2009 | <a href="index.php" style="text-decoration:none;">Retour &agrave; l'accueil</a></small></div>
</body>
</html>