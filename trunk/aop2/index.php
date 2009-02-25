<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<?php
/*
Fichier: index.php
Date: 27/01/2009
Auteur: Mikaël Mayer / Cédric Mayer
But: Accueil du jeu, montre les parties en cours et a le formulaire pour la création d'une partie
*/
include("fonctions.inc");

$max_joueurs = 9;
$array_count = array();

for($i=1; $i<=$max_joueurs; $i++) {
	$array_count[$i] = $i;
}



$game_name = "Age Of Paramecia II";
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
<title><?php echo $game_name;?></title>
<meta name="Description" content="<?php echo $game_name; ?>" /> 
<meta name="Keywords" content="Jeu, jeu en ligne, age of paramecia, jeu de la vie" /> 
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> 
<link rel="stylesheet" type="text/css" href="css/style.css" /> 
<script type="text/javascript" src="clientclasses.js" ></script>
<script language="javascript">
function nomIA(){//génère un nom d'IA
	var syllabes = new Array("kel","gal","mot","juh","syd","fek","péd","van","xor","bel","jol");
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
	document.getElementById("parties").style.display = "block"; document.getElementById('menuparties').style.color = "#FFF";
	document.getElementById("creation").style.display = "none";
	document.getElementById("regles").style.display = "none";	
}
function changerAffichage(quoi,comment){
	if (document.getElementById(quoi).style.display == "none") {
		document.getElementById(quoi).style.display = (comment?comment:"block");
		document.getElementById('menu'+quoi).style.color = "#FFF";
	}
	else {
		document.getElementById(quoi).style.display = "none";
		document.getElementById('menu'+quoi).style.color = "#AFA439";
	}
}

