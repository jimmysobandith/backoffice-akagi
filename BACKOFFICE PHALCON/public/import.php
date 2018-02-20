<?php
	$db_host = "localhost";
	$db_user = "visualadmin";
	$db_pass = "At64f8St";
	$db = "visualadmin";
	
	$sql_link = new mysqli($db_host, $db_user, $db_pass, $db);
	if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}
	
	$file = fopen('imports/WB-Prospection.csv', 'r');
	
	function utf8_encode2($str)
	{ 
		$final_str = $str; 

		$final_str = str_replace('œ', '&#339;',  $final_str); 
		$final_str = str_replace('’', '&#2019;', $final_str); 
		$final_str = str_replace('“', '&#201C;', $final_str); 
		$final_str = str_replace('”', '&#201D;', $final_str); 
		$final_str = str_replace('…', '&#2026;', $final_str); 
		$final_str = str_replace('€', '&#8364;', $final_str); 

		$final_str = utf8_encode($final_str); 

		$final_str = str_replace(utf8_encode('&#339;') , 'Å“',     $final_str); 
		$final_str = str_replace(utf8_encode('&#2019;'), 'â€™', $final_str); 
		$final_str = str_replace(utf8_encode('&#201C;'), 'â€œ', $final_str); 
		$final_str = str_replace(utf8_encode('&#2026;'), 'â€¦', $final_str); 
		$final_str = str_replace(utf8_encode('&#201D;'), 'â€', $final_str); 
		$final_str = str_replace(utf8_encode('&#8364;'), 'â‚¬', $final_str); 

		return $final_str;
	} 
	
	function filtre($txt)
	{
		return str_replace('&#0', '&#', htmlentities(utf8_encode2($txt), ENT_QUOTES | ENT_HTML401));
	}
	
	$i=0;
	$clients = array();
	while ($line = fgetcsv($file, 0, ";"))
	{
		$cas = $i%5;
		
		switch ($cas)
		{
			case 0:
			{
				$client = array();
				$vide = $line[0];
				$nom = $line[1];
				$rue = $line[2];
				$gerant1 = $line[3];
				$numero1 = $line[4];
				$mail1 = $line[5];
				$site1 = $line[6];
				$arguments = $line[8];
				$relance = $line[13];
			}
			break;
			case 1:
			{
				$id_commercial = $line[0];
				$activite = $line[1];
				$cp = $line[2];
				$gerant2 = $line[3];
				$numero2 = $line[4];
				$mail2 = $line[5];
				$site2 = $line[6];
				$commentaires = $line[8];
			}
			break;
			case 2:
			{
				$zone = $line[0];
				$statut = $line[1];
				$ville = $line[2];
				$gerant3 = $line[3];
				$numero3 = $line[4];
				$mail3 = $line[5];
				$site3 = $line[6];
				$societe = $line[8];
				$capital = $line[9];
			}
			break;
			case 3:
			{
				$type = $line[0];
				$creation = $line[1];
				$distance = $line[2];
				$gerant4 = $line[3];
				$numero4 = $line[4];
				$mail4 = $line[5];
				$site4 = $line[6];
				$origine = $line[8];
				$methode = $line[10];
				$flyer = $line[12];
			}
			break;
			case 4:
			{
				//Construction du client
				$client['nom'] = $nom;
				$client['rue'] = $rue;
				$client['gerant1'] = $gerant1;
				$client['numero1'] = $numero1;
				$client['mail1'] = $mail1;
				$client['site1'] = $site1;
				$client['arguments'] = $arguments;
				$client['relance'] = $relance;
				
				$client['id_commercial'] = $id_commercial;
				$client['activite'] = $activite;
				$client['cp'] = $cp;
				$client['gerant2'] = $gerant2;
				$client['numero2'] = $numero2;
				$client['mail2'] = $mail2;
				$client['site2'] = $site2;
				$client['commentaires'] = $commentaires;
				
				$client['zone'] = $zone;
				$client['statut'] = $statut;
				$client['ville'] = $ville;
				$client['gerant3'] = $gerant3;
				$client['numero3'] = $numero3;
				$client['mail3'] = $mail3;
				$client['site3'] = $site3;
				$client['societe'] = $societe;
				$client['capital'] = $capital;
				
				$client['type'] = $type;
				$client['creation'] = $creation;
				$client['distance'] = $distance;
				$client['gerant4'] = $gerant4;
				$client['numero4'] = $numero4;
				$client['mail4'] = $mail4;
				$client['site4'] = $site4;
				$client['origine'] = $origine;
				$client['methode'] = $methode;
				$client['flyer'] = $flyer;
				
				//Ajout au tableau des clients
				$clients[] = $client;
			}
			break;
			default:
			{
				//Erreur
			}
			break;
		}
		
		$i++;
	}
	
	$a=0;
	foreach ($clients as $client)
	{
		echo $client['nom'].'<br />';
		if ($client['nom'] != '')
		{
			if (strpos($client['societe'], 'EUR') !== false)
			{
				$societe = $client['societe'];
				$client['societe'] = $client['capital'];
				$client['capital'] = $societe;
			}
			
			$flyer = 0;
			if ($client['flyer'] == 'Oui')
			{
				$flyer = 1;
			}
			
			$client['relance'] = str_replace('/', '-', $client['relance']);
			
			$id_type = 1;
			$id_statut = 1;
			
			$id_departement = 0;
			if (client['cp'] != '')
			{
				$code_departement = substr($client['cp'], 0, 2);
				
				$req = "select * from wb_departements where code = '".$code_departement."'";
				$departements = mysqli_query($sql_link, $req);
				
				if ($departements != false)
				{
					$departement = @mysqli_fetch_array($departements, MYSQL_ASSOC);
					
					$id_departement = $departement['id'];
				}
			}
			
			$id_zone = 0;
			$req = "select * from wb_zones where nom like '%".$client['zone']."%'";
			$zones = mysqli_query($sql_link, $req);
			
			if ($zones != false)
			{
				$zone = @mysqli_fetch_array($zones, MYSQL_ASSOC);
				
				$id_zone = $zone['id'];
			}
			
			//Insertion du client
			$req = "insert into wb_clients (id_type, id_statut, id_departement, id_zone, date, nom, activite, adresse, cp, ville, arguments, capital, societecom, site1, site2, site3, site4, flyer, relance, maj) values (
'".mysqli_escape_string($sql_link, $id_type)."',
'".mysqli_escape_string($sql_link, $id_statut)."',
'".mysqli_escape_string($sql_link, $id_departement)."',
'".mysqli_escape_string($sql_link, $id_zone)."',
'".mysqli_escape_string($sql_link, time())."',
'".mysqli_escape_string($sql_link, filtre($client['nom']))."',
'".mysqli_escape_string($sql_link, filtre($client['activite']))."',
'".mysqli_escape_string($sql_link, filtre($client['rue']))."',
'".mysqli_escape_string($sql_link, filtre($client['cp']))."',
'".mysqli_escape_string($sql_link, filtre($client['ville']))."',
'".mysqli_escape_string($sql_link, filtre($client['arguments']))."',
'".mysqli_escape_string($sql_link, str_replace(',00', '', str_replace('.', '', str_replace('EUR', '', $client['capital']))))."',
'".mysqli_escape_string($sql_link, filtre($client['societe']))."',
'".mysqli_escape_string($sql_link, $client['site1'])."',
'".mysqli_escape_string($sql_link, $client['site2'])."',
'".mysqli_escape_string($sql_link, $client['site3'])."',
'".mysqli_escape_string($sql_link, $client['site4'])."',
'".mysqli_escape_string($sql_link, $flyer)."',
'".mysqli_escape_string($sql_link, strtotime($client['relance']))."',
'".mysqli_escape_string($sql_link, 0)."'
			)";
			
			$result = @mysqli_query($sql_link, $req);
			
			if ($result == false)
			{
				echo "Erreur : ".$req;
				exit();
			}
			
			$id_client = @mysqli_insert_id($sql_link);
			
			if ($client['commentaires'] != '')
			{
				$client['commentaires'] = str_replace(' :', ':', $client['commentaires']);
				$client['commentaires'] = str_replace(': ', ':', $client['commentaires']);
				$client['commentaires'] = trim($client['commentaires']);
				
				$pattern = "#([0-9]+/[0-9]+/[0-9]+:)#Ui";
				//Isolation des infos
				$tab = preg_split($pattern, $client['commentaires'], -1, PREG_SPLIT_DELIM_CAPTURE);
				
				for ($i=1; $i<(@count($tab)-1); $i=$i+2)
				{
					$date = strtotime(str_replace('/', '-', str_replace(':', '', $tab[$i])));
					$texte = $tab[$i+1];
					$texte = trim($texte);
					$req = "insert into wb_historiques (id_client, date, texte) values ('".mysqli_escape_string($sql_link, $id_client)."', '".mysqli_escape_string($sql_link, $date)."', '".mysqli_escape_string($sql_link, filtre($texte))."')";
					$result = @mysqli_query($sql_link, $req);
				}
			}
			
			if ( ($client['gerant1'] != '') || ($client['numero1'] != '') || ($client['mail1'] != '') )
			{
				$req = "insert into wb_contacts (id_client, nom, email, tel1, tel2) values ('".mysqli_escape_string($sql_link, $id_client)."', '".mysqli_escape_string($sql_link, filtre($client['gerant1']))."', '".mysqli_escape_string($sql_link, $client['mail1'])."', '".mysqli_escape_string($sql_link, $client['numero1'])."', '')";
				$result = @mysqli_query($sql_link, $req);
			}
			
			if ( ($client['gerant2'] != '') || ($client['numero2'] != '') || ($client['mail2'] != '') )
			{
				$req = "insert into wb_contacts (id_client, nom, email, tel1, tel2) values ('".mysqli_escape_string($sql_link, $id_client)."', '".mysqli_escape_string($sql_link, filtre($client['gerant2']))."', '".mysqli_escape_string($sql_link, $client['mail2'])."', '".mysqli_escape_string($sql_link, $client['numero2'])."', '')";
				$result = @mysqli_query($sql_link, $req);
			}
			
			if ( ($client['gerant3'] != '') || ($client['numero3'] != '') || ($client['mail3'] != '') )
			{
				$req = "insert into wb_contacts (id_client, nom, email, tel1, tel2) values ('".mysqli_escape_string($sql_link, $id_client)."', '".mysqli_escape_string($sql_link, filtre($client['gerant3']))."', '".mysqli_escape_string($sql_link, $client['mail3'])."', '".mysqli_escape_string($sql_link, $client['numero3'])."', '')";
				$result = @mysqli_query($sql_link, $req);
			}
			
			if ( ($client['gerant4'] != '') || ($client['numero4'] != '') || ($client['mail4'] != '') )
			{
				$req = "insert into wb_contacts (id_client, nom, email, tel1, tel2) values ('".mysqli_escape_string($sql_link, $id_client)."', '".mysqli_escape_string($sql_link, filtre($client['gerant4']))."', '".mysqli_escape_string($sql_link, $client['mail4'])."', '".mysqli_escape_string($sql_link, $client['numero4'])."', '')";
				$result = @mysqli_query($sql_link, $req);
			}
		}
		
		$a++;
	}
	
	echo count($clients).'<br />';
	echo $a.'<br />';
?>