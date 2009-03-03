<?php
/*** editeur de campagne ***/
/*** paramètres GET :
	c numéro de la campagne
	m numéro de la mission

	save	Enregistre la partie sous le bon format de fichier
		paramètre POST : la partie XML est sous "fichier"
		**/

include_once("../fonctions.inc");

$type_graphisme = isset($_GET["type"])?$_GET["type"]:"atome";

$messagePHP = "";
$fichierExistant = false;
if (array_key_exists("c",$_GET) && array_key_exists("m",$_GET)){
	$fichierExistant = "xaop".$_GET["c"].$_GET["m"].".lvl";
	$fichierExistant = (file_exists($fichierExistant) || array_key_exists("save",$_GET)?$fichierExistant:false);
	if (!$fichierExistant) $messagePHP .= "Fichier inconnu";
}

if (array_key_exists("save",$_GET) && array_key_exists("fichier",$_POST)){//sauvegarde de fichier
	$fi = fopen($fichierExistant,"w");
	fwrite($fi,$_POST["fichier"]);
	fclose($fi);
	$messagePHP .= $fichierExistant." cr&eacute;&eacute;";
}

$max_joueurs = 8;
for($i=1; $i<=$max_joueurs; $i++) {
	$array_count[$i] = $i;
}

?><html>
<head>
<title>Editeur de campagne</title>
<!--link rel="stylesheet" type="text/css" href="../css/style.css" /--> 
<script type="text/javascript" src="../clientclasses.js"></script>
<script type="text/javascript" src="md5.js"></script>
<script type="text/javascript">

var partie = false;
var md5mdpIA = "<?php echo md5mdpIA; ?>";

var current_decor = 0; //Utilisé par la palette
var default_decor = 0; //idem

var current_joueur = 0; //Utilisé par choix_joueur
var default_joueur = 0; //idem

function demarrer(){
	//chargement de la partie de base
	lectXML = new lecteurXML();
	var chargementOK = lectXML.chargeFichier("<?php echo ($fichierExistant?$fichierExistant:"base.xml"); ?>");
	if (!chargementOK) {
		alert("Probl&egrave;me de chargement de fichier");
		return false;
	}
	//alert(lectXML.asXML());
	Xpartie = lectXML.doc.getElementsByTagName("partie");
	Xpartie = Xpartie[0];
	partie = (new Partie()).fromXML(Xpartie);
	
	//alert(partie.joueur[2]);
	
	chargerLesDiv();
	chargerPalette();
	ChargerChoixJoueur();
}

function finaliser(){//appelé avant l'envoi de la partie à l'enregistreur
	partie.tableauJeu.metsLesMax();
	
	// infos de campagne
	partie.histoire = document.camp.histoire.value;
	partie.params.setAttribute("c",document.camp.campagne.value);
	partie.params.setAttribute("m",document.camp.mission.value);
	partie.params.setAttribute("titre",document.camp.titre.value);
	partie.params.setAttribute("infosucces",document.camp.infosucces.value);
	partie.params.setAttribute("suivante",document.camp.missionsuivante.value);
	
	document.sauv.fichier.value=partie.toXML().asXML();
	document.getElementById("resultat").value = document.sauv.fichier.value;
	document.getElementById("resultat").style.display = "block";
	
}

var mode="ajoutercellule";//mode d'action quand on clique sur une case
//différents modes : ajoutercellule enlevercellule setjoueurN setdecorD setderniereactionN clicchateau

function action(x, y){//fonction appelée quand on clique sur une case
	//var nb = parseInt(document.modes.nb.value);
	var nb = current_joueur;
	//En attendant de faire mieux...
	//parseInt(mode.substring(mode.length-1,mode.length));
	switch(mode) {//mode.substring(0,mode.length-1)){
		case "ajoutercellule":partie.tableauJeu.getCase(x,y).addCellules(1);
			if( nb != 0) partie.tableauJeu.getCase(x,y).setJoueur( nb);
			break;
		case "enlevercellule":partie.tableauJeu.getCase(x,y).remCellules(1);break;
		case "setjoueur":partie.tableauJeu.getCase(x,y).setJoueur( nb);break;
		case "setdecor":changeCase(x, y);break;//partie.tableauJeu.getCase(x,y).setDecor(2);break;
		case "setderniereaction":partie.joueur[ nb].derniereAction.ouX = x;partie.joueur[ nb].derniereAction.ouY = y;break;
		case "clicchateau":partie.tableauJeu.getCase(x,y).clicChateau();break;
		case "retrecir":partie.tableauJeu.nouveau(x+1,y+1);document.options.x.value=x;document.options.y.value=y;break;
	}
	afficherPlateau();
}

