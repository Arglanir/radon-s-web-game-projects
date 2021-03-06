<?php
/***
Age of paramecia 2
jeu cr�� par C�dric Mayer

client.php : contient une interface de jeu javascript, cr�e le jeu chez le client et le laisse jouer.
	inclu par index.php

jouer
	communique avec ajax avec jeu.php
	param�tres GET :
		j	nom du joueur courant
		p	num�ro de la partie
		s	son
		pw	optionnel : mot de passe
		offline	jouer sans le serveur (tous sur la m�me fen�tre), pour faire des tests de nos positions
		type	type d'affichage pour les cases
			texte	cases format texte
			atome	cases avec atomes anim�s
			cellule	cases avec cellules
			mediev	cases version moyen-age


********* Structure des donn�es
tableauArguments : tableau des arguments de la page
*/
$mettreLeCadreHTML = !($_GET["comp"]=="client");

if ($mettreLeCadreHTML){
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<!--
<?php echo $mettreLeCadreHTML; ?>
-->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="Content-language" content="fr" />
<title>AOP2</title>
<?php } else { ?>
<div id="divDuClientDeJeu">
<?php } ?>
<script type="text/javascript" src="clientclasses.js" ></script>
<script type="text/javascript">
/*** lecture des arguments de la page ***/
/*	param�tres GET :
		j	nom du joueur courant
		p	num�ro de la partie
		s	son
		pw	optionnel : mot de passe
		offline	arr�t des communications avec le serveur (peut �tre mis en route en cours de jeu) */

tableauArguments["offline"] = parseInt(tableauArguments["offline"]);
if (tableauArguments["j"] != "observateur")
	tableauArguments["j"] = parseInt(tableauArguments["j"]);
tableauArguments["type"] = (tableauArguments["type"]?tableauArguments["type"]:"cellule");


/*** initialisation des param�tres et types ***/
//les options d'affichage
var tempsEntreAffichages = 1000;
var tempsDAttenteAvantRelance = 2000;//connexion au serveur

var quiLanceLIA = 0;//c'est l'h�te
var demarree = 0;

function CreerJeu(docXML){
	var Xpartie = docXML.getElementsByTagName("partie").item(0);
this.nbJoueurs = parseInt(Xpartie.getAttribute('nombredejoueurs'));
this.joueurEnCours = parseInt(Xpartie.getAttribute('joueurencours'));
	demarree = parseInt(Xpartie.getAttribute('demarree'));
this.noTour = parseInt(Xpartie.getAttribute('notour'));
this.joueurs = new Array();var Xjoueurs = Xpartie.getElementsByTagName("joueur");
this.derniereAction = new Array();
	for (var i in Xjoueurs){
		var numero = parseInt(Xjoueurs.item(i).getAttribute('numero'));
this.joueurs[numero] = new Array(Xjoueurs.item(i).getAttribute('nom'),
										Xjoueurs.item(i).getAttribute('couleur'),"0",
										(Xjoueurs.item(i).getAttribute('estia')=="oui"?1:(Xjoueurs.item(i).getAttribute('estnet')=="oui"?2:0)));
		var Xaction = Xjoueurs.item(i).getElementsByTagName("derniereaction").item(0);
this.derniereAction[numero] = new Array(Xaction.getAttribute('type'),
										parseInt(Xaction.getAttribute('x')),
										parseInt(Xaction.getAttribute('y')),
										parseInt(Xaction.getAttribute('notour')));
	}
	for (var j=1; j<=this.nbJoueurs;j++){
		if (this.joueurs[j][3] != 1){ //premier humain 
			quiLanceLIA = j;
			break;
		}
	}
	if (tableauArguments["j"] != "observateur")
		if (this.joueurs[tableauArguments["j"]][3] == 1)
			tableauArguments["j"] = "observateur";

	var Xoptions = Xpartie.getElementsByTagName("option");
this.options = new Array(1,100,2,1,0,1);
	for (var i in Xoptions){
		var valeur = parseInt(Xoptions.item(i).getAttribute('valeur'));
		switch(Xoptions.item(i).getAttribute('type')){
		case "chateaux_actifs":this.options[0] = valeur;break;
		case "profondeur_jeu":this.options[1] = valeur;break;
		case "type_bords":this.options[2] = valeur;break;
		case "ajout_diagonale":this.options[3] = valeur;break;
		case "explosion_joueur":this.options[4] = valeur;break;
		case "augmentation_matiere":this.options[5] = valeur;break;
		}
	}
	var Xtableaupartie = Xpartie.getElementsByTagName("tableaudejeu").item(0);
this.tailleX = parseInt(Xtableaupartie.getAttribute('taillex'));
this.tailleY = parseInt(Xtableaupartie.getAttribute('tailley'));

this.tableauDecor = new Array();
this.tableauJeu = new Array();
this.tableauDesMax = new Array();
this.tableauCasesUtilisees = new Array();
	var Xlignes = Xtableaupartie.getElementsByTagName("ligne");
	for(var i in Xlignes){
		var y = parseInt(Xlignes.item(i).getAttribute('y'));
		this.tableauDecor[y]=new Array();
		this.tableauJeu[y]=new Array();
		this.tableauDesMax[y] = new Array();
		this.tableauCasesUtilisees[y] = new Array();
		var Xcases = Xlignes.item(i).getElementsByTagName("case");
		for(var j in Xcases){
			var XlaCase = Xcases.item(j);
			var x = parseInt(XlaCase.getAttribute('x'));
			this.tableauCasesUtilisees[y][x] = this.noTour + "-" + (this.joueurEnCours-1);
			this.tableauDecor[y][x] = parseInt(XlaCase.getAttribute('decor'));
			this.tableauJeu[y][x] = parseInt(XlaCase.getAttribute('chateau'))*10000+100*parseInt(XlaCase.getAttribute('joueur'))+parseInt(XlaCase.getAttribute('cellules'));
			this.tableauDesMax[y][x] = parseInt(XlaCase.getAttribute('max'));
		}
	}
	//alert(Xpartie.getElementsByTagName("histoire").item(0));
this.histoire = (Xpartie.getElementsByTagName("histoire").length>0?
		(Xpartie.getElementsByTagName("histoire").item(0).firstChild?
			Xpartie.getElementsByTagName("histoire").item(0).firstChild.nodeValue:
			Xpartie.getElementsByTagName("histoire").item(0).getAttribute("h")):false);
this.params = false;
	if (Xpartie.getElementsByTagName("parametrescampagne").length>0){//il y a des param�tres de campagne !
		if (Xpartie.getElementsByTagName("parametrescampagne").item(0).getAttribute("titre")){//ou pas
			this.params = Xpartie.getElementsByTagName("parametrescampagne").item(0);
			if (this.params.getAttribute("deco")){
				tableauArguments["type"] = this.params.getAttribute("deco");
			}
			document.getElementById("infocampagne").innerHTML = "<h3>"+this.params.getAttribute("titre")+"</h3>";
			document.getElementById("infocampagne").style.display = "block";
			document.getElementById("interieurpopup").innerHTML = "<h3>"+this.params.getAttribute("titre")+"</h3>"+
													(this.histoire?this.histoire:"Une mission banale")+"<br />"+
													"<a href='#' onclick='document.getElementById(\"popupcampagne\").style.display = \"none\";'>Commencer le jeu</a>";
			if (tableauArguments["j"] != "observateur" && typeof tableauArguments["synchroserveur"] == undefined)
				document.getElementById("popupcampagne").style.display = "block";
	}
	}
	
//m�thodes
this.purifie=purifie;
this.purifieEtAffiche=purifieEtAffiche;
this.affiche=affiche;
this.jouable=jouable;
this.toString=toString;
this.joueEn=joueEn;
this.chateauEn=chateauEn;
this.afficheOptions=afficheOptions;
this.existeGagnant=existeGagnant;
this.peutJouer=peutJouer;
this.lesJoueurs2string=lesJoueurs2string;
this.faireJouerSuivant=faireJouerSuivant;
this.caseLaPlusEloigneeDe=caseLaPlusEloigneeDe; 
this.distance=distance;
}

