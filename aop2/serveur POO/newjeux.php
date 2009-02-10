<?php
/******** classes de création de jeu ******/

define ("mot_de_passe_ia","jesuisuneia!");
define ("fichier_parties", "lespartiesencours.xml");

include_once ("classes.php");

class CreaJeu{

	var $opt_chateaux_actifs;
	var $opt_type_bords;
	var $opt_ajout_diagonale;
	var $opt_explosion_joueur;
	var $opt_profondeur_jeu;
	var $opt_partie_cachee;
	var $opt_avec_decor;//vaut 0 sans, 1 10%, 2 30%, 3 50% (nombre d'obstacles inférieur strict à min(x,y) )
	var $opt_attente_joueurs;
	
	var $numero_partie;
	var $nomfichier;
	
	var $partie;//la partie résultante
		
	var $erreur;//indique s'il y a une erreur

		
	function CreaJeu(){
		$this->erreur = false;
		$this->partie = new Partie();
		
		//chargement de tous les paramètres
		$erreur = $this->loadParameters();
		if ($erreur) die($erreur);
		
		//création du plateau de jeu
		$this->partie->tableauJeu = new PlateauDeJeu($this->x,$this->y,true);
		
		
		$tabDecor = $this->creationDecor();
		$positionsJoueurs = $this->positionnementJoueurs();
		
		$this->partie->joueurEnCours = rand(1, $this->partie->nbJoueurs);
		for ($j=1;$j <= $this->partie->nbJoueurs;$j++){
			$this->partie->joueur[$j]->derniereAction = new Action("n",$positionsJoueurs[$j][0],$positionsJoueurs[$j][1]);
		}
		$this->partie->tableauJeu->metsLesJoueurs($positionsJoueurs);
		$this->partie->tableauJeu->poseDecor($tabDecor);
		$this->partie->tableauJeu->metsLesMax($this->partie->options);
		
		$this->numero_partie = getNumeroPartie();
		$this->nomfichier = getNomFichier($this->numero_partie);
		$this->partie->demarree = ($this->opt_attente_joueurs == 0);
		
		$partiesEnCours = new PartiesEnCours();
		$partiesEnCours->ajouterPartie($this->numero_partie, $this->opt_partie_cachee, $this->partie);
		
		$this->partie->enregistrerXML($this->nomfichier);
	}
	
	function getPartie(){return $this->partie;}
	
