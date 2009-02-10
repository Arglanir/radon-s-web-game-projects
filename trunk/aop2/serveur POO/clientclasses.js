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