var premierLancement = true;//comme Erwin affiche les parties en cours de suite
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
			var nExistaitPas = (partiesExistantes[numeroP] != nbjoueurs && !premierLancement);
			partiesExistantes[numeroP] = nbjoueurs;
			var cachee = parseInt(partie.getAttribute('cachee'));
			if (cachee && !nExistaitPas) {
				partiesCachees++;
				continue;
			}
			chaineAAfficher += (nExistaitPas?"<b>&#8594;":"")+"Partie n°"+numeroP+" : "; //nbjoueurs+";"+partiesExistantes[numeroP]+";"+
			var listeJoueurs = partie.getElementsByTagName("joueur");
			for (var j=0;j < listeJoueurs.length;j++){
				var joueur = listeJoueurs[j];
				var nom = joueur.getAttribute('nom');
				var numeroJ = joueur.getAttribute('numero');
				var couleur = joueur.getAttribute('couleur');
				var lien = "client.html?j="+numeroJ+"&p="+numeroP;
				chaineAAfficher += '<a style="color:black;background-color:#'+couleur+';" href="'+lien+'">'+nom+'</a> ';
			}
			chaineAAfficher += '<span id="action-'+numeroP+'"><input type="button" value="Entrer" onclick="sajouter2(\''+numeroP+'\');" /></span>'+(nExistaitPas?"</b>":"")+'<br />';
		}
		if (partiesCachees)
			chaineAAfficher += partiesCachees+" autres parties cachées."
		document.getElementById("parties2").innerHTML = chaineAAfficher;
		premierLancement = false;
	},
	function (){
		document.getElementById("parties2").innerHTML = "Aucune partie répertoriée.";
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
<div id="site"> 
	<div id="header"> 
		<div id="bigtitle">
			<h1><a href="index.php" style="text-decoration:none;"><?php echo $game_name; ?></a></h1>
			<small>Jeu développé par <a href="http://radon222.free.fr" style="text-decoration:none;">C&eacute;dric</a>, <a href="http://meak.free.fr" style="text-decoration:none;">Mika&euml;l</a> et <a href="http://www.erwinmayer.com" style="text-decoration:none;">Erwin Mayer</a></small>
		</div>
		<div id="menu"> 
			<ul> 
			<li><a id="menuparties" href="#" onclick="changerAffichage('parties');return false;" >&or; Parties en cours</a></li>
			<li><a id="menucreation" href="#" onclick="changerAffichage('creation');return false;" >&or; Cr&eacute;ation d'une partie</a></li> 
			<li><a id="menuregles" href="#" onclick="changerAffichage('regles');return false;" >&or; Règles</a></li> 
			</ul> 
		</div> 
	</div> 
	<div id="content">
		<div id="parties" class="onglet" style="width: 350px;">
			<h2><a href="#" onclick="chargerPartiesEnCours();changerAffichage('parties');return false;" style="text-decoration:none;">&gt; Parties en cours</a></h2>
			<?php
			//include_once ("newjeux.php");
			//$lesParties = new PartiesEnCours();
			//$lesParties->afficherParties(false);
			?>
			<div id="parties2">Chargement des parties en cours...</div><div id="comm"></div>
			<form method="GET" action="client.html"><h3>Aller dans une partie non affichée</h3>
				<label>Num&eacute;ro partie :</label><input type="text" name="p" value="0000000" onfocus="if (this.value='0000000') this.value='';" /><br />
				<label>Num&eacute;ro du joueur :</label><select name="j"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option></select>
				<br /><br />
				<input type="submit" class="btn" value="Chercher la partie" title="clique ici" />
			</form>
			<br />
			<a href="#" onclick="changerAffichage('admini');return false;" style="text-decoration:none;">&gt; Administration</a><form style="display:none;" id='admini' action="admin.php" method="GET"><input type="text" name="pw" /><input type="submit" class="btn" value="Aller à l'administration" /></form>
		</div>

		<script type="text/javascript">var debut=true;</script>
		<div id="creation" class="onglet" style="width: 450px; display: none">
			<h2><a href="#" onclick="changerAffichage('creation');return false;" style="text-decoration:none;">&gt; Cr&eacute;ation d'une partie</a></h2>
			<iframe style="display:none;" id="framecreation" name="framecreation" src="" height="90" width="200" FRAMEBORDER=0 scrolling="no" onload="if (!debut) {document.getElementById('statuscreation').style.display='none';document.getElementById('parties').style.display='block';chargerPartiesEnCours();} else debut=false;"></iframe>
			<div id="statuscreation" style="display:none;">Création de la partie en cours...</div>		
			<form action="creajeu.php" method="POST" name="cre" target="framecreation" onsubmit="document.getElementById('statuscreation').style.display='block';changerAffichage('creation');">
			<?php
			addSelectOption(
			array("text" => "Nombre de joueurs",
				"idname" => "nbJoueurs",
				"options" => $array_count,
				"callback" => "updateNumberPlayers()"
			));

			foreach($array_count as $i) {
			echo "<div id=divname".$i." style=\"border: 1px dotted #CCC; width: 400px; clear: both; padding: 5px; margin: 5px 0px 5px 0px\">\n";
			echo "<table><tr><td>";
			echo '<label>Nom :</label><input type=text id="no'.$i.'" name="nomJoueur'.$i.'" value="Joueur'.$i.'" onfocus="if (this.value.indexOf(\'Joueur\') != -1) this.value=\'\';" style="background-color:#0000FF"><br />';
			echo '<div id="divias'.$i.'" style="display:inline"><label>Intelligence artificielle :</label><input type="checkbox" name="is_ia'.$i.'" id="is_ia'.$i.'"  onchange="updateIA('.$i.')" />';
			echo '<div id="divia'.$i.'" style="display:none"><label style="clear: none;">&nbsp;&nbsp;Niveau :</label><select type=text id="nivia'.$i.'" name="nivia'.$i.'"><option value=0 selected>0</option><option value=1>1</option><option value=2>2</option></select></div></div>';
			echo '<div id="divmdps'.$i.'" style="display:inline"><label style="clear: none;">&nbsp;&nbsp;Mot de passe :</label><input type="checkbox" name="si_mdp'.$i.'" id="si_mdp'.$i.'"  onchange="updateMotDePasse('.$i.')" />';
			echo '<div id="divmdp'.$i.'" style="display:none"><input type=text id="mdp'.$i.'" name="mdp'.$i.'" value="" /></div></div>';
			echo "<br />";
			addSelectOption(
			array("text" => " Couleur",
				"idname" => "couleur".$i,
				"options" => $color_array,
				"callback" => "changecolor(".$i.")",
				"default_index" => floor(fmod($i*4-1,15)),
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
			<label style="float: right;">Taille :</label></td><td>
			<input type=text id="x" name="x" value="6" style="width:30px">
			<div style="float:left;">x&nbsp;</div>
			<input type=text id="y" name="y" value="6" style="width:30px">
			<input class="btn" type="button" value="." onclick="document.cre.x.value=5;document.cre.y.value=5;" />
			<input class="btn" type="button" value="o" onclick="document.cre.x.value=9;document.cre.y.value=9;" />
			<input class="btn" type="button" value="O" onclick="document.cre.x.value=15;document.cre.y.value=15;" />
			</tr>

			<?php
			//addCheckOption(
			//array("text" => "Châteaux actifs",
			//      "idname" => "opt_chateaux_actifs",
			//      "default" => False));
			addSelectOption(
			array("text" => "Châteaux",
				"idname" => "opt_chateaux_actifs",
				"options" => array("Activés" => 1,
									"Non activés" => 0),
				"default" => 1,
				"table"=>true
			));

			addSelectOption(
			array("text" => "Type bordure",
				"idname" => "opt_type_bords",
				"options" => array("Bloqués" => 1,
									"Non bloquants" => 0,
									"Monde torrique" => 2),
				"default" => 2,
				"table"=>true
			));
			addSelectOption(
			array("text" => "Ajout diagonale ",
				"idname" => "opt_ajout_diagonale",
				"options" => array("On peut cliquer en diagonale" => 1,
									"Uniquement sur les côtés du carré" => 0),
				"default" => 1,
				"table"=>true
			));
			addSelectOption(
			array("text" => "Explosions ",
				"idname" => "opt_explosion_joueur",
				"options" => array("Tous les joueurs sont affectés" => 0,
									"Seulement pour le joueur en cours" => 1),
				"default" => 0,
				"table"=>true
			));
			addSelectOption(
			array("text" => "Decor",
				"idname" => "opt_avec_decor",
				"options" => array("Seulement stable" => 0,
									"Parsemé" => 1,
									"Parsemé" => 2,
									"Dense" => 3),
				"default_index" => 0,
				"table"=>true
			));
			addSelectOption(
			array("text" => "Visibilité de la partie ",
				"idname" => "opt_partie_cachee",
				"options" => array("Cachée" => 1,
									"Visible" => 0),
				"callback" => "if (this.value=='1') document.cre.opt_attente_joueurs.value='0';",
				"default" => 0,
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
			<label style="float: right;">Profondeur de jeu :</label></td><td><input type=text id="opt_profondeur_jeu" name="opt_profondeur_jeu" value="100" style="width:35px" />
			<input class="btn" type="button" value="&infin;" onclick="document.cre.opt_profondeur_jeu.value=100;" />
			<input class="btn" type="button" value="&#8635;" onclick="document.cre.opt_profondeur_jeu.value=parseInt(document.cre.x.value)+parseInt(document.cre.y.value);" />
			<input class="btn" type="button" value="&#8645;" onclick="document.cre.opt_profondeur_jeu.value=Math.floor((parseInt(document.cre.x.value)+parseInt(document.cre.y.value))/2);" />
			</td></tr>
			<tr><td style="text-align:center;" colspan=2>
			<input type="submit" class="btn" name="Envoi" value="Créer une partie !" title="Clique ici pour créer la partie avec les options actuelles" /> 
			<input type="submit" class="btn" name="Envoi" value="Lancer une partie solo" title="Clique ici pour créer la partie avec les options actuelles et le premier joueur humain"
				onclick="for(var i=2;i<=parseInt(document.cre.nbJoueurs.value);i++){eval('document.cre.is_ia'+i).checked=true;updateIA(i);eval('document.cre.nivia'+i).value=Math.floor(3*Math.random());document.cre.opt_partie_cachee.value=1;} return confirm('Continuer avec les options actuelles ?');" /> 
			</td></tr>
			</table>
			</form>
		</div>		
		<div id="regles" class="onglet" style="width: 600px; display: none;">
			<h2><a href="#" onclick="changerAffichage('regles');return false;" style="text-decoration:none;">&gt; Règles</a></h2>
			<h3>Introduction</h3>
			<?php echo $game_name;?> est un jeu hautement instable où vous devez lutter pour la survie de votre colonie de cellules sans cesse grandissante. C'est la dure loi de l'évolution : seuls les plus forts gagneront cette course pour la Vie !

			<h3>Objectif</h3>
			Votre colonie doit finir seule sur le plateau de jeu.

			<h3>D&eacute;roulement</h3>
			Chaque joueur à son tour ajoute une cellule à lui dans une case qui lui appartient ou qui lui est proche<sup><a href="#" onclick="changerAffichage('option4expl','inline');return false;" style="text-decoration:none;">&gt;</a><span id="option4expl" style="display:none;">Options changeables dans "Ajout diagonal"</span></sup>. Puis cette action peut générer des réactions en chaine selon la règle suivante : si le nombre C de cellules dans une case est supérieur ou égal au nombre N de cases autour de cette case (situées en croix), N cellules de cette case vont aller chacune dans une case différente autour (évènement nommé ci-après "explosion"), et cela jusqu'à ce que le jeu redevienne stable<sup><a href="#" onclick="changerAffichage('option7expl','inline');return false;" style="text-decoration:none;">&gt;</a><span id="option7expl" style="display:none;">Option changeable dans "Profondeur de jeu"</span></sup>.<br />
			Si lors d'une explosion, une de vos cellules arrive dans une case contrôlée par un autre joueur, les cellules de cette case deviennent les vôtres.
			<h4><img style="vertical-align:bottom;" src="images/image.php?n=10&d=0&h=1&type=atome" />
			<img style="vertical-align:bottom;" src="images/image.php?n=10&d=0&h=1&type=cellule" />
			<img style="vertical-align:bottom;" src="images/image.php?n=10&d=0&h=1&type=mediev" /> Les membranes ou chateaux<sup><a href="#" onclick="changerAffichage('option7expl','inline');return false;" style="text-decoration:none;">&gt;</a><span id="option7expl" style="display:none;">Option changeable dans "Chateaux"</span></sup></h4>
			L'exception à cette règle d'explosion est lorsque vous tentez de créer quelque chose de plus solidaire avec vos cellules. Pour cela, changez le mode d'addition en mode de création de chateau lors de votre tour de jeu. La cellule que vous créerez sera la messagère et organisera le début de cette membrane avec les autres cellules présentes dans la case. Toute cellule ensuite ajoutée par vous normalement ou par explosion viendra grandir et solidifier l'ensemble.<br />
			Si la membrane est attaquée (par explosion d'un autre joueur), et que les cellules la composant sont trop peu nombreuses (nombre inférieur ou égal à 9), elles se désolidarisent et de plus appartiennent au joueur attaquant. Attention aux réactions en chaine ! Par contre, si elles sont fortes (au moins 10), alors le joueur attaquant perd une cellule et vous aussi, sans que le reste soit affecté.<br />
			Pour désolidariser par vous-même une de vos membranes, il suffit de vous remettre dans le mode de création/destruction de membrane, et d'envoyer une cellule faire le travail. Attention aux réactions en chaine !

			<h3>Effet du décor</h3>
			Il y a 4 types de terrain :
			<dl>
			<dt><img style="vertical-align:bottom;" src="images/image.php?n=0&d=0&type=atome" />
			<img style="vertical-align:bottom;" src="images/image.php?n=0&d=0&type=cellule" />
			<img style="vertical-align:bottom;" src="images/image.php?n=0&d=0&type=mediev" /> Stable </dt>
			<dd>Terrain de base du jeu. Une membrane ne peut être construite que sur ce type de terrain.</dd>

			<dt><img style="vertical-align:bottom;" src="images/image.php?n=0&d=1&type=atome" />
			<img style="vertical-align:bottom;" src="images/image.php?n=0&d=1&type=cellule" />
			<img style="vertical-align:bottom;" src="images/image.php?n=0&d=1&type=mediev" /> Glace </dt>
			<dd>Un endroit plus froid est moins propice au développement de la vie. Vous ne pouvez pas y envoyer de cellule si elle y sera seule, et ni si ensuite elle doit repartir de suite (explosion juste après). Dans ces cas, la seule manière de conquérir une telle case sera par les explosions des cellules d'à côté (au moins 2 explosions, car les cellules ont tendance à mourrir en arrivant sur un endroit froid).</dd>
				
				<dt><img style="vertical-align:bottom;" src="images/image.php?n=0&d=2&type=atome" />
				<img style="vertical-align:bottom;" src="images/image.php?n=0&d=2&type=cellule" />
				<img style="vertical-align:bottom;" src="images/image.php?n=0&d=2&type=mediev" /> Point chaud </dt>
				<dd>Un endroit plus chaud est meilleur pour le développement des cellules. Lorsqu'une cellule arrive sur une telle case, elle se dédouble (une fois par tour de joueur).</dd>

			<dt><img style="vertical-align:bottom;" src="images/image.php?n=0&d=3&type=atome" />
			<img style="vertical-align:bottom;" src="images/image.php?n=0&d=3&type=cellule" />
			<img style="vertical-align:bottom;" src="images/image.php?n=0&d=3&type=mediev" /> Obstacle </dt>
			<dd>Les cellules ne peuvent s'y développer. Ce sera donc une cellule de moins dans la limite de population sans explosion des cases d'à-côté.</dd>
			</dl>
		</div>
	</div> 
	<div id="footer"> 
		<!--div class="side"> 
			<h2>Recherche</h2> 
			<form method="post" action=""> 
				<div> 
				<input type="text" class="champ"  /> 
				<input type="submit" class="recherche" value="" /> 
				</div> 
			</form>  
		</div> 
		<!--div class="side"> 
			<h2>Petit texte</h2> 
			<p></p> 
		</div--> 
		<!--div class="side"> 
			<h2>Récents</h2> 
			<ul> 
				<li><a href="">Home</a></li> 
			</ul> 
			<h2>Archives</h2> 
			<ul> 
				<li><a href="">Home</a></li> 
			</ul> 
		</div-->
		<div id="copy"><small>&copy; C&eacute;dric, Mika&euml;l & Erwin Mayer 2009 <!--Design inspired by Zwatla-->| <a href="index.php" style="text-decoration:none;">Retour &agrave; l'accueil</a></small></div>
	</div> 
</div> 
</body>
</html>