	function loadParameters() {
		$this->opt_chateaux_actifs=1;
		$this->opt_type_bords=1;
		$this->opt_ajout_diagonale=1;
		$this->opt_explosion_joueur=1;
		$this->opt_profondeur_jeu=100;
		$this->opt_partie_cachee=0;
		$this->opt_avec_decor=1;
		$this->opt_attente_joueurs=0;
		if(isset($_POST["nbJoueurs"])) {
			$this->partie->nbJoueurs = (int)$_POST["nbJoueurs"];
		} else return "Erreur : Nombre de joueurs indéterminé";
		
		foreach($_POST as $key=>$nom){ //traitement des joueurs
			if(substr($key,0,9)=="nomJoueur"){
				$indice=0+substr($key,9); 
				if($indice <= $this->partie->nbJoueurs and $indice >= 1) {
					$si_mdp = isset($_POST["si_mdp".$indice]);
					$is_ia = isset($_POST["is_ia".$indice]);
					if(!isset($_POST["mdp".$indice])) return "mdp".$indice." is not available";
					if(!isset($_POST["couleur".$indice])) return "couleur".$indice." is not available";
					$mdp = $_POST["mdp".$indice];
					$couleur = $_POST["couleur".$indice];
					$niveau = $_POST["nivia".$indice];
					$this->partie->joueur[$indice] = new Joueur($nom, 
														$couleur, 
														($is_ia?md5(mot_de_passe_ia):($si_mdp?md5($mdp):"0")),
														($is_ia?1:0),
														$niveau);
				}
			}
		}
		while(True) {
			if(isset($_POST["x"])) {
				$this->x = (int)$_POST["x"];
				if($this->x >= 2) break; // ça veut dire "OK"
			} return "Taille x non fournie";
		}
		while(True) {
			if(isset($_POST["y"])) {
				$this->y = (int)$_POST["y"];
				if($this->y >= 2) break; // ça veut dire "OK"
			} return "Taille y non fournie";
		}
		if(isset($_POST["opt_chateaux_actifs"]))
		$this->opt_chateaux_actifs = (int)$_POST["opt_chateaux_actifs"];
		if(isset($_POST["opt_type_bords"]))
		$this->opt_type_bords = (int)$_POST["opt_type_bords"];
		if(isset($_POST["opt_ajout_diagonale"]))
		$this->opt_ajout_diagonale = (int)$_POST["opt_ajout_diagonale"];
		if(isset($_POST["opt_explosion_joueur"]))
		$this->opt_explosion_joueur = (int)$_POST["opt_explosion_joueur"];
		if(isset($_POST["opt_profondeur_jeu"]))
		$this->opt_profondeur_jeu = (int)$_POST["opt_profondeur_jeu"];
		if(isset($_POST["opt_avec_decor"]))
		$this->opt_avec_decor = (int)$_POST["opt_avec_decor"];
		if(isset($_POST["opt_partie_cachee"]))
		$this->opt_partie_cachee = (int)$_POST["opt_partie_cachee"];
		if(isset($_POST["opt_attente_joueurs"]))
		$this->opt_attente_joueurs = (int)$_POST["opt_attente_joueurs"];
		$this->partie->options = new Options($this->opt_chateaux_actifs,
											$this->opt_profondeur_jeu,
											$this->opt_type_bords,
											$this->opt_ajout_diagonale,
											$this->opt_explosion_joueur);
		return false;
	}
	
	function creationDecor(){//crée un tableau bidimensionnel de décor
		$tabDecor = array();
		for ($y = 0; $y < $this->y; $y++){
			$tabDecor[$y] = array();
			for ($x = 0; $x < $this->x; $x++){
				$tabDecor[$y][$x] = 0;
			}
		}
		if ($this->opt_avec_decor > 0){
			$nbDecor = $this->y * $this->x;
			$nbDecor /= ($this->opt_avec_decor==1?10:($this->opt_avec_decor==2?4:2));
			$nbDecor = round($nbDecor);
			$nbDecor -= $this->partie->nbJoueurs;
			$nbmaxobstacles = min($this->x,$this->y)-1;
			$nbostacles = 0;
			$nbmaxglace = min($this->x,$this->y)-1;
			$nbglace = 0;
			for ($i = 0; $i < $nbDecor; $i++){
				$unDecor = rand(($nbglace>=$nbmaxglace?1:2),($nbostacles>=$nbmaxobstacles?2:3));
				$nbostacles += ($unDecor==3?1:0);
				$nbglace +=  ($unDecor==1?1:0);
				$tabDecor[rand(0,$this->y-1)][rand(0,$this->x-1)] = $unDecor;
			}
		}
		return $tabDecor;
	}
	
