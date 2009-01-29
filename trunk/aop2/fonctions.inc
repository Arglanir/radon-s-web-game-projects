<?php
/*
Fichier: fonction.inc
Date: 27/01/2009
Auteur: Mikaël Mayer / Cédric Mayer
But: Fonctions communes à plusieurs fichiers
*/

$mdpadminmd5 = md5("meuhmeuh");

$fichier_parties = "lespartiesencours.xml";

function entre($avant,$puis,$ensuite){//renvoie vrai si ils sont dans l'ordre
	return ($avant<=$puis) && ($puis<=$ensuite);
}

function mettreEntre($nombre,$base){//fonction permettant le modulo, met le nombre entre 0 et base-1 compris
	while($nombre < 0)
		$nombre+=$base;
	while($nombre >= $base)
		$nombre-=$base;
	return $nombre;
}

//renvoie une chaine contenant l'URL du client pour le joueur
function getUrlJoueur($numero_partie, $numero_joueur, $si_mot_de_passe=false, $mot_de_passe="") {
  $url = "jeu.html?j=".$numero_joueur."&p=".$numero_partie;
  If($si_mot_de_passe) {
    $url = $url."&pw=".$mot_de_passe;
  }
  return $url;
}

//DOM-way of doing that
function ajouterPartie($fichier_parties, $numero, $cachee, $nbJoueurs, $joueur_array) {
if (floatval(phpversion())>=5){
  $xml_parties = new DOMDocument();
  $file_exists = file_exists($fichier_parties);
  if($file_exists) {
    $xml_parties->load( $fichier_parties );
  }
  $xml_parties->formatOutput = true;
  
  $parties_array = $xml_parties->getElementsByTagName( "parties" );
  if($file_exists) {
    $parties = $parties_array->item(0);
  } else {
    $parties = $xml_parties->createElement( "parties" );
    $xml_parties->appendChild($parties);
  }
  $partie = $xml_parties->createElement("partie");
  $partie->setAttribute("numero", $numero);
  $partie->setAttribute("cachee", $cachee);
  $partie->setAttribute("nbJoueurs", $nbJoueurs);
  $parties->appendChild($partie);
  
  foreach($joueur_array as $index => $joueur) {
    $joueur_xml = $xml_parties->createElement("joueur");
    $joueur_xml->setAttribute("numero", $index);
    $joueur_xml->setAttribute("nom", utf8_encode($joueur["nom"]));
    $partie->appendChild($joueur_xml);
  }
  
  $fh = fopen($fichier_parties, 'wb');
  fwrite($fh, $xml_parties->saveXML());
  fclose($fh);
} else {
  $file_exists = file_exists($fichier_parties);
  if($file_exists) {
    $xml_parties = domxml_open_file( $fichier_parties );
  } else {
	$xml_parties = domxml_new_doc("1.0");
  }
  
  $parties_array = $xml_parties->get_elements_by_tagname( "parties" );
  if($file_exists) {
    $parties = $parties_array[0];
  } else {
    $parties = $xml_parties->create_element( "parties" );
    $xml_parties->append_child($parties);
  }
  $partie = $xml_parties->create_element("partie");
  $partie->set_attribute("numero", $numero);
  $partie->set_attribute("cachee", $cachee);
  $partie->set_attribute("nbJoueurs", $nbJoueurs);
  $parties->append_child($partie);
  
  foreach($joueur_array as $index => $joueur) {
    $joueur_xml = $xml_parties->create_element("joueur");
    $joueur_xml->set_attribute("numero", $index);
    $joueur_xml->set_attribute("nom", utf8_encode($joueur["nom"]));
    $partie->append_child($joueur_xml);
  }
  
  $xml_parties->dump_file($fichier_parties, false, true);

}
}

//DOM-way of doing that
function supprimerPartie($numero, $fichier_parties="lespartiesencours.xml", $force=false) {//$force continue sur le XML même si la partie n'existe pas en fichier
$fichier="aop".$numero."bacteries.par";
if (file_exists($fichier)){
	if (!unlink($fichier))
		return false;
} else {
	if (!$force) return false;
}

//traitement du XML
  $file_exists = file_exists($fichier_parties);
  if(!$file_exists) {
	return true;
  }
  $changement=false;
if (floatval(phpversion())>=5){
  $xml_parties = new DOMDocument();
  $xml_parties->load( $fichier_parties );
  $xml_parties->formatOutput = true;
  
  $parties_array = $xml_parties->getElementsByTagName( "parties" );
  $parties = $parties_array->item(0);

  foreach ($parties->getElementsByTagName( "partie" ) as $partie) {
    if($partie->getAttribute("numero") != $numero) continue;
	$parties->removeChild($partie);
	$changement = true;
	break;
  }
  if ($changement) {
	$fh = fopen($fichier_parties, 'wb');
	fwrite($fh, $xml_parties->saveXML());
	fclose($fh);
  }
} else {
  $xml_parties = domxml_open_file( $fichier_parties );
  
  $parties_array = $xml_parties->getElementsByTagName( "parties" );
  $parties = $parties_array[0];

  foreach ($parties->get_elements_by_tagname("partie") as $partie) {
    if($partie->get_attribute("numero") != $numero) continue;
	$parties->remove_child($partie);
	$changement = true;
	break;
    }
  
  if ($changement) {
	$xml_parties->dump_file($fichier_parties, false, true);
  }
}
return true;
}

