<?php
//error_reporting(0);
/********************************************
jeu.php : interface avec le fichier de jeu
	re�oit les requ�tes de jeu, regarde si c'est bon et accepte ou non la requete et agit en cons�quence
	re�oit la requ�te de mise � jour de son propre jeu
	re�oit la requ�te de plateau de jeu et le renvoie
	param�tres GET :
		p	num�ro de la partie
		j	joueur envoyant la requ�te (�a peut �tre "admin" ou "observateur")
		pw	mot de passe du joueur
		a	genre d'action
X			(n normale,
X			c chateau,
X			g demande de grille,
X			m mise � jour joueur,
X			nvp nouveau mot de passe,
X			autrejoueur	ajout d'un nouveau joueur, (param�tres POST)
X			s supprimer la partie
			encore recommencer une partie de m�me d�cor, m�mes param�tres)
		x	abscisse de l'endroit jou�
		y	ordonn�e de l'endroit jou�
		k	si a=m, joueur dont on veut conna�tre la derni�re action
				si k=0, on veut savoir le no du joueur en cours
			si a=n ou c, k=0 => ne pas traiter la requete
			si a=nvp k=nouveau mot de passe
	renvoie une cha�ne xml
		si a=autrejoueur (param�tres en POST)
			Petit texte pour dire si �a a march� et quel est le lien vers la partie
		si a=nvp et k=nouveau mot de passe
			<reponse><action type=\"changement de mot de passe\" traitee="oui"/"non" nouveaumotdepasse="lenouveau" /></reponse>
		si a=s
			<reponse><action type=\"suppression de partie\" traitee="oui"/"non" partiesupprimee="numero" /></reponse>
		si a=n ou a=c
			requ�te possible ?, trait�e ?
			<reponse><action autorisee="oui"/"non" traitee="oui"/"non"/></reponse>
		si a=g
			toute la grille � jour suivant la DTD
		si a=m
			derniers param�tres accept�s de : a de k, x de k et y de k et le no du tour
			<reponse><a valeur="n"/"c" /><x valeur=x /><y valeur=y /><n valeur=noTour /><k valeur=k /></reponse>
			ou no du joueur en cours et le no du tour
			<reponse><n valeur=noTour /><k valeur=k /></reponse>
		si erreur
			<reponse><erreur raison="Devinez l'erreur !" /></reponse>

******************************************************************************/

include_once ("fonctions.inc");
include_once ("classes.php");
include_once ("newjeux.php");

if (!array_key_exists("p",$_GET))
	lancerErreur("Num�ro de partie requis","Lancement du serveur");
$p = (array_key_exists("p",$_GET)?$_GET["p"]:"000001");
$joueurAppelant = (array_key_exists("j",$_GET)?$_GET["j"]:"observateur");
$action = (array_key_exists("a",$_GET)?$_GET["a"]:"g");

header("Cache-Control: no-cache, must-revalidate");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");


///on ajoute un nouveau joueur
if ($action == "autrejoueur"){
	CreaJeu::ajouterJoueur();
	die();
}


$fichierPartie = getNomFichier($p);
$partie = Partie::ouvrirXML($fichierPartie);
if (!$partie){
	if ($action == "s"){//on tente la suppression dans le fichier XML
		$parties = new PartiesEnCours();
		$parties->supprimerPartie($p,true);
	}
	lancerErreur("Partie ".$p." inconnue ou mal formee",($action == "s"?"Suppression de ladite partie":"Ouverture du fichier"));
}

if ($action=="g"){//on renvoie la grille XML
	$partie->enregistrerXML(true,true,(strlen($joueurAppelant)>2?0:$joueurAppelant+0));die();
}

//v�rification des mots de passe
if ($joueurAppelant == "admin"){
	if (!array_key_exists("pw",$_GET))
		lancerErreur("Mot de passe administrateur requis","Login administrateur");
	if (md5($_GET["pw"])!=mdpadminmd5)
		lancerErreur("Mot de passe administrateur incorrect","Login administrateur");	
}
 else if ($joueurAppelant != "observateur") {//joueur jouant
	$joueurAppelant += 0;
	if ($joueurAppelant<1 || $joueurAppelant>$partie->nbJoueurs)
		lancerErreur("Num�ro de joueur incorrect","Login joueur");
	if (strlen($partie->joueur[$joueurAppelant]->mdp) > 3){
		if (!array_key_exists("pw",$_GET))
			lancerErreur("Mot de passe joueur requis","Login joueur");
if (md5($_GET["pw"]) == $partie->joueur[$joueurAppelant]->mdp)//A FAIRE : remplacer par un strcmp
			lancerErreur("Mot de passe joueur incorrect","Login joueur");	
	}
}

 //suppression de la partie
