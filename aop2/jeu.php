<?php
/*
Fichier: jeu.php
Date: 27/01/2009
Auteur: C�dric Mayer / Mika�l Mayer
But: Interface avec le fichier de jeu
	Re�oit les requ�tes de jeu, regarde si c'est bon et accepte ou non la requete et agit en cons�quence
	Re�oit la requ�te de mise � jour de son propre jeu
	Re�oit la requ�te de plateau de jeu et le renvoie
*/
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
include_once("fonctions.inc");
define ("XMLHeader", "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>");

if (!array_key_exists("p",$_GET) || !array_key_exists("j",$_GET) || !array_key_exists("a",$_GET)){
	header("HTTP/1.0 404 Not found",true,404);die();
}

$fichierCourant = "aop".$_GET["p"]."bacteries.par";
if (!file_exists($fichierCourant)){
	if ($_GET["a"] == "s"){//on traite la suppression de partie
		header('Content-Type: text/xml');
		$camarche = supprimerPartie($_GET["p"],"lespartiesencours.xml",true);
		echo XMLHeader."<reponse><action traitee=\"".$camarche."\" partiesupprimee=\"".$_GET["p"]."\" /></reponse>";
		die();//pas besoin d'aller plus loin
	}
	header("HTTP/1.0 404 Not found",true,404);die();
}

$contenuFichier = file($fichierCourant,FILE_IGNORE_NEW_LINES);
foreach ($contenuFichier as $line => $contenu)
	$contenuFichier[$line] = trim($contenu);
$nbJoueurs = 0 + $contenuFichier[0];
$joueurEnCours = 0 + $contenuFichier[1];
$tailleX = 0 + $contenuFichier[7+2*$nbJoueurs];
$tailleY = 0 + $contenuFichier[8+2*$nbJoueurs];
$noTour = 0 + $contenuFichier[9+2*$nbJoueurs+$tailleY*2];

if ($_GET["a"]=="m"){//on peut traiter la mise à jour de connaissance du joueur en cours
	if (!array_key_exists("k",$_GET)){
		header('Content-Type: text/xml');
		echo XMLHeader."<reponse><k valeur=\"".$joueurEnCours."\" /><n valeur=\"".$noTour."\" /></reponse>";
		die();//pas besoin d'aller plus loin
	} else {
		if ($_GET["k"]=="0"){
			header('Content-Type: text/xml');
		echo XMLHeader."<reponse><k valeur=\"".$joueurEnCours."\" /><n valeur=\"".$noTour."\" /></reponse>";
			die();//pas besoin d'aller plus loin
		}
	}
}
$joueurs = array();$derniereAction=array();
for ($i = 1; $i <= $nbJoueurs; $i++)
	$joueurs[$i] = explode("\t",$contenuFichier[1+$i]);

for ($i = 1; $i <= $nbJoueurs; $i++)
	$derniereAction[$i] = explode("\t",$contenuFichier[1+$nbJoueurs+$i]);

$joueurAppelant = 0;
if (strlen($_GET["j"])>=3){//on cherche le n° du joueur avec son nom
	if ($_GET["j"] == "observateur")
		$joueurAppelant=256;
	else if ($_GET["j"] == "admin")
		$joueurAppelant=3000;
	else
		for ($i = 1; $i <= $nbJoueurs; $i++)
			if ($joueurs[$i][0]==$_GET["j"]){
				$joueurAppelant = $i;
				break;
			}
} else {
	$joueurAppelant = 0+$_GET["j"];
}
if (($joueurAppelant <= 0 || $joueurAppelant > $nbJoueurs)&&($joueurAppelant!=256 && $joueurAppelant!=3000)){
	header("HTTP/1.0 404 Not found",true,404);die();
}
if ($joueurAppelant<256) if (strlen($joueurs[$joueurAppelant][2])>1){//on peut traiter le mot de passe
	if (array_key_exists("pw",$_GET)){
		if (md5($_GET["pw"])!=$joueurs[$joueurAppelant][2]){
			header("HTTP/1.0 404 Not found",true,404);die();
		}
	} else {
		header("HTTP/1.0 404 Not found",true,404);die();
	}
}
if ($joueurAppelant==3000){//on peut traiter le mot de passe administrateur
	if (array_key_exists("pw",$_GET)){
		if (md5($_GET["pw"])!=$mdpadminmd5){
			header("HTTP/1.0 404 Not found",true,404);die();
		}
	} else {
		header("HTTP/1.0 404 Not found",true,404);die();
	}
}

