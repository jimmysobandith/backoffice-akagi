<?php
/**
 * Author: Allemandou David
 * Company: Warmbee (Deliv's SARL)
 * Date: 2016
 * Desc: Custom routing
 */
$router = new Phalcon\Mvc\Router();

$router->add("/set-language/{language:[a-z]+}", array(
    'controller' => 'index',
    'action' => 'setLanguage'
));

?>