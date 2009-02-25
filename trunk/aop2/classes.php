<?php

include_once("fonctions.inc");

/****** classe de partie *******/
class Partie {
	var $nbJoueurs;
	var $joueur;//tableau de Joueurs, commence par 1
	var $noTour;
	var $joueurEnCours;
	var $options;//objet Options
	var $tableauJeu;//objet PlateauDeJeu
	var $demarree;
	var $gagnant;
	
	//fonctions de création
	function Partie(){
		$this->demarree = false;
		$this->gagnant = 0;
		$this->nbJoueurs = 0;
		$this->joueur = array();
		$this->noTour = 1;
		$this->joueurEnCours = 0;
	}
	function addJoueur($nom,$couleur,$mdp="0",$type=0,$niveau=0){
		if ($this->demarree) return false;//plus d'ajout après le démarrage
		$this->nbJoueurs++;
		$this->joueur[$this->nbJoueurs] = new Joueur($nom,$couleur,$mdp,$type,$niveau);
		if ($this->nbJoueurs == 1){
			$this->tableauJeu->getCase(rand(0,1),rand(0,1))->placeJoueur(1);//premier joueur en 0,0
		}
		else{
			$leMaxDist = -1;
			$lEndroit = array(0,0);
			for ($i=0;$i<$this->tableauJeu->tailleX;$i++) for ($j=0;$j<$this->tableauJeu->tailleY;$j++){
				$m = $this->tableauJeu->distance($this->options,$i,$j);
				if ($m > $leMaxDist){
					$leMaxDist = $m;
					$lEndroit = array($i,$j);
				}
			}
			$this->tableauJeu->getCase($lEndroit[0],$lEndroit[1])->placeJoueur($this->nbJoueurs);
		}
		return $this->nbJoueurs;
	}
	function setNoTour($noTour){$this->noTour = $noTour;}
	function setJoueurEnCours($joueurEnCours){$this->joueurEnCours = $joueurEnCours;}
	function finaliser(){//finalise le jeu pour le rendre totlament jouable
		if (!$this->joueurEnCours) $this->joueurEnCours = rand(1,$nbJoueurs);
		
	}
	
	function estPrete(){//teste la partie si elle est bien chargée
		
	}
	
	function joueurSuivant(){//met le joueur en cours au joueur suivant
		$this->joueurEnCours = mettreEntre($this->joueurEnCours,$this->nbJoueurs)+1;//on passe au suivant
		if ($this->joueurEnCours == 1) $this->noTour++;//augmentation du n° du tour
		if (!$this->tableauJeu->peutJouer($this->options,$this->joueurEnCours)) $this->joueurSuivant();
		return true;
	}
	function getJoueurEnCours(){
		return $this->joueur[$this->joueurEnCours];
	}
	
	function nouvelle(){//en fonction des données POST
	
	// A FAIRE, copier sur Mikaël
		
	}
	
	function finDePartie(){
		if ($g = $this->tableauJeu->yaGagnant())
			$this->gagnant = $g;
		return ($this->gagnant);
	}
	
