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

function addSelectOption($arrayOptions) {
  $text = "";
  $idname = "";
  $options = "";
  $callback = "";
  $default = 1;
  $table = False;
  $color = False;
  foreach($arrayOptions as $key => $value) {
    switch($key) {
    case "text": $text = $value; break;
    case "idname": $idname = $value; break;
    case "options": $options = $value; break;//tableau
    case "callback": $callback = $value; break;//fonction
    case "default": $default = $value; break;
    case "color": $color = $value; break;//booleen
    case "table": $table = $value; break;//booleen
    }
  }
  if($table) echo "<tr><td>";
  echo $text.' : ';
  if($table) echo "</td><td>";
  echo '<select name="'.$idname.'" id="'.$idname.'" onChange="'.$callback.'">'."\n";
  $i = 1;
  foreach($options as $value => $text) {
    if(isset($value)) {
      echo "<option";
      if(isset($value)) echo ' value="'.$text.'"';
      if($color) echo ' style="background-color:'.$text.'"';
      if($i == $default) echo ' selected';
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
  array("bleu" => "#0000FF",
        "rouge"  => "#FF0000",
        "vert" => "#00FF00",
        "jaune" => "#FFFF00",
        "orange" => "#FF8000",
        "cyan" => "#00FFFF",
        "violet" => "#FF00FF",
        "emeraude" => "#00FF80",
        "noir" => "#000000",
        "autre..." => "....."));
        

$game_name = "Age Of Paramecia II";
?>

<html>
<head>
<title></title>
<script language="javascript">
function bodyOnLoad() {
  document.cre.nbJoueurs.value = 2
  updateNumberPlayers()
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
    document.getElementById("couleur"+n).style = "background-color:"+color;
  }
}
</script>
</head>
<body onload="bodyOnLoad()">
<form action="creajeu.php" method=POST name=cre>
<h1>Création d'une partie : <?php echo $game_name; ?></h1>
<i>Jeu développé par Cédric Mayer et Mikaël Mayer</i>
<br>
<br>
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
  echo 'Nom : <input type=text id="no'.$i.'" name="nomJoueur'.$i.'" value="Joueur'.$i.'" onfocus=""><br>';
  echo ' intelligence artificielle : <input type="checkbox" name="is_ia'.$i.'" id="is_ia'.$i.'" />';
  echo ' Mot de passe : <input type="checkbox" name="si_mdp'.$i.'" id="si_mdp'.$i.'"  onchange="updateMotDePasse('.$i.')" />';
  echo '<div id="divmdp'.$i.'" style="display:none"><input type=text id="mdp'.$i.'" name="mdp'.$i.'" value="" /></div>';
  echo "<br>";
  addSelectOption(
  array("text" => " Couleur",
      "idname" => "couleur".$i,
      "options" => $color_array,
      "callback" => "changecolor(".$i.")",
      "default" => $i,
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
<tr><td>
Taille : </td><td>
<input type=text id="x" name="x" value="7" style="width:30px">
x
<input type=text id="y" name="y" value="7" style="width:30px">
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
      "default" => 1,
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
      "options" => array("Seulement pour le joueur en cours" => 1,
                         "Tous les joueurs sont affectés" => 0),
      "default" => 1,
      "table"=>true
));
?>
<tr><td>
Profondeur de jeu : </td><td><input type=text id="opt_profondeur_jeu" name="opt_profondeur_jeu" value="100" style="width:35px" />
</td></tr>
</table>

<input type="submit" name="Envoi" value="Créer une partie !" title="Clique ici pour créer la partie avec les options actuelles" /> 
</form>

<h3>Parties en cours</h3>

<?php
afficherParties($fichier_parties);
?>
</body>
</html>