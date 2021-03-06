var serveur = "serveur.php";
//alert(typeof serveur);

//arguments de l'appel de la page
var bouh = location.search.substring(1,location.search.length).split(unescape("%26"));//on enl�ve le ? et on s�pare avec les &
var tableauArguments = new Array();
tableauArguments["offline"] = 0;
for(var i=0;i<bouh.length;i++){
   var temp = bouh[i].split("=");
    tableauArguments[temp[0]]=unescape(typeof temp[1] != "undefined"?temp[1]:true);
}

function createXHR() {

    var request = false;
        try {
            request = new ActiveXObject('Msxml2.XMLHTTP');
        }
        catch (err2) {
            try {
                request = new ActiveXObject('Microsoft.XMLHTTP');
            }
            catch (err3) {
				try {
					request = new XMLHttpRequest();
				}
				catch (err1) 
				{
					alert("Votre client n'autorise pas les connexions Ajax.");
					request = false;
				}
            }
        }
    return request;
}

function lecteurXML(atester){
	atester = (typeof atester == "undefined"?false:atester);
	this.doc = false;
	try //Internet Explorer
	{
		this.doc=new ActiveXObject("Microsoft.XMLDOM");
	}
	catch(e)
	{
		try //Firefox, Mozilla, Opera, etc.
		{
			this.doc=document.implementation.createDocument("","",null);
		}
		catch(e) {alert(e.message)}
	}
	
	
	this.chargeFichier = function (fichier){//charge un fichier local
		try 
		{
			this.doc.async=false;
			if (!this.doc.load(fichier)) throw new Exception("");
			return true;
		}
		catch(e) {
			try{
				//alert("ici");
				var xmlhttp = new window.XMLHttpRequest();
				xmlhttp.open("GET",fichier,false);
				xmlhttp.send(null);
				if (xmlhttp.responseXML)
					this.doc = xmlhttp.responseXML;
				else 
					this.chargeChaine(xmlhttp.responseText);
				//alert(xmlhttp.responseText);
				return true;
			}
			catch(e){
				return false;
			}
		}
	}
	
	this.chargeChaine = function (chaineXML){//charge une chaine
		try{
			this.doc.async=false;
			this.doc.loadXML(chaineXML);
			return true;			
		}
		catch(e){
			try{
				var parser=new DOMParser();
				this.doc=parser.parseFromString(chaineXML,"text/xml");
				return true;
			}
			catch (e){
				//alert(e);
				return false;
			}
		}
	}
	
	this.chargeNoeud = function (noeud){
		try{
			this.doc.appendChild(noeud);
			return true;
		} catch(e){
			return false;
		}
	}
	
	this.asXML = function (){//transforme en string
		var chaine = "";
		try{
			var serializer = new XMLSerializer();
            chaine = serializer.serializeToString(this.doc);
		}
		catch(e){
			try{
				var xmlSerializer = document.implementation.createLSSerializer();
                chaine = xmlSerializer.writeToString(this.doc);
			}
			catch(e){
			try{
				chaine = this.doc.xml;
			}
			catch(e){
				return false;
			}
			}
		}
		chaine = chaine.replace(/>/g,'>\n');//pour une meilleure formation
		return chaine;
	}
	
	if (atester){
		if (this.chargeNoeud(atester)) alert(this.asXML());
	}
	//on acc�de aux m�thodes DOM avec truc.doc. ...
}