	function fromText($fichier){
		if (!file_exists($fichier)) return false;
		$partie = new Partie();
		
		$contenuFichier = file($fichier,FILE_IGNORE_NEW_LINES);
		foreach ($contenuFichier as $line => $contenu)
			$contenuFichier[$line] = trim($contenu);
		$partie->nbJoueurs = 0 + $contenuFichier[0];
		$tailleX = 0 + $contenuFichier[7+2*$partie->nbJoueurs];
		$tailleY = 0 + $contenuFichier[8+2*$partie->nbJoueurs];
		$partie->joueurEnCours = 0 + $contenuFichier[1];
		$partie->noTour = 0 + $contenuFichier[9+2*$partie->nbJoueurs+$tailleY*2];
		
		for ($i = 1; $i <= $partie->nbJoueurs; $i++){
			$tab = explode("\t",$contenuFichier[1+$i]);
			$partie->joueur[$i] = new Joueur($tab[0],$tab[1],$tab[2],$tab[3],0,false);
			$tab = explode("\t",$contenuFichier[1+$partie->nbJoueurs+$i]);
			$partie->joueur[$i]->derniereAction = new Action($tab[0],0+$tab[1],0+$tab[2],0+$tab[3]);
		}

		$indice=2+2*$partie->nbJoueurs; //chateauxactivés profondeur bordbloqués diagonale tempspasreel
		$partie->options = new Options(0+$contenuFichier[$indice+$i],
								0+$contenuFichier[$indice+1],
								0+$contenuFichier[$indice+2],
								0+$contenuFichier[$indice+3],
								0+$contenuFichier[$indice+4]);
		$indice=2+2*$partie->nbJoueurs+5+2;
		$tableauDecor = array();
		for($i=0;$i<$tailleY;$i++){
			$tableauDecor[$i]=explode("\t",$contenuFichier[$indice+$i]);
			for($j=0;$j<$tailleX;$j++)
				$tableauDecor[$i][$j]=(int)($tableauDecor[$i][$j]);
		}
		$partie->tableauJeu = new PlateauDeJeu($tableauDecor);
		$indice+=$tailleY;
		for($i=0;$i<$tailleY;$i++){
			$tableauDecor[$i]=explode("\t",$contenuFichier[$indice+$i]);
			for($j=0;$j<$tailleX;$j++){
				$tableauDecor[$i][$j]=(int)($tableauDecor[$i][$j]);
				$partie->tableauJeu->getCase($j,$i)->setCellules(case2cellules($tableauDecor[$i][$j]));
				$partie->tableauJeu->getCase($j,$i)->setChateau(case2chateau($tableauDecor[$i][$j]));
				$partie->tableauJeu->getCase($j,$i)->setJoueur(case2joueur($tableauDecor[$i][$j]));
			}
		}
		$partie->tableauJeu->metsLesMax($partie->options);
		unset($tableauDecor);
		return $partie;
	}
	
	function fromXML($fichier){
		if (!file_exists($fichier)) return false;
		$xml_partie = new DOMDocument();
		$xml_partie->load( $fichier );
		$Xpartie = $xml_partie->get_elements_by_tagname( "partie" );
		$Xpartie = $Xpartie[0];
		$partie = new Partie();
		$partie->demarree = ($Xpartie->get_attribute("demarree")=="1");
		$partie->setNoTour(0+$Xpartie->get_attribute("notour"));
		$partie->setJoueurEnCours(0+$Xpartie->get_attribute("joueurencours"));
		$partie->gagnant = 0+$Xpartie->get_attribute("gagnant");
		$partie->nbJoueurs = 0+$Xpartie->get_attribute("nombredejoueurs");
		$joueurs_array = $Xpartie->get_elements_by_tagname( "joueur" );
		foreach ($joueurs_array as $Xjoueur)
			$partie->joueur[$Xjoueur->get_attribute("numero")+0] = Joueur::fromXML($Xjoueur);
		$partie->options = $Xpartie->get_elements_by_tagname( "options" );
		$partie->options = Options::fromXML($partie->options[0]);
		$partie->tableauJeu = $Xpartie->get_elements_by_tagname( "tableaudejeu" );
		$partie->tableauJeu = PlateauDeJeu::fromXML($partie->tableauJeu[0]);
		return $partie;
	}
	function fromSXML($fichier){
		if (!file_exists($fichier)) return false;
		try {
			$xml_partie = new SimpleXMLElement(file_get_contents($fichier));
		} catch (Exception $e){
			return false;
		}
		if (!$xml_partie) return false;
		
		$Xpartie = $xml_partie;/*->children();
		$Xpartie = $Xpartie[0];*/

		$partie = new Partie();
		
		$partie->setNoTour(0+$Xpartie["notour"]);
		$partie->setJoueurEnCours(0+$Xpartie["joueurencours"]);
		$partie->nbJoueurs = 0+$Xpartie["nombredejoueurs"];
		$partie->gagnant = 0+$Xpartie["gagnant"];
		$partie->demarree = ($Xpartie["demarree"]=="1");
		
		foreach($Xpartie->children() as $Xelement){
			switch($Xelement->getName()){
				case "joueurs":
					foreach ($Xelement->children() as $Xjoueur)
						$partie->joueur[$Xjoueur["numero"]+0] = Joueur::fromSXML($Xjoueur);
					break;
				case "options":
					$partie->options = Options::fromSXML($Xelement);
					break;
				case "tableaudejeu":
					$partie->tableauJeu = PlateauDeJeu::fromSXML($Xelement);
				break;
			}
			
		}	
		return $partie;
	}
	