function purifie(){with(this){//renvoie true s'il y a eu un changement, false sinon
		var changement=false;
		var ouGlaceExplosion = new Array();//var indiceGlace=0;//pr�paration des endroits glac�s
		var differences = new Array(); //pr�paration du traitement des explosions
		var conquetes = new Array();
		for (var y=0;y<tailleY;y++){
			differences[y] = new Array();
			conquetes[y] = new Array();
			for (var x=0;x<tailleX;x++){
				differences[y][x] = 0;
				conquetes[y][x] = new Array();
			}
		}
		for (var x=0;x<tailleX;x++)  //traitement des explosions
			for(var y=0;y<tailleY;y++){
			  nbSurCase = this.tableauJeu[y][x];
			  if ((this.options[4] == 1 && case2joueur(nbSurCase)==this.joueurEnCours) || this.options[4] == 0)
				if (case2cellules(nbSurCase) >= this.tableauDesMax[y][x] && !case2chateau(nbSurCase)){//explosion !
	changement = true;
	for (var ii=-1;ii<2;ii++)//va sur les cases d'� c�t�
		for(var jj=-1;jj<2;jj++)
			if (Math.abs(ii)+Math.abs(jj)==1){//pas diagonale
				var nvx = x+jj; var nvy = y+ii;
				var perteBord = false;
				switch(this.options[2]){
				  case 2: //on regarde apr�s les bords
					nvx = mettreEntre(x+jj,this.tailleX); nvy = mettreEntre(y+ii,this.tailleY);
				  case 0: perteBord=true; //on ne regarde pas au bord mais on perd une cellule
				  case 1: //on regarde pas apr�s les bords
					if (entre(0,nvy,this.tailleY-1) && entre(0,nvx,this.tailleX-1)){
						if (this.options[4]==0 && !case2chateau(this.tableauJeu[nvy][nvx]) && !case2chateau(nbSurCase) && case2cellules(this.tableauJeu[nvy][nvx])>=this.tableauDesMax[nvy][nvx] && case2joueur(nbSurCase)!=case2joueur(this.tableauJeu[nvy][nvx])){//l'autre case explose de m�me, et autre joueur : on ne traverse pas, on garde la cellule
							true;
						} else
						switch(this.tableauDecor[nvy][nvx]){
						case 0: //case normale
							differences[y][x]--;
							if (case2chateau(this.tableauJeu[nvy][nvx])&&(case2joueur(nbSurCase)!=case2joueur(this.tableauJeu[nvy][nvx]))&&case2cellules(this.tableauJeu[nvy][nvx])>=10){//traitement si membrane adverse protg�e
								differences[nvy][nvx]--;
							} else {//jeu normal ou destruction de la membrane et conqu�te des cellules
								//il va y avoir un bug si attaque et d�fense en m�me temps d'un chateau
								//on va dire que les attaquants ont toujours priorit�... C'est un jeu !
								differences[nvy][nvx]++;
								if (case2chateau(this.tableauJeu[nvy][nvx])&&(case2joueur(nbSurCase)!=case2joueur(this.tableauJeu[nvy][nvx]))&&case2cellules(this.tableauJeu[nvy][nvx])<10)
									this.tableauJeu[nvy][nvx]-=(case2chateau(this.tableauJeu[nvy][nvx])?10000:0);
								conquetes[nvy][nvx][conquetes[nvy][nvx].length]=case2joueur(nbSurCase);
							}
							break;
						case 1: //glace
							differences[y][x]--;
							if (isNaN(ouGlaceExplosion[nvy+" "+nvx]) && this.tableauCasesUtilisees[nvy][nvx] != this.noTour + "-" + this.joueurEnCours){//1ere fois
								ouGlaceExplosion[nvy+" "+nvx] = 1;
								this.tableauCasesUtilisees[nvy][nvx] = this.noTour + "-" + this.joueurEnCours;
							} else {//fois apr�s
								//il va y avoir un BUG si 2 personnes tentent de conqu�rir une case de glace
								//c'est � cause du vent, il souffle pour favoriser les joueurs x plus grands puis y
								differences[nvy][nvx]++;
								conquetes[nvy][nvx][conquetes[nvy][nvx].length]=case2joueur(nbSurCase);
							}
							break;
						case 2: //point chaud
							differences[y][x]--;
							differences[nvy][nvx]+=(this.tableauCasesUtilisees[nvy][nvx] != this.noTour + "-" + this.joueurEnCours?2:1);
							this.tableauCasesUtilisees[nvy][nvx] = this.noTour + "-" + this.joueurEnCours;
							conquetes[nvy][nvx][conquetes[nvy][nvx].length]=case2joueur(nbSurCase);
							break;
						case 3: //obstacle:rien !
							break;
						}
					  } else if (perteBord) {
						differences[y][x]--;
					  }
					break;
				}
			}

				}
		}
		
		for (var x=0;x<tailleX;x++)  //post traitement des explosions
			for(var y=0;y<tailleY;y++){
				var nbcellules = case2cellules(this.tableauJeu[y][x]);
				if (nbcellules + differences[y][x] < 0){
					alert("Valeur de case n�gative !");/*debug*/
					this.tableauJeu[y][x] = Math.floor(this.tableauJeu[y][x]/100)*100;
				} else if (nbcellules + differences[y][x] > 99) {
					this.tableauJeu[y][x] = Math.floor(this.tableauJeu[y][x]/100)*100+99;
				} else {
					this.tableauJeu[y][x] += differences[y][x];
				}
				if (conquetes[y][x].length > 1){ //qui gagne la case ?
					var joueursConcernes = new Array(); var leMax = 1;
					for (var nbc=0;nbc<conquetes[y][x].length;nbc++){//on cherche s'il y a une personne qui a plus de pr�tentions
						joueursConcernes[conquetes[y][x][nbc]] = (joueursConcernes[conquetes[y][x][nbc]]?joueursConcernes[conquetes[y][x][nbc]]+1:1);
						leMax = Math.max(joueursConcernes[conquetes[y][x][nbc]],leMax);
					}
					var lesGagnants = new Array();
					for (var joueur in joueursConcernes){
						if (joueursConcernes[joueur] < leMax) continue;
						lesGagnants[lesGagnants.length] = joueur;
					}
					var gagnant = lesGagnants[mettreEntre(x+this.tailleX*y, lesGagnants.length)];
					this.tableauJeu[y][x] += gagnant*100-case2joueur(this.tableauJeu[y][x])*100;
				} else if (conquetes[y][x].length == 1){
					this.tableauJeu[y][x] += conquetes[y][x][0]*100-case2joueur(this.tableauJeu[y][x])*100;
				}
		}
		return changement;
}}

var enCoursDeTraitement = false;//pour ne pas accepter deux fois la m�me r�ponse
function purifieEtAffiche(profondeur){with(this){//fonction r�cursive pour purifier totalement et afficher
	enCoursDeTraitement = true;
	var profondeur = (profondeur == undefined?0:profondeur);
	if (profondeur==0 && tableauArguments["j"] == quiLanceLIA && this.joueurs[mettreEntre(this.joueurEnCours,this.nbJoueurs)+1][3] == 1)
		lancerIA();//on lance l'IA avant d'afficher, pour acc�l�rer le processus
	if (profondeur>=this.options[1]){//fin de la purification
		this.joueurEnCours = mettreEntre(this.joueurEnCours,this.nbJoueurs)+1;//on passe au suivant
		if (this.joueurEnCours == 1) this.noTour++;//augmentation du n� du tour
		while(!this.peutJouer()) true;
		enCoursDeTraitement = false;
		this.affiche();
		this.faireJouerSuivant();
	} else {
		var changement = this.purifie();
		if (changement){//on arr�te s'il y a pas de changements
			window.setTimeout("if (enCoursDeTraitement) unJeu.affiche(0); else unJeu.affiche();",tempsEntreAffichages-500);profondeur++;
			window.setTimeout("unJeu.purifieEtAffiche("+profondeur+")",tempsEntreAffichages);
		}else {
			//this.affiche();
			this.purifieEtAffiche(this.options[1]);
		}
	}
}}


function faireJouerSuivant(){with(this){//fait jouer les joueurs sur internet
	document.title = "AOP2 - "+html_entity_decode(this.joueurs[this.joueurEnCours][0])+(this.joueurEnCours==tableauArguments["j"]?" !":"");
	document.getElementById("grossecaseagauche").innerHTML = "<img src='images/status.php?c="+this.joueurs[this.joueurEnCours][1]+"&s=0' />";statutDuJoueurEnCours=0;
	if (demarree==0){
		document.getElementById("comm").innerHTML = "Attente du d&eacute;marrage de la partie...";
		return;
	}
	if (tableauArguments["offline"]==1 || this.joueurs[this.joueurEnCours][3] == 0 || tableauArguments["synchroserveur"]){
		if (document.mdp.prevenu.checked && !tableauArguments["synchroserveur"]) alert("C'est ton tour de jouer !");
		return;//c'est au joueur de jouer, on le laisse
	}
	if (tableauArguments["j"] == quiLanceLIA && this.joueurs[this.joueurEnCours][3] == 1)
		lancerIA();
	document.getElementById("comm").innerHTML ="Demande de l'action du joueur "+this.joueurEnCours+" au serveur";
	ouJoue(this.joueurEnCours, function(tabReponse){
		if(tabReponse["n"] != unJeu.noTour){// && !enCoursDeTraitement)
			//on pr�voit si la requete pr�c�dente est en cours de traitement
			document.getElementById("comm").innerHTML ="ReDemande de l'action du joueur "+unJeu.joueurEnCours+" au serveur";
			document.getElementById("comm").innerHTML += "<br />a="+tabReponse["a"]+" x="+tabReponse["x"]+" y="+tabReponse["y"]+" k="+tabReponse["k"]+" n="+tabReponse["n"]+" <br />";
			setTimeout("unJeu.faireJouerSuivant()",tempsDAttenteAvantRelance);//pas encore jou�
		}else {
			document.getElementById("comm").innerHTML ="Traitement de l'action du joueur "+unJeu.joueurEnCours;
			document.getElementById("grossecaseagauche").innerHTML = "<img src='images/status.php?c="+unJeu.joueurs[unJeu.joueurEnCours][1]+"&s=1' />";statutDuJoueurEnCours=1;

			document.getElementById("comm").innerHTML += "<br />a="+tabReponse["a"]+" x="+tabReponse["x"]+" y="+tabReponse["y"]+" k="+tabReponse["k"]+" n="+tabReponse["n"]+" <br />";

			if (tabReponse["a"]=="n")
				unJeu.joueEn(tabReponse["x"],tabReponse["y"]);
			else
				unJeu.chateauEn(tabReponse["x"],tabReponse["y"]);
		}
	});
}}

