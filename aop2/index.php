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
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
<title><?php echo $game_name;?></title>
<meta name="Description" content="<?php echo $game_name; ?>" /> 
<meta name="Keywords" content="Jeu, jeu en ligne, age of paramecia, jeu de la vie" /> 
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> 
<link rel="stylesheet" type="text/css" href="css/style.css" /> 
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
	document.getElementById("parties").style.display = "block"; document.getElementById('menuparties').style.color = "#FFF";
	document.getElementById("creation").style.display = "none";
	document.getElementById("regles").style.display = "none";	
}
function changerAffichage(quoi,comment){
	if (document.getElementById(quoi).style.display == "none" && (comment?comment != "none":true)) {
		document.getElementById(quoi).style.display = (comment?comment:"block");
		if (document.getElementById('menu'+quoi))
			document.getElementById('menu'+quoi).style.color = "#FFF";
	}
	else {
		document.getElementById(quoi).style.display = "none";
		if (document.getElementById('menu'+quoi))
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
			chaineAAfficher += (nExistaitPas?"<b>&#8594;":"")+"Partie n�"+numeroP+" : "; //nbjoueurs+";"+partiesExistantes[numeroP]+";"+
			var listeJoueurs = partie.getElementsByTagName("joueur");
			for (var j=0;j < listeJoueurs.length;j++){
				var joueur = listeJoueurs[j];
				var nom = joueur.getAttribute('nom');
				var numeroJ = joueur.getAttribute('numero');
				var couleur = joueur.getAttribute('couleur');
				var lien = "index.php?comp=client&j="+numeroJ+"&p="+numeroP;
				chaineAAfficher += '<a style="color:black;background-color:#'+couleur+';" href="'+lien+'">'+nom+'</a> ';
			}
			chaineAAfficher += '<span id="action-'+numeroP+'"><input type="button" value="Entrer" onclick="sajouter2(\''+numeroP+'\');" /></span>'+(nExistaitPas?"</b>":"")+'<br />';
		}
		if (partiesCachees){
			chaineAAfficher += partiesCachees+" autres parties cach�es.";
			chaineAAfficher += "<form method=\"GET\" action=\"index.php?comp=client\"><h3>Aller dans une partie non affich�e</h3>\n";
			chaineAAfficher += "<label>Num&eacute;ro partie :</label><input type=\"text\" name=\"p\" value=\"0000000\" onfocus=\"if (this.value='0000000') this.value='';\" /><br />";
			chaineAAfficher += "<label>Num&eacute;ro du joueur :</label><select name=\"j\"><option value=\"1\">1</option><option value=\"2\">2</option><option value=\"3\">3</option><option value=\"4\">4</option><option value=\"5\">5</option><option value=\"6\">6</option><option value=\"7\">7</option><option value=\"8\">8</option><option value=\"9\">9</option></select>";
			chaineAAfficher += "<br /><br />";
			chaineAAfficher += "<input type=\"submit\" class=\"btn\" value=\"Chercher la partie\" title=\"clique ici\" />";
			chaineAAfficher += "</form>";
		}
		document.getElementById("parties2").innerHTML = chaineAAfficher;
		premierLancement = false;
	},
	function (){
		document.getElementById("parties2").innerHTML = "Aucune partie r�pertori�e.";
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
	"saut_ligne" => false,
	"color" => True
),false)); ?>";
	chaineaafficher += "<input style=\"vertical-align:bottom;\" type=\"submit\" value=\"OK\" /></form>";
	document.getElementById("action-"+numeroPartie).innerHTML = chaineaafficher;
}