if ($action == "s" && $joueurAppelant != "observateur"){
	$parties = new PartiesEnCours();
	$changement = $parties->supprimerPartie($p);
	envoyerReponse("<action type=\"suppression de partie\" traitee=\"".($changement?"oui":"non")."\" partiesupprimee=\"".$p."\" />");
}

//nouveau mot de passe
if ($action == "nvp" && is_int($joueurAppelant)){
	if (!array_key_exists("k",$_GET))
		lancerErreur("Nouveau mot de passe requis","Changement de mot de passe joueur");
	$partie->joueur[$joueurAppelant]->mdp = md5($_GET["k"]);
	$partie->enregistrerXML($fichierPartie);
	envoyerReponse("<action type=\"changement de mot de passe\" traitee=\"oui\" nouveaumotdepasse=\"".$_GET["k"]."\" />");
}

//recommencement de la partie
if ($action == "encore"){// && is_int($joueurAppelant)
	$partie->nouvelle();
	$partie->enregistrerXML($fichierPartie);
	envoyerReponse("<action type=\"red&amp;eacute;marrage de la partie\" traitee=\"oui\" />");
}

//mise � jour du client
if ($action == "m"){
	if (!array_key_exists("k",$_GET))
		lancerErreur("Commande de mise � jour requise","Mise � jour concernant un joueur");
	if ($_GET["k"]=="0"){//on veut savoir qui joue
		envoyerReponse("<n valeur=\"".$partie->noTour."\"/>". // signification=\"Num&eacute;ro du tour courant\" 
						"<k valeur=\"".$partie->joueurEnCours."\" />"); // signification=\"Joueur qui joue\"
	}
	else {//on veut savoir quel joueur a jou� o�
		$k = 0+$_GET["k"];
		if ($k<1 || $k>$partie->nbJoueurs)
			lancerErreur("Joueur ".$k." inconnu","Recherche de l'action d'un joueur");
		envoyerReponse("<a valeur=\"".$partie->joueur[$k]->derniereAction->quoi."\" />".
						"<x valeur=\"".$partie->joueur[$k]->derniereAction->ouX."\" />".
						"<y valeur=\"".$partie->joueur[$k]->derniereAction->ouY."\" />".
						"<n valeur=\"".$partie->joueur[$k]->derniereAction->quand."\" />". //signification=\"Num&eacute;ro du tour de la derni&egrave;re action\" 
						"<k valeur=\"".$k."\"  />"); //signification=\"Joueur qui a jou&eacute;\"
	}
}

//jeu d'un joueur
if ($partie->gagnant)
		lancerErreur("La partie est termin&eacute;, le gagant &eacute;tait ".$partie->joueur[$partie->gagnant]->nom." !","Peut-on jouer ?");
if (($action == "n" || $action=="c") && is_int($joueurAppelant)) {
	if (!array_key_exists("x",$_GET) || !array_key_exists("y",$_GET))
		lancerErreur("Coordonn�es (x,y) du coup requis","Analyse du coup");
	$x = 0+$_GET["x"]; $y = 0+$_GET["y"];
	$leType="type=\"".($action=="c"?"membrane":"jeu")." en (".$x.",".$y.")\"";
	//est-ce que l'action doit �tre trait�e ?
	$traiter = ((array_key_exists("k",$_GET)?$_GET["k"]!=0:true) && $partie->joueurEnCours==$joueurAppelant);
	//l'action est-elle autoris�e ?
	$autorisee = $partie->tableauJeu->peutJouerEn($x,$y,$joueurAppelant,$action=="c");
	//premier envoi de r�ponse si l'action n'est pas trait�e
	if (!$traiter || !$autorisee)
		envoyerReponse("<action ".$leType." autorisee=\"".($autorisee?"oui":"non")."\" traitee=\"non\" />");
	
	//action prise en compte...
	$partie->demarree = true;//jeu commenc�
	$partie->tableauJeu->clicNormal($x,$y,$joueurAppelant,$action=="c",$partie->noTour);
	$partie->joueur[$joueurAppelant]->derniereAction = new Action($action,$x,$y,$partie->noTour);
	$partie->tableauJeu->purifieTotalement($joueurAppelant,$partie->noTour);
	$partie->joueurSuivant();
	
	//est-ce qu'il y a un gagnant ?
	$yaGagnant = $partie->finDePartie();
	
	$partie->enregistrerXML($fichierPartie);
	
	//on continue le traitement ?
	//if ($yaGagnant || !$partie->getJoueurEnCours()->isIA()){//IA lanc�e par le client du joueur 1
	envoyerReponse("<action ".$leType." autorisee=\"oui\" traitee=\"oui\" />");
	//}
	
}

lancerErreur("Action ".$action." non comprise","Analyse des actions");

?>