function lesJoueurs2string(){with(this){//renvoie un tableau de statistiques par joueur
	var statistiques = new Array();
	for (var n = 1; n<=this.nbJoueurs;n++){
		statistiques[n] = new Array();
		statistiques[n][0] = this.joueurs[n][1];//couleur
		statistiques[n][1] = this.joueurs[n][0];//nom
		statistiques[n][2] = (this.joueurs[n][3]!=undefined?this.joueurs[n][3]:0);//ia? autreordinateur?
		statistiques[n][3] = 0;//nombre de cellules
		statistiques[n][4] = 0;//cases control�es
		statistiques[n][5] = 0;//cases pr�tes � exploser
		statistiques[n][6] = 0;//cases menac�es (on compte plusieurs fois si plusieurs cases � exploser)
	}
		
	for (var x=0;x<this.tailleX;x++) for (var y=0;y<this.tailleY;y++){
			if (this.tableauDecor[y][x] == 3) continue;
			var quelJoueur = case2joueur(this.tableauJeu[y][x]);
			var nbcel = case2cellules(this.tableauJeu[y][x]);
			if (!quelJoueur || nbcel==0){//aucun joueur : on passe apr�s avoir regard� les menaces
				var tabK = new Array();
				for (var ii=-1;ii<2;ii++)for(var jj=-1;jj<2;jj++){//va sur les cases d'� c�t�
					if (ii==0 && jj==0) continue;
					if (!this.options[3] && Math.abs(ii)+Math.abs(jj)!=1) continue;
					var nvx = x+jj; var nvy = y+ii;
					if(this.options[2]==2){//on regarde apr�s les bords
						nvx = mettreEntre(x+jj,this.tailleX); nvy = mettreEntre(y+ii,this.tailleY);
					}
					if (entre(0,nvy,this.tailleY-1) && entre(0,nvx,this.tailleX-1)){
						var n = (case2cellules(this.tableauJeu[nvy][nvx])>0?case2joueur(this.tableauJeu[nvy][nvx]):0);
						if (n==0) continue;
						if (tabK[n]) continue;
						tabK[n] = 1;
						statistiques[n][6]++;
					}
				}
				continue;
			}
			statistiques[quelJoueur][3] += nbcel; //nombre de cellules
			statistiques[quelJoueur][4] += 1;//case control�e
			if (nbcel+1 >= this.tableauDesMax[y][x])
				statistiques[quelJoueur][5] += 1;//case pr�te � exploser
			//cases menac�es autour
		for (var ii=-1;ii<2;ii++)for(var jj=-1;jj<2;jj++){//va sur les cases d'� c�t�
			if (ii==0 && jj==0) continue;
			var nvx = x+jj; var nvy = y+ii;
			if (this.options[2]==2){
				nvx = mettreEntre(x+jj,this.tailleX); nvy = mettreEntre(y+ii,this.tailleY);
			}
			if (entre(0,nvy,this.tailleY-1) && entre(0,nvx,this.tailleX-1))
				if (this.tableauDecor[nvy][nvx]!=3)
					if (case2joueur(this.tableauJeu[nvy][nvx]) != quelJoueur && Math.abs(ii)+Math.abs(jj)==1 && nbcel+1 >= this.tableauDesMax[y][x])
						statistiques[quelJoueur][6]++;//case pouvant �tre obtenue par explosion
		}

	}
	return statistiques;
}}

function peutJouer(quelJoueur){with(this){//v�rifie si le joueur en cours peut jouer, sinon change de joueur
	var changerJoueur = (quelJoueur==undefined? true : false);//ne change que si c'est pas mis
	var quelJoueur = (quelJoueur==undefined || !quelJoueur? this.joueurEnCours : quelJoueur);
	for (var x=0;x<this.tailleX;x++)
		for (var y=0;y<this.tailleY;y++)
			if (this.jouable(x,y) && quelJoueur == this.joueurEnCours)
				return true;
			else if (quelJoueur != this.joueurEnCours && case2joueur(this.tableauJeu[y][x])==quelJoueur && case2cellules(this.tableauJeu[y][x])>0)
				return true;
	if (changerJoueur){
		this.joueurEnCours = mettreEntre(this.joueurEnCours,this.nbJoueurs)+1;
		if (this.joueurEnCours == 1) this.noTour++;
	}
	return false;
}}

function existeGagnant(){with(this){//renvoie le num�ro du joueur gagnant ! false sinon
	var leGagnant = 0;var nb = 0;var qui = new Array();
	for (var x=0;x<this.tailleX;x++)
		for (var y=0;y<this.tailleY;y++)
			if (case2joueur(this.tableauJeu[y][x])>0 && case2cellules(this.tableauJeu[y][x])>0)
				if (!qui[case2joueur(this.tableauJeu[y][x])]){
					if (nb >=1) //d�j� un !
						return false;
					qui[case2joueur(this.tableauJeu[y][x])] = true;
					nb++;
					leGagnant = case2joueur(this.tableauJeu[y][x]);
				}
	return leGagnant;
}}

var popupfinaffichee = false;
var statutDuJoueurEnCours = 0;//0 : il cherche, 1 il a jou�, �a traite

