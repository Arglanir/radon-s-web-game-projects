var serveur = "jeu.php";

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
function communique(tableauArgs,callback){
	var xhr = createXHR();
	var chaineDAppel = serveur+"?";
	if (typeof(tableauArgs)=='string'){
		chaineDAppel = tableauArgs+"?nocache=" + Math.random();
	}
	else {//tableau associatif
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


function Partie(){
	
}