function changeCase(x, y) {
	var decor_avant = partie.tableauJeu.getCase(x, y).getDecor()
	var decor_nouveau = 0
	if(decor_avant == current_decor) {
		decor_nouveau = default_decor;
	} else {
		default_decor = decor_avant;
		decor_nouveau = current_decor;
	}
	partie.tableauJeu.getCase(x, y).setDecor(decor_nouveau);
}


function chargerLesDiv(seulementJoueurs){//charge l'ensemble des div de l'éditeur
	seulementJoueurs = (typeof seulementJoueurs == "undefined"?false:seulementJoueurs);
  if (!seulementJoueurs){
	//plateau
	afficherPlateau();
	//options
	document.options.opt_chateaux_actifs.value=(partie.options.yaPermissionChateau()?1:0)
	document.options.opt_profondeur_jeu.value=partie.options.quelleProfondeur();
	document.options.opt_type_bords.value=partie.options.quelTypeBord();
	document.options.opt_ajout_diagonale.value=(partie.options.yaPlacementDiag()?1:0);
	document.options.opt_explosion_joueur.value=(partie.options.yaExplosionJoueur()?1:0);
	document.options.x.value=partie.tableauJeu.tailleX;
	document.options.y.value=partie.tableauJeu.tailleY;

	//optionscampagne
	if (partie.params){
		var params = partie.params;
		document.camp.campagne.value=params.getAttribute("c");
		document.camp.mission.value=params.getAttribute("m");
		document.camp.titre.value=params.getAttribute("titre");
		document.camp.infosucces.value=params.getAttribute("infosucces");
		document.camp.missionsuivante.value=params.getAttribute("suivante");
		document.camp.histoire.value=partie.histoire;
	}
  }
	//joueurs
	chargerPalette()
	document.joueurs.opt_nb_joueurs.value = partie.nbJoueurs;
	for (var j=1;j<=partie.nbJoueurs;j++){
		eval("document.joueurs.nomjoueur"+j).value=partie.joueur[j].nom;
		eval("document.joueurs.couleur"+j).value=partie.joueur[j].couleur;
		eval("document.joueurs.mdp"+j).value=partie.joueur[j].mdp;
		eval("document.joueurs.nivia"+j).value=partie.joueur[j].niveau;
	}
	if (!seulementJoueurs){ //TODO: To verify that this addition does not break anything
		updateNumberPlayers(false);
	}
}

function nomIA(){//génère un nom d'IA
	var syllabes = new Array("kel","gal","mot","juh","syd","fek","péd","van","fort","bel","jol");
	var suffixe = "ia";
	var nbSyl = 1+Math.floor(2*Math.random());
	var nom = "";
	for (var i=0;i<nbSyl;i++)
		nom+=syllabes[Math.floor(syllabes.length*Math.random())];
	nom += suffixe;
	nom = nom.substr(0,1).toUpperCase() + nom.substr(1,nom.length-1);
	return nom;
}
function updateNumberPlayers(agirsurpartie){
	agirsurpartie = (typeof agirsurpartie == "undefined"?true:agirsurpartie);
	partie.nbJoueurs = parseInt(document.joueurs.opt_nb_joueurs.value);
	for(var i = 1; i <= <?php echo $max_joueurs.""; ?>; i++) {
		if(i <= parseInt(document.joueurs.opt_nb_joueurs.value)) {
			document.getElementById("divname"+i).style.display = "block";
			if (!partie.joueur[i]){
				if (agirsurpartie) partie.joueur[i] = new Joueur(nomIA(),eval("document.joueurs.couleur"+i).value,md5mdpIA,1,0);
			}
			changecolor(i);
		} else {
			document.getElementById("divname"+i).style.display = "none";
			if (agirsurpartie) partie.joueur[i] = null;
		}
	}
	chargerLesDiv(true);
}
function changecolor(n) {
	var color = document.getElementById("couleur"+n).value;
	if(color.length==6) {
		document.getElementById("no"+n).style.backgroundColor = "#"+color;
	}
}

