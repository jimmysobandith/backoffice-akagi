<?php
	$time_start = microtime(true);
?>
<html>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Test fiches</title>
</head>

<body>
<?php
function traiter($txt)
{
	return addslashes(utf8_decode($txt));
}

@include("simple_html_dom.php");
$sql_host = "localhost";
$sql_login = "visualadmin";
$sql_pass = "At64f8St";
$sql_db = "visualadmin";
$sql_new = true;

$sql_link = @mysql_connect($sql_host, $sql_login, $sql_pass, $sql_new);
if ($sql_link == false)
{
	echo "Erreur connexion";
	exit();
}

$select = @mysql_select_db($sql_db, $sql_link);
if ($select == false)
{
	echo "Erreur select";
	exit();
}

$req = "select * from wb_societe limit 5632,1";
$societes = @mysql_query($req, $sql_link);

while (($societe = @mysql_fetch_array($societes, MYSQL_ASSOC)) != false)
{
	$lien="https://www.google.fr/search?num=10&safe=off&q=site%3Awww.societe.com+".urlencode($societe[utf8_decode('dénomination')]);
	
	$options=array(
      CURLOPT_URL            => $lien,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HEADER         => false,
      CURLOPT_FAILONERROR    => true,
      CURLOPT_POST           => false
	);

	$CURL=curl_init();

	if(empty($CURL))
	{
		die("ERREUR curl_init");
	}

	curl_setopt_array($CURL, $options);

	$content=curl_exec($CURL);
	
	if(curl_errno($CURL))
	{
		echo "ERREUR curl_exec : ".curl_error($CURL);
	}
	
	$html = str_get_html($content);
	$cites = $html->find('cite');
	
	foreach ($cites as $cite)
	{
		$plaintext = $cite->plaintext;
		if ((strpos($plaintext, '/societe/') !== false) && (strpos($plaintext, substr($societe['siren'], -4)) !== false))
		{
			echo $plaintext;
		}
	}
}


/*
//Série 1
$annee = 0;
$ca = 0;
$export = 0;
$va = 0;
$ebe = 0;
$resultat = 0;
$net = 0;
$effectif = 0;

if (isset($tables[0]))
{
	$tr = $tables[0]->children(0);
	$th = $tr->children(0);
	if (isset($th))
	{
		//Année
		if (strpos($tr->children(0)->plaintext, "Date") !== false)
		{
			$annee = explode('-', $tr->children(1)->plaintext);
			$annee = $annee[2];
			while (($tr = $tr->next_sibling()) != null)
			{
				//Chiffre d'affaires
				if (strpos($tr->children(0)->plaintext, "affaires") !== false)
				{
					$ca = $tr->children(1)->plaintext;
				}
				
				//Export
				if (strpos($tr->children(0)->plaintext, "export") !== false)
				{
					$export = $tr->children(1)->plaintext;
				}
				
				//Valeur ajoutée
				if (strpos($tr->children(0)->plaintext, "ajoutée") !== false)
				{
					$va = $tr->children(1)->plaintext;
				}
				
				//Excédent brut d'exploitation
				if (strpos($tr->children(0)->plaintext, "Excédent") !== false)
				{
					$ebe = $tr->children(1)->plaintext;
				}
				
				//Résultat
				if (strpos($tr->children(0)->plaintext, "exploitation") !== false)
				{
					$resultat = $tr->children(1)->plaintext;
				}
				
				//Résultat net
				if (strpos($tr->children(0)->plaintext, " net") !== false)
				{
					$net = $tr->children(1)->plaintext;
				}
				
				//Effectif moyen
				if (strpos($tr->children(0)->plaintext, "Effectif") !== false)
				{
					$effectif = $tr->children(1)->plaintext;
					if (! is_numeric($effectif))
					{
						$effectif = 0;
					}
					if ($effectif < 0)
					{
						$effectif = 0;
					}
				}
			}
		}
		else
		{
			//Pas de bilan affiché
			
		}
	}
}
else
{
	//Pas de bilan affiché
	
}

echo "annee : ".$annee.'<br />';
echo "ca : ".$ca.'<br />';
echo "export : ".$export.'<br />';
echo "va : ".$va.'<br />';
echo "ebe : ".$ebe.'<br />';
echo "resultat : ".$resultat.'<br />';
echo "net : ".$net.'<br />';
echo "effectif : ".$effectif.'<br />';
*/
$time_end = microtime(true);
$time = $time_end - $time_start;
?>

Temps de génération de la page : <?php echo $time; ?>
</body>

</html>