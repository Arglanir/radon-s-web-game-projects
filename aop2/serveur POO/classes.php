<?php

include_once("fonctions.inc");

/****** classe de partie *******/
class Partie {
	int $nbJoueurs;
	var $joueur;//tableau de Joueurs, commence par 1
	int $noTour;
	int $joueurEnCours;
	Options $options;//objet Options
	PlateauDeJeu $tableauJeu;//objet PlateauDeJeu
	
	//fonctions de cr�ation
	function Partie(){
		$this->nbJoueurs = 0;
		$this->joueur = array();
		$this->noTour = 1;
		$this->joueurEnCours = 0;
	}
	function addJoueur($nom,$couleur,$mdp="0",$type=0,$niveau=0){
		$this->nbJoueurs++;
		$this->joueur[$this->nbJoueurs] = new Joueur($nom,$couleur,$mdp,$type,$niveau);
	}
	function setNoTour($noTour){$this->noTour = $noTour;}
	function setJoueurEnCours($joueurEnCours){$this->joueurEnCours = $joueurEnCours;}
	function finaliser(){//finalise le jeu pour le rendre totlament jouable
		if (!$this->joueurEnCours) $this->joueurEnCours = rand(1,$nbJoueurs);
		
	}
	
	function estPrete(){//teste la partie si elle est bien charg�e
		
	}
	
	function joueurSuivant(){//met le joueur en cours au joueur suivant
		$this->joueurEnCours = mettreEntre($this->joueurEnCours,$this->nbJoueurs)+1;//on passe au suivant
		if ($this->joueurEnCours == 1) $this->noTour++;//augmentation du n� du tour
		if (!$this->tableauJeu->peutJouer($this->joueurEnCours)) joueurSuivant();
		return true;
	}
	
	function nouvelle(){// A FAIRE
		
	}
	
	function fromXML($fichier){
		$xml_partie = new DOMDocument();
		$xml_partie->load( $fichier );
		$Xpartie = $xml_partie->get_elements_by_tagname( "partie" )[0];
		$partie = new Partie();
		$partie->setNoTour(0+$Xpartie->get_attribute("notour"));
		$partie->setJoueurEnCours(0+$Xpartie->get_attribute("joueurencours"));
		$partie->nbJoueurs = 0+$Xpartie->get_attribute("nbjoueurs");
		$joueurs_array = $Xpartie->get_elements_by_tagname( "joueurs" )[0]->get_elements_by_tagname( "joueur" );
		foreach ($joueurs_array as $Xjoueur)
			$partie->joueur[$Xjoueur->get_attribute("numero")+0] = Joueur::fromXML($Xjoueur);
		$partie->options = Options::fromXML($Xpartie->get_elements_by_tagname( "options" )[0]);
		$partie->tableauJeu = PlateauDeJeu::fromXML($Xpartie->get_elements_by_tagname( "tableaudejeu" )[0]);
		return $partie;
	}
	
	function toXML(){//renvoie le document XML de partie
		$xml_partie = domxml_new_doc("1.0");
		$Xpartie = $xml_partie->add_root( "partie" );
		$Xpartie->set_attribute("nombredejoueurs", $this->nbJoueurs);
		$Xpartie->set_attribute("notour", $this->noTour);
		$Xpartie->set_attribute("joueurencours", $this->joueurEnCours);
		
		$Xjoueurs = $Xpartie->create_element("joueurs");
		$Xpartie->append_child($Xjoueurs);
		
		for ($i = 1; $i <= $nbJoueurs; $i++){
			$Xjoueur = $this->joueur[$i]->toXML($Xjoueurs,$i);
			$Xjoueurs->append_child($Xjoueur);
		}

		$Xoptions = $this->options->toXML($Xpartie);
		$Xpartie->append_child($Xoptions);
		
		$Xtableau = $this->tableauJeu->toXML($Xpartie);
		$Xpartie->append_child($Xtableau);

		return $xml_partie;

	}
	
	function enregistrerXML($fichierCourant){
		$this->toXML()->dump_file("x".$fichierCourant, false, true);
	}
}

/****** classe g�rant un joueur *******/
class Joueur {
	string $nom;
	string $couleur;//string de couleur
	string $mdp;
	int $type;//0 : joueur humain 1 : ia (client : 2 : net)
	int $niveau;//niveau IA : 0 jeu al�atoire, n meilleur coup profondeur n-1
	Action $derniereAction;
	
	function Joueur($nom,$couleur,$mdp="0",$type=0,$niveau=0,$mettrederniereaction=true){
		$this->nom = $nom;
		$this->couleur = $couleur;
		$this->mdp = $mdp;
		$this->type = 0+$type;
		$this->niveau = 0+$niveau;
		if ($mettrederniereaction)
			$this->derniereAction = new Action();
	}
	