function metsLesIA(jusqua,niveau,debut){
	debut = (debut==undefined?2:debut);//premier joueur humain
	for(var i=debut;i<=jusqua;i++){
		eval('document.cre.is_ia'+i).checked=true;updateIA(i);
		eval('document.cre.nivia'+i).value=(niveau==undefined || niveau<0?Math.floor(3*Math.random()):niveau);
	}
}
</script>
</head>
<body onload="bodyOnLoad();chargerPartiesEnCours();<? echo ($_GET["comp"]=="client") ?"changerAffichage('parties','none');main();" : "chargerPartiesEnCours();" ?>">
<div id="site"> 
	<div id="header" <? echo ($_GET["comp"]=="client") ?"style=\"height:50px;\"" : "" ?>> 
		<div id="bigtitle" <? echo ($_GET["comp"]=="client") ?"style=\"display:none;\"" : "" ?>>
			<h1><a href="index.php" style="text-decoration:none;"><?php echo $game_name; ?></a></h1>
			<small>Jeu d�velopp� par <a href="http://radon222.free.fr" style="text-decoration:none;">C&eacute;dric</a>, <a href="http://meak.free.fr" style="text-decoration:none;">Mika&euml;l</a> et <a href="http://www.erwinmayer.com" style="text-decoration:none;">Erwin Mayer</a></small>
		</div>
		<div id="menu" <? echo ($_GET["comp"]=="client") ?"style=\"top:10px;\"" : "" ?>> 
			<ul> 
			<li><a id="menuparties" href="#" onclick="chargerPartiesEnCours();changerAffichage('parties');return false;" <?php echo ($_GET['comp'] <> "client")? "style='color: #FFF;'":""; ?> >&or; Parties en cours</a></li>
			<li><a id="menucreation" href="#" onclick="changerAffichage('creation');return false;" >&or; Cr&eacute;ation d'une partie</a></li> 
			<li><a id="menucampagnes" href="#" onclick="changerAffichage('campagnes');return false;" >&or; Campagnes</a></li> 
			<li><a id="menuregles" href="#" onclick="changerAffichage('regles');return false;" >&or; R&egrave;gles</a></li> 
			<!--li><a id="menuclient" href="index.php?comp=client" >&or; Acc&egrave;s &agrave; un jeu</a></li--> 
			<li><a id="menumerci" href="#" onclick="changerAffichage('merci');return false;" >&or; Remerciements</a></li> 
			</ul> 
		</div> 
	</div> 
	
	<div id="content" <? echo ($_GET["comp"]=="client") ?"style=\"top:50px;\"" : "" ?>>
		<div id="client" style="display:<?php echo ($_GET['comp'] == "client")? "visible": "none"; ?>;">
			<?php if ($_GET['comp'] == "client") include("client.php"); ?>
		</div>	
		<div id="parties" class="onglet" style="width: 350px;display:<?php echo ($_GET['comp'] == "client")? "none": "visible"; ?>;">
			<h2><a href="#" onclick="chargerPartiesEnCours();changerAffichage('parties');return false;" style="text-decoration:none;">&gt; Parties en cours</a></h2>
			<?php
			//include_once ("newjeux.php");
			//$lesParties = new PartiesEnCours();
			//$lesParties->afficherParties(false);
			?>
			<div id="parties2">Chargement des parties en cours...</div><div id="comm"></div>
			<form method="GET" action="index.php?comp=client" style="display:none;"><h3>Aller dans une partie non affich�e</h3>
				<label>Num&eacute;ro partie :</label><input type="text" name="p" value="0000000" onfocus="if (this.value='0000000') this.value='';" /><br />
				<label>Num&eacute;ro du joueur :</label><select name="j"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option></select>
				<br /><br />
				<input type="submit" class="btn" value="Chercher la partie" title="clique ici" />
			</form>
			<br />
			<a href="#" onclick="changerAffichage('admini_form');" style="text-decoration:none;">&gt; Administration</a>
			<div id="admini_form"  style="display:none;"><form name="adminform" action="admin.php" method="GET">
				<input type="text" name="pw" /><input type="submit" class="btn" value="Aller &agrave; l'administration" /><br />
				<input type="submit" class="btn" value="Aller � l'&eacute;diteur" onclick="document.adminform.action = 'campagnes/editeurcampagnes.php';"/>
			</form></div>
		</div>

		<script type="text/javascript">var debut=true;</script>
		<div id="creation" class="onglet" style="width: 450px; display: none">
			<h2><a href="#" onclick="changerAffichage('creation');return false;" style="text-decoration:none;">&gt; Cr&eacute;ation d'une partie</a></h2>
			<iframe style="display:none;" id="framecreation" name="framecreation" src="" height="90" width="200" FRAMEBORDER=0 scrolling="no" onload="if (!debut) {document.getElementById('statuscreation').style.display='none';document.getElementById('parties').style.display='block';chargerPartiesEnCours();} else debut=false;"></iframe>
			<div id="statuscreation" style="display:none;">Cr�ation de la partie en cours...</div>		
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
			<div id="options_jeu"></div>
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
			<tr><td style="text-align:right;">Taille :</td><td>
			<input class="btno" type="button" value="." onclick="document.cre.x.value=5;document.cre.y.value=5;" title="petit : 5x5" />
			<input class="btno" type="button" value="o" onclick="document.cre.x.value=9;document.cre.y.value=9;" title="moyen : 9x9" />
			<input class="btno" type="button" value="O" onclick="document.cre.x.value=15;document.cre.y.value=15;" title="grand : 15x15" />
			<input type=text id="x" name="x" value="6" style="width:30px">
			x&nbsp;
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
				"options" => array("Tous les joueurs sont affect�s" => 0,
									"Seulement pour le joueur en cours" => 1),
				"default_index" => 0,
				"table"=>true
			));
			addSelectOption(
			array("text" => "Decor",
				"idname" => "opt_avec_decor",
				"options" => array("Seulement stable" => 0,
									"Parsem�" => 1,
									"Serr�" => 2,
									"Dense" => 3),
				"default_index" => 0,
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
			<label style="float: right;">Profondeur de jeu :</label></td><td>
			<input class="btno" type="button" value="&infin;" title="Aller au bout des explosions" onclick="document.cre.opt_profondeur_jeu.value=100;" />
			<input class="btno" type="button" value="&#8635;" title="Les explosions peuvent traverser le plateau dans 2 dimensions" onclick="document.cre.opt_profondeur_jeu.value=parseInt(document.cre.x.value)+parseInt(document.cre.y.value);" />
			<input class="btno" type="button" value="&#8645;" title="Les explosions peuvent traverser le plateau en verticla ou en horizontal" onclick="document.cre.opt_profondeur_jeu.value=Math.floor((parseInt(document.cre.x.value)+parseInt(document.cre.y.value))/2);" />
			<input type=text id="opt_profondeur_jeu" name="opt_profondeur_jeu" value="100" style="width:35px" />
			</td></tr>
			<tr><td style="text-align:center;" colspan=2>
			<input type="submit" class="btn" name="Envoi" value="Cr�er une partie !" title="Clique ici pour cr�er la partie avec les options actuelles" /> 
			<input type="submit" class="btn" name="Solo" value="Lancer une partie solo" title="Clique ici pour cr�er la partie avec les options actuelles et le premier joueur humain"
				onclick="metsLesIA(parseInt(document.cre.nbJoueurs.value));document.cre.opt_partie_cachee.value=1;return confirm('Continuer avec les options actuelles ?');" /> 

				</td></tr>
			</table>
			<!--center><input type="button" class="btn" onclick="lancerPopupJeuReseau()" value="Lancer une partie r�seau" /></center-->
			</form>
		</div>		
		
<script type="text/javascript"><!--
/** pour lancer le popup de cr�ation de partie r�seau **/
function lancerPopupJeuReseau(){
	var chaineAAfficher = "";
	chaineAAfficher += "<form name='rez'>Nom de l'h&ocirc;te : <input type=\"text\" value=\""+document.cre.nomJoueur1.value+"\" onfocus=\"if (this.value=='"+document.cre.nomJoueur1.value+"') this.value='';\" onchange=\"document.cre.nomJoueur1.value=this.value;\" /> ";
	var couleurDu1 = document.cre.couleur1.value;
	var leCouleurSelect = "<?php echo addslashes(addSelectOption(
array("text" => " couleur de l'h&ocirc;te",
	"idname" => "couleur",
	"options" => $GLOBALS["color_array"],
	"callback" => "document.cre.couleur1.value=this.value;",//"changecolor(".$i.")",
	"default" => "Ilnenaurapas",
	"saut_ligne" => false,
	"table" => false,
	"color" => True
),false)); ?>";
	leCouleurSelect = leCouleurSelect.split("value=\""+couleurDu1+"\"").join("value=\""+couleurDu1+"\" selected");
	chaineAAfficher += leCouleurSelect + "<br />";
	chaineAAfficher += "Nombre d'IA : <select onchange=\"document.cre.nbJoueurs.value=parseInt(this.value)+1;\"><option value=\"1\">1</option><option value=\"2\">2</option><option value=\"3\">3</option><option value=\"4\">4</option></select>" + "<br />";
	// metsLesIA(jusqua,niveau,debut){
	chaineAAfficher += "Nombre d'IA : " + "<br />";
	chaineAAfficher += "<input type=\"button\" class=\"btn\" value=\"Lancer\" onclick=\"document.getElementById('popupreseaucontent').innerHTML='Lancement de la partie en cours...';document.cre.submit();\" /> </form>";
	document.getElementById("popupreseaucontent").innerHTML = chaineAAfficher;
	document.getElementById("popupreseau").style.display = "block";
}