function affiche(typeJouable){with(this){//fonction qui affiche le jeu dans la fen�tre, jouable 0non/1oui/2chat
	//argument : 0 non jouable, 1 jouable, 2 chateau, 254 non jouable joueur externe 255 non jouable gagnant
	var mettreLesOnload = true;
	var nGagnant = this.existeGagnant();
	if (nGagnant==tableauArguments["j"] && !popupfinaffichee && this.params){
		document.getElementById("interieurpopup").innerHTML = "<h3>"+this.params.getAttribute("titre")+"</h3>"+
													this.params.getAttribute("infosucces")+"<br />"+
													(this.params.getAttribute("suivante") != "fin" ? 
														"<a href='#' onclick=\"if (this.href='#'){var lien=continuerCampagne(unJeu);this.innerHTML='Si la partie ne s\\'affiche pas, cliquez ici d\\'ici quelques secondes.';this.href=lien;return false;}\">Mission suivante</a>" :
													"Campagne termin&eacute;e ! F&eacute;licitations !<br />"+
														"<a href='#' onclick=\"supprimerPartie();\">Revenir &agrave; l'accueil</a>");
		document.getElementById("popupcampagne").style.display = "block";
		popupfinaffichee = true;
	} else if (nGagnant && !popupfinaffichee && this.params){
		document.getElementById("interieurpopup").innerHTML = "<h3>"+this.params.getAttribute("titre")+"</h3>"+
													"Dommage ! Tu feras mieux la prochaine fois..."+"<br />"+
													"<a href='#' onclick=\"recommencer(unJeu);\">Recommencer</a>";
		document.getElementById("popupcampagne").style.display = "block";
		popupfinaffichee = true;
	}
	if (!tableauArguments["offline"] && this.joueurs[this.joueurEnCours][3] != 0) typeJouable = 254;//joueur ailleurs
	if (nGagnant) typeJouable = 255;
	var typeJouable = (typeJouable == undefined?1:typeJouable);
	
	var statistiques = this.lesJoueurs2string();
	//Mika�l veut que le bord de la table soit de la couleur du joueur ayant le client... Ne marche que sous Chrome !
	var colorTable = (tableauArguments["j"]=="observateur"?"FFFFFF":statistiques[tableauArguments["j"]][0]);
	var debutChaineHTML = "<center><table><tr>";
	var chaineHTML = "<td style=\"border:solid #"+versRRGGBB(colorTable)+";\">";
	chaineHTML += (tableauArguments["type"]=="texte"?"<table cellpadding=\"0\" cellspacing=\"0\" >":"");
	for(var y=0;y<this.tailleY;y++){
		chaineHTML += (tableauArguments["type"]=="texte"?"<tr>":"");
		for(var x=0;x<this.tailleX;x++){
			var decor = this.tableauDecor[y][x];
			var joueur = case2joueur(this.tableauJeu[y][x]);
			var couleur = (joueur == 0 ? "BFBFBF" : this.joueurs[joueur][1]);
			if (decor==3) couleur = "000000";
			var cellules = case2cellules(this.tableauJeu[y][x]);
			var chateau = case2chateau(this.tableauJeu[y][x]);
			
			var dernier = false;
			for (j=1; j<= this.nbJoueurs; j++)
				dernier=(dernier||(this.derniereAction[j][1]==x && this.derniereAction[j][2]==y));
			
			onclick_string="";
			//debug(typeJouable+";"+onclick_string+":"+x+","+y);
			switch(typeJouable){
				case 0:
					onclick_string += " onclick=\"alert('Pause')\"";
					break;
				case 254:
					onclick_string += " onclick=\"alert('Attente de "+this.joueurs[this.joueurEnCours][0]+"')\"";
					break;
				case 255:
					if (nGagnant == tableauArguments["j"])
						onclick_string += " onclick=\"alert('Tu es le meilleur, bravo !')\"";
					else
						onclick_string += " onclick=\"alert('Tu as fait de ton mieux, bravo !')\"";
					break;
				case 1:
					onclick_string += " onclick=\"unJeu.joueEn("+x+","+y+")\"";
					break;
				case 2:
					onclick_string += " onclick=\"unJeu.chateauEn("+x+","+y+")\"";
			}
			//debug(onclick_string);
			if (tableauArguments["type"]=="texte"){
			if (cellules == 0 && joueur != 0){//�claircir la couleur
				var coulvide = parseInt("FF",16);
				var tabCouleur;
				switch (couleur.substring(0,1)){
				case "#"://couleur #HHHHHH
					tabCouleur = new Array(parseInt(couleur.substring(1,3),16),parseInt(couleur.substring(3,5),16),parseInt(couleur.substring(5,7),16));break;
				case "r"://couleur rgb(rr,gg,bb)
					tabCouleur = couleur.split(",");
					tabCouleur = new Array(parseInt(couleur[0].split("(")[1]),parseInt(couleur[1]),parseInt(couleur[2].split(")")[0]));break;
				default://couleur RRGGBB
					tabCouleur = new Array(parseInt(couleur.substring(0,2),16),parseInt(couleur.substring(2,4),16),parseInt(couleur.substring(4,6),16));break;
				}
				tabCouleur[0] = Math.floor((tabCouleur[0]+coulvide)/2);
				tabCouleur[1] = Math.floor((tabCouleur[1]+coulvide)/2);
				tabCouleur[2] = Math.floor((tabCouleur[2]+coulvide)/2);
				couleur = "rgb("+tabCouleur[0]+","+tabCouleur[1]+","+tabCouleur[2]+")";
			}
			if (cellules >= this.tableauDesMax[y][x]){//Foncer la couleur
				var coulvide = parseInt("BF",16);
				var tabCouleur;
				switch (couleur.substring(0,1)){
				case "#"://couleur #HHHHHH
					tabCouleur = new Array(parseInt(couleur.substring(1,3),16),parseInt(couleur.substring(3,5),16),parseInt(couleur.substring(5,7),16));break;
				case "r"://couleur rgb(rr,gg,bb)
					tabCouleur = couleur.split(",");
					tabCouleur = new Array(parseInt(couleur[0].split("(")[1]),parseInt(couleur[1],16),parseInt(couleur[2].split(")")[0]));break;
				default://couleur rgb(rr,gg,bb)
					tabCouleur = new Array(parseInt(couleur.substring(0,2),16),parseInt(couleur.substring(2,4),16),parseInt(couleur.substring(4,6),16));break;
				}
				tabCouleur[0] = Math.floor((tabCouleur[0]+coulvide)/2);
				tabCouleur[1] = Math.floor((tabCouleur[1]+coulvide)/2);
				tabCouleur[2] = Math.floor((tabCouleur[2]+coulvide)/2);
				couleur = "rgb("+tabCouleur[0]+","+tabCouleur[1]+","+tabCouleur[2]+")";
			}
			couleur = "#"+versRRGGBB(couleur);
			
			chaineHTML += "<td style=\"";
			var leStyle = "align:center;";
			if (chateau){
				if (cellules<10)
					leStyle += "border-style:dotted;";
				else
					leStyle += "border-style:solid;";
				leStyle += "border-color:black;";
			} else {
				leStyle += "border-style:hidden;border-color:"+couleur+";";
			}
			if (decor == 1)
				leStyle += "text-decoration:line-through;";
			if (decor == 2)
				leStyle += "font-style:italic;font-style:bold;";
			if (dernier)
				leStyle += "font-style: underline;text-decoration: underline;";
			
			//chaineHTML += leStyle;
			chaineHTML += "\">";
			chaineHTML += "<input id=\"c"+x+"_"+y+"\" title=\""+(this.jouable(x,y)?"Jouable":"Non jouable")+"\" type=\"button\" style=\"";
			chaineHTML += "align:center;height:30px;width:30px;background-color:"+couleur+";";
			chaineHTML += leStyle + "\" ";
			chaineHTML += " value=\""+cellules+"\" "+onclick_string+" />";
			chaineHTML += "</td>";
			
			} else {// border=\"0\"
				onload_string = (mettreLesOnload?'onload="chargementImageJeu('+zonePouvantCharger+');"':'');
				chaineHTML += "<img width=33 height=33 style=\"vertical-align:bottom;\" title=\""+cellules+"\" alt=\""+cellules+"\" "+onload_string+" src=\"images/image.php?c="+versRRGGBB(couleur)+"&n="+cellules+"&h="+(chateau?1:0)+"&d="+decor+"&type="+tableauArguments["type"]+"&m="+(cellules >= this.tableauDesMax[y][x]?1:0)+"&r="+(dernier?1:0)+"\" "+onclick_string+" />";
			}
		}
		chaineHTML += (tableauArguments["type"]=="texte"?"</tr>\n":"<br />\n");
	}
	chaineHTML += (tableauArguments["type"]=="texte"?"</table>":"");
	chaineHTML += "</td>";
	
	var tableauStatistiquesChaine = "";// /!\ pas de <td commen�ant>
	//Statistiques pr�sent�es sur un tableau
	tableauStatistiquesChaine += "<table border=1>";
	tableauStatistiquesChaine += "<tr>";
	tableauStatistiquesChaine += "<td border=0>&nbsp;</td>";
	tableauStatistiquesChaine += "<td>Cellules</td>";
	tableauStatistiquesChaine += "<td>Contr&ocirc;le</td>";
	tableauStatistiquesChaine += "<td>Explosables</td>";
	tableauStatistiquesChaine += "<td>Menaces</td>";
	tableauStatistiquesChaine += "</tr>";
	for (var n = 1; n<=this.nbJoueurs;n++){
		tableauStatistiquesChaine += "<tr><td style=\"background-color:#"+versRRGGBB(statistiques[n][0])+"\">";
		//chaineHTML += "<p>";
		tableauStatistiquesChaine += (this.joueurEnCours == n?"&gt;":"");
		//chaineHTML += "<span style=\"background-color:"+statistiques[n][0]+"\">";
		tableauStatistiquesChaine += (tableauArguments["j"] == n?"<b>":"");		
		tableauStatistiquesChaine += statistiques[n][1]+"";//nom
		//if(n != tableauArguments["j"]) {
		//	chaineHTML += "</span>";
		//}
		if (statistiques[n][2]>0)//joueur sp�cial
			tableauStatistiquesChaine += (statistiques[n][2]==1?" (IA)":" (NET)");
		tableauStatistiquesChaine += (tableauArguments["j"] == n?" (moi)</b>":"");
		
		tableauStatistiquesChaine += "</td><td style=\"text-align:center;\">";
		//chaineHTML += (this.joueurEnCours == n?"</u>":"");
		tableauStatistiquesChaine += " <span title='Nombre de cellules'>"+statistiques[n][3]+"</span> ";
		tableauStatistiquesChaine += "</td><td style=\"text-align:center;\">";
		tableauStatistiquesChaine += "<span title='Cases contr&ocirc;l&eacute;es'>"+statistiques[n][4]+"</span> ";
		tableauStatistiquesChaine += "</td><td style=\"text-align:center;\">";
		tableauStatistiquesChaine += "<span title='Cases pr&ecirc;tes � exploser'>"+statistiques[n][5]+"</span> ";
		tableauStatistiquesChaine += "</td><td style=\"text-align:center;\">";
		tableauStatistiquesChaine += "<span title='Cases menac&eacute;es'>"+statistiques[n][6]+"</span>";
		//if(n == tableauArguments["j"]) {
		//	chaineHTML += "</span>";
		//}
		//chaineHTML += "</p>";
		tableauStatistiquesChaine += "</td><tr>";
	}
	tableauStatistiquesChaine += "</td></tr></table>";
	
	if (quiLanceLIA == tableauArguments["j"] && demarree==0 && this.joueurs[this.joueurEnCours][3]==1)
		tableauStatistiquesChaine += "<input type='button' class='btn' value='D&eacute;marrer' onclick='demarree=1;this.style.display=\"none\";unJeu.faireJouerSuivant();' />";

	if (quiLanceLIA == tableauArguments["j"] && demarree==0 && this.joueurs[this.joueurEnCours][3]==0)
		tableauStatistiquesChaine += "<input type='button' class='btn' value='Pour d&eacute;marrer, jouez !' onclick='this.style.display=\"none\";' />";

	if (quiLanceLIA == tableauArguments["j"] && demarree==1 && this.joueurs[this.joueurEnCours][3]==1)
		tableauStatistiquesChaine += "<input type='button' class='btn' value='Lancer une IA' onclick='lancerIA();' title=\"Si tu penses que l'IA n'a pas �t� lanc�e\" />";
	tableauStatistiquesChaine += "<input type='button' class='btn' value='Recharger le jeu' onclick='tableauArguments.synchroserveur=true;getJeuXML();tableauArguments.synchroserveur=false;' title='Si tu penses que le jeu est coinc� dans ton client' />";
	
	chaineHTML = debutChaineHTML + "<td id='grossecaseagauche'><img src='images/status.php?c="+this.joueurs[this.joueurEnCours][1]+"&s="+statutDuJoueurEnCours+"' /></td>"+//tableauStatistiquesChaine+
				chaineHTML+"<td>"+tableauStatistiquesChaine+"</td></tr></table></center><br />";

	//chaineHTML += "</td></tr></table><br />";
	switch (typeJouable){
		case 1:
			if (this.options[0]) {
				chaineHTML += "A toi de jouer : ";
				chaineHTML += "<input type='button' class='btn' style='background-color:#"+this.joueurs[this.joueurEnCours][1]+"' value='Mode : Ajout de cellule' onclick='unJeu.affiche(2)' title='' />";
				break;
			}
		case 2:
			if (this.options[0]) {
				chaineHTML += "A toi de jouer : ";
				chaineHTML += "<input type='button' class='btn' style='background-color:#"+this.joueurs[this.joueurEnCours][1]+"' value='Mode : Construire/d&eacute;truire un chateau' onclick='unJeu.affiche(1)' title='' />";
				break;
			}
			chaineHTML += "<input type='button' class='btn' style='background-color:#"+this.joueurs[this.joueurEnCours][1]+"' value='Jouez...' title='' />";
			break;
		case 0:
			chaineHTML += "<input type='button' class='btn' style='background-color:#"+this.joueurs[this.joueurEnCours][1]+"' value='Patientez pendant le traitement...' onclick=\"alert('Pause')\" title='' />";
			break;
		case 255:
			chaineHTML += "<input type='button' class='btn' style='background-color:#"+this.joueurs[nGagnant][1]+"' value='"+this.joueurs[nGagnant][0]+" a gagn&eacute; !' onclick=\"alert('Bravo !')\" title='' />";
			if (nGagnant && tableauArguments["j"]==quiLanceLIA){
				setTimeout("document.getElementById(\"comm\").innerHTML = \"<input type=\\\"button\\\" class='btn' onclick=\\\"supprimerPartie()\\\" value=\\\"Supprimer la partie\\\" />\";",10000);
			}
			break;
		case 254:
			chaineHTML += "<input type='button' class='btn' value='Patientez pendant que "+this.joueurs[this.joueurEnCours][0]+" joue...' onclick=\"alert('Pause')\" title='' />";
//			chaineHTML += "<input type='button' style='background-color:"+this.joueurs[this.joueurEnCours][1]+"' value='Patientez pendant que "+this.joueurs[this.joueurEnCours][0]+" joue...' onclick=\"alert('Pause')\" title='' />";
//			chaineHTML += "<input type='button' BORDERCOLOR='"+this.joueurs[this.joueurEnCours][1]+"' value='Patientez pendant que "+this.joueurs[this.joueurEnCours][0]+" joue...' onclick=\"alert('Pause')\" title='' />";
			break;
		default:
			//alert("Argument de affiche() non reconnu : "+typeJouable);
	}
		
	if (tableauArguments["type"]=="texte" || !mettreLesOnload){
		document.getElementById("jeu").innerHTML = chaineHTML;
	} else {
		document.getElementById("jeu").style.display = "none";
		RAZZone(zonePouvantCharger);
		document.getElementById("jeu"+zonePouvantCharger).innerHTML = chaineHTML;
	}
	
}}