function image_case_type(typedecor) {
	return image_case(0, 0, 0, typedecor, 0, 0)
}
function image_case_couleur(nojoueur) {
	return image_case((nojoueur == 0?"D0D0FF":partie.joueur[nojoueur].couleur), 7, 0, 0, 0, 0)
}
function image_case(couleur, cellules, chateau, typedecor, va_exploser, dernier) {
	return "../images/image.php?c="+couleur+"&n="+cellules+"&h="+chateau+"&m="+va_exploser+"&type=<?php echo $type_graphisme;?>&d="+typedecor+"&r="+dernier;
}
function afficherPlateau(){
	var chaineHTML = "";
	for (var y = 0; y<partie.tableauJeu.tailleY;y++){for (var x = 0; x<partie.tableauJeu.tailleX;x++){
		var laCase = partie.tableauJeu.getCase(x,y);
		var cellules = laCase.getCellules();
		var nojoueur = laCase.getJoueur();
		var couleur = (nojoueur == 0?"D0D0FF":partie.joueur[nojoueur].couleur);
		var dernier = (nojoueur == 0?false:partie.joueur[nojoueur].derniereAction.ouX == x && partie.joueur[nojoueur].derniereAction.ouY == y );
		onclick_string = 'onclick="action('+x+','+y+');"';
		onload_string = 'onload="chargementImageJeu('+zonePouvantCharger+');"';
		chaineHTML += "<img width=33 height=33 style=\"vertical-align:bottom;\" title=\""+x+","+y+":"+cellules+"\" alt=\""+x+","+y+":"+cellules+"\" "+onload_string+" src=\"../images/image.php?c="+couleur+"&n="+cellules+"&h="+(laCase.getChateau()?1:0)+"&d="+laCase.getDecor()+"&type=<?php echo $type_graphisme;?>&m="+(laCase.vaExploser()?1:0)+"&r="+(dernier?1:0)+"\" "+onclick_string+" />";
	}
		chaineHTML += "<br />";
	}
	document.getElementById("texteremplacement").style.display = "block";
	RAZZone(zonePouvantCharger);
	document.getElementById("plat"+zonePouvantCharger).innerHTML = chaineHTML;
	AfficherChoixJoueur();
}