function fermePopupReseau(){
	document.getElementById("popupreseau").style.display="none";
}
//--></script>
<div style="z-index: 99; display: none; position: absolute; left: 0; top: 0; width: 100%; height: 100%" id="popupreseau">
	<table border="0" cellpadding="0" cellspacing="0" style="width: 100%; height: 100%;"><!-- background: url('css/img/footer.jpg')-->
		<tr>
			<td align="center">
				<div style="width: 400px; height: 400px; border: 1px solid #C0C793; background: #FFFFFF">
				<input class="btn" style="align:center;font-size:6px;height:12px;width:12px;padding:0;float:right;right:0;top:0;" value="x" onclick="fermePopupReseau()" />
					<h2>D�marrer une partie r�seau</h2>
					<p id="popupreseaucontent"></p>
				
				
				</div>
			</td>
		</tr>
	</table>
</div>

		<div id="campagnes" class="onglet" style="width: 400px;display:none;">
			<h2><a href="#" onclick="changerAffichage('campagnes');return false;" style="text-decoration:none;">&gt; Campagnes</a></h2>
			<form name="camp" action="creajeucampagne.php" target="framecreation">
				Nom : <input type="text" name="joueur" /> <!--| <?php echo addSelectOption(
array("text" => "Couleur",
	"idname" => "couleur",
	"options" => $GLOBALS["color_array"],
	"callback" => "document.camp.joueur.style.backgroundColor = '#'+this.value;",//"changecolor(".$i.")",
	"table" => false,
	"default_index" => 0,
	"saut_ligne" => false,
	"color" => True
),false); ?>--><br /><input type="hidden" name="couleur" value="0000FF" />
				<input type="hidden" name="m"/><input type="hidden" name="c"/><input type="hidden" name="n" value="0"/>
				<!--input type="hidden" name="noredirection"/-->
				<!-- ajouter les campagnes ici -->
				<input onclick="document.getElementById('framecreation').style.display='block';document.camp.c.value=9;document.camp.m.value='0001';document.camp.submit();" value="Campagne de test" title="Lancer la campagne" type="button" class="btn" />
				<input onclick="document.getElementById('framecreation').style.display='block';document.camp.c.value='v';document.camp.m.value='0001';document.camp.submit();" value="Campagne de la vie (AOP1)" title="Lancer la campagne" type="button" class="btn" />
				<input onclick="document.getElementById('framecreation').style.display='block';document.camp.c.value='e';document.camp.m.value='0001';document.camp.submit();" value="Campagne d'exploration" title="Lancer la campagne" type="button" class="btn" />
				<input onclick="document.getElementById('framecreation').style.display='block';document.camp.c.value=2;document.camp.m.value='0001';document.camp.submit();" value="Campagne de Mika&euml;l" title="Lancer la campagne" type="button" class="btn" />
			</form>
		</div>
		
		<div id="regles" class="onglet" style="width: 600px; display: none;">
			<h2><a href="#" onclick="changerAffichage('regles');return false;" style="text-decoration:none;">&gt; R&egrave;gles</a></h2>
			<h3>Introduction</h3>
			<?php echo $game_name;?> est un jeu hautement instable o� vous devez lutter pour la survie de votre colonie de cellules sans cesse grandissante. C'est la dure loi de l'�volution : seuls les plus forts gagneront cette course pour la Vie !

			<h3>Objectif</h3>
			Votre colonie doit finir seule sur le plateau de jeu.

			<h3>D&eacute;roulement</h3>
			Chaque joueur � son tour ajoute une cellule � lui dans une case qui lui appartient ou qui lui est proche<sup><a href="#" onclick="changerAffichage('option4expl','inline');return false;" style="text-decoration:none;">&gt;</a><span id="option4expl" style="display:none;">Options changeables dans "Ajout diagonal"</span></sup>. Puis cette action peut g�n�rer des r�actions en chaine selon la r�gle suivante : si le nombre C de cellules dans une case est sup�rieur ou �gal au nombre N de cases autour de cette case (situ�es en croix), N cellules de cette case vont aller chacune dans une case diff�rente autour (�v�nement nomm� ci-apr�s "explosion"), et cela jusqu'� ce que le jeu redevienne stable<sup><a href="#" onclick="changerAffichage('option7expl','inline');return false;" style="text-decoration:none;">&gt;</a><span id="option7expl" style="display:none;">Option changeable dans "Profondeur de jeu"</span></sup>.<br />
			Si lors d'une explosion, une de vos cellules arrive dans une case contr�l�e par un autre joueur, les cellules de cette case deviennent les v�tres.
			<h4><img style="vertical-align:bottom;" src="images/image.php?n=10&d=0&h=1&type=atome" />
			<img style="vertical-align:bottom;" src="images/image.php?n=10&d=0&h=1&type=cellule" />
			<img style="vertical-align:bottom;" src="images/image.php?n=10&d=0&h=1&type=mediev" /> Les membranes ou chateaux<sup><a href="#" onclick="changerAffichage('option7expl','inline');return false;" style="text-decoration:none;">&gt;</a><span id="option7expl" style="display:none;">Option changeable dans "Chateaux"</span></sup></h4>
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
		</div>
		<div id="merci" class="onglet" style="width: 300px; display: none;">
			<h2><a href="#" onclick="changerAffichage('merci');return false;" style="text-decoration:none;">&gt; Remerciements</a></h2>
			Pierre Fritsch <i>Concept initial du jeu</i><br />
			Erwin Mayer <i>Style du site</i><br />
			Mika�l Mayer <i>Fonctions PHP->HTML, Index, Campagnes</i><br />
			C�dric Mayer <i>Plein de choses...</i><br />
			Et tous les testeurs...
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
			<h2>R�cents</h2> 
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