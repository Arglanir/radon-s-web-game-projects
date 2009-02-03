<?php

include_once("fonctions.inc");

/****** classe de partie *******/
class Partie {
	
}

/****** classe gérant un joueur *******/
class Joueur {

}

/****** classe prenant en compte les options ******/
class Options {
	var $chateauxPermis;//	Options : chateaux activés ? true/false
	var $profondeur;	//Profondeur de jeu
	var $typeBord;	//Bord bloqués ?	1/0/2:monde rond
	var $ajoutDiag;	//Ajout diagonale ? 1/0  (peut-on cliquer en diagonale ou seulement à côté ?)
	var $explosionJoueur;//Explosion slt pour joueur en cours ? 1/0
	
	function Options($chateauxPermis=0,$profondeur=100,$typeBord=1,$ajoutDiag=1,$explosionJoueur=1){
		$this->chateauxPermis = ($chateauxPermis?true:false);
		$this->profondeur = $profondeur;
		$this->typeBord = $typeBord;
		$this->ajoutDiag = ($ajoutDiag?true:false);
		$this->explosionJoueur = ($explosionJoueur?true:false);
	}
	function setPermissionChateau($chateauxPermis){$this->chateauxPermis = ($chateauxPermis?true:false);}
	function setProfondeur($profondeur){$this->profondeur = $profondeur;}
	function setTypeBord($typeBord){$this->typeBord = $typeBord;}
	function setPlacementDiag($ajoutDiag){$this->ajoutDiag = ($ajoutDiag?true:false);}
	function setExplosionJoueur($explosionJoueur){$this->explosionJoueur = ($explosionJoueur?true:false);}

	function yaPermissionChateau(){return $this->chateauxPermis;}
	function quelleProfondeur(){return $this->profondeur;}
	function quelTypeBord(){return $this->typeBord;}
	function yaPlacementDiag(){return $this->ajoutDiag;}
	function yaExplosionJoueur(){return $this->explosionJoueur;}
}

/****** classe de plateau de jeu ******/
class PlateauDeJeu {
	var $plateau;//tableau bi dim de UneCase
	var $tailleX;
	var $tailleY;
	
