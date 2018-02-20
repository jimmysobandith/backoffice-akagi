<style type="text/css">
li
{
	margin-bottom: 75px;
}
</style>

<ul>
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
	
	$req = "select * from wb_clients where id_statut = 2";
	$clients = mysqli_query($sql_link, $req);
	
	while ($client = mysqli_fetch_assoc($clients))
	{
		echo '<li>'.utf8_encode($client['nom']).'</li>';
	}
?>
</ul>