var nbImagesChargeesZone = new Array(0,0,0);
var zoneVisible = 1; var zonePouvantCharger = 2;
function RAZZone(zone){	nbImagesChargeesZone[zone] = 0;}
function chargementImageJeu(zone){//appel� quand une image se charge
	nbImagesChargeesZone[zone]++;
	if (nbImagesChargeesZone[zone] == unJeu.tailleX*unJeu.tailleY){//fin du chargement des images : zone de jeu affich�e
		var zone2 = 3-zone;
		document.getElementById("jeu"+zone2).style.display = "none";
		document.getElementById("jeu"+zone).style.display = "block";
		zonePouvantCharger = zone2;
		zoneVisible = zone;
	}
}

function toString(){with(this){//recr�e une string de jeu
	var chaine = ""+this.nbJoueurs+"\n";
	chaine += this.joueurEnCours+"\n";
	for(var i=0;i<this.nbJoueurs;i++)
		for(var j=0;j<this.joueurs[i+1].length;j++)
			chaine += this.joueurs[i+1][j] +(j+1==this.joueurs[i+1].length?"\n":"\t");
	for(var i=0;i<this.nbJoueurs;i++)
		for(var j=0;j<this.derniereAction[i+1].length;j++)
			chaine += this.derniereAction[i+1][j] +(j+1==this.derniereAction[i+1].length?"\n":"\t");
	var nombreDOptions = 5;
	for(var i=0;i<nombreDOptions;i++)	//chateauxactiv�s profondeur bordbloqu�s diagonale tempspasreel
		chaine += this.options[i]+"\n";
	chaine += this.tailleX + "\n";
	chaine += this.tailleY + "\n";
	for(var i=0;i<this.tailleY;i++)
		for(var j=0;j<this.tailleX;j++)
			chaine += this.tableauDecor[i][j] +(j+1==this.tailleX?"\n":"\t");
	for(var i=0;i<this.tailleY;i++)
		for(var j=0;j<this.tailleX;j++)
			chaine += this.tableauJeu[i][j] +(j+1==this.tailleX?"\n":"\t");
	chaine += this.noTour+"\n";
	for(var i=0;i<this.tailleY;i++)
		for(var j=0;j<this.tailleX;j++)
			chaine += this.tableauDesMax[i][j] +(j+1==this.tailleX?"\n":"\t");
	return chaine;
}}

function distance (x,y,x2,y2){with(this){//renvoie un flottant
	var dx=x2-x; var dy=y2-y;
	switch(this.options[2]){
		case 0:case 1:
			if (this.options[3])
				return	distN0(dx,dy);//max(abs(dx),abs(dy));
						//round(sqrt(pow(dx,2)+pow(dy,2)),1);
			else
				return Math.abs(dx)+Math.abs(dy);
		case 2://torrique
			var d = this.tailleX + this.tailleY;
			if (this.partie.options.yaPlacementDiag())
				for(var i=-1;i<2;i++) for(var j=-1;j<2;j++)
					d = Math.min(d,distN0(dx+i*this.tailleX,dy+j*this.tailleY));//sqrt(pow(dx+i*this.tailleX,2)+pow(dy+j*this.tailleY,2));
			else
				for(var i=-1;i<2;i++) for(var j=-1;j<2;j++)
					d = Math.min(d,Math.abs(dx+i*this.tailleX)+Math.abs(dy+j*this.tailleY));
			return d;
	}
}}
function caseLaPlusEloigneeDe (x,y,joueur){with(this){//renvoie le array(x,y) de la case du joueur la plus �loign�e
	//entre ex-aequo, on prend la case du milieu, dans un parcours en y puis x
	var distanceMax = -1;
	var tabReponses = null;
	for (var i=0;i<this.tailleY;i++) for (var j=0;j<this.tailleX;j++){
		var cetteCase = this.tableauJeu[i][j];
		if (case2joueur(cetteCase) == joueur && case2cellules(cetteCase) > 0){
			var dist = this.distance(j,i,x,y);
			if (dist > distanceMax){
				tabReponses = new Array(new Array(j,i));
				distanceMax = dist;
			}
			else if (dist == distanceMax){
				tabReponses[tabReponses.length] = new Array(j,i);
			}
		}
	}
	return tabReponses[Math.floor(tabReponses.length*0.5)];
}}

