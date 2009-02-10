<?php
/********************************
ia.php : cherche le plateau de jeu et joue à la place d'un joueur, appelée par le client du joueur 1 (créateur)
	communique rapidement avec jeu.php
	cherche la partie, quel joueur elle remplace, et joue suivant son niveau
	paramètres GET :
		p	numéro de la partie
************************/
include_once ("fonctions.inc");
include_once ("classes.php");

if (!array_key_exists("p",$_GET))
	lancerErreur("Numéro de partie requis","Lancement de l'IA");

$p = (array_key_exists("p",$_GET)?$_GET["p"]:"000001");
$fichierPartie = getNomFichier($p);
$partie = Partie::ouvrirXML($fichierPartie);

$joueurIA = $partie->joueurEnCours;
if (!$partie->joueur[$joueurIA]->isIA())
	lancerErreur("Le joueur en cours est humain","Lancement de l'IA");
$niveauIA = $partie->joueur[$joueurIA]->niveau;

$_GET["a"] = "n";
$_GET["j"] = "".$joueurIA;
$_GET["pw"] = md5mdpIA;
//$_GET["p"] = $p;
$_GET["x"] = 0;
$_GET["y"] = 0;

function vaJouerAvant($joueurEnCours,$joueurConsidere,$joueurApres,$nbJoueurs){
	if ($joueurApres<$joueurEnCours) $joueurApres += $nbJoueurs;
	if ($joueurConsidere<$joueurEnCours) $joueurConsidere += $nbJoueurs;
	if ($joueurConsidere < $joueurApres) return true;
	return false;
}

function heuristique($plateau,$joueur,$options,$joueurIA){//renvoie un nombre évaluant la position pour un joueur venant de jouer
	global $partie;
	$nbJoueurs = $partie->nbJoueurs;
	
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
			if ($plateau->peutJouerEn($options,$x,$y,$joueur)){
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
						if ($c->preteAExploser() && $c2->preteAExploser()) $resultat += (vaJouerAvant($joueur,$joueurEnnemi,$joueurIA,$nbJoueurs)?-10:10);//$frontiereBrulante += 1;
					}
				}
			}
		}
	}
	if ($cellules == 0) return -100000;//on se pose pas de question
	if ($cellulesEnnemis == 0) return 100000;//là non plus
	$resultat += 3*$cellules;
	//$resultat += ($tourSuivantASoi?1:-1)*10*$frontiereBrulante;
	$resultat += -5*$casesASoiMenacees;
	$resultat += 5*$casesMenacees;
	$resultat += 2*$casesControlees;
	$resultat += 2*$casesPretesAExploser;
	
	return $resultat;
}
//le plateau, le joueur qui va jouer
function descente($plateau,$joueur,$options,$joueurIA,$nbJoueurs,$profondeurMax,$profondeur=0,$alpha=-10000000,$beta=10000000){
	$lesPositions = $plateau->ouPeutJouer($options,$joueur);
	if (count($lesPositions) == 0)
		return descente($plateau,mettreEntre($joueur,$nbJoueurs)+1,$options,$joueurIA,$nbJoueurs,$profondeurMax,$profondeur+1,$alpha,$beta);
	$evaluationPositions = array();
	$noeudMax = ($joueur == $joueurIA);
	$meilleur = ($noeudMax ? -100000 : 100000);
	$meilleurePos = $lesPositions[0];
	foreach($lesPositions as $key => $pos){
		$plateau2 = $plateau->copie();
		$plateau2->clicNormal($pos[0],$pos[1],$joueur);
		$plateau2->purifieTotalement($options,$joueur);
		if ($profondeur >= $profondeurMax)
			$evaluationPositions = heuristique($plateau2,$joueurIA,$options,false);
		else
			$evaluationPositions = descente($plateau2,mettreEntre($joueur,$nbJoueurs)+1,$options,$joueurIA,$nbJoueurs,$profondeurMax,$profondeur+1,$alpha,$beta);
			//heuristique($plateau2,$joueurIA,$options,false);
		$valeur = $evaluationPositions;
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
	$lesPositions = $partie->tableauJeu->ouPeutJouer($partie->options,$joueurIA);
	$laPos = $lesPositions[rand(0,count($lesPositions)-1)];
	$_GET["x"] = $laPos[0];
	$_GET["y"] = $laPos[1];
}
else {
	$lActionEn = descente($partie->tableauJeu,$joueurIA,$partie->options,$joueurIA,$partie->nbJoueurs,$niveauIA*$partie->nbJoueurs/2);
	$_GET["x"] = $lActionEn[0];
	$_GET["y"] = $lActionEn[1];
}


include ("serveur.php");

?>