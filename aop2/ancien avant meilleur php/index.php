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

function addSelectOption($arrayOptions) {
  $text = "";
  $idname = "";
  $options = "";
  $callback = "";
  $default = -1;
	$default_index = -1;
  $table = False;
  $color = False;
  foreach($arrayOptions as $key => $value) {
    switch($key) {
    case "text": $text = $value; break;
    case "idname": $idname = $value; break;
    case "options": $options = $value; break;//tableau
    case "callback": $callback = $value; break;//fonction
    case "default": $default = $value; break;
		case "default_index": $default_index = $value; break;
    case "color": $color = $value; break;//booleen
    case "table": $table = $value; break;//booleen
    }
  }
  if($table) echo "<tr><td>";
  echo '<label>'.$text.' :</label>';
  if($table) echo "</td><td>";
  echo '<select name="'.$idname.'" id="'.$idname.'" onChange="'.$callback.'">'."\n";
  $i = 1;
  foreach($options as $value => $text) {
    if(isset($value)) {
      echo "<option";
      if(isset($value)) echo ' value="'.$text.'"';
      if($color) echo ' style="background-color:'.$text.'"';
      if($value == $default or $text == $default or $i == $default_index) echo ' selected';
      echo ">".$value."</option>\n";
    } else {
      echo "<option>".$text."</option>\n";
    }
    $i += 1;
  }
  echo '</select>';
  if($table) echo "</td></tr>";
  else echo '<br/>';
}

function addCheckOption($arrayOptions) {
  $text = "";
  $idname = "";
  $callback = "";
  $default = True;
  foreach($arrayOptions as $key => $value) {
    switch($key) {
    case "text": $text = $value; break;
    case "idname": $idname = $value; break;
    case "callback": $callback = $value; break;
    case "default": $default = $value; break;
    }
  }
  echo $text.' : <input type=checkbox name="'.$idname.'" id="'.$idname.'" onChange="'.$callback.'"';
  if($default) {
    echo ' checked';
  }
  echo ' /><br />'."\n";

}

$color_array = (
  array("Bleu" => "#4040FF",
        "Rouge"  => "#FF0000",
        "Vert" => "#00FF00",
        "Jaune" => "#FFFF00",
        "Orange" => "#FF8000",
        "Cyan" => "#00FFFF",
        "Violet" => "#FF00FF",
        "Emeraude" => "#00FF80",
        "Gris" => "#808080",
        "Autre..." => "....."));
        