function joueEn(x,y){with(this){//ajoute une cellule du joueur en cours en x,y
	//alert("Joue en "+x+", "+y)
	if (!this.jouable(x,y))
		return false;
	demarree = 1;
	if (!this.options[5]){
		var temp = this.caseLaPlusEloigneeDe(x,y,joueurEnCours);
		x2 = temp[0]; y2 = temp[1];
		if (case2cellules(this.tableauJeu[y2][x2])>0)
			this.tableauJeu[y2][x2] -= 1;
	}
	if (tableauArguments["offline"]==0 && this.joueurEnCours==tableauArguments["j"])
		jeJoueEn(x,y,false);//on n'attend pas de savoir si c'est bon, ce client est bien programm�
	if (case2cellules(this.tableauJeu[y][x]) >= 100 - (this.tableauDecor[y][x]==2?2:1))
		this.tableauJeu[y][x] = Math.floor(this.tableauJeu[y][x]/100)*100+99;//il a trop de cellules d�j�
	else
		this.tableauJeu[y][x] += (this.tableauDecor[y][x]==2?2:1);
	this.tableauCasesUtilisees[y][x] = this.noTour + "-" + this.joueurEnCours;
	this.tableauJeu[y][x] += this.joueurEnCours*100-case2joueur(this.tableauJeu[y][x])*100;
	this.derniereAction[this.joueurEnCours][0] = "n";
	this.derniereAction[this.joueurEnCours][1] = x;
	this.derniereAction[this.joueurEnCours][2] = y;
	this.derniereAction[this.joueurEnCours][3] = this.noTour;
	affiche(0);
	window.setTimeout("unJeu.purifieEtAffiche()",tempsEntreAffichages);
	return true;
}}

function chateauEn(x,y){with(this){//ajoute une cellule du joueur en cours en x,y et y cr�e une membrane
	if (this.tableauDecor[y][x] != 0 || this.options[0] == 0)//on ne peut construire de membrane que sur un endroit stable
		return false;
	if (!this.jouable(x,y))
		return false;
	demarree = 1;
	if (tableauArguments["offline"]==0 && this.joueurEnCours==tableauArguments["j"])
		jeJoueEn(x,y,true);//on n'attend pas de savoir si c'est bon, ce client est bien programm�
	if (case2cellules(this.tableauJeu[y][x]) >= 99)
		this.tableauJeu[y][x] = Math.floor(this.tableauJeu[y][x]/100)*100+99;//il a trop de cellules d�j�
	else
		this.tableauJeu[y][x] += 1;
	this.tableauCasesUtilisees[y][x] = this.noTour + "-" + this.joueurEnCours;
	this.tableauJeu[y][x] += this.joueurEnCours*100-case2joueur(this.tableauJeu[y][x])*100;
	if (case2chateau(this.tableauJeu[y][x]))
		this.tableauJeu[y][x] -= 10000;//destruction d'un chateau
	else
		this.tableauJeu[y][x] += 10000;//construction d'un chateau
	this.derniereAction[this.joueurEnCours][0] = "c";
	this.derniereAction[this.joueurEnCours][1] = x;
	this.derniereAction[this.joueurEnCours][2] = y;
	this.derniereAction[this.joueurEnCours][3] = this.noTour;
	affiche(0);
	window.setTimeout("unJeu.purifieEtAffiche()",tempsEntreAffichages);
	return true;
}}

function jouable(x,y){with(this){//v�rifie si le joueur en cours peut jouer en x,y
	if (case2joueur(this.tableauJeu[y][x]) != this.joueurEnCours && case2cellules(this.tableauJeu[y][x]) > 0)
		return false; //case d�j� control�e par joueur adverse
	if (case2joueur(this.tableauJeu[y][x]) == this.joueurEnCours && case2cellules(this.tableauJeu[y][x]) > 0)
		if (this.tableauDecor[y][x] == 1 && case2cellules(this.tableauJeu[y][x]) >= this.tableauDesMax[y][x] - 1) //case d�j� control�e par ce joueur
			return false // mais glace et limite atteinte
		else
			return true; // pas de probl�me
	if (this.tableauDecor[y][x] == 1 && (case2joueur(this.tableauJeu[y][x]) != this.joueurEnCours || case2cellules(this.tableauJeu[y][x])==0))
		return false; // glace et case non control�e
	if (this.tableauDecor[y][x] == 3)//obstacle
		return false;
	for(var i=-1;i<2;i++)//on va regarder si une case autour appartient au joueur
		for(var j=-1;j<2;j++){// 2 bordbloqu�s  3 diagonale
			if (i==0 && j==0)
				continue; // on a d�j� test� la case centrale
			if (this.options[3] == 0 && Math.abs(i)+Math.abs(j)==2)
				continue;//pas en diagonale
			var nvx = x+i; var nvy = y+j;
			if (this.options[2] != 2 && (!entre(0,nvx,this.tailleX-1) || !entre(0,nvy,this.tailleY-1)))
				continue;//apr�s le bord
			nvx = mettreEntre(nvx,this.tailleX);//au cas o� le monde est rond
			nvy = mettreEntre(nvy,this.tailleY);
			if (case2joueur(this.tableauJeu[nvy][nvx]) == this.joueurEnCours && case2cellules(this.tableauJeu[nvy][nvx]) > 0)
				return true; //case control�e par ce joueur
	}
	return false;
}}

function afficheOptions(){with(this){//affiche les options en cours, pour faire conna�tre les r�gles
//chateauxactiv�s profondeur bordbloqu�s diagonale tempspasreel
	var chaineHTML = "";
	chaineHTML += (this.options[0]?"Chateaux permis":"Chateaux non permis")+"<br />";
	chaineHTML += "Profondeur de d&eacute;veloppement : "+this.options[1]+"<br />";
	switch(this.options[2]){
		case 0: chaineHTML += "Cellules perdues aux bords<br />";
			break;
		case 1: chaineHTML += "Bords solides<br />";
			break;
		case 2: chaineHTML += "Monde torrique<br />";
			break;
	}
	chaineHTML += (this.options[3]?"Placement autour des cases &agrave; soi":"Placement sur cases adjacentes en croix")+"<br />";
	chaineHTML += (this.options[4]?"D&eacute;veloppement unique du joueur en cours":"D&eacute;veloppement de tout le monde")+"<br />";
	chaineHTML += (tableauArguments["j"] == quiLanceLIA?"<input type='button' class='btn' onclick='supprimerPartie();' value='Supprimer la partie' />"+"<br />":"");
	chaineHTML += (tableauArguments["j"] == quiLanceLIA && this.params?"<a href='#' onclick=\"recommencer(unJeu);\">Recommencer la mission</a>"+"<br />":"");
	chaineHTML += (tableauArguments["j"] == quiLanceLIA && this.joueurs[1][0] == "Testeur" && this.params?"<a href='#' onclick=\"editer(unJeu);\">Aller &agrave; l'&eacute;diteur</a>"+"<br />":"");
	
	document.getElementById("options").innerHTML = chaineHTML;
}}

function recommencer(leJeu){
	if (!leJeu.params) return false;
	var lien="creajeucampagne.php?n=0&c="+leJeu.params.getAttribute("c")+
		"&m="+leJeu.params.getAttribute("m")+"&joueur="+leJeu.joueurs[1][0]+
		"&couleur="+leJeu.joueurs[1][1];
	debug(lien);debug(framecom);
	supprimerPartie(false);
	setTimeout("framecom.src='"+lien+"';",2000);
	setTimeout("location.replace('"+lien+"');",5000);
	return lien;
}
function continuerCampagne(leJeu){
	if (!leJeu.params) return false;
	var lien="creajeucampagne.php?n=0&c="+leJeu.params.getAttribute("c")+
		"&m="+leJeu.params.getAttribute("suivante")+"&joueur="+leJeu.joueurs[1][0]+
		"&couleur="+leJeu.joueurs[1][1];
	debug(lien);debug(framecom);
	supprimerPartie(false);
	setTimeout("framecom.src='"+lien+"';",2000);
	setTimeout("location.replace('"+lien+"');",5000);
	return lien;
}
function editer(leJeu){
	var lien = "campagnes/editeurcampagnes.php?c="+
				leJeu.params.getAttribute("c")+"&m="+leJeu.params.getAttribute("m")+
				"&pw="+("undefined" != typeof tableauArguments["pwtest"]?
					tableauArguments["pwtest"]:prompt("Mot de passe Testeur/Administrateur requis",""));
	supprimerPartie(false);
	setTimeout("location.replace('"+lien+"');",2000);
}

//fonctions utiles
function case2chateau(entier){//regarde si sur une case il y a un chateau
	return (entier>=10000);
}

function case2cellules(entier){//retourne le nombre de cellules dans la case
	return (entier-(Math.floor(entier/100)*100));
}

function case2joueur(entier){//retourne le n� d'un joueur � qui est la case
	/*if (case2cellules(entier) == 0)
		return 0; //renvoie 0 si aucune cellule*/
	return Math.floor((entier-(case2chateau(entier)?10000:0))/100);
}

function versRRGGBB(couleur){//retourne le bon format de couleur pour les images
	switch (couleur.substring(0,1)){
	case "#"://couleur #HHHHHH
		return couleur.substring(1);
	case "r"://couleur rgb(rr,gg,bb)
		tabCouleur = couleur.split(",");
		tabCouleur = new Array(parseInt(tabCouleur[0].split("(")[1]),parseInt(tabCouleur[1]),parseInt(tabCouleur[2].split(")")[0]));
		retourne = (tabCouleur[0]<16?"0":"")+tabCouleur[0].toString(16)+(tabCouleur[1]<16?"0":"")+tabCouleur[1].toString(16)+(tabCouleur[2]<16?"0":"")+tabCouleur[2].toString(16);
		//alert(couleur+" "+tabCouleur+" "+retourne);
		return retourne;
	default://couleur RRGGBB
		return couleur;
	}
}