	function fromXML($Xjoueur){
		$joueur = new Joueur($Xjoueur->get_attribute("nom"),
							$Xjoueur->get_attribute("couleur"),
							$Xjoueur->get_attribute("mdp"),
							($Xjoueur->get_attribute("estia")=="oui"?1:
								($Xjoueur->get_attribute("estnet")=="oui"?2:0)),
							0+$Xjoueur->get_attribute("niveau"),false);
		
		$joueur->derniereAction = Action::fromXML($Xjoueur->get_elements_by_tagname("derniereaction")[0]);
		return joueur;
	}
	
	function toXML($parent,$numero){
		$Xjoueur = $parent->create_element("joueur");
		$Xjoueur->set_attribute("numero", $numero);
		$Xjoueur->set_attribute("nom", $this->nom);
		$Xjoueur->set_attribute("mdp", $this->mdp);
		$Xjoueur->set_attribute("couleur", $this->couleur);
		$Xjoueur->set_attribute("estia", ($this->type==1?"oui":"non"));
		$Xjoueur->set_attribute("niveau", $this->niveau);//� int�grer
		$Xjoueur->set_attribute("estnet", ($this->type==2?"oui":"non"));
		$Xjoueur->append_child($this->derniereAction->toXML($Xjoueur));
		return $joueur;
	}
	
}

class Action {
	string $quoi;//"n" ou "c"
	int $ouX;
	int $ouY;
	int $quand;//no du tour
	
	function Action($quoi="n",$ouX=0,$ouY=0,$quand=0){
		$this->quoi = $quoi;
		$this->ouX = $ouX;
		$this->ouY = $ouY;
		$this->quand = $quand;
	}
	
	function fromXML($Xaction){
		return new Action($Xaction->get_attribute("type"),
						0+$Xaction->get_attribute("x"),
						0+$Xaction->get_attribute("y"),
						0+$Xaction->get_attribute("notour"));
	}
	
	function toXML($parent){
		$derniereact = $parent->create_element("derniereaction");
		$derniereact->set_attribute("type", $this->quoi);
		$derniereact->set_attribute("x", $this->ouX);
		$derniereact->set_attribute("y", $this->ouY);
		$derniereact->set_attribute("notour", $this->quand);
		return $derniereact;
	}
}

/****** classe prenant en compte les options ******/
class Options {
	bool $chateauxPermis;//	Options : chateaux activ�s ? true/false
	int $profondeur;	//Profondeur de jeu
	int $typeBord;	//Bord bloqu�s ?	1/0/2:monde rond
	bool $ajoutDiag;	//Ajout diagonale ? true/false  (peut-on cliquer en diagonale ou seulement � c�t� ?)
	bool $explosionJoueur;//Explosion slt pour joueur en cours ? true/false
	
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
	
	function fromXML($Xoptions){
		$options = new Options();

		$lesOptions = array();
		
		$options_array = $Xoptions->get_elements_by_tagname( "option" );
		
		foreach ($options_array as $Xoption){
			$valeur = 0+$Xoption->get_attribute("valeur");
			switch($Xoption->get_attribute("type")){
				case "chateaux_actifs": setPermissionChateau($valeur);
					break;
				case "profondeur_jeu": setProfondeur($valeur);
					break;
				case "type_bords": setTypeBord($valeur);
					break;
				case "ajout_diagonale": setPlacementDiag($valeur);
					break;
				case "explosion_joueur": setExplosionJoueur($valeur);
					break;
			}
		}
		return $options;
	}
	
	function toXML($parent){
		$Xoptions = $parent->create_element("options");
		
		$lesOptions = array("chateaux_actifs", "profondeur_jeu" , "type_bords", "ajout_diagonale", "explosion_joueur");
		$options = array((yaPermissionChateau()?1:0), quelleProfondeur() , quelTypeBord(), (yaPlacementDiag()?1:0), (yaExplosionJoueur()?1:0));
		for ($i = 0; $i < 5; $i++){
			$Xoption = $Xoptions->create_element("option");
			$Xoption->set_attribute("type", $lesOptions[$i]);
			$Xoption->set_attribute("valeur", $options[$i]);
			$Xoptions->append_child($Xoption);
		}
		
		return $Xoptions;
	}
}

/****** classe de plateau de jeu ******/
class PlateauDeJeu {
	var $plateau;//tableau bi dim de UneCase
	int $tailleX;
	int $tailleY;
	