	function PlateauDeJeu(){
		$this->plateau = array();
		$tableauPlein = true;
		switch( func_num_args ()){
			case 1:
			  if (is_object(func_get_arg(0))){//c'est un objet similaire à copier
				$ancienPlateau= func_get_arg(0);
				$this->tailleX = $ancienPlateau->tailleX;
				$this->tailleY = $ancienPlateau->tailleY;
				for ($i = 0; $i < $this->tailleY; $i++){
					$this->plateau[$i] = array();
					for ($j = 0; $j < $this->tailleX; $j++)
						$this->plateau[$i][$j] = $ancienPlateau->plateau[$i][$j]->copie();
				}
			  } else if (is_array(func_get_arg(0))){//c'est un tableau bidim d'entiers de décor
				$decor = func_get_arg(0);
				$this->tailleX = count($decor[0]);
				$this->tailleY = count($decor);
				for ($i = 0; $i < $this->tailleY; $i++){
					$this->plateau[$i] = array();
					for ($j = 0; $j < $this->tailleX; $j++)
						$this->plateau[$i][$j] = new UneCase($decor[$i][$j]);
				}
			  }
				break;
			case 3://paramètre optionnel spécifiant si on construit les cases déjà (tableau non vide ?)
				$tableauPlein = func_get_arg(2);
			case 2://juste les paramètres $tailleX et $tailleY
				$this->tailleX = func_get_arg(0);
				$this->tailleY = func_get_arg(1);
				for ($i = 0; $i < $this->tailleY; $i++){
					$this->plateau[$i] = array();
					if ($tableauPlein) for ($j = 0; $j < $this->tailleX; $j++)
						$this->plateau[$i][$j] = new UneCase();
				}
				break;
		}
	}
	function copie(){//crée une copie du plateau
		$leNouveau = new PlateauDeJeu($this->tailleX, $this->tailleY, false);
		for ($i = 0; $i < $this->$tailleY; $i++)
			for ($j = 0; $j < $this->$tailleX; $j++)
				$leNouveau->plateau[$i][$j] = $this->plateau[$i][$j]->copie();
		return $leNouveau;
	}
	function getCase($x, $y){ return $this->plateau[$y][$x];}
	function poseDecor($tableauDecor){//tableau en Y X
		if (count($tableauDecor) <  $this->$tailleY) return false;
		for ($i = 0; $i < $this->$tailleY; $i++){
			if (count($tableauDecor[$i]) <  $this->$tailleX) return false;
			for ($j = 0; $j < $this->$tailleX; $j++)
				$this->plateau[$i][$j]->setDecor($tableauDecor[$i][$j]);
		}
		return true;
	}
	function metsLesMax($options){//en fonction du plateau et des options
		for($i=0;$i<$this->tailleY;$i++) for($j=0;$j<$this->tailleX;$j++){
			$k = 4;
			if ($options->quelTypeBord() == 1){//on compte les bords
					if ($i==0 || $i==$this->tailleY-1) $k--;
					if ($j==0 || $j==$this->tailleX-1) $k--;
			}
			for ($ii=-1;$ii<2;$ii++) for($jj=-1;$jj<2;$jj++)//on regarde les obstacles
					if (abs($ii)+abs($jj)==1){//pas diagonale
						switch($options->quelTypeBord()){
						case 1: //on regarde pas après les bords
						case 0:
							if (entre(0,$ii+$i,$this->tailleY-1)&&entre(0,$jj+$j,$this->tailleX-1))
								if ($this->getCase($j+$jj,$i+$ii)->getDecor()==3) $k--;
							break;
						case 2://on regarde après le bord
							if ($this->getCase(mettreEntre($j+$jj,$tailleX),mettreEntre($i+$ii,$tailleY))->getDecor()==3) $k--;
							break;
						}
					}
			$this->getCase($j,$i)->setMax($k);
		}
	}
	function purifie($options,$joueurEnCours){//en fonction des options, 1 itération
		$changement=false;
		$ouGlaceExplosion = array();//var indiceGlace=0;//préparation des endroits glacés
		$differences = array(); //préparation du traitement des explosions
		$conquetes = array();
		for ($y=0;$y<$tailleY;$y++){
			$differences[$y] = array();
			$conquetes[$y] = array();
			for ($x=0;$x<$this->tailleX;$x++){
				$differences[$y][$x] = 0;
				$conquetes[$y][$x] = array();
			}
		}
		//parcours du plateau pour traiter les explosions
		for ($x=0;$x<$this->tailleX;$x++) for($y=0;$y<$this->tailleY;$y++){
			$cetteCase = $this->getCase($x,$y);
			if (($options->yaExplosionJoueur() && $$cetteCase->getJoueur()==$joueurEnCours) || !$options->yaExplosionJoueur())
			if ($cetteCase->vaExploser() && !$cetteCase->getChateau()){//explosion !
				$changement = true;			//va sur les cases d'à côté
				for ($ii=-1;$ii<2;$ii++) for($jj=-1;$jj<2;$jj++) if (abs($ii)+abs($jj)==1){//pas diagonale
					$nvx = $x+$jj; $nvy = $y+$ii;
					$perteBord = false;
					switch($options->quelTypeBord()){
					  case 2: //on regarde après les bords
						$nvx = mettreEntre($x+$jj,$this->tailleX); $nvy = mettreEntre($y+$ii,$this->tailleY);
					  case 0: $perteBord=true; //on ne regarde pas au bord mais on perd une cellule
					  case 1: //on regarde pas après les bords
						if (entre(0,$nvy,$this->tailleY-1) && entre(0,$nvx,$this->tailleX-1)){
							$autreCase=$this->getCase($nvx,$nvy);
							switch($autreCase->getDecor()){
							case 0: //case normale
								$differences[$y][$x]--;
								if ($autreCase->getChateau()&&($cetteCase->getJoueur()!=$autreCase->getJoueur())&&$autreCase->getCellules()>=10){//traitement si membrane adverse protgée
									$differences[$nvy][$nvx]--;
								} else {//jeu normal ou destruction de la membrane et conquète des cellules
									//il va y avoir un bug si attaque et défense en même temps d'un chateau
									//on va dire que les attaquants ont toujours priorité... C'est un jeu !
									$differences[$nvy][$nvx]++;
									if ($autreCase->getChateau()&&($cetteCase->getJoueur()!=$autreCase->getJoueur())&&$autreCase->getCellules()<10)
										$autreCase->setChateau(false);
									$conquetes[$nvy][$nvx][count($conquetes[$nvy][$nvx])]=$cetteCase->getJoueur();
								}
								break;
							case 1: //glace
								$differences[$y][$x]--;
								if (!array_key_exists($nvy." ".$nvx,$ouGlaceExplosion)){//1ere fois
									$ouGlaceExplosion[$nvy." ".$nvx] = 1;
								} else {//fois après
									//il va y avoir un BUG si 2 personnes tentent de conquérir une case de glace
									//c'est à cause du vent, il souffle pour favoriser les joueurs x plus grands puis y
									$differences[$nvy][$nvx]++;
									$conquetes[$nvy][$nvx][count($conquetes[$nvy][$nvx])]=$cetteCase->getJoueur();
								}
								break;
							case 2: //point chaud
								$differences[$y][$x]--;
								$differences[$nvy][$nvx]+=2;
								$conquetes[$nvy][$nvx][count($conquetes[$nvy][$nvx])]=$cetteCase->getJoueur();
								break;
							case 3: //obstacle:rien !
								break;
							}
						  } else if ($perteBord) {
							$differences[$y][$x]--;
						  }
						break;
					}
				}
			}
		}
		//post traitement des explosions
		for ($x=0;$x<$this->tailleX;$x++) for($y=0;$y<$this->tailleY;$y++){
			$cetteCase = $this->getCase($x,$y);
			$nbcellules = $cetteCase->getCellules();
			$cetteCase->addCellules($differences[$y][$x]);
			if (count($conquetes[$y][$x]) > 1){ //qui gagne la case ?
				$gagnant = mettreEntre($x+$this->tailleX*$y, count($conquetes[$y][$x]));
				$cetteCase->setJoueur($conquetes[$y][$x][$gagnant]);
			} else if (count($conquetes[$y][$x]) == 1){
				$cetteCase->setJoueur($conquetes[$y][$x][0]);
			}
		}
		return $changement;
	}
	function clicNormal($x,$y,$joueurEnCours,$chateau=false){//ajoute une cellule
		$laCase = $this->getCase($x,$y);
		$laCase->setJoueur($joueurEnCours);
		$laCase->addCellules($laCase->getDecor()==2?2:1);
		if ($chateau) $laCase->clicChateau();
	}
	function clicChateau($x,$y,$joueurEnCours){return $this->clicNormal($x,$y,$joueurEnCours,true);}
	function peutJouer($x,$y,$joueurAppelant,$chateau=false){
		$laCase = $this->getCase($x,$y);
		if ($laCase->getDecor() != 0 && $chateau)
			return false; // chateau et case instable
		if ($laCase->getJoueur() != $joueurAppelant && $laCase->getCellules() > 0)
			return false; //case déjà controlée par joueur adverse
		if ($laCase->getJoueur() == $joueurAppelant && $laCase->getCellules() > 0)
		//case controlée par ce joueur
			if ($laCase->getDecor() == 1 && $laCase->getCellules() >= $laCase->getMax() - 1)
				return false; // mais glace et limite atteinte
			else
				return true; // pas de problème
		if ($laCase->getDecor() == 1 && $laCase->getJoueur() != $joueurAppelant)
			return false; // glace et case non controlée
		if ($laCase->getDecor() == 3)//obstacle
			return false;
		for($i=-1;$i<2;$i++) for($j=-1;$j<2;$j++){//on va regarder si une case autour appartient au joueur
			if ($i==0 && $j==0) continue; // on a déjà testé la case centrale
			if (!$options->yaPlacementDiag() && abs($i)+abs($j)==2) continue;//pas en diagonale
			$nvx = $x+$i; $nvy = $y+$j;
			if ($options->quelTypeBord() != 2 && (!entre(0,$nvx,$tailleX-1) || !entre(0,$nvy,$tailleY-1)))
				continue;//après le bord
			$nvx = mettreEntre($nvx,$tailleX);$nvy = mettreEntre($nvy,$tailleY);//au cas où le monde est rond
			$autreCase = $this->getCase($nvx,$nvy);
			if ($autreCase->getJoueur() == $joueurAppelant && $autreCase->getCellules() > 0)
				return true; //case controlée par ce joueur
		}
		return false;
	}
}