/**** fonctions de communication AJAX ****/
function getJeuXML(){//demande de jeu au serveur
	if (demarree==1 && !tableauArguments["synchroserveur"])
		return;
	var tabGet = { "p": tableauArguments["p"], "a": "g", "j": tableauArguments["j"] };
	if (tableauArguments["pw"])
		tabGet["pw"] = tableauArguments["pw"];
	communiqueGET( { "p": tableauArguments["p"], "a": "g", "j": tableauArguments["j"] },traiterJeu2);
}
function traiterJeu2(leJeuXML){//fonction de traitement apr�s getJeu()
	//document.getElementById('jeu').innerHTML = leJeuChaine.split('\n').join('<br/>');
	document.getElementById("jeu").innerHTML = "Jeu re&ccedil;u, traitement en cours...";
	unJeu = new CreerJeu(leJeuXML);
	if (demarree==0)
		window.setTimeout("getJeuXML()",tempsDAttenteAvantRelance);
	if (tableauArguments["type"] != "texte")
		chargeImages();//on charge les images !
	else
		apresChargement();
}
function apresChargement(){
	unJeu.affiche(1);
	unJeu.afficheOptions();
	unJeu.faireJouerSuivant();
}

function lancerIA(){
	communiqueGET( "ia.php?p="+tableauArguments["p"],null);
}

function jeJoueEn(x,y,chateau,fonctionALancer){//dit au serveur o� on joue, arr�te le jeu si contraire au serveur
	var a=(chateau?"c":"n");
	var xhr = createXHR();
	var chaineDAppel = serveur+"?a="+a+"&x="+x+(tableauArguments["pw"]?"&pw="+tableauArguments["pw"]:"")+"&y="+y+"&p="+tableauArguments["p"]+"&j="+tableauArguments["j"]+"&nocache=" + Math.random();
	xhr.onreadystatechange  = function(){ 
		if(xhr.readyState  == 4){
			if(xhr.status  == 200) {
				document.getElementById("comm").innerHTML = "Information re&ccedil;ue.";
				if (xhr.responseXML.getElementsByTagName("action").item(0).getAttribute("autorisee")!="oui"){
					alert("Action refus�e par le serveur !");
				}
				if (fonctionALancer != undefined)
					fonctionALancer(xhr);
			} else {
				document.getElementById("comm").innerHTML = "Information non re&ccedil;ue.<br />";
				document.getElementById("comm").innerHTML += "Info envoy&eacute;e :" + chaineDAppel;
				alert("Erreur de connexion au serveur");
			}
         }
    }; 
	document.getElementById("comm").innerHTML = "Attente du serveur...";
	
	xhr.open("GET", chaineDAppel,  true); 
	xhr.send(null);
}

function ouJoue(noJoueur, fonctionALancer){//demande au serveur qu'a jou� le joueur no
	var xhr = createXHR();
	var chaineDAppel = serveur+"?a=m&k="+noJoueur+"&p="+tableauArguments["p"]+(tableauArguments["pw"]?"&pw="+tableauArguments["pw"]:"")+"&j="+tableauArguments["j"]+"&nocache=" + Math.random();
	xhr.onreadystatechange  = function(){ 
		if(xhr.readyState  == 4){
			if(xhr.status  == 200) {
				traiterOuJoue(noJoueur,xhr.responseXML,fonctionALancer);
			} else {
				document.getElementById("comm").innerHTML = "Information non re&ccedil;ue.<br />";
				document.getElementById("comm").innerHTML += "Info envoy&eacute;e :" + chaineDAppel;
			}
         }
    };
	
	xhr.open("GET", chaineDAppel,  true); 
	xhr.send(null);
}
function traiterOuJoue(noJoueur,reponse,fonctionALancer){//fonction de traitement apr�s ouJoue()
	//si entre balises, utiliser .getElementsByTagName("laBalise")[0].childNodes[0].nodeValue
	var a = reponse.getElementsByTagName("a").item(0).getAttribute("valeur");
	var x = parseInt(reponse.getElementsByTagName("x").item(0).getAttribute("valeur"));
	var y = parseInt(reponse.getElementsByTagName("y").item(0).getAttribute("valeur"));
	var k = parseInt(reponse.getElementsByTagName("k").item(0).getAttribute("valeur"));
	var n = parseInt(reponse.getElementsByTagName("n").item(0).getAttribute("valeur"));
	var tabReponse = new Array();
	tabReponse["a"] = a;tabReponse["x"] = x;tabReponse["y"] = y;tabReponse["k"] = k;tabReponse["n"] = n;
	if (fonctionALancer != undefined)
		fonctionALancer(tabReponse);
}

function quiJoue(fonctionALancer){// demande au serveur qui est en train de jouer
	var xhr = createXHR();
	var chaineDAppel = serveur+"?a=m&k=0&p="+tableauArguments["p"]+(tableauArguments["pw"]?"&pw="+tableauArguments["pw"]:"")+"&j="+tableauArguments["j"]+"&nocache=" + Math.random();
	xhr.onreadystatechange  = function(){ 
		if(xhr.readyState  == 4){
			if(xhr.status  == 200) {
				document.getElementById("comm").innerHTML = "Information re&ccedil;ue.";
				traiterQuiJoue(xhr.responseXML,fonctionALancer);
			} else {
				document.getElementById("comm").innerHTML = "Information non re&ccedil;ue.<br />";
				document.getElementById("comm").innerHTML += "Info envoy&eacute;e :" + chaineDAppel;
			}
         }
    }; 
	document.getElementById("comm").innerHTML = "Attente du serveur...";
	
	xhr.open("GET", chaineDAppel,  true); 
	xhr.send(null);
	
}
function traiterQuiJoue(reponse,fonctionALancer){//fonction de traitement apr�s quiJoue()
	//si entre balises, utiliser .getElementsByTagName("laBalise")[0].childNodes[0].nodeValue
	var k = parseInt(reponse.getElementsByTagName("k").item(0).getAttribute("valeur"));
	var n = parseInt(reponse.getElementsByTagName("n").item(0).getAttribute("valeur"));
	document.getElementById("comm").innerHTML += "<br />k="+k+" ";
	var tabReponse = new Array();
	tabReponse["k"] = k;tabReponse["n"] = n;
	if (fonctionALancer != undefined)
		fonctionALancer(tabReponse);
}

function changeMP(){//demande au serveur de changer le mot de passe
	var xhr = createXHR();
	var chaineDAppel = serveur+"?a=nvp"+(tableauArguments["pw"]?"&pw="+tableauArguments["pw"]:"")+"&k="+escape(document.mdp.motdepasse.value)+"&p="+tableauArguments["p"]+"&j="+tableauArguments["j"]+"&nocache=" + Math.random();
	xhr.onreadystatechange  = function(){ 
		if(xhr.readyState  == 4){
			if(xhr.status  == 200) {
				window.location.replace("jeu.html?p="+tableauArguments["p"]+"&j="+tableauArguments["j"]+"&pw="+escape(document.mdp.motdepasse.value));
			} else {
				alert("Mot de passe non chang�");
			}
         }
    }; 
	document.getElementById("comm").innerHTML = "Attente du serveur...";
	
	xhr.open("GET", chaineDAppel,  true); 
	xhr.send(null);
	
}
function chargeMP(){
	window.location.replace("jeu.html?pw="+escape(document.mdp.motdepasse.value)+"&p="+tableauArguments["p"]+"&j="+tableauArguments["j"]);
}

function supprimerPartie(revenirAccueil){
	revenirAccueil = (typeof revenirAccueil == "undefined"?true:revenirAccueil);
	var xhr = createXHR();
	var chaineDAppel = serveur+"?a=s"+(tableauArguments["pw"]?"&pw="+tableauArguments["pw"]:"")+"&p="+tableauArguments["p"]+"&j="+tableauArguments["j"]+"&nocache=" + Math.random();
	xhr.onreadystatechange  = function(){ 
		if(xhr.readyState  == 4){
			if(xhr.status  == 200) {
				document.getElementById("options").innerHTML = "Partie supprim&eacute;e.<br />";
				if (revenirAccueil) window.location.replace('index.php');
			} else {
			}
         }
    }; 
	document.getElementById("comm").innerHTML = "Attente du serveur...";
	
	xhr.open("GET", chaineDAppel, true); 
	xhr.send(null);
}


/*** fonction principale ***/
var unJeu;
function main(){
	if (!tableauArguments["p"]) return;//le jeu n'est pas pr�cis�
	getJeuXML();
	//getJeu();
	if (tableauArguments["synchroserveur"])
		setInterval("getJeuXML()",7000);
	/*unJeu = new CreerTableauJeu(jeuTest);
	unJeu.affiche(1);
	unJeu.afficheOptions();
	unJeu.faireJouerSuivant();*/
	
}