if ($_GET["a"] == "s"){//on traite la suppression de partie
	$camarche = supprimerPartie($_GET["p"],"lespartiesencours.xml",true);
	header('Content-Type: text/xml');
	echo XMLHeader."<reponse><action traitee=\"".$camarche."\" partiesupprimee=\"".$_GET["p"]."\" /></reponse>";
	die();//pas besoin d'aller plus loin
}


if ($_GET["a"]=="g") {//on peut traiter l'envoi du fichier actuel
	for ($i = 1; $i <= $nbJoueurs; $i++){
		if ($i != $joueurAppelant)
			$joueurs[$i][2] = "0";//on efface les mots de passe sauf le sien
		if ($i != $joueurAppelant && $joueurs[$i][3] != 1)
			$joueurs[$i][3] = "2";//on dit que les autres joueurs jouent sur le net
		$contenuFichier[1+$i] = implode("\t",$joueurs[$i]);
	}
	echo implode("\n",$contenuFichier);
	die();
}
if ($_GET["a"]=="m") {//on renvoie où le joueur k a joué et quoi
	$k = 0+$_GET["k"];//cas k == 0 ou absent traité
	if ($k<0 || $k>$nbJoueurs) {header("HTTP/1.0 404 Not found",true,404);die();}
	$tabReponse = $derniereAction[$k];
	header('Content-Type: text/xml');
	$chaineReponse = XMLHeader."<reponse><a valeur=\"".$tabReponse[0]."\" /><x valeur=\"".$tabReponse[1]."\" /><y valeur=\"".$tabReponse[2]."\" /><n valeur=\"".$tabReponse[3]."\" /><k valeur=\"".$k."\" /></reponse>";
	echo $chaineReponse;
	die();
}

/************ lecture des paramètres du jeu *********/
	$N_OPT_CHATEAUX_ACTIFS = 0;
	$N_OPT_PROFONDEUR = 1;
	$N_OPT_BORDS_BLOQUES = 2;
	$N_OPT_DIAGONALE = 3;
	$N_OPT_TEMPS_PAS_REEL = 4;

	$options = array(); $nombreDOptions = 5; $indice=2+2*$nbJoueurs;
	for($i=0;$i<$nombreDOptions;$i++)	//chateauxactivés profondeur bordbloqués diagonale tempspasreel
		$options[$i]=0+$contenuFichier[$indice+$i];
	$indice+=$nombreDOptions;
//$tailleX = (int)($contenuFichier[$indice]);
//$tailleY = (int)($contenuFichier[$indice+1]);
	$indice+=2;
$tableauDecor = array();
	for($i=0;$i<$tailleY;$i++){
		$tableauDecor[$i]=explode("\t",$contenuFichier[$indice+$i]);
		for($j=0;$j<$tailleX;$j++)
			$tableauDecor[$i][$j]=(int)($tableauDecor[$i][$j]);
	}
	$indice+=$tailleY;
$tableauJeu = array();
	for($i=0;$i<$tailleY;$i++){
		$tableauJeu[$i]=explode("\t",$contenuFichier[$indice+$i]);
		for($j=0;$j<$tailleX;$j++)
			$tableauJeu[$i][$j]=(int)($tableauJeu[$i][$j]);
	}
	$indice+=$tailleY;
