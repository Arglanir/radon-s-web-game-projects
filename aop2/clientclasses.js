var serveur = "serveur.php";

//arguments de l'appel de la page
var bouh = location.search.substring(1,location.search.length).split(unescape("%26"));//on enlËve le ? et on sÈpare avec les &
var tableauArguments = new Array();
tableauArguments["offline"] = 0;
for (i=0;i<bouh.length;i++){
   var temp = bouh[i].split("=");
    tableauArguments[temp[0]]=unescape(temp[1]);
}

function createXHR() 
{
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
			request = false;
		}
            }
        }
    return request;
}

function HTMLentities(texte) {
	texte = texte.replace(/"/g,'&quot;'); // 34 22
	texte = texte.replace(/&/g,'&amp;'); // 38 26
	texte = texte.replace(/\'/g,'&#39;'); // 39 27
	texte = texte.replace(/</g,'&lt;'); // 60 3C
	texte = texte.replace(/>/g,'&gt;'); // 62 3E
	texte = texte.replace(/\^/g,'&circ;'); // 94 5E
	texte = texte.replace(/ë/g,'&lsquo;'); // 145 91
	texte = texte.replace(/í/g,'&rsquo;'); // 146 92
	texte = texte.replace(/ì/g,'&ldquo;'); // 147 93
	texte = texte.replace(/î/g,'&rdquo;'); // 148 94
	texte = texte.replace(/ï/g,'&bull;'); // 149 95
	texte = texte.replace(/ñ/g,'&ndash;'); // 150 96
	texte = texte.replace(/ó/g,'&mdash;'); // 151 97
	texte = texte.replace(/ò/g,'&tilde;'); // 152 98
	texte = texte.replace(/ô/g,'&trade;'); // 153 99
	texte = texte.replace(/ö/g,'&scaron;'); // 154 9A
	texte = texte.replace(/õ/g,'&rsaquo;'); // 155 9B
	texte = texte.replace(/ú/g,'&oelig;'); // 156 9C
	texte = texte.replace(/ù/g,'&#357;'); // 157 9D
	texte = texte.replace(/û/g,'&#382;'); // 158 9E
	texte = texte.replace(/ü/g,'&Yuml;'); // 159 9F
	// texte = texte.replace(/ /g,'&nbsp;'); // 160 A0
	texte = texte.replace(/°/g,'&iexcl;'); // 161 A1
	texte = texte.replace(/¢/g,'&cent;'); // 162 A2
	texte = texte.replace(/£/g,'&pound;'); // 163 A3
	//texte = texte.replace(/ /g,'&curren;'); // 164 A4
	texte = texte.replace(/•/g,'&yen;'); // 165 A5
	texte = texte.replace(/¶/g,'&brvbar;'); // 166 A6
	texte = texte.replace(/ß/g,'&sect;'); // 167 A7
	texte = texte.replace(/®/g,'&uml;'); // 168 A8
	texte = texte.replace(/©/g,'&copy;'); // 169 A9
	texte = texte.replace(/™/g,'&ordf;'); // 170 AA
	texte = texte.replace(/´/g,'&laquo;'); // 171 AB
	texte = texte.replace(/¨/g,'&not;'); // 172 AC
	texte = texte.replace(/ /g,'&shy;'); // 173 AD
	texte = texte.replace(/Æ/g,'&reg;'); // 174 AE
	texte = texte.replace(/Ø/g,'&macr;'); // 175 AF
	texte = texte.replace(/∞/g,'&deg;'); // 176 B0
	texte = texte.replace(/±/g,'&plusmn;'); // 177 B1
	texte = texte.replace(/≤/g,'&sup2;'); // 178 B2
	texte = texte.replace(/≥/g,'&sup3;'); // 179 B3
	texte = texte.replace(/¥/g,'&acute;'); // 180 B4
	texte = texte.replace(/µ/g,'&micro;'); // 181 B5
	texte = texte.replace(/∂/g,'&para'); // 182 B6
	texte = texte.replace(/∑/g,'&middot;'); // 183 B7
	texte = texte.replace(/∏/g,'&cedil;'); // 184 B8
	texte = texte.replace(/π/g,'&sup1;'); // 185 B9
	texte = texte.replace(/∫/g,'&ordm;'); // 186 BA
	texte = texte.replace(/ª/g,'&raquo;'); // 187 BB
	texte = texte.replace(/º/g,'&frac14;'); // 188 BC
	texte = texte.replace(/Ω/g,'&frac12;'); // 189 BD
	texte = texte.replace(/æ/g,'&frac34;'); // 190 BE
	texte = texte.replace(/ø/g,'&iquest;'); // 191 BF
	texte = texte.replace(/¿/g,'&Agrave;'); // 192 C0
	texte = texte.replace(/¡/g,'&Aacute;'); // 193 C1
	texte = texte.replace(/¬/g,'&Acirc;'); // 194 C2
	texte = texte.replace(/√/g,'&Atilde;'); // 195 C3
	texte = texte.replace(/ƒ/g,'&Auml;'); // 196 C4
	texte = texte.replace(/≈/g,'&Aring;'); // 197 C5
	texte = texte.replace(/∆/g,'&AElig;'); // 198 C6
	texte = texte.replace(/«/g,'&Ccedil;'); // 199 C7
	texte = texte.replace(/»/g,'&Egrave;'); // 200 C8
	texte = texte.replace(/…/g,'&Eacute;'); // 201 C9
	texte = texte.replace(/ /g,'&Ecirc;'); // 202 CA
	texte = texte.replace(/À/g,'&Euml;'); // 203 CB
	texte = texte.replace(/Ã/g,'&Igrave;'); // 204 CC
	texte = texte.replace(/Õ/g,'&Iacute;'); // 205 CD
	texte = texte.replace(/Œ/g,'&Icirc;'); // 206 CE
	texte = texte.replace(/œ/g,'&Iuml;'); // 207 CF
	texte = texte.replace(/–/g,'&ETH;'); // 208 D0
	texte = texte.replace(/—/g,'&Ntilde;'); // 209 D1
	texte = texte.replace(/“/g,'&Ograve;'); // 210 D2
	texte = texte.replace(/”/g,'&Oacute;'); // 211 D3
	texte = texte.replace(/‘/g,'&Ocirc;'); // 212 D4
	texte = texte.replace(/’/g,'&Otilde;'); // 213 D5
	texte = texte.replace(/÷/g,'&Ouml;'); // 214 D6
	texte = texte.replace(/◊/g,'&times;'); // 215 D7
	texte = texte.replace(/ÿ/g,'&Oslash;'); // 216 D8
	texte = texte.replace(/Ÿ/g,'&Ugrave;'); // 217 D9
	texte = texte.replace(/⁄/g,'&Uacute;'); // 218 DA
	texte = texte.replace(/€/g,'&Ucirc;'); // 219 DB
	texte = texte.replace(/‹/g,'&Uuml;'); // 220 DC
	texte = texte.replace(/›/g,'&Yacute;'); // 221 DD
	texte = texte.replace(/ﬁ/g,'&THORN;'); // 222 DE
	texte = texte.replace(/ﬂ/g,'&szlig;'); // 223 DF
	texte = texte.replace(/‡/g,'&aacute;'); // 224 E0
	texte = texte.replace(/·/g,'&aacute;'); // 225 E1
	texte = texte.replace(/‚/g,'&acirc;'); // 226 E2
	texte = texte.replace(/„/g,'&atilde;'); // 227 E3
	texte = texte.replace(/‰/g,'&auml;'); // 228 E4
	texte = texte.replace(/Â/g,'&aring;'); // 229 E5
	texte = texte.replace(/Ê/g,'&aelig;'); // 230 E6
	texte = texte.replace(/Á/g,'&ccedil;'); // 231 E7
	texte = texte.replace(/Ë/g,'&egrave;'); // 232 E8
	texte = texte.replace(/È/g,'&eacute;'); // 233 E9
	texte = texte.replace(/Í/g,'&ecirc;'); // 234 EA
	texte = texte.replace(/Î/g,'&euml;'); // 235 EB
	texte = texte.replace(/Ï/g,'&igrave;'); // 236 EC
	texte = texte.replace(/Ì/g,'&iacute;'); // 237 ED
	texte = texte.replace(/Ó/g,'&icirc;'); // 238 EE
	texte = texte.replace(/Ô/g,'&iuml;'); // 239 EF
	texte = texte.replace(//g,'&eth;'); // 240 F0
	texte = texte.replace(/Ò/g,'&ntilde;'); // 241 F1
	texte = texte.replace(/Ú/g,'&ograve;'); // 242 F2
	texte = texte.replace(/Û/g,'&oacute;'); // 243 F3
	texte = texte.replace(/Ù/g,'&ocirc;'); // 244 F4
	texte = texte.replace(/ı/g,'&otilde;'); // 245 F5
	texte = texte.replace(/ˆ/g,'&ouml;'); // 246 F6
	texte = texte.replace(/˜/g,'&divide;'); // 247 F7
	texte = texte.replace(/¯/g,'&oslash;'); // 248 F8
	texte = texte.replace(/˘/g,'&ugrave;'); // 249 F9
	texte = texte.replace(/˙/g,'&uacute;'); // 250 FA
	texte = texte.replace(/˚/g,'&ucirc;'); // 251 FB
	texte = texte.replace(/¸/g,'&uuml;'); // 252 FC
	texte = texte.replace(/˝/g,'&yacute;'); // 253 FD
	texte = texte.replace(/˛/g,'&thorn;'); // 254 FE
	texte = texte.replace(/ˇ/g,'&yuml;'); // 255 FF
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
					else
						document.getElementById("comm").innerHTML = "Erreur lors du chargement du XML";
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

function Partie(){
	
}