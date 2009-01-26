<?php
/* à faire :
	vérifications (adresse mail, nom (lettres puis evt numero))
*/

if(isset($_POST["nomJ"])) {

	require("config.inc");
	require("fonctions.inc");
	
	seconnecte();
	$n = 0;$m = 0;	$c = 0;
	while(!$c && $n < 10) {
		$m = cherche_numero_valide("Joueur","IdJoueur");
		$c = mysql_query ("INSERT INTO Joueur(nom,mdp,email,IdJoueur) VALUES (\""
				.$_POST["nomJ"]."\",\""
				.$_POST["mdpJ"]."\",\""
				.$_POST["emailJ"]."\","
				. $m .");");
		if (!$c) echo "Tentative n°".$n++." ($m) échouée : ".mysql_error()."<br>";
	}
	if (!$c) echo "Incription échouée...";
	else echo "Inscription réussie !";

} else {
?>
<html>
<head>
<title>Inscription</title>
</head>
<body>
<form method=POST action="<? echo $_SERVER["SCRIPT_NAME"] ; ?>">
Nom : <input type=text name=nomJ><br>
Mot de passe : <input type=password name=mdpJ><br>
Email : <input type=text name=emailJ><br>
<input type=submit name=envoi value="S'inscrire !">
</form>
</body>
</html>
<?php
}
?>