function HTMLentities(texte) {
	texte = texte.replace(/"/g,'&quot;'); // 34 22
	texte = texte.replace(/&/g,'&amp;'); // 38 26
	texte = texte.replace(/\'/g,'&#39;'); // 39 27
	texte = texte.replace(/</g,'&lt;'); // 60 3C
	texte = texte.replace(/>/g,'&gt;'); // 62 3E
	texte = texte.replace(/\^/g,'&circ;'); // 94 5E
	texte = texte.replace(/�/g,'&lsquo;'); // 145 91
	texte = texte.replace(/�/g,'&rsquo;'); // 146 92
	texte = texte.replace(/�/g,'&ldquo;'); // 147 93
	texte = texte.replace(/�/g,'&rdquo;'); // 148 94
	texte = texte.replace(/�/g,'&bull;'); // 149 95
	texte = texte.replace(/�/g,'&ndash;'); // 150 96
	texte = texte.replace(/�/g,'&mdash;'); // 151 97
	texte = texte.replace(/�/g,'&tilde;'); // 152 98
	texte = texte.replace(/�/g,'&trade;'); // 153 99
	texte = texte.replace(/�/g,'&scaron;'); // 154 9A
	texte = texte.replace(/�/g,'&rsaquo;'); // 155 9B
	texte = texte.replace(/�/g,'&oelig;'); // 156 9C
	texte = texte.replace(/�/g,'&#357;'); // 157 9D
	texte = texte.replace(/�/g,'&#382;'); // 158 9E
	texte = texte.replace(/�/g,'&Yuml;'); // 159 9F
	// texte = texte.replace(/ /g,'&nbsp;'); // 160 A0
	texte = texte.replace(/�/g,'&iexcl;'); // 161 A1
	texte = texte.replace(/�/g,'&cent;'); // 162 A2
	texte = texte.replace(/�/g,'&pound;'); // 163 A3
	//texte = texte.replace(/ /g,'&curren;'); // 164 A4
	texte = texte.replace(/�/g,'&yen;'); // 165 A5
	texte = texte.replace(/�/g,'&brvbar;'); // 166 A6
	texte = texte.replace(/�/g,'&sect;'); // 167 A7
	texte = texte.replace(/�/g,'&uml;'); // 168 A8
	texte = texte.replace(/�/g,'&copy;'); // 169 A9
	texte = texte.replace(/�/g,'&ordf;'); // 170 AA
	texte = texte.replace(/�/g,'&laquo;'); // 171 AB
	texte = texte.replace(/�/g,'&not;'); // 172 AC
	//texte = texte.replace(/ /g,'&shy;'); // 173 AD
	texte = texte.replace(/�/g,'&reg;'); // 174 AE
	texte = texte.replace(/�/g,'&macr;'); // 175 AF
	texte = texte.replace(/�/g,'&deg;'); // 176 B0
	texte = texte.replace(/�/g,'&plusmn;'); // 177 B1
	texte = texte.replace(/�/g,'&sup2;'); // 178 B2
	texte = texte.replace(/�/g,'&sup3;'); // 179 B3
	texte = texte.replace(/�/g,'&acute;'); // 180 B4
	texte = texte.replace(/�/g,'&micro;'); // 181 B5
	texte = texte.replace(/�/g,'&para'); // 182 B6
	texte = texte.replace(/�/g,'&middot;'); // 183 B7
	texte = texte.replace(/�/g,'&cedil;'); // 184 B8
	texte = texte.replace(/�/g,'&sup1;'); // 185 B9
	texte = texte.replace(/�/g,'&ordm;'); // 186 BA
	texte = texte.replace(/�/g,'&raquo;'); // 187 BB
	texte = texte.replace(/�/g,'&frac14;'); // 188 BC
	texte = texte.replace(/�/g,'&frac12;'); // 189 BD
	texte = texte.replace(/�/g,'&frac34;'); // 190 BE
	texte = texte.replace(/�/g,'&iquest;'); // 191 BF
	texte = texte.replace(/�/g,'&Agrave;'); // 192 C0
	texte = texte.replace(/�/g,'&Aacute;'); // 193 C1
	texte = texte.replace(/�/g,'&Acirc;'); // 194 C2
	texte = texte.replace(/�/g,'&Atilde;'); // 195 C3
	texte = texte.replace(/�/g,'&Auml;'); // 196 C4
	texte = texte.replace(/�/g,'&Aring;'); // 197 C5
	texte = texte.replace(/�/g,'&AElig;'); // 198 C6
	texte = texte.replace(/�/g,'&Ccedil;'); // 199 C7
	texte = texte.replace(/�/g,'&Egrave;'); // 200 C8
	texte = texte.replace(/�/g,'&Eacute;'); // 201 C9
	texte = texte.replace(/�/g,'&Ecirc;'); // 202 CA
	texte = texte.replace(/�/g,'&Euml;'); // 203 CB
	texte = texte.replace(/�/g,'&Igrave;'); // 204 CC
	texte = texte.replace(/�/g,'&Iacute;'); // 205 CD
	texte = texte.replace(/�/g,'&Icirc;'); // 206 CE
	texte = texte.replace(/�/g,'&Iuml;'); // 207 CF
	texte = texte.replace(/�/g,'&ETH;'); // 208 D0
	texte = texte.replace(/�/g,'&Ntilde;'); // 209 D1
	texte = texte.replace(/�/g,'&Ograve;'); // 210 D2
	texte = texte.replace(/�/g,'&Oacute;'); // 211 D3
	texte = texte.replace(/�/g,'&Ocirc;'); // 212 D4
	texte = texte.replace(/�/g,'&Otilde;'); // 213 D5
	texte = texte.replace(/�/g,'&Ouml;'); // 214 D6
	texte = texte.replace(/�/g,'&times;'); // 215 D7
	texte = texte.replace(/�/g,'&Oslash;'); // 216 D8
	texte = texte.replace(/�/g,'&Ugrave;'); // 217 D9
	texte = texte.replace(/�/g,'&Uacute;'); // 218 DA
	texte = texte.replace(/�/g,'&Ucirc;'); // 219 DB
	texte = texte.replace(/�/g,'&Uuml;'); // 220 DC
	texte = texte.replace(/�/g,'&Yacute;'); // 221 DD
	texte = texte.replace(/�/g,'&THORN;'); // 222 DE
	texte = texte.replace(/�/g,'&szlig;'); // 223 DF
	texte = texte.replace(/�/g,'&aacute;'); // 224 E0
	texte = texte.replace(/�/g,'&aacute;'); // 225 E1
	texte = texte.replace(/�/g,'&acirc;'); // 226 E2
	texte = texte.replace(/�/g,'&atilde;'); // 227 E3
	texte = texte.replace(/�/g,'&auml;'); // 228 E4
	texte = texte.replace(/�/g,'&aring;'); // 229 E5
	texte = texte.replace(/�/g,'&aelig;'); // 230 E6
	texte = texte.replace(/�/g,'&ccedil;'); // 231 E7
	texte = texte.replace(/�/g,'&egrave;'); // 232 E8
	texte = texte.replace(/�/g,'&eacute;'); // 233 E9
	texte = texte.replace(/�/g,'&ecirc;'); // 234 EA
	texte = texte.replace(/�/g,'&euml;'); // 235 EB
	texte = texte.replace(/�/g,'&igrave;'); // 236 EC
	texte = texte.replace(/�/g,'&iacute;'); // 237 ED
	texte = texte.replace(/�/g,'&icirc;'); // 238 EE
	texte = texte.replace(/�/g,'&iuml;'); // 239 EF
	texte = texte.replace(/�/g,'&eth;'); // 240 F0
	texte = texte.replace(/�/g,'&ntilde;'); // 241 F1
	texte = texte.replace(/�/g,'&ograve;'); // 242 F2
	texte = texte.replace(/�/g,'&oacute;'); // 243 F3
	texte = texte.replace(/�/g,'&ocirc;'); // 244 F4
	texte = texte.replace(/�/g,'&otilde;'); // 245 F5
	texte = texte.replace(/�/g,'&ouml;'); // 246 F6
	texte = texte.replace(/�/g,'&divide;'); // 247 F7
	texte = texte.replace(/�/g,'&oslash;'); // 248 F8
	texte = texte.replace(/�/g,'&ugrave;'); // 249 F9
	texte = texte.replace(/�/g,'&uacute;'); // 250 FA
	texte = texte.replace(/�/g,'&ucirc;'); // 251 FB
	texte = texte.replace(/�/g,'&uuml;'); // 252 FC
	texte = texte.replace(/�/g,'&yacute;'); // 253 FD
	texte = texte.replace(/�/g,'&thorn;'); // 254 FE
	texte = texte.replace(/�/g,'&yuml;'); // 255 FF
	return texte;
}
 
function html_entity_decode(str) {
  html_entity_decode.ta=(html_entity_decode.ta?html_entity_decode.ta:document.createElement("textarea"));
  html_entity_decode.ta.innerHTML=str.replace(/</g,"&lt;").replace(/>/g,"&gt;");
  return html_entity_decode.ta.value;
}

function patiente(millis) {
	var maintenant=new Date();
	var start = maintenant.getTime();
	while(start+millis > maintenant.getTime()){
	    maintenant = new Date();
		//document.f.u.value = start + millis - maintenant.getTime();
	}
	return;
}

//fonction de communication avec le serveur
function communiqueGET(tableauArgs,callback,callbackerreur){
	var xhr = createXHR();
	var chaineDAppel = "";
	if (typeof(tableauArgs)=='string'){
		chaineDAppel = tableauArgs+(tableauArgs.indexOf("?")>=0?"&":"?")+"nocache=" + Math.random();
	}
	else {//tableau associatif
		chaineDAppel+=serveur+"?";
		for(var clef in tableauArgs)
			chaineDAppel+=clef+"="+tableauArgs[clef]+"&";
		chaineDAppel += "nocache=" + Math.random();
	}
	xhr.onreadystatechange  = function(){ 
		if(xhr.readyState  == 4){
			if(xhr.status  == 200) {
				leXML = xhr.responseXML;
				yaErreur = !leXML || leXML.getElementsByTagName("erreur").length;
				if (yaErreur && leXML){
					document.getElementById("comm").innerHTML = 
						"Erreur : "+leXML.getElementsByTagName("erreur").item(0).getAttribute('raison')+
						" lors de "+leXML.getElementsByTagName("erreur").item(0).getAttribute('origine')+".";
				}
				else if (!yaErreur){
					document.getElementById("comm").innerHTML = "";
					if (callback)
						callback(xhr.responseXML);
				}
				else {
					if (callbackerreur){
						document.getElementById("comm").innerHTML = "";
						callbackerreur();
					}
					else{
						alert(xhr.responseXML);
						alert(xhr.responseText);
						document.getElementById("comm").innerHTML = "Erreur lors du chargement du XML";
					}
				}
			} else {
				if (callbackerreur){
					document.getElementById("comm").innerHTML = "";
					callbackerreur();
				}
				else
					document.getElementById("comm").innerHTML = "La communication "+chaineDAppel+" n'a pas abouti.";
			}
         }
    }; 
	document.getElementById("comm").innerHTML = "Attente du serveur...";
	
	xhr.open("GET", chaineDAppel, true); 
	xhr.send(null);
	
}

function communiquePOST(tableauArgsGET,tableauArgsPOST,callback){
	var xhr = createXHR();
	var chaineDAppel = serveur+"?";
	if (typeof(tableauArgsGET)=='string'){
		chaineDAppel = tableauArgsGET+(tableauArgsGET.indexOf("?")>=0?"&":"?")+"nocache=" + Math.random();
	}
	else {//tableau associatif
	for(var clef in tableauArgsGET)
		chaineDAppel+=clef+"="+tableauArgsGET[clef]+"&";
	chaineDAppel += "nocache=" + Math.random();
	}
	chaineEnvoyee = "";
	for(var clef in tableauArgsPOST)
		chaineEnvoyee+=clef+"="+encodeURIComponent(tableauArgsPOST[clef])+"&";
	chaineEnvoyee = chaineEnvoyee.substr(0,chaineEnvoyee.length-1);
	xhr.onreadystatechange  = function(){ 
		if(xhr.readyState  == 4){
			if(xhr.status  == 200) {
				leXML = xhr.responseXML;
				yaErreur = leXML.getElementsByTagName("erreur").length;
				if (yaErreur){
					document.getElementById("comm").innerHTML = 
						"Erreur : "+leXML.getElementsByTagName("erreur").item(0).getAttribute('raison')+
						" lors de "+leXML.getElementsByTagName("erreur").item(0).getAttribute('origine')+".";
				}
				else {
					document.getElementById("comm").innerHTML = "";
					if (callback)
						callback(xhr.responseXML,xhr.responseText);
				}
			} else {
				document.getElementById("comm").innerHTML = "La communication "+chaineDAppel+" n'a pas abouti.";
			}
         }
    }; 
	document.getElementById("comm").innerHTML = "Attente du serveur...";
	
	xhr.open("POST", chaineDAppel, true); 
	xhr.send(chaineEnvoyee);
	
}

function envoieFormulaire(tableauArgsGET,formulaire,callback){
	var liste = formulaire.elements;
	var tableauPOST = new Array();
	for (var clef in liste){
		var lInput = liste[clef];
		if (lInput.value)
			tableauPOST[lInput.name] = (lInput.value?lInput.value:lInput.checked);
		if (lInput.checked)
			tableauPOST[lInput.name] = "1";
	}
	communiquePOST(tableauArgsGET,tableauPOST,callback);
}

//autres fonctions de calcul r�p�titif
function entre(avant,puis,ensuite){//renvoie vrai si ils sont dans l'ordre
	return (avant<=puis) && (puis<=ensuite);
}

function mettreEntre(nombre,base){//fonction permettant le modulo
	while(nombre < 0)
		nombre+=base;
	while(nombre >= base)
		nombre-=base;
	return nombre;
}

function distN0(a,b){return Math.max(Math.abs(a),Math.abs(b))+(typeof multiplicateur == "undefined"?0.1:multiplicateur)*Math.min(Math.abs(b),Math.abs(b));}

function melanger(tableau){
	for (var i in tableau){
		var i2 = Math.floor(Math.random()*tableau.length);
		var temp = tableau[i];
		tableau[i] = tableau[i2];
		tableau[i2] = temp;
	}
}

//la classe de partie
function Partie(){
	
	this.nbJoueurs = 0;
	this.joueur = new Array();//tableau de Joueurs, commence par 1
	this.noTour = 0;
	this.joueurEnCours = 0;
	this.options = new Options();//objet Options
	this.tableauJeu = 0;//objet PlateauDeJeu
	this.demarree = false;
	this.gagnant = 0;
	this.histoire = false;//texte
	this.params = false;//reste un DOMNode <parametrescampagne>...</parametrescampagne>
	
	this.fromXML = function(Xpartie){
		if (typeof Xpartie != "object") return false;
		var partie = new Partie();
		partie.demarree = (Xpartie.getAttribute("demarree")=="1");
		partie.noTour = parseInt(Xpartie.getAttribute("notour"));
		partie.joueurEnCours = parseInt(Xpartie.getAttribute("joueurencours"));
		partie.gagnant = parseInt(Xpartie.getAttribute("gagnant"));
		partie.nbJoueurs = parseInt(Xpartie.getAttribute("nombredejoueurs"));
		var joueurs_array = Xpartie.getElementsByTagName( "joueur" );
		for( var i = 0; i< partie.nbJoueurs;i++)
			partie.joueur[parseInt(joueurs_array.item(i).getAttribute("numero"))] = (new Joueur()).fromXML(joueurs_array[i]);
		partie.options = Xpartie.getElementsByTagName( "options" );
		partie.options = (new Options()).fromXML(partie.options[0]);
		partie.tableauJeu = Xpartie.getElementsByTagName( "tableaudejeu" );
		partie.tableauJeu = (new PlateauDeJeu()).fromXML(partie.tableauJeu[0]);
		//alert(partie.tableauJeu);
		partie.tableauJeu.setPartie(partie);
		/*partie.commentaire = Xpartie.getElementsByTagName( "commentaire" );
		if (partie.commentaire.length>0) partie.commentaire = partie.commentaire[0];
		else partie.commentaire = false;*/
		//new lecteurXML(partie.commentaire);
		partie.histoire = Xpartie.getElementsByTagName( "histoire" );
		if (partie.histoire.length>0)
			if (partie.histoire[0].firstChild)
				partie.histoire = partie.histoire[0].firstChild.nodeValue;
			else 
				partie.histoire = partie.histoire[0].getAttribute("h");
		else partie.histoire = false;
		//new lecteurXML(partie.histoire);
		partie.params = Xpartie.getElementsByTagName( "parametrescampagne" );
		if (partie.params.length>0) partie.params = partie.params[0];
		else partie.params = false;
		//new lecteurXML(partie.params);
		return partie;
	}
	
	this.toXML = function (){
		var Xjeu = new lecteurXML();
		Xjeu.chargeChaine("<?xml version=\"1.0\" encoding=\"UTF-8\"?><partie></partie>");
		var Xpartie = Xjeu.doc.getElementsByTagName("partie");
		//alert(Xpartie.length);
		Xpartie = Xpartie[0];
		Xpartie.setAttribute("nombredejoueurs",this.nbJoueurs);
		Xpartie.setAttribute("demarree",(this.demarree?1:0));
		Xpartie.setAttribute("notour",this.noTour);
		Xpartie.setAttribute("gagnant",this.gagnant);
		Xpartie.setAttribute("joueurencours",this.joueurEnCours);
		
		var Xjoueurs = Xjeu.doc.createElement("joueurs");
		for (var n = 1;n <= this.nbJoueurs;n++){
			var Xjoueur = this.joueur[n].toXML(Xjeu.doc,n);
			Xjoueurs.appendChild(Xjoueur);
		}
		Xpartie.appendChild(Xjoueurs);
		Xpartie.appendChild(this.options.toXML(Xjeu.doc));
		Xpartie.appendChild(this.tableauJeu.toXML(Xjeu.doc));
		var Xcommentaire = Xjeu.doc.createElement("commentaire");
		var Xhistoire = Xjeu.doc.createElement("histoire");
		Xhistoire.setAttribute("h",this.histoire);
		Xcommentaire.appendChild(Xhistoire);
		var Xparams = Xjeu.doc.createElement("parametrescampagne");
		Xparams.setAttribute("c",this.params.getAttribute("c"));
		Xparams.setAttribute("m",this.params.getAttribute("m"));
		Xparams.setAttribute("titre",this.params.getAttribute("titre"));
		Xparams.setAttribute("infosucces",this.params.getAttribute("infosucces"));
		Xparams.setAttribute("suivante",this.params.getAttribute("suivante"));
		Xparams.setAttribute("deco",this.params.getAttribute("deco"));
		Xcommentaire.appendChild(Xparams);
		Xpartie.appendChild(Xcommentaire);
		return Xjeu;
	}
	
}

function Joueur(nom,couleur,mdp,type,niveau){
/*		var nom;
	var couleur;//var de couleur
	var mdp;
	var type;//0 : joueur humain 1 : ia (client : 2 : net)
	var niveau;//niveau IA : 0 jeu al�atoire, n meilleur coup profondeur n-1
	var derniereAction;*/
	
	mdp = ((typeof mdp=="undefined")?"0":mdp);
	type = ((typeof type=="undefined")?0:type);
	niveau = ((typeof niveau=="undefined")?"0":niveau);
	
	this.nom = nom;
	this.couleur = couleur;
	this.mdp = mdp;
	this.type = parseInt(type);
	this.niveau = parseInt(niveau);
	this.derniereAction = { "quoi": "n", "ouX": 0, "ouY": 0, "quand": 0 };
	
	this.isIA = function isIA(){
		return (this.type == 1);
	}
	
	this.setDerniereAction = function (Xaction){
		var quoi = 0;
		try{ quoi = Xaction.getAttribute("type"); 
			if (!quoi) quoi = Xaction.getAttribute("a"); 
		} catch (err){ quoi = Xaction.getAttribute("a"); 
		}
		var quand = 0;
		try{ quand = Xaction.getAttribute("notour"); 
			if (!quand) quand = Xaction.getAttribute("n"); 
		} catch (err){ quand = Xaction.getAttribute("n"); 
		}
		this.derniereAction = { "quoi": quoi,
							"ouX": parseInt(Xaction.getAttribute("x")),
							"ouY": parseInt(Xaction.getAttribute("y")),
							"quand": parseInt(quand)};
	}
	
	this.aPourDerniereAction = function (Xaction){//teste la derni�re action du joueur avec un noeud XML d'action
		var temp = this.derniereAction;
		this.setDerniereAction(Xaction);
		var aRenvoyer = false;
		if (temp.quoi == this.derniereAction.quoi && temp.ouX == this.derniereAction.ouX
				&& temp.ouY == this.derniereAction.ouY && temp.quand == this.derniereAction.quand){
			aRenvoyer = true;
		}
		this.derniereAction = temp;
		return aRenvoyer;
	}
	
	this.ouDerniereAction = function (){
		return {"x" : this.derniereAction["ouX"],"y" : this.derniereAction["ouY"]};
	}
		
	this.fromXML = function fromXML(Xjoueur){
		var joueur = new Joueur(Xjoueur.getAttribute("nom"),
							Xjoueur.getAttribute("couleur"),
							Xjoueur.getAttribute("mdp"),
							(Xjoueur.getAttribute("estia")=="oui"?1:
								(Xjoueur.getAttribute("estnet")=="oui"?2:0)),
							parseInt(Xjoueur.getAttribute("niveau")));
		
		joueur.derniereAction = Xjoueur.getElementsByTagName("derniereaction");
		joueur.setDerniereAction(joueur.derniereAction[0]);
		return joueur;
	}

	this.toXML = function (xml_partie,numero){
		var Xjoueur = xml_partie.createElement("joueur");
		/*		numero NMTOKEN #REQUIRED
		nom CDATA #REQUIRED
		couleur CDATA #REQUIRED
		estia (oui | non)  #REQUIRED
		niveau NMTOKEN  "0"
		estnet (oui | non) non
		mdp CDATA "0">*/
		Xjoueur.setAttribute("nom",this.nom);
		Xjoueur.setAttribute("numero",numero);
		Xjoueur.setAttribute("couleur",this.couleur);
		Xjoueur.setAttribute("estia",(this.type==1?"oui":"non"));
		Xjoueur.setAttribute("niveau",this.niveau);
		Xjoueur.setAttribute("estnet",(this.type==2?"oui":"non"));
		Xjoueur.setAttribute("mdp",this.mdp);
		var Xderniere = xml_partie.createElement("derniereaction");
		Xderniere.setAttribute("type",this.derniereAction.quoi);
		Xderniere.setAttribute("x",this.derniereAction.ouX);
		Xderniere.setAttribute("y",this.derniereAction.ouY);
		Xderniere.setAttribute("notour",this.derniereAction.quand);
		Xjoueur.appendChild(Xderniere);		
		return Xjoueur;
	}
}

function Options(chateauxPermis,profondeur,typeBord,ajoutDiag,explosionJoueur,augmentationMatiere){
	/*var chateauxPermis;//	Options : chateaux activ�s ? true/false
	var profondeur;	//Profondeur de jeu
	var typeBord;	//Bord bloqu�s ?	1/0/2:monde rond
	var ajoutDiag;	//Ajout diagonale ? true/false  (peut-on cliquer en diagonale ou seulement � c�t� ?)
	var explosionJoueur;//Explosion slt pour joueur en cours ? true/false*/
	
	this.chateauxPermis = ((typeof chateauPermis=="undefined")?0:(chateauxPermis?true:false));
	this.profondeur = ((typeof profondeur=="undefined")?100:profondeur);
	this.typeBord = ((typeof typeBord=="undefined")?1:typeBord);
	this.ajoutDiag = ((typeof ajoutDiag=="undefined")?1:(ajoutDiag?true:false));
	this.explosionJoueur = ((typeof explosionJoueur=="undefined")?0:(explosionJoueur?true:false));
	this.augmentationMatiere = ((typeof augmentationMatiere=="undefined")?true:(augmentationMatiere?true:false));

	this.setPermissionChateau = function (chateauxPermis){this.chateauxPermis = (chateauxPermis?true:false);}
	this.setProfondeur = function (profondeur){this.profondeur = profondeur;}
	this.setTypeBord = function (typeBord){this.typeBord = typeBord;}
	this.setPlacementDiag = function (ajoutDiag){this.ajoutDiag = (ajoutDiag?true:false);}
	this.setExplosionJoueur = function (explosionJoueur){this.explosionJoueur = (explosionJoueur?true:false);}
	this.setAugmentationMatiere = function (augmentationMatiere){this.augmentationMatiere = (augmentationMatiere?true:false);}

	this.yaPermissionChateau = function (){return this.chateauxPermis;}
	this.quelleProfondeur = function (){return this.profondeur;}
	this.quelTypeBord = function (){return this.typeBord;}
	this.yaPlacementDiag = function (){return this.ajoutDiag;}
	this.yaExplosionJoueur = function (){return this.explosionJoueur;}
	this.yaAugmentationMatiere = function (){return this.augmentationMatiere;}
	
	this.fromXML = function (Xoptions){
		options = new Options();

		options_array = Xoptions.getElementsByTagName( "option" );
		
		for (var i = 0; i<5;i++){//in options_array
			var Xoption = options_array[i];
			var valeur = parseInt(Xoption.getAttribute("valeur"));
			switch(Xoption.getAttribute("type")){
				case "chateaux_actifs": options.setPermissionChateau(valeur);
					break;
				case "profondeur_jeu": options.setProfondeur(valeur);
					break;
				case "type_bords": options.setTypeBord(valeur);
					break;
				case "ajout_diagonale": options.setPlacementDiag(valeur);
					break;
				case "explosion_joueur": options.setExplosionJoueur(valeur);
					break;
				case "augmentation_matiere": options.setAugmentationMatiere(valeur);
					break;
			}
		}
		return options;
	}
	this.toXML = function (xml_partie){
		var Xoptions = xml_partie.createElement("options");
		var Xoption = xml_partie.createElement("option");
		Xoption.setAttribute("type","chateaux_actifs");Xoption.setAttribute("valeur",(this.yaPermissionChateau()?1:0));
		Xoptions.appendChild(Xoption);
		Xoption = xml_partie.createElement("option");
		Xoption.setAttribute("type","profondeur_jeu");Xoption.setAttribute("valeur",this.quelleProfondeur());
		Xoptions.appendChild(Xoption);
		Xoption = xml_partie.createElement("option");
		Xoption.setAttribute("type","type_bords");Xoption.setAttribute("valeur",this.quelTypeBord());
		Xoptions.appendChild(Xoption);
		Xoption = xml_partie.createElement("option");
		Xoption.setAttribute("type","ajout_diagonale");Xoption.setAttribute("valeur",(this.yaPlacementDiag()?1:0));
		Xoptions.appendChild(Xoption);
		Xoption = xml_partie.createElement("option");
		Xoption.setAttribute("type","explosion_joueur");Xoption.setAttribute("valeur",(this.yaExplosionJoueur()?1:0));
		Xoptions.appendChild(Xoption);
		Xoption = xml_partie.createElement("option");
		Xoption.setAttribute("type","augmentation_matiere");Xoption.setAttribute("valeur",(this.yaAugmentationMatiere()?1:0));
		Xoptions.appendChild(Xoption);
		
		return Xoptions;
	}	
}

function Aafficher(nomDeThis){//cr�e une liste de plateaux � afficher
	var compteur = 0;
	var compteurAffichage = 0;
	var liste = new Array();
	var fonctionAppelee = null;//fonction appel�e s'il y a d'autres images � afficher apr�s
	var fonctionAppeleeFin = null;//fonction appel�e si c'est la fin des images disponibles
	var maxImages = 1000;
	
	var nomVar = nomDeThis;
	
	this.lance = false;
	
	this.ajouter = function(unTableau){
		liste[compteur++] = unTableau;
		if (compteur = maxImages)
			compteur = 0;
	}
	
	function estCeDerniere(){//regarde si c'est le dernier plateau � afficher
		if (compteur-1 == compteurAffichage) return true;//juste avant
		if (compteur == 0 && compteurAffichage==maxImages-1) return true;
		return false;
	}
		
	this.afficher = function afficher(){
		if (!fonctionAppelee || !fonctionAppeleeFin) return;
		if (compteur != compteurAffichage){
			if (estCeDerniere())
				fonctionAppeleeFin(liste[compteurAffichage]);
			else
				fonctionAppelee(liste[compteurAffichage]);
			liste[compteurAffichage] = null;
			compteurAffichage++;
			if (compteurAffichage = maxImages)
				compteurAffichage = 0;
		}
	}
	
	this.lancer = function (tempsEntre,fonctionAppelee2,fonctionAppeleeFin2){//lance 
		fonctionAppelee = fonctionAppelee2;
		fonctionAppeleeFin = fonctionAppeleeFin2;
		if (this.lance) return;
		window.setInterval(nomVar+".afficher()",tempsEntre);
		this.lance = true;
	}
	
}

var tableauAAfficher = new Aafficher("tableauAAfficher");

//classe de plateau de jeu
function PlateauDeJeu() {
	this.plateau = new Array();//tableau bi dim de UneCase
	this.tailleX = 0;
	this.tailleY = 0;
	this.partie = new Object();
		this.partie.options = new Options();

	var tableauPlein = true;
	switch( arguments.length){
		case 1:
		  if ('object' == typeof (arguments[0])){//c'est un objet similaire � copier
			ancienPlateau = arguments[0];
			this.tailleX = ancienPlateau.tailleX;
			this.tailleY = ancienPlateau.tailleY;
			this.partie = ancienPlateau.partie;
			for(var i = 0; i < this.tailleY; i++){
				this.plateau[i] = new Array();
				for(var j = 0; j < this.tailleX; j++)
					this.plateau[i][j] = ancienPlateau.plateau[i][j].copie();
			}
		  }
			break;
		case 3://param�tre optionnel sp�cifiant si on construit les cases d�j� (tableau non vide ?)
			tableauPlein = arguments[2];
		case 2://juste les param�tres tailleX et tailleY
			this.tailleX = arguments[0];
			this.tailleY = arguments[1];
			for(var i = 0; i < this.tailleY; i++){
				this.plateau[i] = new Array();
				if (tableauPlein) for(var j = 0; j < this.tailleX; j++)
					this.plateau[i][j] = new UneCase();
			}
			break;
	}
	
	this.copie = function (){//cr�e une copie du plateau
		var leNouveau = new PlateauDeJeu(this.tailleX, this.tailleY, false);
		leNouveau.partie = this.partie;
		for(var i = 0; i < this.tailleY; i++)
			for(var j = 0; j < this.tailleX; j++)
				leNouveau.plateau[i][j] = this.plateau[i][j].copie();
		return leNouveau;
	}
	this.setPartie = function (partie){
		this.partie = partie;
		//alert(this.plateau);
		for(var i = 0; i < this.tailleY; i++)
			for(var j = 0; j < this.tailleX; j++){
				this.getCase(j,i).partie = partie;
			}
	}
	
	this.nouveau = function (taillex,tailley){//cr�e un nouveau plateau
		this.tailleX = ((typeof taillex=="undefined")?this.tailleX:taillex);
		this.tailleY = ((typeof tailley=="undefined")?this.tailleY:tailley);
		var ancienPlateau = this.plateau;
		this.plateau = new Array();
		for(var y = 0; y < this.tailleY; y++){
			this.plateau[y] = new Array();
			for (var x = 0; x < this.tailleX;x++){
				if (typeof ancienPlateau[y] != "undefined") if (typeof ancienPlateau[y][x] != "undefined"){
					this.plateau[y][x] = ancienPlateau[y][x];continue;
				}
				this.plateau[y][x] = new UneCase();
			}
		}
	}
	
	this.metsLesMax = function (){//en fonction du plateau et des options
		for(var i=0;i<this.tailleY;i++) for(var j=0;j<this.tailleX;j++){
			var k = 4;
			if (this.partie.options.quelTypeBord() == 1){//on compte les bords
					if (i==0 || i==this.tailleY-1) k--;
					if (j==0 || j==this.tailleX-1) k--;
			}
			for (var ii=-1;ii<2;ii++) for(var jj=-1;jj<2;jj++)//on regarde les obstacles
					if (Math.abs(ii)+Math.abs(jj)==1){//pas diagonale
						switch(this.partie.options.quelTypeBord()){
						case 1: //on regarde pas apr�s les bords
						case 0:
							if (entre(0,ii+i,this.tailleY-1)&&entre(0,jj+j,this.tailleX-1))
								if (this.getCase(j+jj,i+ii).getDecor()==3) k--;
							break;
						case 2://on regarde apr�s le bord
							if (this.getCase(mettreEntre(j+jj,this.tailleX),mettreEntre(i+ii,this.tailleY)).getDecor()==3) k--;
							break;
						}
					}
			this.getCase(j,i).setMax(k);
		}
	}

	this.getCase = function (x, y){
		if (this.partie.options.quelTypeBord() == 2) return this.plateau[mettreEntre(y,this.tailleY)][mettreEntre(x,this.tailleX)];
		if (x>=0 && x<this.tailleX && y>=0 && y<this.tailleY) return this.plateau[y][x];
		else return false;
	}
	
	this.distance = function (x,y,x2,y2){//renvoie un flottant
		if (typeof x2 == "undefined") x2 = -1;
		if (typeof y2 == "undefined") y2 = -1;
		if (x2>=0 && y2>=0){//entre 2 cases
			var dx=x2-x; var dy=y2-y;
			switch(this.partie.options.quelTypeBord()){
				case 0:case 1:
					if (this.partie.options.yaPlacementDiag())
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
		}
		else {//entre la case et les joueurs existants
			var posJoueurs = new Array();
			for(var j = 0;j < this.tailleY;j++) for(var i = 0;i < this.tailleX;i++){
				//recherche des positions des joueurs
				if (c = this.getCase(i,j))
					if (jou = c.getJoueur())
						posJoueurs[jou] = new Array(i,j);
			}
			var d = this.tailleX + this.tailleY;
			for(var i in posJoueurs){
				var pos = posJoueurs[i];
				d = Math.min(d,this.distance(options,x,y,pos[0],pos[1]));
			}
			return d;
		}
	}
	this.caseLaPlusEloigneeDe = function (x,y,joueur){//renvoie le array(x,y) de la case du joueur la plus �loign�e
		//entre ex-aequo, on prend la case du milieu, dans un parcours en y puis x
		var distanceMax = -1;
		var tabReponses = null;
		for (var i=0;i<this.tailleY;i++) for (var j=0;j<this.tailleX;j++){
			var cetteCase = this.getCase(j,i);
			if (cetteCase.getJoueur() == joueur && cetteCase.getCellules() > 0){
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
	}
	
	this.purifie = function (joueurEnCours){//en fonction des options, 1 it�ration
		var changement=false;
		var ouGlaceExplosion = new Array();//var indiceGlace=0;//pr�paration des endroits glac�s
		var differences = new Array(); //pr�paration du traitement des explosions
		var conquetes = new Array();
		for(var y=0;y<this.tailleY;y++){
			differences[y] = new Array();
			conquetes[y] = new Array();
			for(var x=0;x<this.tailleX;x++){
				differences[y][x] = 0;
				conquetes[y][x] = new Array();
			}
		}
		var numtour = this.partie.noTour+"-"+this.partie.joueurEnCours;
		//parcours du plateau pour traiter les explosions
		for(var x=0;x<this.tailleX;x++) for(var y=0;y<this.tailleY;y++){
			var cetteCase = this.getCase(x,y);
			if ((this.partie.options.yaExplosionJoueur() && cetteCase.getJoueur()==joueurEnCours) || !this.partie.options.yaExplosionJoueur())
			if (cetteCase.vaExploser() && !cetteCase.getChateau()){//explosion !
				changement = true;			//va sur les cases d'� c�t�
				for(var ii=-1;ii<2;ii++) for(var jj=-1;jj<2;jj++) if (Math.abs(ii)+Math.abs(jj)==1){//pas diagonale
					var nvx = x+jj; var nvy = y+ii;
					var perteBord = false;
					switch(this.partie.options.quelTypeBord()){
					  case 2: //on regarde apr�s les bords
						nvx = mettreEntre(x+jj,this.tailleX); nvy = mettreEntre(y+ii,this.tailleY);
					  case 0: perteBord=true; //on ne regarde pas au bord mais on perd une cellule
					  case 1: //on regarde pas apr�s les bords
						if (entre(0,nvy,this.tailleY-1) && entre(0,nvx,this.tailleX-1)){
							var autreCase=this.getCase(nvx,nvy);
							if (!this.partie.options.yaExplosionJoueur() && !autreCase.getChateau() && !cetteCase.getChateau() && autreCase.vaExploser() && autreCase.getJoueur() != cetteCase.getJoueur()){//l'autre case explose de m�me, et autre joueur : on ne traverse pas, on garde la cellule
								true;
							} else
							switch(autreCase.getDecor()){
							case 0: //case normale
								differences[y][x]--;
								if (autreCase.getChateau()&&(cetteCase.getJoueur()!=autreCase.getJoueur())&&autreCase.getCellules()>=10){//traitement si membrane adverse protg�e
									differences[nvy][nvx]--;
								} else {//jeu normal ou destruction de la membrane et conqu�te des cellules
									//il va y avoir un bug si attaque et d�fense en m�me temps d'un chateau
									//on va dire que les attaquants ont toujours priorit�... C'est un jeu !
									differences[nvy][nvx]++;
									if (autreCase.getChateau()&&(cetteCase.getJoueur()!=autreCase.getJoueur())&&autreCase.getCellules()<10)
										autreCase.setChateau(false);
									conquetes[nvy][nvx][conquetes[nvy][nvx].length]=cetteCase.getJoueur();
								}
								break;
							case 1: //glace
								differences[y][x]--;
								if (!ouGlaceExplosion[nvy+" "+nvx] && autreCase.numTourUtilisee != numtour){//1ere fois
									ouGlaceExplosion[nvy+" "+nvx] = 1;
									autreCase.numTourUtilisee = numtour;
								} else {//fois apr�s
									//il va y avoir un BUG si 2 personnes tentent de conqu�rir une case de glace
									//c'est � cause du vent, il souffle pour favoriser les joueurs x plus grands puis y
									differences[nvy][nvx]++;
								}
								conquetes[nvy][nvx][conquetes[nvy][nvx].length]=cetteCase.getJoueur();
								break;
							case 2: //point chaud
								differences[y][x]--;
								differences[nvy][nvx] = (autreCase.numTourUtilisee != numtour?2:1);
								autreCase.numTourUtilisee = numtour;
								conquetes[nvy][nvx][conquetes[nvy][nvx].length]=cetteCase.getJoueur();
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
		//post traitement des explosions
		for(var x=0;x<this.tailleX;x++) for(var y=0;y<this.tailleY;y++){
			cetteCase = this.getCase(x,y);
			nbcellules = cetteCase.getCellules();
			cetteCase.addCellules(differences[y][x]);
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
				var gagnant = mettreEntre(x+this.tailleX*y, lesGagnants.length);
				cetteCase.setJoueur(lesGagnants[gagnant]);
			} else if (conquetes[y][x].length == 1){
				cetteCase.setJoueur(conquetes[y][x][0]);
			}
		}
		return changement;
	}
	this.purifieTotalement = function (afficher,joueurEnCours,profondeur){
		if (typeof profondeur == "undefined") profondeur=0;
		if (profondeur>=this.partie.options.quelleProfondeur()){
			this.RAZ();
			return true;
		} else {
			changements = this.purifie(joueurEnCours);
			if (afficher) tableauAAfficher.ajouter(this.copie());
			profondeur++;
			if (changements)//on arr�te s'il y a pas de changements
				this.purifieTotalement(afficher,joueurEnCours,profondeur);
			else
				this.purifieTotalement(afficher,joueurEnCours,this.partie.options.quelleProfondeur());
		}
	}
	this.clicNormal = function (x,y,joueurEnCours,chateau){//ajoute une cellule
		if (typeof chateau == "undefined") chateau=false;
		laCase = this.getCase(x,y);
		//if (!laCase) var_dump(this);
		if (!this.partie.options.yaAugmentationMatiere()){
			var temp = this.caseLaPlusEloigneeDe(x,y,joueurEnCours);
			x2 = temp[0]; y2 = temp[1];
			this.getCase(x2,y2).remCellules(1);
		}
		laCase.setJoueur(joueurEnCours);
		laCase.decorAjouter1Cellule();
		//laCase.addCellules(laCase.getDecor()==2?2:1);
		//laCase.numTourUtilisee = thisnumTour."-".joueurEnCours;
		if (chateau) laCase.clicChateau();
	}
	this.clicChateau = function (x,y,joueurEnCours){return this.clicNormal(x,y,joueurEnCours,true);}
	this.peutJouerEn = function (x,y,joueurAppelant,chateau){
		if (typeof chateau == "undefined") chateau=false;
		var laCase = this.getCase(x,y);
		if (laCase.getDecor() != 0 && chateau)
			return false; // chateau et case instable
		if (laCase.getJoueur() != joueurAppelant && laCase.getCellules() > 0)
			return false; //case d�j� control�e par joueur adverse
		if (laCase.getJoueur() == joueurAppelant && laCase.getCellules() > 0)
		//case control�e par ce joueur
			if (laCase.getDecor() == 1 && laCase.getCellules() >= laCase.getMax() - 1)
				return false; // mais glace et limite atteinte
			else
				return true; // pas de probl�me
		if (laCase.getDecor() == 1 && (laCase.getJoueur() != joueurAppelant || laCase.getCellules()==0))
			return false; // glace et case non control�e
		if (laCase.getDecor() == 3)//obstacle
			return false;
		for(var i=-1;i<2;i++) for(var j=-1;j<2;j++){//on va regarder si une case autour appartient au joueur
			if (i==0 && j==0) continue; // on a d�j� test� la case centrale
			if (!this.partie.options.yaPlacementDiag() && Math.abs(i)+Math.abs(j)==2) continue;//pas en diagonale
			var nvx = x+i; var nvy = y+j;
			if (this.partie.options.quelTypeBord() != 2 && (!entre(0,nvx,this.tailleX-1) || !entre(0,nvy,this.tailleY-1)))
				continue;//apr�s le bord
			nvx = mettreEntre(nvx,this.tailleX);nvy = mettreEntre(nvy,this.tailleY);//au cas o� le monde est rond
			autreCase = this.getCase(nvx,nvy);
			if (autreCase.getJoueur() == joueurAppelant && autreCase.getCellules() > 0)
				return true; //case control�e par ce joueur
		}
		return false;
	}
	this.ouPeutJouer = function (joueurAppelant,chateau){//renvoie un tableau des positions jouables
		if (typeof chateau == "undefined") chateau=false;
		var positions = new Array();
		for (var x=0;x<this.tailleX;x++)
			for (var y=0;y<this.tailleY;y++)
				if (this.peutJouerEn(x,y,joueurAppelant,chateau))
					positions[positions.length] = new Array(x,y);
		return positions;
	}
	
	this.peutJouer = function (joueurAppelant){//v�rifie si le joueur appelant peut jouer
		return ouPeutJouer(joueurAppelant).length > 0;
	}
	
	this.yaGagnant = function (){//regarde s'il n'y a qu'un type de joueur sur la carte
		var gagnant = 0;
		for (var x=0;x<this.tailleX;x++) for (var y=0;y<this.tailleY;y++){
			var j = this.getCase(x,y).getJoueur();
			var c = this.getCase(x,y).getCellules();
			if (c>0 && j>0) {
				if (gagnant != 0 && j != gagnant)
					return false;
				else 
					gagnant = j;
			}
		}
		return j;
	}
	this.RAZ = function (){//remet � z�ro les cellules, pr�ts � une nouvelle purification
		for (var x=0;x<this.tailleX;x++) for(var y=0;y<this.tailleY;y++) this.getCase(x,y).RAZ();
	}
	
	this.fromXML = function (Xplateau){
		var lePlateau = new PlateauDeJeu(parseInt(Xplateau.getAttribute("taillex")),
								parseInt(Xplateau.getAttribute("tailley")),
								false);//pour que les cases ne soient pas initialis�es
		//alert(typeof lePlateau.tailleX+","+typeof lePlateau.tailleY)
		var lignes_array = Xplateau.getElementsByTagName( "ligne" );
		for(var i in lignes_array){// =0; i<this.tailleY;i++
			Xligne = lignes_array[i];
			if (typeof Xligne != "object") continue;
			var y = parseInt(Xligne.getAttribute("y"));
			cases_array = Xligne.getElementsByTagName( "case" );
			for(var j in cases_array){ //= 0;j< this.tailleX;j++
				var Xcase = cases_array[j];
				if (typeof Xcase != "object") continue;
				var x = parseInt(Xcase.getAttribute("x"));
				var y2 = parseInt(Xcase.getAttribute("y"));
				var laCase = (new UneCase()).fromXML(Xcase);
				//alert(x+","+y+","+y2+":"+laCase.toInt());
				lePlateau.plateau[y][x] = laCase;
			}
		}
		//alert(lePlateau.plateau[0][0].toInt());
		return lePlateau;
	}
	
	this.toXML = function (xml_partie){//renvoie un DOMNode
		var Xtableau = xml_partie.createElement("tableaudejeu");
		Xtableau.setAttribute("taillex", this.tailleX);
		Xtableau.setAttribute("tailley", this.tailleY);

		for(var i=0;i<this.tailleY;i++){
			var Xligne = xml_partie.createElement("ligne");
			Xligne.setAttribute("y", i);
			for(var j=0;j<this.tailleX;j++){
				var Xcase = this.getCase(j,i).toXML(xml_partie,j,i);
				Xligne.appendChild(Xcase);
			}
			Xtableau.appendChild(Xligne);
		}
		return Xtableau;
	}
}

//classe de Cases
function UneCase(){
/*	var joueur;//� qui appartient la case
	var nbcellules;//combien de cellules sont sur la case
	var chateau;//y a t il un chateau ? 
	var max;//maximum de cellules sur la cas
	var decor;//0 rien, 1 glace, 2 chaud, 3 obstacle
	var numTourUtilisee;*/
	
	//la construction
	this.numTourUtilisee = "0-0";
	this.utilisee = false;
	arguments[0] = (arguments[0]?arguments[0]:0);
	var decor = arguments[0]; cEstObjet = ('object' == typeof decor);
	switch(arguments.length){
		case 1:
		case 0:
			if (!cEstObjet){
				this.joueur = 0;
				this.nbcellules = 0;
				this.chateau = false;
				this.max = 4;
				this.decor = decor;
			} else if (cEstObjet){ //copie d'une case existante
				this.joueur = decor.joueur;
				this.nbcellules = decor.nbcellules;
				this.chateau = decor.chateau;
				this.max = decor.max;
				this.decor = decor.decor;
				this.numTourUtilisee = decor.numTourUtilisee;
				this.utilisee = decor.utilisee;
				this.partie = decor.partie;
			}
			break;
		case 5: //joueur, cellules, chateau?, max, decor
			this.joueur = arguments[0];
			this.nbcellules = arguments[1];
			this.chateau = (arguments[2]?true:false);
			this.max = arguments[3];
			this.decor = arguments[4];
	}
	
	this.copie = function copie(){//copie une cellule
		uneCase = new UneCase();
		uneCase.setJoueur(this.getJoueur());
		uneCase.numTourUtilisee = this.numTourUtilisee;
		uneCase.utilisee = this.utilisee;
		uneCase.partie = this.partie;
		uneCase.setCellules(this.getCellules());
		uneCase.setChateau(this.getChateau());
		uneCase.setMax(this.getMax());
		uneCase.setDecor(this.getDecor());
		return uneCase;
	}
	
	if (!this.partie){//on cr�e une partie bidon
		this.partie=new Object();
		this.partie.noTour = 1;
		this.partie.joueurEnCours = 1;
	}
	this.decorAjouter1Cellule = function decorAjouter1Cellule(){//en fonction du d�cor lors du jeu d'un joueur
		var nb = 1;
		switch(this.decor){
			case 3: return false;//pas le droit de jouer l�
			case 1: if (this.getCellules() == 0 || this.presquePreteAExploser()) return false;//pas le droit non plus
			case 2: if (!this.utilisee && this.numTourUtilisee != this.partie.noTour+"-"+this.partie.joueurEnCours){
				//this.numTourUtilisee = this.partie.noTour+"-"+this.partie.joueurEnCours;
				this.utilisee = true;
				if (this.decor == 2) this.addCellules(1);
				nb--;
			}
			case 0:
				this.addCellules(nb);
		}
		return true;
	}
	this.decorAjouterCellules2 = function decorAjouterCellules2(nb){//en fonction du d�cor lors de la purification
		switch(this.decor){
			case 1:case 2: if (!this.utilisee && this.numTourUtilisee != this.partie.noTour+"-"+this.partie.joueurEnCours){
				this.numTourUtilisee = this.partie.noTour+"-"+this.partie.joueurEnCours;
				this.utilisee = true;
				if (this.decor == 2) this.addCellules(2);
				nb--;
			}
			case 0:
				this.addCellules(nb);
		}
	}
	this.RAZ = function RAZ(){this.utilisee = false;}
	
	this.setJoueur = function setJoueur(joueur){this.joueur = joueur;}
	this.getJoueur = function getJoueur(){return this.joueur;}
	this.placeJoueur = function placeJoueur(joueur){//place un joueur au d�but du jeu
		this.setDecor(0);
		this.setJoueur(joueur);
		this.setCellules(1);
	}
	
	
	this.getCellules = function getCellules(){return this.nbcellules;}
	this.setCellules = function setCellules(nb){this.nbcellules = nb;}
	this.addCellules = function addCellules(nb){this.nbcellules += nb;this.checkCellule();}
	this.remCellules = function remCellules(nb){this.nbcellules -= nb;	this.checkCellule();}
	this.checkCellule = function checkCellule(){this.nbcellules = Math.min(Math.max(0,this.nbcellules),99);}
	
	this.getChateau = function getChateau(){return this.chateau;}
	this.clicChateau = function clicChateau(){this.chateau = !this.chateau;}
	this.setChateau = function setChateau(mettreChateau){this.chateau = (mettreChateau?true:false);}
	
	this.getMax = function getMax(){return this.max;}
	this.setMax = function setMax(leMax){this.max = leMax;}
	this.vaExploser = function vaExploser(){return (this.nbcellules >= this.max);}
	this.preteAExploser = function preteAExploser(){return (this.nbcellules >= this.max-(this.decor==2?2:1));}
	this.presquePreteAExploser = function presquePreteAExploser(){return (this.nbcellules >= this.max-(this.decor==2?2:1)-1);}
	
	this.getDecor = function getDecor(){return this.decor;}
	this.setDecor = function setDecor(decor){if (decor!=3 || this.nbcellules==0) this.decor = decor;}
	
	this.toInt = function toInt(){return (this.getChateau()?10000:0)+this.getJoueur()*100+this.getCellules();}
	
	this.fromXML = function fromXML(Xcase){
		//joueur, cellules, chateau?, max, decor
		laCase = new UneCase(parseInt(Xcase.getAttribute("joueur")),
								parseInt(Xcase.getAttribute("cellules")),
								parseInt(Xcase.getAttribute("chateau")),
								parseInt(Xcase.getAttribute("max")),
								parseInt(Xcase.getAttribute("decor"))
							);
		return laCase;
	}
	
	this.toXML = function toXML(xml_partie,x,y){//renvoie un DOMNode
		Xcase = xml_partie.createElement("case");
		Xcase.setAttribute("x", x);
		Xcase.setAttribute("y", y);
		Xcase.setAttribute("decor", this.getDecor());
		Xcase.setAttribute("joueur", this.getJoueur());
		Xcase.setAttribute("cellules", this.getCellules());
		Xcase.setAttribute("max", this.getMax());
		Xcase.setAttribute("chateau", this.getChateau()?1:0);
		return Xcase;
	}

}