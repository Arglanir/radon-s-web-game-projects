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


function envoyerEtRecevoir(parametres, url, methode){ //paramètres est un tableau ou une chaine
	var xhr = createXHR();
	var message = "";
	xhr.onreadystatechange  = function(){ //la fonction pour recevoir le texte
         if(xhr.readyState  == 4)
         {
              if(xhr.status  == 200) 
                 message="Received:"  + xhr.responseText; 
              else 
                 message="Error code " + xhr.status;
         }
    }; 
	xhr.open((methode=="POST"?"POST":"GET"), url, true); //connexion
	if (methode=="POST")
		xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	if(isString(parametres)) {//envoi des informations
		xhr.send(parametres);
	} else {
		var param="";
		for ( i in parametres) {
			param=i+"="+parametres[i]+"&";
		}
		param = param.substr(0,param.length-1);
		xhr.send(param);
	}
	return message;
}

function envoyer(parametres, url, methode){ //paramètres est un tableau ou une chaine
	var xhr = createXHR();
	var message = "";
	xhr.onreadystatechange  = function(){ //la fonction pour recevoir le texte
         if(xhr.readyState  == 4)
         {
              if(xhr.status  == 200) 
                 message="Received:"  + xhr.responseText; 
              else 
                 message="Error code " + xhr.status;
         }
    }; 
	xhr.open((methode=="POST"?"POST":"GET"), url, true); //connexion
	if (methode=="POST")
		xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	if(isString(parametres)) {//envoi des informations
		xhr.send(parametres);
	} else {
		var param="";
		for ( i in parametres) {
			param=i+"="+parametres[i]+"&";
		}
		param = param.substr(0,param.length-1);
		xhr.send(param);
	}
	return message;
}