	function toSXML($cacherMotsDePasse=false,$joueurAppelant=0){//renvoie le document SimpleXML de partie
		$Xpartie = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><partie></partie>');
		//$Xpartie = $xml_partie->addChild('partie');
	
		$Xpartie->addAttribute("nombredejoueurs", $this->nbJoueurs);
		$Xpartie->addAttribute("notour", $this->noTour);
		$Xpartie->addAttribute("gagnant", $this->gagnant);
		$Xpartie->addAttribute("joueurencours", $this->joueurEnCours);
		$Xpartie->addAttribute("demarree", ($this->demarree?1:0));
		
		$Xjoueurs = $Xpartie->addChild('joueurs');
		
		for ($i = 1; $i <= $this->nbJoueurs; $i++){
			$Xjoueur = $this->joueur[$i]->toSXML($Xjoueurs,$i,$cacherMotsDePasse,$joueurAppelant!=$i);
		}

		$Xoptions = $this->options->toSXML($Xpartie);
		
		$Xtableau = $this->tableauJeu->toSXML($Xpartie);

		return $Xpartie;
	}
	function toXML($cacherMotsDePasse=false,$joueurAppelant=0){//renvoie le document XML de partie
		$xml_partie = domxml_new_doc("1.0");
		$Xpartie = $xml_partie->create_element( "partie" );
		$xml_partie->append_child($Xpartie);
		$Xpartie->set_attribute("nombredejoueurs", $this->nbJoueurs);
		$Xpartie->set_attribute("notour", $this->noTour);
		$Xpartie->set_attribute("gagnant", $this->gagnant);
		$Xpartie->set_attribute("joueurencours", $this->joueurEnCours);
		$Xpartie->set_attribute("demarree", ($this->demarree?1:0));
		
		$Xjoueurs = $xml_partie->create_element("joueurs");
		$Xpartie->append_child($Xjoueurs);
		
		for ($i = 1; $i <= $this->nbJoueurs; $i++){
			$Xjoueur = $this->joueur[$i]->toXML($xml_partie,$i,$cacherMotsDePasse,$joueurAppelant!=$i);
			$Xjoueurs->append_child($Xjoueur);
		}

		$Xoptions = $this->options->toXML($xml_partie);
		$Xpartie->append_child($Xoptions);
		
		$Xtableau = $this->tableauJeu->toXML($xml_partie);
		$Xpartie->append_child($Xtableau);

		return $xml_partie;

	}
	
	function enregistrerXML($fichierCourant=true,$cacherMotsDePasse=false,$joueurAppelant=0){//chaine de fichier, sinon, booléen demandant l'affichage
		$chaine = "";
		if (floatval(phpversion())>=5)
			if (is_string($fichierCourant))
				return $this->toSXML($cacherMotsDePasse,$joueurAppelant)->asXML($fichierCourant);
			else
				if ($fichierCourant) {header('Content-Type: text/xml');echo $this->toSXML($cacherMotsDePasse,$joueurAppelant)->asXML();}
				else return $this->toSXML($cacherMotsDePasse,$joueurAppelant)->asXML();
		else	
			if (is_string($fichierCourant))
				return $this->toXML($cacherMotsDePasse,$joueurAppelant)->dump_file($fichierCourant, false, true);
			else
				if ($fichierCourant) {header('Content-Type: text/xml');echo $this->toXML($cacherMotsDePasse,$joueurAppelant)->dump_mem(true);}
				else return $this->toXML($cacherMotsDePasse,$joueurAppelant)->dump_mem(true);
	}
	function ouvrirXML($fichierCourant){
		if (floatval(phpversion())>=5)
			return Partie::fromSXML($fichierCourant);
		else	
			return Partie::fromXML($fichierCourant);
	}
}

/****** classe gérant un joueur *******/
class Joueur {
	var $nom;
	var $couleur;//var de couleur
	var $mdp;
	var $type;//0 : joueur humain 1 : ia (client : 2 : net)
	var $niveau;//niveau IA : 0 jeu aléatoire, n meilleur coup profondeur n-1
	var $derniereAction;
	
	function Joueur($nom,$couleur,$mdp="0",$type=0,$niveau=0,$mettrederniereaction=true){
		$this->nom = $nom;
		$this->couleur = $couleur;
		$this->mdp = ($type==1?md5mdpIA:$mdp);
		$this->type = 0+$type;
		$this->niveau = 0+$niveau;
		if ($mettrederniereaction)
			$this->derniereAction = new Action();
	}
	
