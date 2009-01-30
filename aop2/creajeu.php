<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<?php
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
/*
Fichier: creajeu.php
Date: 27/01/2009
Auteurs: Mikaël Mayer / Cédric Mayer
But : crée un jeu
	Dans les noms des joueurs, les espaces et les \n sont remplacés par _
	crée une partie de rien, avec des paramètre envoyés en POST
	crée une partie à partir d'une grille de base
	affiche le n° de la partie créée avec les liens pour les joueurs humains

  Nombre de joueurs
	N°Joueur en cours entre 1 et NbJoueurs
	Nom joueur 1	couleur	option : motdepasse	
	Nom joueur 2	couleur	option : motdepasse	option : estIA?
	Dernière action joueur 1
	Dernière action joueur 2
	Options : chateaux activés ? 1/0
		Profondeur de jeu
		Bord bloqués ?	1/0/2:monde rond
		Ajout diagonale ? 1/0  (peut-on cliquer en diagonale ou seulement à côté ?)
		Explosion slt pour joueur en cours ? 1/0
	Taille X
	Taille y
	Tableau de décor - 0 rien 1 glace 2 chaud 3 obstacle
	Tableau de jeu - +10000 : chateau  +N00 : case au joueur N +XX : nombre de cellules
*/
?>
<html>
<head>
<title>Age of Paramecia II création de partie</title>
</head>
<body>
<?php
include ("fonctions.inc");

$nbJoueurs=0;
$joueurs=array();
$x=0;
$y=0;
$opt_chateaux_actifs=0;
$opt_type_bords=0;
$opt_ajout_diagonale=0;
$opt_explosion_joueur=0;
$opt_profondeur_jeu=0;
$opt_partie_cachee=0;

function loadParameters() {
  Global $nbJoueurs, $joueurs, $x, $y, $opt_chateaux_actifs, $opt_type_bords, $opt_ajout_diagonale, $opt_explosion_joueur,$opt_profondeur_jeu, $opt_partie_cachee;
  if(isset($_POST["nbJoueurs"])) {
    $nbJoueurs = (int)$_POST["nbJoueurs"];
  } else return "Erreur : Nombre de joueurs indéterminé";
  foreach($_POST as $key=>$nom){ 
    if(substr($key,0,9)=="nomJoueur"){
      $indice=substr($key,9); 
      if((int)$indice <= $nbJoueurs and (int)$indice >= 1) {
        $si_mdp = isset($_POST["si_mdp".$indice]);
        $is_ia = isset($_POST["is_ia".$indice]);
        if(!isset($_POST["mdp".$indice])) return "mdp".$indice." is not available";
        if(!isset($_POST["couleur".$indice])) return "couleur".$indice." is not available";
        $mdp = $_POST["mdp".$indice];
        $couleur = $_POST["couleur".$indice];
        $joueurs[$indice] = array("nom" => $nom,
                                  "couleur" => $couleur,
                                  "si_mdp" => $si_mdp,
                                  "is_ia" => $is_ia,
                                  "mdp" => $mdp);
      }
    }
  }
  while(True) {
    if(isset($_POST["x"])) {
      $x = (int)$_POST["x"];
      if($x >= 2) break; // ça veut dire "OK"
    } return "Taille x non fournie";
  }
  while(True) {
    if(isset($_POST["y"])) {
      $y = (int)$_POST["y"];
      if($y >= 2) break; // ça veut dire "OK"
    } return "Taille y non fournie";
  }
  if(isset($_POST["opt_chateaux_actifs"]))
    $opt_chateaux_actifs = (int)$_POST["opt_chateaux_actifs"];
  if(isset($_POST["opt_type_bords"]))
    $opt_type_bords = (int)$_POST["opt_type_bords"];
  if(isset($_POST["opt_ajout_diagonale"]))
    $opt_ajout_diagonale = (int)$_POST["opt_ajout_diagonale"];
  if(isset($_POST["opt_explosion_joueur"]))
    $opt_explosion_joueur = (int)$_POST["opt_explosion_joueur"];
  if(isset($_POST["opt_profondeur_jeu"]))
    $opt_profondeur_jeu = (int)$_POST["opt_profondeur_jeu"];
	if(isset($_POST["opt_partie_cachee"]))
    $opt_partie_cachee = (int)$_POST["opt_partie_cachee"];
  return "";
}

