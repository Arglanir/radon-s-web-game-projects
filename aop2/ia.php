<?php
/********************************
ia.php : cherche le plateau de jeu et joue � la place d'un joueur, appel�e par le client du joueur 1 (cr�ateur)
	communique rapidement avec jeu.php
	cherche la partie, quel joueur elle remplace, et joue suivant son niveau
	param�tres GET :
		p	num�ro de la partie
************************/
include_once ("fonctions.inc");
include_once ("classes.php");

function tempsRestant(){
	static $debut;
	if (!isset($debut)) $debut=microtime(true);
	$la = microtime(true);
	return 30-$la+$debut;
}
tempsRestant();//lance le chrono
function melanger(&$unTableau){//comme on n'a pas le temps de parcourir tout l'arbre
	$temp = NULL;$longueur = count($unTableau);
	foreach($unTableau as $key => $value){
		$key2 = rand(0,$longueur-1);
		$temp = $value;
		$unTableau[$key] = $unTableau[$key2];
		$unTableau[$key2] = $temp;
	}
	return $unTableau;
}

if (!array_key_exists("p",$_GET))
	lancerErreur("Num�ro de partie requis","Lancement de l'IA");

$p = (array_key_exists("p",$_GET)?$_GET["p"]:"000001");
$fichierPartie = getNomFichier($p);
$partie = Partie::ouvrirXML($fichierPartie);
if (!$partie)
	lancerErreur("Partie ".$p." inconnue.","Lancement de l'IA");

$joueurIA = $partie->joueurEnCours;
$forcerAJouer = (array_key_exists("pw",$_GET)?md5($_GET["pw"])==md5mdpIA:false);
if (!$partie->joueur[$joueurIA]->isIA() && !$forcerAJouer)
	lancerErreur("Le joueur en cours est humain","Lancement de l'IA");
$niveauIA = $partie->joueur[$joueurIA]->niveau;

$_GET["a"] = "n";
$_GET["j"] = "".$joueurIA;
$_GET["pw"] = md5mdpIA;
//$_GET["p"] = $p;
$_GET["x"] = 0;
$_GET["y"] = 0;

$nombreDeNoeuds = 0;

function vaJouerAvant($joueurEnCours,$joueurConsidere,$joueurApres,$nbJoueurs){
	if ($joueurApres<$joueurEnCours) $joueurApres += $nbJoueurs;
	if ($joueurConsidere<$joueurEnCours) $joueurConsidere += $nbJoueurs;
	if ($joueurConsidere < $joueurApres) return true;
	return false;
}