/****** classe de cases du plateau de jeu  ****/
class UneCase {
	var $joueur;//à qui appartient la case
	var $nbcellules;//combien de cellules sont sur la case
	var $chateau;//y a t il un chateau ? 
	var $max;//maximum de cellules sur la cas
	var $decor;//0 rien, 1 glace, 2 chaud, 3 obstacle
	
	function UneCase($decor = 0){
		switch( func_num_args ()){
			case 1:
			case 0:
				if (is_numeric($decor)){
					$this->joueur = 0;
					$this->nbcellules = 0;
					$this->chateau = false;
					$this->max = 4;
					$this->decor = $decor;
				} else if (is_object($decor)){ //copie d'une case existante
					$this->joueur = $decor->joueur;
					$this->nbcellules = $decor->nbcellules;
					$this->chateau = $decor->chateau;
					$this->max = $decor->max;
					$this->decor = $decor->decor;
				}
			case 5: //joueur, cellules, chateau?, max, decor
				$this->joueur = func_get_arg(0);
				$this->nbcellules = func_get_arg(1);
				$this->chateau = (func_get_arg(2)?true:false);
				$this->max = func_get_arg(3);
				$this->decor = func_get_arg(4);
		}
	}
	function copie(){//copie une cellule
		$uneCase = new UneCase();
		$uneCase->setJoueur($this->getJoueur());
		$uneCase->setCellules($this->getCellules());
		$uneCase->setChateau($this->getChateau());
		$uneCase->setMax($this->getMax());
		$uneCase->setDecor($this->getDecor());
	}
	function setJoueur($joueur){$this->joueur = $joueur;}
	function getJoueur(){return $this->joueur;}
	
	function getCellules(){return $this->nbcellules;}
	function setCellules($nb){$this->nbcellules = $nb;}
	function addCellules($nb = 1){$this->nbcellules += $nb;$this->checkCellule();}
	function remCellules($nb = 1){$this->nbcellules -= $nb;	$this->checkCellule();}
	function checkCellule(){$this->nbcellules = min(max(0,$this->nbcellules),99);}
	
	function getChateau(){return $this->chateau;}
	function clicChateau(){$this->chateau = !$this->chateau;}
	function setChateau($mettreChateau){$this->chateau = ($mettreChateau?true:false);}
	
	function getMax(){return $this->max;}
	function setMax($leMax){$this->max = $leMax;}
	function vaExploser(){return ($this->cellules >= $this->max);}
	
	function getDecor(){return $this->decor;}
	function setDecor($decor){$this->decor = $decor;}
	
	function toInt(){return ($this->getChateau()?10000:0)+$this->getJoueur()*100+$this->getCellules();}
	
	function toXML($x,$y){
		
	}
}
?>