<?php
include(dirname(__DIR__).'/vendor/autoload.php');
include(dirname(__DIR__).'/MockRouteException.php');
include(dirname(__DIR__).'/Stateless.php');
define('base','https://example.com');

\Neoan3\Apps\Stateless::setSecret('abcdefg');
$jwt = \Neoan3\Apps\Stateless::assign('someId','user');

echo "JWT:\n";
print_r($jwt);