function heuristique($plateau,$joueur,$joueurIA){//renvoie un nombre �valuant la position pour un joueur venant de jouer
	global $partie, $nombreDeNoeuds;

	$nombreDeNoeuds++;
	
	$casesPretesAExploser = 0;
	$casesMenacees = 0;
	$cellules = 0;
	$casesControlees = 0;
	$casesASoiMenacees = 0;
	$frontiereBrulante = 0;
	
	$cellulesEnnemis = 0;
	$resultat = 0;
	
	for ($x=0;$x<$plateau->tailleX;$x++){
		for ($y=0;$y<$plateau->tailleY;$y++){
			$c = $plateau->getCase($x,$y);
			if ($c->getJoueur()==$joueurIA){
				$cellules += ($nb = $c->getCellules());
				if ($nb==0) $casesMenacees += 1;
				if ($nb>0) $casesControlees += 1;
				if ($c->preteAExploser()) $casesPretesAExploser += 1;
			}
			else if ($c->getDecor() != 3){
				$cellulesEnnemis += $c->getCellules();
				$joueurEnnemi = $c->getJoueur();
				for ($i=-1;$i<2;$i++) for ($j=($i!=0?0:-1);$j<($i!=0?1:2);$j+=2){
					$c2 = $plateau->getCase($x,$y);
					if (!$c2) continue;
					if ($c2->getJoueur() == $joueur){
						if ($c2->preteAExploser()) $casesMenacees += 1;
						if ($c->preteAExploser()) $casesASoiMenacees += 1;
						if ($c->preteAExploser() && $c2->preteAExploser()) $resultat += (vaJouerAvant($joueur,$joueurEnnemi,$joueurIA,$partie->nbJoueurs)?-10:10);//$frontiereBrulante += 1;
					}
				}
			}
		}
	}
	if ($cellules == 0) return -100000;//on se pose pas de question
	if ($cellulesEnnemis == 0) return 100000;//l� non plus
	$resultat += 3*$cellules;
	//$resultat += ($tourSuivantASoi?1:-1)*10*$frontiereBrulante;
	//$resultat += -5*$casesASoiMenacees;
	//$resultat += 5*$casesMenacees;
	//$resultat += 2*$casesControlees;
	//$resultat += 2*$casesPretesAExploser;
	
	return $resultat;
}
//le plateau, le joueur qui va jouer
function descente($plateau,$joueur,$joueurIA,$profondeurMax,$profondeur=0,$alpha=-10000000,$beta=10000000){
	global $partie;
	$nbJoueurs = $partie->nbJoueurs;
	
	$lesPositions = melanger($plateau->ouPeutJouer($joueur));
	if (count($lesPositions) == 0)
		return descente($plateau,mettreEntre($joueur,$nbJoueurs)+1,$joueurIA,$profondeurMax,$profondeur+1,$alpha,$beta);
	$evaluationPositions = array();
	$noeudMax = ($joueur == $joueurIA);
	$meilleur = ($noeudMax ? -100000 : 100000);
	$meilleurePos = $lesPositions[0];
	foreach($lesPositions as $key => $pos){
		$plateau2 = $plateau->copie();
		$plateau2->clicNormal($pos[0],$pos[1],$joueur,$partie->noTour);
		$plateau2->purifieTotalement($joueur,$partie->noTour);
		$g = $plateau2->yaGagnant();
		if ($g){
			if ($g==$joueurIA) $evaluationPositions[$key] = 100000;
			else if ($g!=$joueurIA) $evaluationPositions[$key] = -100000;
		} else {
			if ($profondeur >= $profondeurMax || tempsRestant()<3)//on arr�te � caus du temps
				$evaluationPositions[$key] = heuristique($plateau2,$joueur,$joueurIA);
			else
				$evaluationPositions[$key] = descente($plateau2,mettreEntre($joueur,$nbJoueurs)+1,$joueurIA,$profondeurMax,$profondeur+1,$alpha,$beta);
				//heuristique($plateau2,$joueurIA,$options,false);
		}
		$valeur = $evaluationPositions[$key];
		if ((!$noeudMax && $meilleur > $valeur) || ($noeudMax && $meilleur < $valeur)){
			$meilleur = $valeur;
			$meilleurePos = $lesPositions[$key];
		}
		if (!$noeudMax){
			if ($alpha>=$valeur)
				return $valeur;
			$beta = min($beta, $valeur);
		}
		else {
			if ($valeur>=$beta)
				return $valeur;
			$alpha = max($alpha, $valeur);
		}
	}
	if ($profondeur == 0){
		return $meilleurePos;
	}
	else {
		return $meilleur;
	}
}

if ($niveauIA == 0){//jeu au hasard
	$lesPositions = $partie->tableauJeu->ouPeutJouer($joueurIA);
	$laPos = $lesPositions[rand(0,count($lesPositions)-1)];
	$_GET["x"] = $laPos[0];
	$_GET["y"] = $laPos[1];
}
else {
	$lActionEn = descente($partie->tableauJeu,$joueurIA,$joueurIA,$niveauIA*$partie->nbJoueurs/2);
	$_GET["x"] = $lActionEn[0];
	$_GET["y"] = $lActionEn[1];
}

$f = fopen("ia.log","a");
fwrite($f,$nombreDeNoeuds." noeuds parcourus.\n");
fclose($f);

//echo $_GET["x"]." ".$_GET["y"];

include ("serveur.php");

?>