	function isIA(){
		return ($this->type == 1);
	}
	
	function fromXML($Xjoueur){
		$joueur = new Joueur($Xjoueur->get_attribute("nom"),
							$Xjoueur->get_attribute("couleur"),
							$Xjoueur->get_attribute("mdp"),
							($Xjoueur->get_attribute("estia")=="oui"?1:
								($Xjoueur->get_attribute("estnet")=="oui"?2:0)),
							0+$Xjoueur->get_attribute("niveau"),false);
		
		$joueur->derniereAction = $Xjoueur->get_elements_by_tagname("derniereaction");
		$joueur->derniereAction = Action::fromXML($joueur->derniereAction[0]);
		return $joueur;
	}
	function fromSXML($Xjoueur){
		$joueur = new Joueur($Xjoueur["nom"],
							$Xjoueur["couleur"],
							$Xjoueur["mdp"],
							($Xjoueur["estia"]=="oui"?1:
								($Xjoueur["estnet"]=="oui"?2:0)),
							0+$Xjoueur["niveau"],false);
		
		$joueur->derniereAction = Action::fromSXML($Xjoueur->derniereaction[0]);
		return $joueur;
	}
	
	function toSXML($parent,$numero,$cacherMotsDePasse=false,$mettreNet=false){//renvoie un noeud SimpleXML
		$Xjoueur = $parent->addChild("joueur");
		$Xjoueur->addAttribute("numero", $numero);
		$Xjoueur->addAttribute("nom", $this->nom);
		$Xjoueur->addAttribute("mdp", ($cacherMotsDePasse?0:$this->mdp));
		$Xjoueur->addAttribute("couleur", $this->couleur);
		$Xjoueur->addAttribute("estia", ($this->type==1?"oui":"non"));
		$Xjoueur->addAttribute("niveau", $this->niveau);//à intégrer
		$Xjoueur->addAttribute("estnet", ($mettreNet ?"oui":"non"));
		$this->derniereAction->toSXML($Xjoueur);
		return $Xjoueur;
	}
	function toXML($xml_partie,$numero,$cacherMotsDePasse=false,$mettreNet=false){//renvoie un DOMNode
		$Xjoueur = $xml_partie->create_element("joueur");
		$Xjoueur->set_attribute("numero", $numero);
		$Xjoueur->set_attribute("nom", $this->nom);
		$Xjoueur->set_attribute("mdp", ($cacherMotsDePasse?0:$this->mdp));
		$Xjoueur->set_attribute("couleur", $this->couleur);
		$Xjoueur->set_attribute("estia", ($this->type==1?"oui":"non"));
		$Xjoueur->set_attribute("niveau", $this->niveau);//à intégrer
		$Xjoueur->set_attribute("estnet", ($mettreNet ?"oui":"non"));
		$Xjoueur->append_child($this->derniereAction->toXML($xml_partie));
		return $Xjoueur;
	}
	
}

class Action {
	var $quoi;//"n" ou "c"
	var $ouX;
	var $ouY;
	var $quand;//no du tour
	
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
	function fromSXML($Xaction){
		return new Action($Xaction["type"],
						0+$Xaction["x"],
						0+$Xaction["y"],
						0+$Xaction["notour"]);
	}
	
	function toSXML($parent){//renvoie un noeud SimpleXML
		$derniereact = $parent->addChild("derniereaction");
		$derniereact->addAttribute("type", $this->quoi);
		$derniereact->addAttribute("x", $this->ouX);
		$derniereact->addAttribute("y", $this->ouY);
		$derniereact->addAttribute("notour", $this->quand);
		return $derniereact;
	}
	function toXML($xml_partie){//renvoie un DOMNode
		$derniereact = $xml_partie->create_element("derniereaction");
		$derniereact->set_attribute("type", $this->quoi);
		$derniereact->set_attribute("x", $this->ouX);
		$derniereact->set_attribute("y", $this->ouY);
		$derniereact->set_attribute("notour", $this->quand);
		return $derniereact;
	}
}