/*$noTour = (int)($contenuFichier[indice++]);*/$indice++;
$tableauDesMax = array();//calcule le maximum de cellules dans les cases
	for($i=0;$i<$tailleY;$i++){
		$tableauDesMax[$i] = array();
		for($j=0;$j<$tailleX;$j++){
			$k = 4;
			if ($options[$N_OPT_BORDS_BLOQUES] == 1){//on compte les bords
					if ($i==0 || $i==$tailleY-1)
						$k--;
					if ($j==0 || $j==$tailleX-1)
						$k--;
			}
			for ($ii=-1;$ii<=1;$ii++)//on regarde les obstacles
				for($jj=-1;$jj<=1;$jj++)
					if (abs($ii)+abs($jj)==1){//pas diagonale
						switch($options[$N_OPT_BORDS_BLOQUES]){
						case 1: //on regarde pas après les bords
						case 0:
							if (entre(0,$ii+$i,$tailleY-1)&&entre(0,$jj+$j,$tailleX-1))
								if ($tableauDecor[$i+$ii][$j+$jj]==3)
									$k--;
							break;
						case 2://on regarde après le bord
							if ($tableauDecor[mettreEntre($i+$ii,$tailleY)][mettreEntre($j+$jj,$tailleX)]==3)
								$k--;
							break;
						}
					}
			$tableauDesMax[$i][$j] = $k;
		}
	}

if ($_GET["a"]=="nvp"){ //changement de mot de passe
	$joueurs[$joueurAppelant][2] = md5($_GET["k"]);
	enregistrerPartie();
	header('Content-Type: text/xml');
	$chaineReponse = XMLHeader."<reponse><action traitee=\"oui\" nouveaumotdepasse=\"".$_GET["k"]."\" /></reponse>";
	echo $chaineReponse;
	die();
}
	
/************* Le joueur joue ! *****************/

//il ne reste que la requete de jeu en x,y
//renvoyer <reponse><action autorisee="oui"/"non"/></reponse>

function pasAutorisee(){//arrete le script si l'action n'est pas autorisée
	header('Content-Type: text/xml');
	$chaineReponse = XMLHeader."<reponse><action autorisee=\"non\" traitee=\"non\" /></reponse>";
	echo $chaineReponse;
	die();
}

function case2chateau($entier){//regarde si sur une case il y a un chateau
	return ($entier>=10000);
}

function case2cellules($entier){//retourne le nombre de cellules dans la case
	return ($entier-(floor($entier/100)*100));
}

function case2joueur($entier){//retourne le n° d'un joueur à qui est la case, même désertée
	/*if (case2cellules($entier) == 0)
		return 0; //renvoie 0 si aucune cellule*/
	return floor(($entier-(case2chateau($entier)?10000:0))/100);
}



	$x = (int) $_GET["x"];
	$y = (int) $_GET["y"];
	$action = ($_GET["a"]=="c"?"c":"n");
	
/************ on vérifie si l'action est autorisée *********/
	if ($tableauDecor[$y][$x] != 0 && $action == "c")
		pasAutorisee(); // chateau et case instable
	if (case2joueur($tableauJeu[$y][$x]) != $joueurAppelant && case2cellules($tableauJeu[$y][$x]) > 0)
		pasAutorisee(); //case déjà controlée par joueur adverse
	if (case2joueur($tableauJeu[$y][$x]) == $joueurAppelant && case2cellules($tableauJeu[$y][$x]) > 0)
		//case controlée par ce joueur
		if ($tableauDecor[$y][$x] == 1 && case2cellules($tableauJeu[$y][$x]) >= $tableauDesMax[$y][$x] - 1)
			pasAutorisee(); // mais glace et limite atteinte
		else
			CbonPeutJouer(); // pas de problème
	if ($tableauDecor[$y][$x] == 3)//obstacle
		pasAutorisee();
	for($i=-1;$i<2;$i++)//on va regarder si une case autour appartient au joueur
		for($j=-1;$j<2;$j++){// 2 bordbloqués  3 diagonale
			if ($i==0 && $j==0)
				continue; // on a déjà testé la case centrale
			if ($options[$N_OPT_DIAGONALE] == 0 && abs($i)+abs($j)==2)
				continue;//pas en diagonale
			$nvx = $x+$i; $nvy = $y+$j;
			if ($options[$N_OPT_BORDS_BLOQUES] != 2 && (!entre(0,$nvx,$tailleX-1) || !entre(0,$nvy,$tailleY-1)))
				continue;//après le bord
			$nvx = mettreEntre($nvx,$tailleX);//au cas où le monde est rond
			$nvy = mettreEntre($nvy,$tailleY);
			if (case2joueur($tableauJeu[$nvy][$nvx]) == $joueurAppelant && case2cellules($tableauJeu[$nvy][$nvx]) > 0)
				CbonPeutJouer(); //case controlée par ce joueur
	}
	pasAutorisee();