$result = loadParameters();
if($result != "") {
  die($result);
}

function getNomFichier($i) {
  return "aop".$i."bacteries.par";
}

function getNumeroPartie() {
  $i = 0;
  $nomFichier = "";
  do {
    $i = rand(1000000, 9999999);
    $nomFichier = getNomFichier($i);
  } while(file_exists($nomFichier));
  return $i;
}

$numero_partie = getNumeroPartie();
$nomfichier = getNomFichier($numero_partie);

$fh = fopen($nomfichier, 'wb');
//	Nombre de joueurs
fwrite($fh, $nbJoueurs."\n");
//	N°Joueur en cours entre 1 et NbJoueurs
fwrite($fh, rand(1, (int)$nbJoueurs)."\n");
//	Nom joueur 1	couleur	option : motdepasse	
foreach($joueurs as $key => $value) {
  fwrite($fh, $value["nom"]."\t".$value["couleur"]);
  if($value["si_mdp"]) {
    fwrite($fh, "\t".md5($value["mdp"]));
	} else {
		fwrite($fh, "\t0");
	}
	fwrite($fh, "\t".($value["is_ia"]?1:0));
  fwrite($fh, "\n");
}
//0	Dernière action joueur 1 : a x y tour
foreach($joueurs as $key => $value) {
  fwrite($fh, "n\t0\t0\t0\n");
}

//	Options : chateaux activés ? 1/0
//		Profondeur de jeu
//		Bord bloqués ?	1/0/2:monde rond
//		Ajout diagonale ? 1/0  (peut-on cliquer en diagonale ou seulement à côté ?)
//		Explosion slt pour joueur en cours ? 1/0
fwrite($fh, $opt_chateaux_actifs."\n");
fwrite($fh, $opt_profondeur_jeu."\n");
fwrite($fh, $opt_type_bords."\n");
fwrite($fh, $opt_ajout_diagonale."\n");
fwrite($fh, $opt_explosion_joueur."\n");
//	Taille X
//	Taille y
fwrite($fh, $x."\n");
fwrite($fh, $y."\n");
//	Tableau de décor - 0 rien 1 glace 2 chaud 3 obstacle
$x = (int)$x;
$y = (int)$y;
for($i = 1; $i <= $x; $i++) {
  for($j = 1; $j <= $y; $j++) {
    if($j > 1) fwrite($fh, "\t");
    fwrite($fh, "0");
  }
  fwrite($fh, "\n");
}
//  Positionnement des joueurs de manière à minimiser la somme des inverses des distances.
//  Début aléatoire de manire à maximiser les distances.
$pos = array();
for($i = 1; $i <= $nbJoueurs; $i++) {
  $pos[$i] = array(rand(1, $x), rand(1, $y));
  //echo "Joueur ".$i." est sur ".$pos[$i] [0].",".$pos[$i] [1]."<br>";
}
function inverseDistance2($pos1, $pos2) {
  $denom = (pow($pos1[0] - $pos2[0], 2) + pow($pos1[1] - $pos2[1], 2));
  if($denom == 0) return 100000;
  return 1/$denom;
}
function adjust($value, $max, $min = 1) {
  if($value>$max) $value=$max;
  if($value<$min) $value=$min;
  return $value;
}
function stochasticPositionning($pos, $iterations) {
  Global $nbJoueurs, $x, $y;
  $distance_map = array();
  
  for($i = 1; $i <= $nbJoueurs; $i++) {
    for($j = 1; $j <= $nbJoueurs; $j++) {
      if($i != $j) {
        $distance_map[$i][$j] = inverseDistance2($pos[$i], $pos[$j]);
      }
    }
  }
  //print_r ($distance_map);
  
  for($k = 1; $k <= $iterations; $k++) {
    $joueur_to_move = rand(1, $nbJoueurs);
    $pos_current = $pos[$joueur_to_move];
    $new_pos = array(adjust($pos_current[0]+rand(-1, 1), $x), adjust($pos_current[1]+rand(-1, 1), $y));
    $sum_before = 0;
    $sum_after = 0;
    $new_partial_distance_map = array();
    for($j = 1; $j <= $nbJoueurs; $j++) {
      $new_partial_distance_map[$j] = inverseDistance2($new_pos, $pos[$j]);
      if($j != $joueur_to_move) {
        $sum_before += $distance_map[$joueur_to_move][$j];
        $sum_after += $new_partial_distance_map[$j];
      }
    }
    $diff = $sum_after - $sum_before;
    if($diff < 0) {
      //echo "<br>Réussi joueur ".$joueur_to_move." de ".$pos_current[0].",".$pos_current[1]." vers ".$new_pos[0].",".$new_pos[1]."(After=".$sum_after.", before=".$sum_before.")";
      for($j = 1; $j <= $nbJoueurs; $j++) {
        if($j != $joueur_to_move) {
          $distance_map[$joueur_to_move][$j] = $new_partial_distance_map[$j];
          $distance_map[$j][$joueur_to_move] = $new_partial_distance_map[$j];
        }
      }
      $pos[$joueur_to_move] = $new_pos;
    } else {
      //echo "<br>Déplacement Raté joueur ".$joueur_to_move." de ".$pos_current[0].",".$pos_current[1]." vers ".$new_pos[0].",".$new_pos[1]."(After=".$sum_after.", before=".$sum_before.")";
    }
  }
  return $pos;
}
//On lance l'algorithme de maximisation des distances
$pos = stochasticPositionning($pos, 1000);

