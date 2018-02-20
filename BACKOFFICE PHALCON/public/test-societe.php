<?php
	$time_start = microtime(true);
	
for ($i=0; $i<150; $i++)
{
	$lien="http://www.societe.com/societe/le-satory-800463424.html";
	
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
	
	$html = $content;
}

echo $html;

$time_end = microtime(true);
$time = $time_end - $time_start;
?>

Temps de génération de la page : <?php echo $time; ?>
</body>

</html>