function CbonPeutJouer(){//continue de jouer
	extract($GLOBALS,EXTR_REFS);//pour continuer sur les variables

	if ($joueurEnCours != $joueurAppelant){//ne pas traiter la requête, ce n'est pas le bon joueur
		header('Content-Type: text/xml');//si le client est bon, pas besoin d'aller beaucoup plus loin :-)
		$chaineReponse = XMLHeader."<reponse><action autorisee=\"oui\" traitee=\"non\"/></reponse>";
		echo $chaineReponse;
		die();//on arrête le script
	}
	if (array_key_exists("k",$_GET)) if ($_GET["k"] == "0"){
		header('Content-Type: text/xml');//ne pas traiter la requete, c'était pour savoir
		$chaineReponse = XMLHeader."<reponse><action autorisee=\"oui\" traitee=\"non\"/></reponse>";
		echo $chaineReponse;
		die();//on arrête le script
	}
	
	$tableauJeu[$y][$x] += $joueurEnCours*100-case2joueur($tableauJeu[$y][$x])*100;
	if (case2cellules($tableauJeu[$y][$x]) >= 100 - ($tableauDecor[$y][$x]==2?2:1))
		$tableauJeu[$y][$x] = floor($tableauJeu[$y][$x]/100)*100+99;//il a trop de cellules déjà
	else
		$tableauJeu[$y][$x] += ($tableauDecor[$y][$x]==2?2:1);
	if ($action=="c")
		$tableauJeu[$y][$x] += (case2chateau($tableauJeu[$y][$x])?-1:1)*10000;//de||construction d'un chateau
	
	$derniereAction[$joueurEnCours][0] = $action;
	$derniereAction[$joueurEnCours][1] = $x;
	$derniereAction[$joueurEnCours][2] = $y;
	$derniereAction[$joueurEnCours][3] = $noTour;
	purifierTotalement();
	enregistrerPartie();
	header('Content-Type: text/xml');//si le client est bon, pas besoin d'aller beaucoup plus loin :-)
	$chaineReponse = XMLHeader."<reponse><action autorisee=\"oui\" traitee=\"oui\"/></reponse>";
	echo $chaineReponse;
	die();//on arrête le script
}

