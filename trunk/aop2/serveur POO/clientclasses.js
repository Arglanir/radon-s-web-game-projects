var serveur = "serveur.php";

//arguments de l'appel de la page
var bouh = location.search.substring(1,location.search.length).split(unescape("%26"));//on enlève le ? et on sépare avec les &
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
function communiqueGET(tableauArgs,callback){
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
				yaErreur = leXML.getElementsByTagName("erreur").length;
				if (yaErreur){
					document.getElementById("comm").innerHTML = 
						"Erreur : "+leXML.getElementsByTagName("erreur").item(0).getAttribute('raison')+
						" lors de "+leXML.getElementsByTagName("erreur").item(0).getAttribute('origine')+".";
				}
				else {
					document.getElementById("comm").innerHTML = "";
					if (callback)
						callback(xhr.responseXML);
				}
			} else {
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