	function PlateauDeJeu(){
		$this->plateau = array();
		$tableauPlein = true;
		switch( func_num_args ()){
			case 1:
			  if (is_object(func_get_arg(0))){//c'est un objet similaire � copier
				$ancienPlateau= func_get_arg(0);
				$this->tailleX = $ancienPlateau->tailleX;
				$this->tailleY = $ancienPlateau->tailleY;
				for ($i = 0; $i < $this->tailleY; $i++){
					$this->plateau[$i] = array();
					for ($j = 0; $j < $this->tailleX; $j++)
						$this->plateau[$i][$j] = $ancienPlateau->plateau[$i][$j]->copie();
				}
			  } else if (is_array(func_get_arg(0))){//c'est un tableau bidim d'entiers de d�cor
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
			case 3://param�tre optionnel sp�cifiant si on construit les cases d�j� (tableau non vide ?)
				$tableauPlein = func_get_arg(2);
			case 2://juste les param�tres $tailleX et $tailleY
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
	function copie(){//cr�e une copie du plateau
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
						case 1: //on regarde pas apr�s les bords
						case 0:
							if (entre(0,$ii+$i,$this->tailleY-1)&&entre(0,$jj+$j,$this->tailleX-1))
								if ($this->getCase($j+$jj,$i+$ii)->getDecor()==3) $k--;
							break;
						case 2://on regarde apr�s le bord
							if ($this->getCase(mettreEntre($j+$jj,$tailleX),mettreEntre($i+$ii,$tailleY))->getDecor()==3) $k--;
							break;
						}
					}
			$this->getCase($j,$i)->setMax($k);
		}
	}
	function purifie($options,$joueurEnCours){//en fonction des options, 1 it�ration
		$changement=false;
		$ouGlaceExplosion = array();//var indiceGlace=0;//pr�paration des endroits glac�s
		$differences = array(); //pr�paration du traitement des explosions
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
				$changement = true;			//va sur les cases d'� c�t�
				for ($ii=-1;$ii<2;$ii++) for($jj=-1;$jj<2;$jj++) if (abs($ii)+abs($jj)==1){//pas diagonale
					$nvx = $x+$jj; $nvy = $y+$ii;
					$perteBord = false;
					switch($options->quelTypeBord()){
					  case 2: //on regarde apr�s les bords
						$nvx = mettreEntre($x+$jj,$this->tailleX); $nvy = mettreEntre($y+$ii,$this->tailleY);
					  case 0: $perteBord=true; //on ne regarde pas au bord mais on perd une cellule
					  case 1: //on regarde pas apr�s les bords
						if (entre(0,$nvy,$this->tailleY-1) && entre(0,$nvx,$this->tailleX-1)){
							$autreCase=$this->getCase($nvx,$nvy);
							switch($autreCase->getDecor()){
							case 0: //case normale
								$differences[$y][$x]--;
								if ($autreCase->getChateau()&&($cetteCase->getJoueur()!=$autreCase->getJoueur())&&$autreCase->getCellules()>=10){//traitement si membrane adverse protg�e
									$differences[$nvy][$nvx]--;
								} else {//jeu normal ou destruction de la membrane et conqu�te des cellules
									//il va y avoir un bug si attaque et d�fense en m�me temps d'un chateau
									//on va dire que les attaquants ont toujours priorit�... C'est un jeu !
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
								} else {//fois apr�s
									//il va y avoir un BUG si 2 personnes tentent de conqu�rir une case de glace
									//c'est � cause du vent, il souffle pour favoriser les joueurs x plus grands puis y
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
	function purifieTotalement($options,$joueurEnCours,$profondeur=0){
		if ($profondeur>=$options->quelleProfondeur()){
			return true;
		} else {
			$changements = $this->purifie($options,$joueurEnCours);
			$profondeur++;
			if ($changements)//on arr�te s'il y a pas de changements
				purifierTotalement($options,$joueurEnCours,$profondeur);
			else
				purifierTotalement($options,$joueurEnCours,$options->quelleProfondeur());
		}
	}
	function clicNormal($x,$y,$joueurEnCours,$chateau=false){//ajoute une cellule
		$laCase = $this->getCase($x,$y);
		$laCase->setJoueur($joueurEnCours);
		$laCase->addCellules($laCase->getDecor()==2?2:1);
		if ($chateau) $laCase->clicChateau();
	}
	function clicChateau($x,$y,$joueurEnCours){return $this->clicNormal($x,$y,$joueurEnCours,true);}
	function peutJouerEn($x,$y,$joueurAppelant,$chateau=false){
		$laCase = $this->getCase($x,$y);
		if ($laCase->getDecor() != 0 && $chateau)
			return false; // chateau et case instable
		if ($laCase->getJoueur() != $joueurAppelant && $laCase->getCellules() > 0)
			return false; //case d�j� control�e par joueur adverse
		if ($laCase->getJoueur() == $joueurAppelant && $laCase->getCellules() > 0)
		//case control�e par ce joueur
			if ($laCase->getDecor() == 1 && $laCase->getCellules() >= $laCase->getMax() - 1)
				return false; // mais glace et limite atteinte
			else
				return true; // pas de probl�me
		if ($laCase->getDecor() == 1 && $laCase->getJoueur() != $joueurAppelant)
			return false; // glace et case non control�e
		if ($laCase->getDecor() == 3)//obstacle
			return false;
		for($i=-1;$i<2;$i++) for($j=-1;$j<2;$j++){//on va regarder si une case autour appartient au joueur
			if ($i==0 && $j==0) continue; // on a d�j� test� la case centrale
			if (!$options->yaPlacementDiag() && abs($i)+abs($j)==2) continue;//pas en diagonale
			$nvx = $x+$i; $nvy = $y+$j;
			if ($options->quelTypeBord() != 2 && (!entre(0,$nvx,$tailleX-1) || !entre(0,$nvy,$tailleY-1)))
				continue;//apr�s le bord
			$nvx = mettreEntre($nvx,$tailleX);$nvy = mettreEntre($nvy,$tailleY);//au cas o� le monde est rond
			$autreCase = $this->getCase($nvx,$nvy);
			if ($autreCase->getJoueur() == $joueurAppelant && $autreCase->getCellules() > 0)
				return true; //case control�e par ce joueur
		}
		return false;
	}
	function peutJouer($joueurAppelant){//v�rifie si le joueur appelant peut jouer
		for ($x=0;$x<$tailleX;$x++)
			for ($y=0;$y<$tailleY;$y++)
				if ($this->getCase($x, $y)->getJoueur()==$joueurAppelant && $this->getCase($x, $y)->getCellules()>0)
					return true;
		return false;
	}
	
	function fromXML($Xplateau){
		$lePlateau = new PlateauDeJeu(0+$Xplateau->get_attribute("taillex"),
								0+$Xplateau->get_attribute("tailley"),
								false);//pour que les cases ne soient pas initialis�es
		$lignes_array = $Xplateau->get_elements_by_tagname( "ligne" );
		foreach($lignes_array as $Xligne){
			$y = 0+$Xligne->get_attribute("y");
			$cases_array = $Xligne->get_elements_by_tagname( "case" );
			foreach($cases_array as $Xcase){
				$x = 0+$Xcase->get_attribute("x");
				$this->plateau[$y][$x] = UneCase::fromXML($Xcase);
			}
		}
		return $lePlateau;
	}
	
	function toXML($parent){
		$Xtableau = $parent->create_element("tableaudejeu");
		$Xtableau->set_attribute("taillex", $this->tailleX);
		$Xtableau->set_attribute("tailley", $this->tailleY);

		for($i=0;$i<$tailleY;$i++){
			$Xligne = $Xtableau->create_element("ligne");
			$Xtableau->append_child($Xligne);
			$Xligne->set_attribute("y", $i);
			for($j=0;$j<$tailleX;$j++){
				$Xcase = $this->getCase($x,$y)->toXML($Xligne,$x,$y);
				$Xligne->append_child($Xcase);
			}
		}
		return $Xtableau;
	}
}

/****** classe de cases du plateau de jeu  ****/
class UneCase {
	int $joueur;//� qui appartient la case
	int $nbcellules;//combien de cellules sont sur la case
	bool $chateau;//y a t il un chateau ? 
	int $max;//maximum de cellules sur la cas
	int $decor;//0 rien, 1 glace, 2 chaud, 3 obstacle
	
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
	
	function fromXML($Xcase){
		//joueur, cellules, chateau?, max, decor
		$laCase = new UneCase(0+$Xcase->get_attribute("joueur"),
								0+$Xcase->get_attribute("cellules"),
								0+$Xcase->get_attribute("chateau"),
								0+$Xcase->get_attribute("max"),
								0+$Xcase->get_attribute("decor")
								);
		return $laCase;
	}
	
	function toXML($parent,$x,$y){//renvoie un DOMNode
		$Xcase = $parent->create_element("case");
		$Xcase->set_attribute("x", $x);
		$Xcase->set_attribute("y", $y);
		$Xcase->set_attribute("decor", $this->getDecor());
		$Xcase->set_attribute("joueur", $this->getJoueur());
		$Xcase->set_attribute("cellules", $this->getCellules());
		$Xcase->set_attribute("max", $this->getMax());
		$Xcase->set_attribute("chateau", $this->getChateau()?1:0);
		return $Xcase;
	}
}

$partie = new Partie();
?>