/****** classe prenant en compte les options ******/
class Options {
	var $chateauxPermis;//	Options : chateaux activés ? true/false
	var $profondeur;	//Profondeur de jeu
	var $typeBord;	//Bord bloqués ?	1/0/2:monde rond
	var $ajoutDiag;	//Ajout diagonale ? true/false  (peut-on cliquer en diagonale ou seulement à côté ?)
	var $explosionJoueur;//Explosion slt pour joueur en cours ? true/false
	
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

		$options_array = $Xoptions->get_elements_by_tagname( "option" );
		
		foreach ($options_array as $Xoption){
			$valeur = 0+$Xoption->get_attribute("valeur");
			switch($Xoption->get_attribute("type")){
				case "chateaux_actifs": $options->setPermissionChateau($valeur);
					break;
				case "profondeur_jeu": $options->setProfondeur($valeur);
					break;
				case "type_bords": $options->setTypeBord($valeur);
					break;
				case "ajout_diagonale": $options->setPlacementDiag($valeur);
					break;
				case "explosion_joueur": $options->setExplosionJoueur($valeur);
					break;
			}
		}
		return $options;
	}
	function fromSXML($Xoptions){
		$options = new Options();

		foreach ($Xoptions->children() as $Xoption){
			$valeur = 0+$Xoption["valeur"];
			switch($Xoption["type"]){
				case "chateaux_actifs": $options->setPermissionChateau($valeur);
					break;
				case "profondeur_jeu": $options->setProfondeur($valeur);
					break;
				case "type_bords": $options->setTypeBord($valeur);
					break;
				case "ajout_diagonale": $options->setPlacementDiag($valeur);
					break;
				case "explosion_joueur": $options->setExplosionJoueur($valeur);
					break;
			}
		}
		return $options;
	}
	
	function toSXML($parent){//renvoie le noeud SimpleXML
		$Xoptions = $parent->addChild("options");
		
		$lesOptions = array("chateaux_actifs", "profondeur_jeu" , "type_bords", "ajout_diagonale", "explosion_joueur");
		$options = array(($this->yaPermissionChateau()?1:0),
							$this->quelleProfondeur() , 
							$this->quelTypeBord(), 
							($this->yaPlacementDiag()?1:0), 
							($this->yaExplosionJoueur()?1:0));
		for ($i = 0; $i < 5; $i++){
			$Xoption = $Xoptions->addChild("option");
			$Xoption->addAttribute("type", $lesOptions[$i]);
			$Xoption->addAttribute("valeur", $options[$i]);
		}
		
		return $Xoptions;
	}
	function toXML($xml_partie){//renvoie un DOMNode
		$Xoptions = $xml_partie->create_element("options");
		
		$lesOptions = array("chateaux_actifs", "profondeur_jeu" , "type_bords", "ajout_diagonale", "explosion_joueur");
		$options = array(($this->yaPermissionChateau()?1:0),
							$this->quelleProfondeur() , 
							$this->quelTypeBord(), 
							($this->yaPlacementDiag()?1:0), 
							($this->yaExplosionJoueur()?1:0));
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
		for ($i = 0; $i < $this->tailleY; $i++)
			for ($j = 0; $j < $this->tailleX; $j++)
				$leNouveau->plateau[$i][$j] = $this->plateau[$i][$j]->copie();
		return $leNouveau;
	}
	function getCase($x, $y){
		if ($x>=0 && $x<$this->tailleX && $y>=0 && $y<$this->tailleY) return $this->plateau[$y][$x];
		else return false;
	}
	function poseDecor($tableauDecor){//tableau en Y X
		if (count($tableauDecor) <  $this->tailleY) return false;
		for ($i = 0; $i < $this->tailleY; $i++){
			if (count($tableauDecor[$i]) <  $this->tailleX) return false;
			for ($j = 0; $j < $this->tailleX; $j++)
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
							if ($this->getCase(mettreEntre($j+$jj,$this->tailleX),mettreEntre($i+$ii,$this->tailleY))->getDecor()==3) $k--;
							break;
						}
					}
			$this->getCase($j,$i)->setMax($k);
		}
	}
	function metsLesJoueurs($tabPosJoueurs){
		foreach ($tabPosJoueurs as $joueur => $tab){
			list($x,$y) = $tab;
			$this->getCase($x,$y)->setJoueur($joueur);
			$this->getCase($x,$y)->setCellules(1);
		}
	}
	
	function distance($options,$x,$y,$x2=-1,$y2=-1){//renvoie un flottant
		if ($x2>=0 && $y2>=0){//entre 2 cases
			$dx=$x2-$x;$dy=$y2-$y;
			switch($options->quelTypeBord()){
				case 0:case 1:
					if ($options->yaPlacementDiag())
						return	distN0($dx,$dy);//max(abs($dx),abs($dy));
								//round(sqrt(pow($dx,2)+pow($dy,2)),1);
					else
						return abs($dx)+abs($dy);
				case 2://torrique
					$d = $this->tailleX + $this->tailleY;
					if ($options->yaPlacementDiag())
						for ($i=-1;$i<2;$i++) for ($j=-1;$j<2;$j++)
							$d = min($d,distN0($dx+$i*$this->tailleX,$dy+$j*$this->tailleY));//sqrt(pow($dx+$i*$this->tailleX,2)+pow($dy+$j*$this->tailleY,2));
					else
						for ($i=-1;$i<2;$i++) for ($j=-1;$j<2;$j++)
							$d = min($d,abs($dx+$i*$this->tailleX)+abs($dy+$j*$this->tailleY));
					return $d;
			}
		}
		else {//entre la case et les joueurs existants
			$posJoueurs = array();
			for ($j = 0;$j < $this->tailleY;$j++) for ($i = 0;$i < $this->tailleX;$i++){
				//recherche des positions des joueurs
				if ($c = $this->getCase($i,$j))
					if ($jou = $c->getJoueur())
						$posJoueurs[$jou] = array($i,$j);
			}
			$d = $this->tailleX + $this->tailleY;
			foreach($posJoueurs as $pos){
				$d = min($d,$this->distance($options,$x,$y,$pos[0],$pos[1]));
			}
			return $d;
		}
	}
	
	function purifie($options,$joueurEnCours,$numtour="1-0"){//en fonction des options, 1 itération
		$changement=false;
		$ouGlaceExplosion = array();//var indiceGlace=0;//préparation des endroits glacés
		$differences = array(); //préparation du traitement des explosions
		$conquetes = array();
		for ($y=0;$y<$this->tailleY;$y++){
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
			if (($options->yaExplosionJoueur() && $cetteCase->getJoueur()==$joueurEnCours) || !$options->yaExplosionJoueur())
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
								if (!array_key_exists($nvy." ".$nvx,$ouGlaceExplosion) && $autreCase->numTourUtilisee != $numtour){//1ere fois
									$ouGlaceExplosion[$nvy." ".$nvx] = 1;
									$autreCase->numTourUtilisee = $numtour;
								} else {//fois après
									//il va y avoir un BUG si 2 personnes tentent de conquérir une case de glace
									//c'est à cause du vent, il souffle pour favoriser les joueurs x plus grands puis y
									$differences[$nvy][$nvx]++;
									$conquetes[$nvy][$nvx][count($conquetes[$nvy][$nvx])]=$cetteCase->getJoueur();
								}
								break;
							case 2: //povar chaud
								$differences[$y][$x]--;
								$differences[$nvy][$nvx]+= ($autreCase->numTourUtilisee != $numtour?2:1);
								$autreCase->numTourUtilisee = $numtour;
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
	function purifieTotalement($options,$joueurEnCours,$numTour,$profondeur=0){
		if ($profondeur>=$options->quelleProfondeur()){
			return true;
		} else {
			$changements = $this->purifie($options,$joueurEnCours,$numTour."-".$joueurEnCours);
			$profondeur++;
			if ($changements)//on arrête s'il y a pas de changements
				$this->purifieTotalement($options,$joueurEnCours,$numTour,$profondeur);
			else
				$this->purifieTotalement($options,$joueurEnCours,$numTour,$options->quelleProfondeur());
		}
	}
	function clicNormal($x,$y,$joueurEnCours,$chateau=false,$numTour=1){//ajoute une cellule
		$laCase = $this->getCase($x,$y);
		if (!$laCase) var_dump($this);
		$laCase->setJoueur($joueurEnCours);
		$laCase->addCellules($laCase->getDecor()==2?2:1);
		$laCase->numTourUtilisee = $numTour."-".$joueurEnCours;
		if ($chateau) $laCase->clicChateau();
	}
	function clicChateau($x,$y,$joueurEnCours,$numTour=1){return $this->clicNormal($x,$y,$joueurEnCours,true,$numTour);}
	function peutJouerEn($options,$x,$y,$joueurAppelant,$chateau=false){
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
		if ($laCase->getDecor() == 1 && ($laCase->getJoueur() != $joueurAppelant || $laCase->getCellules() > 0))
			return false; // glace et case non controlée
		if ($laCase->getDecor() == 3)//obstacle
			return false;
		for($i=-1;$i<2;$i++) for($j=-1;$j<2;$j++){//on va regarder si une case autour appartient au joueur
			if ($i==0 && $j==0) continue; // on a déjà testé la case centrale
			if (!$options->yaPlacementDiag() && abs($i)+abs($j)==2) continue;//pas en diagonale
			$nvx = $x+$i; $nvy = $y+$j;
			if ($options->quelTypeBord() != 2 && (!entre(0,$nvx,$this->tailleX-1) || !entre(0,$nvy,$this->tailleY-1)))
				continue;//après le bord
			$nvx = mettreEntre($nvx,$this->tailleX);$nvy = mettreEntre($nvy,$this->tailleY);//au cas où le monde est rond
			$autreCase = $this->getCase($nvx,$nvy);
			if ($autreCase->getJoueur() == $joueurAppelant && $autreCase->getCellules() > 0)
				return true; //case controlée par ce joueur
		}
		return false;
	}
	function ouPeutJouer($options,$joueurAppelant,$chateau=false){//renvoie un tableau des positions jouables
		$positions = array();
		for ($x=0;$x<$this->tailleX;$x++)
			for ($y=0;$y<$this->tailleY;$y++)
				if ($this->peutJouerEn($options,$x,$y,$joueurAppelant,$chateau))
					$positions[] = array($x,$y);
		return $positions;
	}
	function peutJouer($options,$joueurAppelant){//vérifie si le joueur appelant peut jouer
		return count($this->ouPeutJouer($options,$joueurAppelant)) > 0;
		for ($x=0;$x<$this->tailleX;$x++)
			for ($y=0;$y<$this->tailleY;$y++)
				if ($this->getCase($x, $y)->getJoueur()==$joueurAppelant && $this->getCase($x, $y)->getCellules()>0)
					return true;
		return false;
	}
	function yaGagnant(){//regarde s'il n'y a qu'un type de joueur sur la carte
		$joueursRestants = array();$gagnant = 0;
		for ($x=0;$x<$this->tailleX;$x++) for ($y=0;$y<$this->tailleY;$y++){
			$j = $this->getCase($x,$y)->getJoueur();
			$c = $this->getCase($x,$y)->getCellules();
			if ($c>0 && $j>0) {
				$joueursRestants[$j] = 1; $gagnant = $j;
				if (count($joueursRestants)>=2) return false;
			}
		}
		if (count($joueursRestants)>=2) return false;
		return $j;
	}
	
	function fromXML($Xplateau){
		$lePlateau = new PlateauDeJeu(0+$Xplateau->get_attribute("taillex"),
								0+$Xplateau->get_attribute("tailley"),
								false);//pour que les cases ne soient pas initialisées
		$lignes_array = $Xplateau->get_elements_by_tagname( "ligne" );
		foreach($lignes_array as $Xligne){
			$y = 0+$Xligne->get_attribute("y");
			$cases_array = $Xligne->get_elements_by_tagname( "case" );
			foreach($cases_array as $Xcase){
				$x = 0+$Xcase->get_attribute("x");
				$lePlateau->plateau[$y][$x] = UneCase::fromXML($Xcase);
			}
		}
		return $lePlateau;
	}
	function fromSXML($Xplateau){
		$lePlateau = new PlateauDeJeu(0+$Xplateau["taillex"],
								0+$Xplateau["tailley"],
								false);//pour que les cases ne soient pas initialisées
		foreach($Xplateau->children() as $Xligne){
			$y = 0+$Xligne["y"];
			foreach($Xligne->children() as $Xcase){
				$x = 0+$Xcase["x"];
				$lePlateau->plateau[$y][$x] = UneCase::fromSXML($Xcase);
			}
		}
		return $lePlateau;
	}
	
	function toSXML($parent){//renvoie le noeud simpleXML
		$Xtableau = $parent->addChild("tableaudejeu");
		$Xtableau->addAttribute("taillex", $this->tailleX);
		$Xtableau->addAttribute("tailley", $this->tailleY);

		for($i=0;$i<$this->tailleY;$i++){
			$Xligne = $Xtableau->addChild("ligne");
			$Xligne->addAttribute("y", $i);
			for($j=0;$j<$this->tailleX;$j++){
				$Xcase = $this->getCase($j,$i)->toSXML($Xligne,$j,$i);
			}
		}
		return $Xtableau;
	}
	function toXML($xml_partie){//renvoie un DOMNode
		$Xtableau = $xml_partie->create_element("tableaudejeu");
		$Xtableau->set_attribute("taillex", $this->tailleX);
		$Xtableau->set_attribute("tailley", $this->tailleY);

		for($i=0;$i<$this->tailleY;$i++){
			$Xligne = $Xtableau->create_element("ligne");
			$Xtableau->append_child($Xligne);
			$Xligne->set_attribute("y", $i);
			for($j=0;$j<$this->tailleX;$j++){
				$Xcase = $this->getCase($j,$i)->toXML($xml_partie,$j,$i);
				$Xligne->append_child($Xcase);
			}
		}
		return $Xtableau;
	}
}

/****** classe de cases du plateau de jeu  ****/
class UneCase {
	var $joueur;//à qui appartient la case
	var $nbcellules;//combien de cellules sont sur la case
	var $chateau;//y a t il un chateau ? 
	var $max;//maximum de cellules sur la cas
	var $decor;//0 rien, 1 glace, 2 chaud, 3 obstacle
	var $numTourUtilisee;
	
	function UneCase($decor = 0){
		$this->numTourUtilisee = 0;
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
					$this->numTourUtilisee = $decor->numTourUtilisee;
				}
				break;
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
		$uneCase->numTourUtilisee = $this->numTourUtilisee;
		$uneCase->setCellules($this->getCellules());
		$uneCase->setChateau($this->getChateau());
		$uneCase->setMax($this->getMax());
		$uneCase->setDecor($this->getDecor());
		return $uneCase;
	}
	function setJoueur($joueur){$this->joueur = $joueur;}
	function getJoueur(){return $this->joueur;}
	function placeJoueur($joueur){//place un joueur au début du jeu
		$this->setDecor(0);
		$this->setJoueur($joueur);
		$this->setCellules(1);
	}
	
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
	function vaExploser(){return ($this->nbcellules >= $this->max);}
	function preteAExploser(){return ($this->nbcellules >= $this->max-($this->decor==2?2:1));}
	
	function getDecor(){return $this->decor;}
	function setDecor($decor){if ($decor!=3 || $this->nbcellules==0) $this->decor = $decor;}
	
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
	function fromSXML($Xcase){
		//joueur, cellules, chateau?, max, decor
		$laCase = new UneCase(0+$Xcase["joueur"],
								0+$Xcase["cellules"],
								0+$Xcase["chateau"],
								0+$Xcase["max"],
								0+$Xcase["decor"]
								);
		return $laCase;
	}
	
	function toSXML($parent,$x,$y){//ajoute un enfant au parent
		$Xcase = $parent->addChild("case");
		$Xcase->addAttribute("x", $x);
		$Xcase->addAttribute("y", $y);
		$Xcase->addAttribute("decor", $this->getDecor());
		$Xcase->addAttribute("joueur", $this->getJoueur());
		$Xcase->addAttribute("cellules", $this->getCellules());
		$Xcase->addAttribute("max", $this->getMax());
		$Xcase->addAttribute("chateau", $this->getChateau()?1:0);
		return $Xcase;
	}
	function toXML($xml_partie,$x,$y){//renvoie un DOMNode
		$Xcase = $xml_partie->create_element("case");
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

/*
$plateau = new PlateauDeJeu(4,4,true);
$plateau->getCase(0,0)->setJoueur(1);
$plateau->getCase(3,3)->setJoueur(2);
$options = new Options($chateauxPermis=0,$profondeur=100,$typeBord=2,$ajoutDiag=1,$explosionJoueur=1);
echo "testons !<br />";
$tabDist = array();
for ($y=0;$y<4;$y++){
	echo "<br />\n";
	$tabDist[$y] = array();
	for ($x=0;$x<4;$x++){
		echo ($tabDist[$y][$x] = $plateau->distance($options,$x,$y))." ";
	}
}
var_dump( $tabDist);
*/
?>