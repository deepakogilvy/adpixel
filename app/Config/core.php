<?php
Configure::write('debug', 0);
Configure::write('Error', array('handler' => 'ErrorHandler::handleError', 'level' => E_ALL & ~(E_DEPRECATED | E_STRICT | E_NOTICE), 'trace' => true ));
Configure::write('Exception', array( 'handler'=> 'ErrorHandler::handleException', 'renderer' => 'ExceptionRenderer', 'log' => true ));
Configure::write('App.encoding', 'UTF-8');
define('LOG_ERROR', LOG_ERR);
Configure::write('Session', array( 'defaults' => 'php' ));
Configure::write('Security.salt', '');
Configure::write('Security.cipherSeed', '');
Configure::write('Acl.classname', 'DbAcl');
Configure::write('Acl.database', 'default');
Configure::write('Asset.timestamp', 'force');

$engine = 'File';
$duration = '+999 days';

$duration = '+1 seconds';
$prefix = 'ogilvyadpixel_';
Cache::config('_cake_core_', array('engine' => $engine, 'prefix' => $prefix . 'cake_core_', 'path' => CACHE . 'persistent' . DS, 'serialize' => ($engine === 'File'), 'duration' => $duration ));
Cache::config('_cake_model_', array( 'engine' => $engine, 'prefix' => $prefix . 'cake_model_', 'path' => CACHE . 'models' . DS, 'serialize' => ($engine === 'File'), 'duration' => $duration ));

//CakePDF Configure
Configure::write('CakePdf', array(
    'engine' => 'CakePdf.WkHtmlToPdf',
    'options' => array( 'print-media-type' => false, 'outline' => true, 'dpi' => 96 ),
    'orientation' => 'landscape',
    'download' => false
));
Configure::write( 'logoutUrl', 'http://adpixel.com' );
Configure::write( 'semail', 'abc@xyz.com' );