/*** fonctions diverses ***/
var typeImagesChargees = false; var nbJoueursCharges = -1;
var listeDimages = new Array();var indiceImages = 0;var maxCellules = 0;var maxIndURL = 0;
function chargeImages(){//charge les images
/*image.php : cr�e une image de case
	param�tres GET :
		c	couleur RRGGBB
		n	nombre de cellules
		h	chateau ? 1/0
		d	d�cor 0/1/2/3
		m	max atteint ? 1/0
		type type d'affichage
			texte	cases format texte
			atome	cases avec atomes anim�s
			cellule	cases avec cellules
			mediev	cases version moyen-age
		taille	param�tre optionnel pour une taille d'atomes*/
	if (tableauArguments["type"] == typeImagesChargees && nbJoueursCharges==unJeu.nbJoueurs){//images d�j� charg�es
		apresChargement();
		return;
	}
	if (tableauArguments["type"] != typeImagesChargees){//images d�j� charg�es mais manque des joueurs
		nbJoueursCharges = -1;
	}
	var quelsDecors = new Array();
	var quelsJoueurs = new Array();
	for (x=0;x<4;x++) quelsDecors[x] = false;
	for (x=0;x<=unJeu.nbJoueurs;x++) quelsJoueurs[x] = false;
	for (x=0;x<unJeu.tailleX;x++)for (y=0;y<unJeu.tailleY;y++){
			quelsDecors[unJeu.tableauDecor[y][x]] = true;
			quelsJoueurs[case2joueur(unJeu.tableauJeu[y][x])] = (quelsJoueurs[case2joueur(unJeu.tableauJeu[y][x])] || 0<case2cellules(unJeu.tableauJeu[y][x]));
			maxCellules = Math.max(maxCellules, case2cellules(unJeu.tableauJeu[y][x]));
	}
	
	if (tableauArguments["type"] != "texte"){
		var ims = new Array();var ind=0;
		var chaine = "";
		for(j=nbJoueursCharges+1;j<=unJeu.nbJoueurs;j++)
			for (d=0; d<4 && quelsJoueurs[j]; d++)
				for (r=0;r<2;r++){//cases vides
					if (!quelsDecors[d]) continue;
					url = "images/image.php?c="+(j==0?"808080":versRRGGBB(unJeu.joueurs[j][1]))+"&n=0&h=0&d="+d+"&type="+tableauArguments["type"]+"&m=0&r="+r;
					listeDimages[indiceImages++] = url;
				}
		for (n=1; n<15; n++){
			for (r=0;r<2;r++)
				for(j=nbJoueursCharges+1;j<=unJeu.nbJoueurs;j++)
					for (m=0; m<2 && quelsJoueurs[j]; m++)
						for (h=0;h<2 && m==0;h++)
							for (d=0;d==0 || (d<3 && h==0);d++) {
								if (!quelsDecors[d]) continue;
								url = "images/image.php?c="+(j==0?"BFBFBF":versRRGGBB(unJeu.joueurs[j][1]))+"&n="+n+"&h="+h+"&d="+d+"&type="+tableauArguments["type"]+"&m="+m+"&r="+r;
								listeDimages[indiceImages++] = url;
							}
			if (n < maxCellules) maxIndURL = indiceImages+1;
		}
		indiceImages = 0;
		document.getElementById("imgs").innerHTML = "Images : ";
		var prechargement = true;
		if(prechargement) {
			imageSuivante();
		} else {
			apresChargement();
		}
	}
}
var lefaireunefois = false;
function imageSuivante(){
	url = listeDimages[indiceImages++];
	if (!url) {
		typeImagesChargees = tableauArguments["type"]; nbJoueursCharges = unJeu.nbJoueurs;
		document.getElementById("imgs").innerHTML = "Chargement des images termin&eacute; !";
		if (!lefaireunefois){
			apresChargement();//on lance l'affichage
			lefaireunefois = true;
		}
	} else {
		if (maxIndURL<indiceImages){
			if (!lefaireunefois){
				apresChargement();//on lance l'affichage
				lefaireunefois = true;
			}
			document.getElementById("imgs").innerHTML = "Chargement des autres images "+indiceImages+" sur "+listeDimages.length+" : <img alt=\"Si cette image n'apparait pas, recharger la page\" style=\"vertical-align:bottom\" src=\""+url+"\" onload=\"imageSuivante();\" />";
		} else {
			document.getElementById("imgs").innerHTML = "Chargement de l'image "+indiceImages+" sur "+maxIndURL+" : <img alt=\"Si cette image n'apparait pas, recharger la page\" style=\"vertical-align:bottom\" src=\""+url+"\" onload=\"imageSuivante();\" />";
		}
	}
}

function mettreOnlineOffline(){

}

</script>
<?php
if ($mettreLeCadreHTML){
?>
</head>
<body onload="main()">
<?php } else { ?>

<?php
}
?>
<div id="infocampagne" style="display:none;"></div>
<div style="z-index: 99; display: none; position: absolute; left: 0; top: 0; width: 100%; height: 100%" id="popupcampagne">
	<table border="0" cellpadding="0" cellspacing="0" style="width: 100%; height: 100%;"><!-- background: url('css/img/footer.jpg')-->
		<tr>
			<td align="center">
				<div style="width: 400px; height: 400px; border: 1px solid #C0C793; background: #FFFFFF">
				<input class="btn" style="align:center;font-size:6px;height:12px;width:12px;padding:0;float:right;right:0;top:0;" value="x" onclick="document.getElementById('popupcampagne').style.display='none';" />
				<div id="interieurpopup"></div>
				
				
				</div>
			</td>
		</tr>
	</table>
</div>
<div name="Joueurs" id="joueurs">
<form name="mdp">
<input type="text" name="motdepasse" value="Mot de passe" onfocus="this.value='';" />
<input type="button" class='btn' onclick="chargeMP();" value="OK" title="cliquer ici pour charger le mot de passe" />
<input type="button" class='btn' onclick="changeMP();" value="Change" title="cliquer ici pour changer le mot de passe" />
 | 
<input type="button" class='btn' name="mettreoffline" value="Online/offline" title="Sortir" onclick="if (tableauArguments['offline'] == 0) if (unJeu.joueurEnCours==tableauArguments['j']) {tableauArguments['offline'] = 1;this.value='Recharger le jeu online';} else {tableauArguments['synchroserveur']=1;getJeuXML();tableauArguments['synchroserveur']=0;tableauArguments['offline'] = 1;this.value='Recharger le jeu online';} else {tableauArguments['synchroserveur']=1;getJeuXML();tableauArguments['synchroserveur']=0;tableauArguments['offline'] = 0;this.value='Se remettre offline';}" onmouseover="if (tableauArguments['offline'] == 0) {this.title='Actuellement : online'; this.value='Se mettre offline';} else {this.title='Actuellement : offline'; this.value='Recharger le jeu online';}" />
 | 
<input type="checkbox" name="prevenu" /> Etre pr&eacute;venu quand c'est notre tour
 | 
<input type="checkbox" name="instantane" onclick="if (this.checked) tempsEntreAffichages=10; else tempsEntreAffichages=1000;" /> D&eacute;veloppement instantan&eacute;
 | 
Format : <select name="type"><option value="texte">texte</option><option value="atome">atomes</option><option value="cellule">cellules</option><option value="mediev">moyen-&acirc;ge</option><option value="cool">new age</option></select>
<input type="button" class='btn' value="OK" onclick="window.location.replace(window.location.href.replace(/&type=\w*/g, '').replace(/type=\w*&/g, '') +'&type='+document.mdp.type.value);">
</form>
</div>

<div name="Jeu" id="jeu"></div><br />
<div name="Jeu1" id="jeu1" style="display:block;"></div>
<div name="Jeu2" id="jeu2" style="display:none;"></div>
<div name="options" id="options">
<form method="GET">
Num&eacute;ro partie : <input type="text" name="p" value="0000000" onfocus="if (this.value='0000000') this.value='';" /><br/>
Num&eacute;ro du joueur : <select name="j"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option></select><br/>
<input type="submit" class='btn' value="Chercher la partie" title="clique ici" />
</form>
</div>
<div name="imagesprechargees" id="imgs">
</div>

<script type="text/javascript"><!-- Debugage !
function debug(chaine){
	document.getElementById("debug").innerHTML = chaine+"<br />"+document.getElementById("debug").innerHTML;
}
//-->
</script>
<div id="debug">

</div>
<iframe id="framecom" name="framecom" style="display:none;"></iframe><!-- -->
<div id="comm">

</div>
<div id="bas"><!--small>&copy; C&eacute;dric & Mika&euml;l Mayer 2009 | <a href="index.php" style="text-decoration:none;">Retour &agrave; l'accueil</a></small--></div>
<?php
if ($mettreLeCadreHTML){
?>
</body>
</html>
<?php } else { ?>
<script type="text/javascript">
//main(); -> d�plac� dans l'index
</script>
</div>
<?php
}
?>