function purifier(){
	extract($GLOBALS,EXTR_REFS);//pour continuer sur les variables
	
	$changement=false;
	$ouGlaceExplosion = array();//var indiceGlace=0;//préparation des endroits glacés
	$differences = array(); //préparation du traitement des explosions
	$conquetes = array();
	for ($y=0;$y<$tailleY;$y++){
		$differences[$y] = array();
		$conquetes[$y] = array();
		for ($x=0;$x<$tailleX;$x++){
			$differences[$y][$x] = 0;
			$conquetes[$y][$x] = array();
		}
	}
	for ($x=0;$x<$tailleX;$x++) for($y=0;$y<$tailleY;$y++){//traitement des explosions
		  $nbSurCase = $tableauJeu[$y][$x];
		  if (($options[$N_OPT_TEMPS_PAS_REEL] == 1 && case2joueur($nbSurCase)==$joueurEnCours) || $options[$N_OPT_TEMPS_PAS_REEL] == 0)
			if (case2cellules($nbSurCase) >= $tableauDesMax[$y][$x] && !case2chateau($nbSurCase)){//explosion !
	$changement = true;
	for ($ii=-1;$ii<2;$ii++)//va sur les cases d'à côté
		for($jj=-1;$jj<2;$jj++)
			if (abs($ii)+abs($jj)==1){//pas diagonale
				$nvx = $x+$jj; $nvy = $y+$ii;
				$perteBord = false;
				switch($options[$N_OPT_BORDS_BLOQUES]){
				  case 2: //on regarde après les bords
					$nvx = mettreEntre($x+$jj,$tailleX); $nvy = mettreEntre($y+$ii,$tailleY);
				  case 0: $perteBord=true; //on ne regarde pas au bord mais on perd une cellule
				  case 1: //on regarde pas après les bords
					if (entre(0,$nvy,$tailleY-1) && entre(0,$nvx,$tailleX-1)){
						switch($tableauDecor[$nvy][$nvx]){
						case 0: //case normale
							$differences[$y][$x]--;
							if (case2chateau($tableauJeu[$nvy][$nvx])&&(case2joueur($nbSurCase)!=case2joueur($tableauJeu[$nvy][$nvx]))&&case2cellules($tableauJeu[$nvy][$nvx])>=10){//traitement si membrane adverse protgée
								$differences[$nvy][$nvx]--;
							} else {//jeu normal ou destruction de la membrane et conquète des cellules
								//il va y avoir un bug si attaque et défense en même temps d'un chateau
								//on va dire que les attaquants ont toujours priorité... C'est un jeu !
								$differences[$nvy][$nvx]++;
								if (case2chateau($tableauJeu[$nvy][$nvx])&&(case2joueur($nbSurCase)!=case2joueur($tableauJeu[$nvy][$nvx]))&&case2cellules($tableauJeu[$nvy][$nvx])<10)
									$tableauJeu[$nvy][$nvx]-=(case2chateau($tableauJeu[$nvy][$nvx])?10000:0);
								$conquetes[$nvy][$nvx][count($conquetes[$nvy][$nvx])]=case2joueur($nbSurCase);
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
								$conquetes[$nvy][$nvx][count($conquetes[$nvy][$nvx])]=case2joueur($nbSurCase);
							}
							break;
						case 2: //point chaud
							$differences[$y][$x]--;
							$differences[$nvy][$nvx]+=2;
							$conquetes[$nvy][$nvx][count($conquetes[$nvy][$nvx])]=case2joueur($nbSurCase);
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
		
	for ($x=0;$x<$tailleX;$x++)  //post traitement des explosions
		for($y=0;$y<$tailleY;$y++){
			$nbcellules = case2cellules($tableauJeu[$y][$x]);
			if ($nbcellules + $differences[$y][$x] < 0){
				$tableauJeu[$y][$x] = floor($tableauJeu[$y][$x]/100)*100;
			} else if ($nbcellules + $differences[$y][$x] > 99) {
				$tableauJeu[$y][$x] = floor($tableauJeu[$y][$x]/100)*100+99;
			} else {
				$tableauJeu[$y][$x] += $differences[$y][$x];
			}
			if (count($conquetes[$y][$x]) > 1){ //qui gagne la case ?
				$gagnant = mettreEntre($x+$tailleX*$y, count($conquetes[$y][$x]));
				$tableauJeu[$y][$x] += $conquetes[$y][$x][$gagnant]*100-case2joueur($tableauJeu[$y][$x])*100;
			} else if (count($conquetes[$y][$x]) == 1){
				$tableauJeu[$y][$x] += $conquetes[$y][$x][0]*100-case2joueur($tableauJeu[$y][$x])*100;
			}
	}
	return $changement;
}
function purifierTotalement($profondeur=0){
	extract($GLOBALS,EXTR_REFS);//pour continuer sur les variables
	if ($profondeur>=$options[$N_OPT_PROFONDEUR]){
		$joueurEnCours = mettreEntre($joueurEnCours,$nbJoueurs)+1;//on passe au suivant
		if ($joueurEnCours == 1) $noTour++;//augmentation du n° du tour
		while(!peutJouer()) true;//tant que 
	} else {
		$changements = purifier();
		$profondeur++;
		if ($changements)//on arrête s'il y a pas de changements
			purifierTotalement($profondeur);
		else
			purifierTotalement($options[1]);
	}
}

function peutJouer(){//vérifie si le joueur en cours peut jouer, sinon change de joueur
	extract($GLOBALS,EXTR_REFS);
	for ($i=0;$i<$tailleX;$i++)
		for ($j=0;$j<$tailleY;$j++)
			if (case2joueur($tableauJeu[$j][$i])==$joueurEnCours && case2cellules($tableauJeu[$j][$i])>0)
				return true;
	$joueurEnCours = mettreEntre($joueurEnCours,$nbJoueurs)+1;
	if ($joueurEnCours == 1) $noTour++;
	return false;
}

function enregistrerPartie(){
	extract($GLOBALS,EXTR_REFS);//pour continuer sur les variables

	
	$chaine = "".$nbJoueurs."\n";
	$chaine .= $joueurEnCours."\n";
	for($i=0;$i<$nbJoueurs;$i++)
		for($j=0;$j<4;$j++)
			$chaine .= $joueurs[$i+1][$j] .($j+1==4?"\n":"\t");
	for($i=0;$i<$nbJoueurs;$i++)
		for($j=0;$j<4;$j++)
			$chaine .= $derniereAction[$i+1][$j] .($j+1==4?"\n":"\t");
	$nombreDOptions = 5;
	for($i=0;$i<$nombreDOptions;$i++)	//chateauxactivés profondeur bordbloqués diagonale tempspasreel
		$chaine .= $options[$i]."\n";
	$chaine .= $tailleX . "\n";
	$chaine .= $tailleY . "\n";
	for($i=0;$i<$tailleY;$i++)
		for($j=0;$j<$tailleX;$j++)
			$chaine .= $tableauDecor[$i][$j] .($j+1==$tailleX?"\n":"\t");
	for($i=0;$i<$tailleY;$i++)
		for($j=0;$j<$tailleX;$j++)
			$chaine .= $tableauJeu[$i][$j] .($j+1==$tailleX?"\n":"\t");
	$chaine .= $noTour;

	$f = fopen($fichierCourant,"w");
	fwrite($f,$chaine);
	fclose($f);
}


/******jeu.php : interface avec le fichier de jeu
	reçoit les requêtes de jeu, regarde si c'est bon et accepte ou non la requete et agit en conséquence
	reçoit la requête de mise à jour de son propre jeu
	reçoit la requête de plateau de jeu et le renvoie
	paramètres GET :
		p	numéro de la partie
		j	joueur envoyant la requête
X		pw	mot de passe du joueur
		a	genre d'action
0			(n normale,
0			c chateau,
X			g demande de grille (tout le jeu),
X			m mise à jour)
		x	abscisse de l'endroit joué
		y	ordonnée de l'endroit joué
X		k	si a=m, joueur dont on veut connaître la dernière action
X			si k=0, on veut savoir le no du joueur en cours
	renvoie une chaîne xml
		si a=n ou a=c
0			requête prise en compte ou non
		si a=g
X			toute la grille à jour
		si a=m
X			derniers paramètres acceptés de : a de k, x de k et y de k
X			ou no du joueur en cours

			
parties sous le nom aopNNNNNNbacteries.par
	Nombre de joueurs
	N°Joueur en cours entre 1 et NbJoueurs
	Nom joueur 1	couleur	option : motdepasse	
	Nom joueur 2	couleur	option : motdepasse	option : estIA?estenréseau?
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