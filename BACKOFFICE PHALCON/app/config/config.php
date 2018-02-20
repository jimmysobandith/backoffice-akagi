<?php
/**
 * Author: Allemandou David
 * Company: Warmbee (Deliv's SARL)
 * Date: 2016
 * Desc: Configuration file (database, directories)
 */
$config = new Phalcon\Config(array(
    'database' => array(
        'adapter' => 'mysql',
        'host' => 'localhost',
        'username' => 'jimmy02',
        'password' => 'p69iUEf5',
        'dbname' => 'jimmy02'
    ),
    'phalcon' => array(
        'controllersDir' => '/../app/controllers/',
        'modelsDir' => '/../app/models/',
        'libraryDir' => '/../app/library/',
        'viewsDir' => '/../app/views/',
        'baseUri' => '/'
    ),
    'models' => array(
        'metadata' => array(
            'adapter' => 'Apc',
    		'lifetime' => 86400
        )
    )
));
