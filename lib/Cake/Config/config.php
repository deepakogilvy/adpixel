<?php
$versionFile = file(CAKE . 'VERSION.txt');
$config['Cake.version'] = trim(array_pop($versionFile));
return $config;