var nbImagesChargeesZone = new Array(0,0,0);
var zoneVisible = 1; var zonePouvantCharger = 2;
function RAZZone(zone){	nbImagesChargeesZone[zone] = 0;}
function chargementImageJeu(zone){//appelé quand une image se charge
	nbImagesChargeesZone[zone]++;
	if (nbImagesChargeesZone[zone] == partie.tableauJeu.tailleX*partie.tableauJeu.tailleY){//fin du chargement des images : zone de jeu affichée
		var zone2 = 3-zone;
		document.getElementById("texteremplacement").style.display = "none";
		document.getElementById("plat"+zone2).style.display = "none";
		document.getElementById("plat"+zone).style.display = "block";
		zonePouvantCharger = zone2;
		zoneVisible = zone;
	}
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

// Fonctions palette
function chargerPalette() {
	var paletteHTML = ""
	for(i = 0; i <= 3; i++) {
		paletteHTML += "<img id=\"palette_"+i+"\" width=33 height=33 alt=\"\" src=\""+image_case_type(i)+"\" border=2 onClick=\"select_palette('palette_"+i+"', "+i+")\"/>&nbsp;";
	}
	document.getElementById("palette").innerHTML = paletteHTML;
	select_palette('palette_3', 3);
}

function select_palette(id, i) {
	for(j = 0; j <= 3; j++) {
		if(j != i) document.getElementById("palette_"+j).style.borderColor = "white";
	}
	document.getElementById(id).style.borderColor = "black";
	current_decor = i;
}

// Fonction choix joueur
function id_choix_joueur(i) {
	return "choix_joueur_"+i;
}

function AfficherChoixJoueur() {
	var choix_joueurHTML = ""
	for(i = 0; i <= partie.nbJoueurs; i++) {
		choix_joueurHTML += "<img id=\""+id_choix_joueur(i)+"\" width=33 height=33 alt=\"\" src=\""+image_case_couleur(i)+"\" border=2 onClick=\"select_choix_joueur("+i+")\"/>&nbsp;";
	}
	document.getElementById("choix_joueur").innerHTML = choix_joueurHTML;
	select_choix_joueur(current_joueur);
}

function ChargerChoixJoueur() {
	current_joueur = 0;
	AfficherChoixJoueur();
}

function select_choix_joueur(i) {
	if(document.modes.m.value == 'setderniereaction' && i==0) i = 1;
	for(j = 0; j <= partie.nbJoueurs; j++) {
		if(j != i) document.getElementById(id_choix_joueur(j)).style.borderColor = "white";
	}
	document.getElementById(id_choix_joueur(i)).style.borderColor = "black";
	current_joueur = i;
}

</script>
</head>
<body onload="demarrer();">
<div id="lesdiv" style="color:#FFF;background-color:#000;position:absolute;top:0;right:0;">
<?php echo $messagePHP; ?>
	<a href="#" id="menudivplateau" onclick="changerAffichage('divplateau')" style="text-decoration:none;color:#FFF;font-weight:bold;">Plateau</a>
	| <a href="#" id="menudivjoueurs" onclick="changerAffichage('divjoueurs')" style="text-decoration:none;color:#FFF;font-weight:bold;">Joueurs</a>
	| <a href="#" id="menudivoptions" onclick="changerAffichage('divoptions')" style="text-decoration:none;color:#AFA439;font-weight:bold;">Options</a>
	| <a href="#" id="menudivoptionscampagne" onclick="changerAffichage('divoptionscampagne')" style="text-decoration:none;color:#FFF;font-weight:bold;">Campagne</a>
	| <a href="#" id="menudivautres" onclick="changerAffichage('divautres')" style="text-decoration:none;color:#AFA439;font-weight:bold;">Autres</a>
</div>
<div id="divplateau" class="ccontent" style="display:inline;">
	<div id="plat1"></div>
	<div id="plat2"></div>
	<div id="texteremplacement">Chargement du plateau...</div>
	<div id="modes" style="align:center;"><form name="modes"><!--//différents modes : ajoutercellule enlevercellule setjoueurN setdecorD setderniereactionN clicchateau-->
<script type="text/javascript">
function onchange_mode(sel) {
	//if (sel.value=='setjoueur' || sel.value=='setderniereaction') {
		//document.modes.nb.value=1;
		//document.modes.nb.style.display='inline';
	//	mode=sel.value;//+document.modes.nb.value;
	//} else {
		//document.modes.nb.style.display='none';
		mode=sel.value;
	//}
	document.getElementById("palette").style.display = (sel.value=='setdecor') ? 'inline':'none';
	document.getElementById("choix_joueur").style.display = (sel.value=='ajoutercellule' || sel.value=='setjoueur' || sel.value=='setderniereaction') ? 'inline':'none';
	select_choix_joueur(current_joueur); //Pour être sûr que le joueur sélectionné est bien valide.
}
/*function onchange_nb(sel) {
	var unmin = document.modes.m.value=='setderniereaction' ? 1 : 0;
	var unmax = (document.modes.m.value=='setjoueur'||document.modes.m.value=='setderniereaction'?partie.nbJoueurs:3);
	sel.value=Math.min(unmax,Math.max(parseInt(sel.value),unmin));
	mode = document.modes.m.value;
}*/
</script>
		Mode : <select style="display:inline;" name="m" onchange="onchange_mode(this)">
			<option value="ajoutercellule">Ajouter une cellule</option>
			<option value="enlevercellule">Enlever une cellule</option>
			<option value="setjoueur">Mettre joueur</option>
			<option value="setdecor">Mettre d&eacute;cor</option>
			<option value="setderniereaction">Fixer derni&egrave;re action joueur</option>
			<option value="clicchateau">Activer/désactiver chateau</option>
			<option value="retrecir">R&eacute;tr&eacute;cir le plateau</option>
		</select>
		<!--select name="nb" style="display:none;" onchange="onchange_nb(this)">
			<option value=0>0</option>
			<option value=1>1</option>
			<option value=2>2</option>
			<option value=3>3</option>
			<option value=4>4</option>
			<option value=5>5</option>
			<option value=6>6</option>
			<option value=7>7</option>
			<option value=8>8</option>
		</select-->
	</form>
		<div id="palette" style="display:none">
		</div>
		<div id="choix_joueur" style="display:inline">
		</div>
	</div>
</div>
<div id="divjoueurs" class="ccontent" style="display:inline;"><form name="joueurs">
<?php
			addSelectOption(
			array("text" => "Nombre de joueurs",
				"idname" => "opt_nb_joueurs",
				"options" => $array_count,
				"default" => 2,
				"callback" => "this.value=Math.max(parseInt(this.value),2);updateNumberPlayers()"
			));

			foreach($array_count as $i) {
			echo "<div id=\"divname".$i."\" style=\"display:none;background-color:#FFFFFF;border: 1px dotted #CCC; width: 400px; clear: both; padding: 5px; margin: 5px 0px 5px 0px\">\n";
			echo "<table><tr><td>";
			echo '<label>Nom : </label><input type=text id="no'.$i.'" name="nomjoueur'.$i.'" value="Joueur'.$i.'" onfocus="if (this.value.indexOf(\'Joueur\') != -1) this.value=\'\';" style="background-color:#0000FF"><br />';
			if ($i == 1) echo '<div id="divias'.$i.'" style="display:inline"><label>Joueur humain</label><input style="display:none;" type="checkbox" name="is_ia'.$i.'" id="is_ia'.$i.'"  onchange="updateIA('.$i.')" />';
			else echo '<div id="divias'.$i.'" style="display:inline"><label>Intelligence artificielle</label><input style="display:none;" type="checkbox" name="is_ia'.$i.'" id="is_ia'.$i.'" checked  onchange="updateIA('.$i.')" />';
			if ($i == 1) echo '<div id="divia'.$i.'" style="display:none"><label style="clear: none;">&nbsp;&nbsp;Niveau :</label><select type=text id="nivia'.$i.'" name="nivia'.$i.'"><option value=0 selected>0</option><option value=1>1</option><option value=2>2</option></select></div></div>';
			else echo '<div id="divia'.$i.'" style="display:inline"><label style="clear: none;">&nbsp;-&nbsp;Niveau : </label><select type=text id="nivia'.$i.'" name="nivia'.$i.'" onchange="partie.joueur['.$i.'].niveau=parseInt(this.value);"><option value=0 selected>0</option><option value=1>1</option><option value=2>2</option></select></div></div>';
			echo '<div id="divmdps'.$i.'" style="display:none"><label style="clear: none;">&nbsp;&nbsp;Mot de passe :</label><input type="checkbox" name="si_mdp'.$i.'" id="si_mdp'.$i.'"  onchange="updateMotDePasse('.$i.')" /></div>';
			if ($i == 1) echo '<div id="divmdp'.$i.'" style="display:inline"> - Mot de passe : <input type=text id="mdp'.$i.'" name="mdp'.$i.'" value="" onchange="if (this.value.length > 3) this.value = md5(this.value); partie.joueur['.$i.'].mdp = this.value;" /></div>';
			else echo '<div id="divmdp'.$i.'" style="display:none"><input type=text id="mdp'.$i.'" name="mdp'.$i.'" value="'.md5mdpIA.'" /></div></div>';
			echo "<br />";
			addSelectOption(
			array("text" => "Couleur",
				"idname" => "couleur".$i,
				"options" => $color_array,
				"callback" => "changecolor(".$i.");partie.joueur[".$i."].couleur=this.value;afficherPlateau();",
				"default_index" => floor(fmod($i*4-1,15)),
				"color" => True
			));
			echo "</td></tr></table>";
			echo "</div>\n";
			}
			?>
</form></div>
<div id="divoptions" class="ccontent" style="display:none;"><form name="options"><table>
	<tr><td style="text-align:right;">
			<label style="float: right;">Taille :</label></td><td>
			<div style="float:left;">
			<input type=text id="x" name="x" value="6" style="width:30px">
			x&nbsp;
			<input type=text id="y" name="y" value="6" style="width:30px">
			<input type="button" value="Recréer" onclick="partie.tableauJeu.nouveau(parseInt(document.options.x.value),parseInt(document.options.y.value));afficherPlateau();" />
			</div></td></tr>
<?php
			addSelectOption(
			array("text" => "Châteaux",
				"idname" => "opt_chateaux_actifs",
				"options" => array("Activés" => 1,
									"Non activés" => 0),
				"default" => 1,
				"callback" => "partie.options.setPermissionChateau(parseInt(this.value));",
				"table"=>true
			));

			addSelectOption(
			array("text" => "Type bordure",
				"idname" => "opt_type_bords",
				"options" => array("Bloqués" => 1,
									"Non bloquants" => 0,
									"Monde torrique" => 2),
				"default" => 2,
				"callback" => "partie.options.setTypeBord(parseInt(this.value));",
				"table"=>true
			));
			addSelectOption(
			array("text" => "Ajout diagonale",
				"idname" => "opt_ajout_diagonale",
				"options" => array("On peut cliquer en diagonale" => 1,
									"Uniquement sur les côtés du carré" => 0),
				"default" => 1,
				"callback" => "partie.options.setPlacementDiag(parseInt(this.value));",
				"table"=>true
			));
			addSelectOption(
			array("text" => "Explosions ",
				"idname" => "opt_explosion_joueur",
				"options" => array("Tous les joueurs sont affectés" => 0,
									"Seulement pour le joueur en cours" => 1),
				"default_index" => 0,
				"callback" => "partie.options.setExplosionJoueur(parseInt(this.value));",
				"table"=>true
			));

?>
	<tr><td style="text-align:right;">
			<label style="float: right;">Profondeur de jeu :</label></td><td>
			<input class="btno" type="button" value="&infin;" title="Aller au bout des explosions" onclick="document.options.opt_profondeur_jeu.value=100;partie.options.setProfondeur(parseInt(document.options.opt_profondeur_jeu.value));" />
			<input class="btno" type="button" value="&#8635;" title="Les explosions peuvent traverser le plateau dans 2 dimensions" onclick="document.options.opt_profondeur_jeu.value=parseInt(document.options.x.value)+parseInt(document.options.y.value);partie.options.setProfondeur(parseInt(document.options.opt_profondeur_jeu.value));" />
			<input class="btno" type="button" value="&#8645;" title="Les explosions peuvent traverser le plateau en verticla ou en horizontal" onclick="document.options.opt_profondeur_jeu.value=Math.floor((parseInt(document.options.x.value)+parseInt(document.options.y.value))/2);partie.options.setProfondeur(parseInt(document.options.opt_profondeur_jeu.value));" />
			<input type=text id="opt_profondeur_jeu" name="opt_profondeur_jeu" value="100" style="width:35px" onchange="partie.options.setProfondeur(parseInt(this.value));" />
			</td></tr>
	<tr><td>
</table></form></div>
<div id="divoptionscampagne" class="ccontent" style="display:inline;">
	<form name="camp">
	Campagne/mission : <input type="text" name="campagne" value="<?php echo (array_key_exists("c",$_GET)?$_GET["c"]:0); ?>" style="width:30px" /> / 
	<input type="text" name="mission" value="<?php echo (array_key_exists("m",$_GET)?$_GET["m"]:"0000"); ?>" style="width:60px" /> ->
	<a style="text-decoration:none;" title="Cliquer ici pour accéder à la mission suivante" href="" onclick="if (document.camp.missionsuivante.value != 'fin') this.href='editeurcampagnes.php?c='+document.camp.campagne.value+'m='+document.camp.missionsuivante.value;"> mission suivante</a> : 
	<input type="text" name="missionsuivante" value="fin" style="width:60px" /><br />
	<input type="text" name="titre" value="Titre de la mission" onfocus="if (this.value=='Titre de la mission') this.value='';" /><br />
	<input name="infosucces" value="Texte en cas de succ&egrave;s" onfocus="if (this.value.indexOf('Texte')==0) this.value='';" /><br />
	<textarea name="histoire" onfocus="if (this.value.indexOf('Histoire')==0) this.value='';" >Histoire</textarea>
	</form>
</div>
<div id="divautres" class="ccontent"  style="diplay:none;">
<form name="sauv" method="POST" action="editeurcampagnes.php">
<input type="submit" class="btn" value="Recharger" onclick="document.sauv.action='editeurcampagnes.php?c='+document.camp.campagne.value+'&m='+document.camp.mission.value;" />
<input type="hidden" value="" name="fichier" />
<input type="button" class="btn" value="Enregistrer" onclick="document.sauv.action='editeurcampagnes.php?save=1&c='+document.camp.campagne.value+'&m='+document.camp.mission.value;finaliser();document.sauv.submit();" /> <!--//-->
</form>
<form name="charg" method="GET" action="editeurcampagnes.php"><!-- style="display:none;"-->
Charger une mission existante : <input type="text" name="c" value="<?php echo (array_key_exists("c",$_GET)?$_GET["c"]:0); ?>" style="width:30px" /> / 
	<input type="text" name="m" value="<?php echo (array_key_exists("m",$_GET)?$_GET["m"]:"0000"); ?>" style="width:60px" />
<input class="btn" type="submit" value="Charger !">
</form>
</div>
<textarea id="resultat" style="<?php if (!array_key_exists("fichier",$_POST)) echo "display:none;";?>"><?php echo $_POST["fichier"]; ?></textarea>
</body>
</html>