$game_name = "Age Of Paramecia II";
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr"> 
<head>
<meta name="Description" content="<?php echo $game_name; ?>" /> 
<meta name="Keywords" content="Jeu, jeu en ligne, age of paramecia, jeu de la vie" /> 
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /> 
<link rel="stylesheet" type="text/css" href="css/style.css" /> 
<title><?php echo $game_name;?></title>
<script language="javascript">
function bodyOnLoad() {
	document.cre.nbJoueurs.value = 2
	updateNumberPlayers()
	cacherTout();
	for(var i = 1; i <= <?php echo $max_joueurs.""; ?>; i++) {
		changecolor(i)
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
}

function changecolor(n) {
	var color = document.getElementById("couleur"+n).value;
	if(color.substr(0, 1)=="#") {
		document.getElementById("no"+n).style.backgroundColor = color;
	}
}

function cacherTout(){
	document.getElementById("parties").style.display = "block";
	document.getElementById("creation").style.display = "none";
	document.getElementById("regles").style.display = "none";
}
function changerAffichage(quoi,comment){
	if (document.getElementById(quoi).style.display == "none")
		document.getElementById(quoi).style.display = (comment?comment:"block");
	else
		document.getElementById(quoi).style.display = "none";
}
</script>
</head>
<body onload="bodyOnLoad()">
 
<div id="site"> 
	<div id="header"> 
		<div id="bigtitle">
			<h1><?php echo $game_name; ?></h1>
			<small>Jeu d�velopp� par C�dric, Mika�l et Erwin Mayer</small>
		</div>
		<div id="menu"> 
			<ul> 
			<li><a href="#" onclick="changerAffichage('parties');return false;" >&or; Parties en cours</a></li>
			<li><a href="#" onclick="changerAffichage('creation');return false;" >&or; Cr&eacute;ation d'une partie</a></li> 
			<li><a href="#" onclick="changerAffichage('regles');return false;" >&or; R�gles</a></li> 
			</ul> 
		</div> 
	</div> 
	<div id="content">
		<div id="parties" class="onglet" style="width: 350px;">
		<h2><a href="" onclick="changerAffichage('parties');return false;" style="text-decoration:none;">&gt; Parties en cours</a></h2>		
			<?php
			afficherParties($fichier_parties);
			?>
			<form method="GET" action="jeu.html">Aller dans une partie non affich�e<br/><br/>
				<label>Num&eacute;ro partie :</label><input type="text" name="p" value="0000000" onfocus="if (this.value='0000000') this.value='';" /><br/>
				<label>Num&eacute;ro du joueur :</label><select name="j"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option></select>
				<br /><br />
				<input type="submit" class="btn" value="Chercher la partie" title="clique ici" />
			</form>
			<br />
			<form action="admin.php" method="GET">
				<input type="text" name="pw" />
				<input type="submit" class="btn" value="Aller � l'administration" />
			</form>
		</div>	
		<div id="creation" class="onglet" style="width: 450px;">
			<h2><a href="" onclick="changerAffichage('creation');return false;" style="text-decoration:none;">&gt; Cr&eacute;ation d'une partie</a></h2>
			<form action="creajeu.php" method=POST name=cre>
			<?php
			addSelectOption(
			array("text" => "Nombre de joueurs",
			      "idname" => "nbJoueurs",
			      "options" => $array_count,
			      "callback" => "updateNumberPlayers()"
			));

			foreach($array_count as $i) {
			  echo "<div id=\"divname".$i."\" style=\"border: 1px dotted #CCC; width: 400px; padding: 5px; margin: 5px 0px 5px 0px\">\n";
			  echo "<table><tr><td>\n";
			  echo '<label>Nom :</label><input type=text id="no'.$i.'" name="nomJoueur'.$i.'" value="Joueur'.$i.'" onfocus="if (this.value.indexOf(\'Joueur\') != -1) this.value=\'\';" style="background-color:#0000FF"><br />';
			  echo '<label>Intelligence artificielle :</label><input type="checkbox" name="is_ia'.$i.'" id="is_ia'.$i.'" />';
			  echo '<label>Mot de passe :</label><input type="checkbox" name="si_mdp'.$i.'" id="si_mdp'.$i.'"  onchange="updateMotDePasse('.$i.')" /><br />';
			  echo '<div id="divmdp'.$i.'" style="display:none"><input type=text id="mdp'.$i.'" name="mdp'.$i.'" value="" /></div>';
			  echo "<br />";
			  addSelectOption(
			  array("text" => "Couleur",
			      "idname" => "couleur".$i,
			      "options" => $color_array,
			      "callback" => "changecolor(".$i.")",
			      "default_index" => $i,
			      "color" => True
			));
			  echo "</td></tr></table>\n";
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
			<tr><td>
			<label>Taille :</label></td><td>
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
			      "default" => 1,
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
			      "default" => 0,
			      "table"=>true
			));
			?>
			<tr><td>
			Profondeur de jeu : </td><td><input type=text id="opt_profondeur_jeu" name="opt_profondeur_jeu" value="100" style="width:35px" />
			</td></tr>
			</table>

			<input type="submit" class="btn" name="Envoi" value="Cr�er une partie !" title="Clique ici pour cr�er la partie avec les options actuelles" /> 
			</form>
		</div>
		<div id="regles" class="onglet" style="width: 600px;">
			<h2><a href="" onclick="changerAffichage('regles');return false;" style="text-decoration:none;">&gt; R�gles</a></h2>
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
				<dd>Un endroit plus chaud est meilleur pour le d�veloppement des cellules. Lorsqu'une cellule arrive sur une telle case, elle se d�double tout de suite.</dd>
			    
				<dt><img style="vertical-align:bottom;" src="images/image.php?n=0&d=3&type=atome" />
				<img style="vertical-align:bottom;" src="images/image.php?n=0&d=3&type=cellule" />
				<img style="vertical-align:bottom;" src="images/image.php?n=0&d=3&type=mediev" /> Obstacle </dt>
				<dd>Les cellules ne peuvent s'y d�velopper. Ce sera donc une cellule de moins dans la limite de population sans explosion des cases d'�-c�t�.</dd>
			</dl>
		</div>
	</div> 
	<div id="footer"> 
		<div class="side"> 
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
		<div class="side"> 
			<h2>Articles r�cents</h2> 
			<ul> 
				<li><a href="http://www.e-news.name/">Home</a></li> 
			</ul> 
			<h2>Archives</h2> 
			<ul> 
				<li><a href="http://www.e-news.name/">Home</a></li> 
			</ul> 
		</div>
		<div id="copy"><small>&copy; C&eacute;dric, Mika&euml;l & Erwin Mayer 2009 <!--Design inspired by Zwatla-->| <a href="index.php" style="text-decoration:none;">Retour &agrave; l'accueil</a></small></div>				 
	</div> 
</div> 
</body>
</html>