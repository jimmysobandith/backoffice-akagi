<?php echo phpversion( "phalcon" ); ?>
<br />
<?php
function getOptimalCost($timeTarget)
{ 
    $cost = 9;
    do {
        $cost++;
        $start = microtime(true);
        password_hash("test", PASSWORD_BCRYPT, ["cost" => $cost]);
        $end = microtime(true);
    } while (($end - $start) < $timeTarget);
    
    return $cost;
}

$cost = getOptimalCost(0.5);

$pass = '5j35gzKP';
$hash = password_hash($pass,PASSWORD_BCRYPT,['cost' => $cost]);
echo $cost;
echo '<br />';
echo $hash;
?>
<br />
<?php
phpinfo();
?>