//SimpleXml-way of doing that (requires PHP5)
function ajouterPartieSimpleXml($fichier_parties, $numero, $cachee, $nbJoueurs, $joueur_array) {

  if(!file_exists($fichier_parties)) {
    $content = '<?xml version="1.0" encoding="UTF-8"?><root></root>';
  } else {
    $content = file_get_contents($fichier_parties);
  }
  $xml_parties = new SimpleXMLElement($content);

  // Ajoute la partie
  if(!isset($xml_parties->parties)) {
    $parties = $xml_parties->addChild('parties');
  } else {
    $parties = $xml_parties->parties;
  }
  $partie = $parties->addChild('partie');
  $partie->addAttribute("numero", $numero);
  $partie->addAttribute("cachee", $cachee);
  $partie->addAttribute("nbJoueurs", $nbJoueurs);
  
  foreach($joueur_array as $index => $joueur) {
    $joueur_xml = $partie->addChild("joueur");
    $joueur_xml->addAttribute("numero", $index);
    $joueur_xml->addAttribute("nom", $joueur["nom"]);
  }
  $fh = fopen($fichier_parties, 'wb');
  fwrite($fh, $xml_parties->asXML());
  fclose($fh);
}

$no_party = "<tr><td>Aucune</tr></td></table>";
function afficherParties($fichier_parties,$admin=false) {
	global $no_party;
  echo "<table>";
  if(!file_exists($fichier_parties)) {
    echo $no_party;
  } else {
  if (floatval(phpversion())>=5){//dom change !

    $xml_parties = new DOMDocument();
    $xml_parties->load($fichier_parties);

    $parties = $xml_parties->getElementsByTagName("partie");
    foreach ($parties as $partie) {
      if($partie->getAttribute("cachee") == "1" && !$admin) continue;
      echo "<tr>";
      echo "<td>";
      $numero_partie = $partie->getAttribute("numero");
      echo "Partie ".$numero_partie;
      echo " : </td>";
      foreach ($partie->getElementsByTagName("joueur") as $joueur) {
        echo "<td>";
        $url = getUrlJoueur($numero_partie, $joueur->getAttribute("numero"));
        echo '<a href="'.$url.'">'.$joueur->getAttribute("nom").'</a>';
        echo "</td>";
      }
	  if ($admin){
		  echo "<td>";
		  echo "<input type=\"button\" value=\"Supprimer\" onclick=\"supprimerPartie('".$numero_partie."');\" />";
		  echo "</td>";
	  }
      echo "</tr>\n";
    }
  } else {//ancienne version de DOM
	
    $xml_parties = @domxml_open_file( $fichier_parties );

    $parties = $xml_parties->get_elements_by_tagname("partie");
    foreach ($parties as $partie) {
      if($partie->get_attribute("cachee") == "1" && !$admin) continue;
      echo "<tr>";
      echo "<td>";
      $numero_partie = $partie->get_attribute("numero");
      echo "Partie ".$numero_partie;
      echo " : </td>";
      foreach ($partie->get_elements_by_tagname("joueur") as $joueur) {
        echo "<td>";
        $url = getUrlJoueur($numero_partie, $joueur->get_attribute("numero"));
        echo '<a href="'.$url.'">'.$joueur->get_attribute("nom").'</a>';
        echo "</td>";
      }
	  if ($admin){
		  echo "<td>";
		  echo "<input type=\"button\" value=\"Supprimer\" onclick=\"supprimerPartie('".$numero_partie."');\" />";
		  echo "</td>";
	  }
      echo "</tr>\n";
    }
  }
  }
  echo "</table>";
}

function afficherPartiesSimpleXml($fichier_parties) {
  echo "<table>";
  if(!file_exists($fichier_parties)) {
    echo $no_party;
  } else {
    $xml_parties = new SimpleXMLElement(file_get_contents($fichier_parties));
    if(!isset($xml_parties->parties)) {
      echo $no_party;
      return;
    }
    $parties = $xml_parties->parties;
    foreach ($parties->children() as $partie) {
      if($partie["cachee"]=="1") continue;
      echo "<tr>";
      echo "<td>";
      echo "Partie ".$partie["numero"];
      echo " : </td>";
      foreach ($partie->children() as $joueur) {
        echo "<td>";
        $url = getUrlJoueur($partie["numero"], $joueur["numero"]);
        echo '<a href="'.$url.'">'.$joueur["nom"].'</a>';
        echo "</td>";
      }
      echo "</tr>\n";
    }
  }
  echo "</table>";
}

?>