//On calcule la grille finale contenant les positions, avec 1 cellule par joueur et par case.
$final_grid = array();
for($i = 1; $i <= $x; $i++) {
  for($j = 1; $j <= $y; $j++) {
    $final_grid[$i][$j] = 0;
  }
}
for($i = 1; $i <= $nbJoueurs; $i++) {
  $final_grid[$pos[$i][0]][$pos[$i][1]] = $i*100+1;
}

//	Tableau de jeu - +10000 : chateau  +N00 : case au joueur N +XX : nombre de cellules
for($i = 1; $i <= $x; $i++) {
  for($j = 1; $j <= $y; $j++) {
    if($j > 1) fwrite($fh, "\t");   
    fwrite($fh, "".$final_grid[$i][$j]);
  }
  fwrite($fh, "\n");
}
//0	n° du tour
fwrite($fh, "1\n");
fclose($fh);

ajouterPartie($fichier_parties, $numero_partie, $opt_partie_cachee, $nbJoueurs, $joueurs);
?>
Partie <?php echo $numero_partie;?> créée !<br />
<?php
for($i = 1; $i <= $nbJoueurs; $i++) {
  $url = getUrlJoueur($numero_partie, $i, $joueurs[$i]["si_mdp"], $joueurs[$i]["mdp"]);
  echo '<a href="'.$url.'">Le jeu pour '.$joueurs[$i]["nom"].'</a><br>';
}
?>
<?php
//écriture du fichier lespartiesencours.xml
/*	<parties>
		<partie numero=numero cachee=0/1 nbjoueurs=N>
			<joueur numero=1 nom=nom1 />
			<joueur numero=2 nom=nom2 />
		</partie>
		...
	</parties>*/

?>
</body>
</html>
<?php
/*
	Nombre de joueurs
	N°Joueur en cours entre 1 et NbJoueurs
	Nom joueur 1	couleur	option : motdepasse	
	Nom joueur 2	couleur	option : motdepasse	option : estIA?
0	Dernière action joueur 1 : a x y tour
0	Dernière action joueur 2 : a x y tour
	Options : chateaux activés ? 1/0
		Profondeur de jeu
		Bord bloqués ?	1/0/2:monde rond
		Ajout diagonale ? 1/0  (peut-on cliquer en diagonale ou seulement à côté ?)
		Explosion slt pour joueur en cours ? 1/0
	Taille X
	Taille y
	Tableau de décor - 0 rien 1 glace 2 chaud 3 obstacle
	Tableau de jeu - +10000 : chateau  +N00 : case au joueur N +XX : nombre de cellules
0	n° du tour
*/


?>