	//  Positionnement des joueurs de manière à minimiser la somme des inverses des distances.
	function positionnementJoueurs(){
		//  Début aléatoire de manire à maximiser les distances.
		$pos = array();$x = $this->x;$y = $this->y;
		$nbJoueurs = $this->partie->nbJoueurs;
		for($i = 1; $i <= $nbJoueurs; $i++) {
			$pos[$i] = array(rand(0, $x-1), rand(0, $y-1));
		}
		function inverseDistance2($pos1, $pos2,$typebord=1,$x, $y) {
			if ($typebord != 2){
				$denom = (pow($pos1[0] - $pos2[0], 2) + pow($pos1[1] - $pos2[1], 2));//carré de la distance
				if($denom == 0) return 100000;
				return 1/$denom;
			}
			else {
				$denom1 = (pow($pos1[0] - $pos2[0], 2) + pow($pos1[1] - $pos2[1], 2));//carré de la distance
				$denom2 = (pow($pos1[0] - $pos2[0]-$x, 2) + pow($pos1[1] - $pos2[1], 2));//carré de la distance
				$denom3 = (pow($pos1[0] - $pos2[0]+$x, 2) + pow($pos1[1] - $pos2[1], 2));//carré de la distance
				$denom4 = (pow($pos1[0] - $pos2[0], 2) + pow($pos1[1] - $pos2[1]-$y, 2));//carré de la distance
				$denom5 = (pow($pos1[0] - $pos2[0], 2) + pow($pos1[1] - $pos2[1]+$y, 2));//carré de la distance
				$denom6 = (pow($pos1[0] - $pos2[0]-$x, 2) + pow($pos1[1] - $pos2[1]-$y, 2));//carré de la distance
				$denom7 = (pow($pos1[0] - $pos2[0]+$x, 2) + pow($pos1[1] - $pos2[1]-$y, 2));//carré de la distance
				$denom8 = (pow($pos1[0] - $pos2[0]+$x, 2) + pow($pos1[1] - $pos2[1]+$y, 2));//carré de la distance
				$denom9 = (pow($pos1[0] - $pos2[0]-$x, 2) + pow($pos1[1] - $pos2[1]+$y, 2));//carré de la distance
				$denom = min($denom1,$denom2,$denom3,$denom4,$denom5,$denom6,$denom7,$denom8,$denom9);
				if($denom == 0) return 100000;
				return 1/$denom;
			}
		}
		function adjust($value, $max, $min = 1) {
			if($value>$max) $value=$max;
			if($value<$min) $value=$min;
			return $value;
		}
		function stochasticPositionning($pos, $iterations,$nbJoueurs, $x, $y,$typebord) {
			$distance_map = array();

			for($i = 1; $i <= $nbJoueurs; $i++) {
				for($j = 1; $j <= $nbJoueurs; $j++) {
					if($i != $j) {
						$distance_map[$i][$j] = inverseDistance2($pos[$i], $pos[$j],$typebord, $x, $y);
					}
				}
			}
			//print_r ($distance_map);

			for($k = 1; $k <= $iterations; $k++) {
				$joueur_to_move = rand(1, $nbJoueurs);
				$pos_current = $pos[$joueur_to_move];
				$new_pos = array(adjust($pos_current[0]+rand(-1, 1), $x-1,0), adjust($pos_current[1]+rand(-1, 1), $y-1,0));
				$sum_before = 0;
				$sum_after = 0;
				$new_partial_distance_map = array();
				for($j = 1; $j <= $nbJoueurs; $j++) {
					$new_partial_distance_map[$j] = inverseDistance2($new_pos, $pos[$j],$typebord, $x, $y);
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
		$pos = stochasticPositionning($pos, 1000,$nbJoueurs, $x, $y,$this->opt_type_bords);
		return $pos;
	}

	function affichageLiensPartie(){//affiche les liens pour la partie
		echo "Partie ".$this->numero_partie." créée !<br />\n";

		for($i = 1; $i <= $this->partie->nbJoueurs; $i++) {
			$url = getUrlJoueur($this->numero_partie, $i, isset($_POST["si_mdp".$i]), $_POST["mdp".$i]);
			echo '<a href="'.$url.'">Le jeu pour '.$this->partie->joueur[$i]->nom.'</a><br />';
		}
	}
	
	function ajouterJoueur(){//avec paramètres POST, seulement humain
		$raison_erreur = "";
		while (true){
			if (!array_key_exists("p",$_POST) || !array_key_exists("nom",$_POST) || !array_key_exists("couleur",$_POST)){
				$raison_erreur = "Information de partie, de nom ou de couleur manquante.";
				break;//c'est pas autorisé
			}
			$noPartie = $_POST["p"];
			$fichier = getNomFichier($noPartie);
			$partie = Partie::ouvrirXML($fichier);
			if (!$partie){
				$raison_erreur = "Partie inconnue.";
				break;//c'est pas autorisé
			}
			$noJoueur = $partie->addJoueur($_POST["nom"],$_POST["couleur"]);
			if (!$noJoueur){
				$raison_erreur = "Partie probablement déjà commencée.";
				break;//c'est pas autorisé
			}
			$partie->enregistrerXML($fichier);
			$PEC = new PartiesEnCours();
			$PEC->supprimerPartie($noPartie,false);
			$PEC->ajouterPartie($noPartie,false,$partie);

			echo "Vous avez été ajouté avec succès à la partie ".$noPartie.".<br />\n";
			echo "<a href=\"".getUrlJoueur($noPartie, $noJoueur )."\">Cliquez ici pour rentrer</a><br />";
			echo "Bienvenue dans le jeu ".$_POST["nom"]." !\n";
			return true;
		}
		echo "Action non autoris&eacute;e : \n".$raison_erreur;
		return false;
	}	
}

class PartiesEnCours {
	var $enSimpleXML;
	var $enDOMDocument;
	var $chargementOK;
	
	function PartiesEnCours(){//charge les parties en cours
		$this->chargementOK = false;
		if (floatval(phpversion())>=5)
			$this->chargementOK = $this->chargeSXML();
		else
			$this->chargementOK = $this->chargeXML();
	}

	function chargeSXML(){//charge en SimpleXML
		$file_exists = file_exists(fichier_parties);
		if ($file_exists){
			$this->enSimpleXML = new SimpleXMLElement(file_get_contents(fichier_parties));
		}
		else {
			$this->enSimpleXML = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><parties></parties>');
		}
		return true;
	}
	function chargeXML(){//charge en DOMDocument
		$file_exists = file_exists(fichier_parties);
		if ($file_exists){
			$this->enDOMDocument = new DOMDocument();
			$this->enDOMDocument->load(fichier_parties);
		}
		else {
			$this->enDOMDocument = domxml_new_doc("1.0");
			$this->enDOMDocument->append_child($this->enDOMDocument->create_element( "parties" ));
		}
	}
	
	function ajouterUnJoueur(){//avec les paramètre POST
		CreaJeu::ajouterJoueur();
	}
	
	function ajouterPartie($numero, $cachee, $partie){//ajoute une partie
		if (floatval(phpversion())>=5)
			$this->ajouterPartieSXML($numero, $cachee, $partie);
		else
			$this->ajouterPartieXML($numero, $cachee, $partie);
		$this->enregistrerParties();
	}
	function ajouterPartieSXML($numero, $cachee, $partie){
		$Xpartie = $this->enSimpleXML->addChild("partie");
		$Xpartie->addAttribute("numero", $numero);
		$Xpartie->addAttribute("cachee", $cachee);
		$Xpartie->addAttribute("nbJoueurs", $partie->nbJoueurs);
		foreach($partie->joueur as $index => $joueur) {
			$joueur_xml = $Xpartie->addChild("joueur");
			$joueur_xml->addAttribute("numero", $index);
			$joueur_xml->addAttribute("couleur", $joueur->couleur);
			$joueur_xml->addAttribute("nom", utf8_encode($joueur->nom));
		}
		$this->enregistrerParties();
	}
	function ajouterPartieXML($numero, $cachee, $partie){
		$parties_array = $this->enDOMDocument->get_elements_by_tagname( "parties" );
		$Xparties = $parties_array[0];

		$Xpartie = $this->enDOMDocument->create_element("partie");
		$Xpartie->set_attribute("numero", $numero);
		$Xpartie->set_attribute("cachee", $cachee);
		$Xpartie->set_attribute("nbJoueurs", $nbJoueurs);
		$Xparties->append_child($Xpartie);

		foreach($partie->joueur as $index => $joueur) {
			$joueur_xml = $this->enDOMDocument->create_element("joueur");
			$joueur_xml->set_attribute("numero", $index);
			$joueur_xml->set_attribute("couleur", $joueur->couleur);
			$joueur_xml->set_attribute("nom", utf8_encode($joueur->nom));
			$Xpartie->append_child($joueur_xml);
		}

	}
	
	function supprimerPartie($numero,$suppressionFichier=true){//charge les parties en cours
		$fichier = getNomFichier($numero);
		if (file_exists($fichier) && $suppressionFichier)
			unlink($fichier);
		$changement = false;
		if (floatval(phpversion())>=5)
			$changement = $this->supprimerPartieSXML($numero);
		else
			$changement = $this->supprimerPartieXML($numero);
		if ($changement)
			$this->enregistrerParties();
	}
	function supprimerPartieSXML($numero){
		$Xparties = $this->enSimpleXML;
		//ce qui suit bidouille la variable pour enlever ce qu'on cherche
		for($p=0;$Xparties->partie[$p];$p++){
			if (0+$Xparties->partie[$p]["numero"]==0+$numero){
				unset($Xparties->partie[$p]);
				return true;
			}
		}
		return false;
	}
	function supprimerPartieXML($numero){
		$Xparties = $this->enDOMDocument->get_elements_by_tagname( "parties" );
		$Xparties = $Xparties[0];
		foreach ($Xparties->getElementsByTagName( "partie" ) as $Xpartie) {
			if($Xpartie->getAttribute("numero") != $numero) continue;
			$Xparties->removeChild($partie);
			return true;
			break;
		}
		return false;
	}
	
	function afficherParties($admin=false){//tableauArguments["pw"] doit être fixé, supprimerPartie(numero) opérationnel, et un <div id="comm"/>
		if ($admin){//on écrit la fonction de suppression
			/*echo "<script type='text/javascript'>\nfunction supprimerPartie(numero){\nvar xhr = createXHR();\n".
				"var chaineDAppel = '".serveur_fichier."?a=s&pw='+tableauArguments['pw']+'&p='+numero+'&j=admin&nocache=' + Math.random();\n".
				"xhr.onreadystatechange  = function(){\nif(xhr.readyState  == 4){\nif(xhr.status  == 200) {\n".
				"window.location.reload();\n} else {\ndocument.getElementById('comm').innerHTML = 'partie non supprimée';\n".
				"}\n}\n};\ndocument.getElementById('comm').innerHTML = 'Attente du serveur...';\n".
				"xhr.open('GET', chaineDAppel, true); xhr.send(null);}\n</script>\n";*/
		} else {
?>
<script type='text/javascript'>//crée un formulaire
function sajouter(numeroPartie){
	chaineaafficher = "<form action=\"<?php echo serveur_fichier; ?>?a=autrejoueur&p="+numeroPartie+"\" method='POST'>";
	chaineaafficher += "<input type=\"hidden\" name=\"p\" value=\""+numeroPartie+"\" />";
	chaineaafficher += "<input style=\"vertical-align:bottom;\" type=\"text\" name=\"nom\" value=\"Votre nom\" onfocus=\"if (this.value=='Votre nom') this.value='';\" />";
	chaineaafficher += "<?php echo addslashes(addSelectOption(
array("text" => " Couleur",
	"idname" => "couleur",
	"options" => $GLOBALS["color_array"],
	"callback" => "",//"changecolor(".$i.")",
	"default_index" => 0,
	"color" => True
),false)); ?>";
	chaineaafficher += "<input style=\"vertical-align:bottom;\" type=\"submit\" value=\"OK\" /></form>";
	document.getElementById("action"+numeroPartie).innerHTML = chaineaafficher;
}
</script>
<?php
		}
		if (floatval(phpversion())>=5)
			$this->afficherPartiesSXML($admin);
		else
			$this->afficherPartiesXML($admin);
	}
	
	function afficherPartiesSXML($admin=false){
		echo "<table>";
		$nbParties = 0; $nbCachees=0;
		$chaineAAfficherDansTable = "";
		$parties = $this->enSimpleXML;
		foreach ($parties->children() as $partie) {
			if($partie["cachee"]=="1") {$nbCachees++;if (!$admin) continue;}
			$nbParties++;
			$chaineAAfficherDansTable .= "<tr>";
			$chaineAAfficherDansTable .= "<td ".($partie["cachee"]=="1"?'style="font-style:italic;"':"").">";
			$chaineAAfficherDansTable .= "Partie ".$partie["numero"];
			$chaineAAfficherDansTable .= " : </td>";
			foreach ($partie->children() as $joueur) {
				$chaineAAfficherDansTable .= "<td>";
				$url = getUrlJoueur($partie["numero"], $joueur["numero"]);
				$chaineAAfficherDansTable .= '<a style="color:black;background-color:#'.$joueur["couleur"].';" href="'.$url.'">'.$joueur["nom"].'</a>';
				$chaineAAfficherDansTable .= "</td>";
			}
			$chaineAAfficherDansTable .=  "<td id='action".$partie["numero"]."'>";
			if ($admin)
				$chaineAAfficherDansTable .= "<input style=\"vertical-align:bottom;\" type=\"button\" value=\"Supprimer\" onclick=\"supprimerPartie('".$partie["numero"]."');\" />";
			else
				$chaineAAfficherDansTable .= "<input style=\"vertical-align:bottom;\" type=\"button\" value=\"S'ajouter\" onclick=\"sajouter('".$partie["numero"]."');\" />";
			$chaineAAfficherDansTable .=  "</td>";

			$chaineAAfficherDansTable .= "</tr>\n";
		}
		echo "<tr><td colspan=\"3\">$nbParties parties plus $nbCachees cachées.</td></tr>";
		echo $chaineAAfficherDansTable;
		echo "</table>";
	}
	function afficherPartiesXML($admin=false){
		$parties = $this->enDOMDocument->get_elements_by_tagname("partie");
		if (count($parties) == 0)
			echo $no_party;
		foreach ($parties as $partie) {
			if($partie->get_attribute("cachee") == "1" && !$admin) continue;
			echo "<tr>";
			echo "<td>";
			$numero_partie = $partie->get_attribute("numero");
			echo "Partie ".$numero_partie;
			echo " : </td>";
			foreach ($partie->get_elements_by_tagname("joueur") as $joueur) {
				echo "<td>";
				$url = getUrlJoueur($numero_partie, $joueur->get_attribute("numero"));
				echo '<a href="'.$url.'">'.$joueur->get_attribute("nom").'</a>';
				echo "</td>";
			}
			echo "<td>";
			if ($admin){
				echo "<input type=\"button\" value=\"Supprimer\" onclick=\"supprimerPartie('".$numero_partie."');\" />";
			}
			echo "</td>";
			echo "</tr>\n";
		}
		echo "</table>";

	}

	function enregistrerParties(){
		if (floatval(phpversion())>=5)
			$this->enregistrerPartiesSXML();
		else
			$this->enregistrerPartiesXML();
	}
	function enregistrerPartiesSXML(){
		$this->enSimpleXML->asXML(fichier_parties);
	}
	function enregistrerPartiesXML(){
		$this->enDOMDocument->dump_file(fichier_parties);
	}

}
/*
$_POST["nbJoueurs"] = "2";
$_POST["x"] = 6;
$_POST["y"] = 6;
$_POST["nomJoueur1"] = "Ced";
$_POST["mdp1"] = "Ced";
$_POST["nivia1"] = "0";
$_POST["couleur1"] = "0000FF";
$_POST["nomJoueur2"] = "Sara";
$_POST["mdp2"] = "Sara";
$_POST["couleur2"] = "FF8080";
$_POST["nivia2"] = "0";
$_POST["opt_partie_cachee"] = "0";
$_POST["opt_type_bords"] = "2";

$jeu = new CreaJeu();
$jeu->affichageLiensPartie();

echo "<br /><br />";

$_POST["nom"] = "Mik";
$_POST["couleur"] = "00FF00";
$_POST["p"] = $jeu->numero_partie;

CreaJeu::ajouterJoueur();


echo "<br /><br />";
$parties = new PartiesEnCours();
//$parties->afficherParties(true);
$parties->afficherParties(false);



//echo "<textarea cols=70>".$jeu->getPartie()->enregistrerXML(false)."</textarea>";

$parties->supprimerPartie($jeu->numero_partie);
/*
$parties->afficherParties(true);
$parties->afficherParties();
*/

?>