<?php
Router::parseExtensions( 'json', 'xml' );
Router::connect( '/', [ 'controller' => 'users', 'action' => 'login' ] );
//Router::connect( '/json/get/*', [ 'controller' => 'utility', 'action' => 'getJson' ] );
CakePlugin::routes();
require CAKE . 'Config' . DS . 'routes.php';