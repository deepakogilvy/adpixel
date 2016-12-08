<?php
Cache::config( 'default', [ 'Engine' => 'Redis', 'prefix' => 'adpixel_' ] );
Cache::config( 'router', [ 'Engine' => 'Redis', 'prefix' => 'routing_', 'server' => 'localhost', 'port' => 6379, ] );
Configure::write( 'Dispatcher.filters', [ 'AssetDispatcher', 'CacheDispatcher' ] );
App::uses( 'CakeLog', 'Log' );
CakeLog::config( 'debug', [ 'engine' => 'FileLog', 'types' => [ 'warning', 'notice', 'info', 'debug' ], 'file' => 'debug', ] );
CakeLog::config( 'error', [ 'engine' => 'FileLog', 'types' => [ 'error', 'critical', 'alert', 'emergency' ], 'file' => 'error', ] );
CakePlugin::load( [ 'Saml', 'DataTable', 'CakeResque' => [ 'bootstrap' => true ], 'CakePdf' => [ 'bootstrap' => true, 'routes' => true ] ] );

// Email failsafe. This will redirect all emails to "override_email_with"
Configure::write( 'override_email', 0 );

function setOffset( $array, $offset = 0 ) {
    if( !is_array( $array ) ) $array = $array;
    foreach( $array as $key => $value ) {
        $array[$key]['offset'] = $offset;
        $offset += $value['len'];
    }
    return $array;
}

define( 'XL_PER', '_(* #,##0.00_)%;_(* \(#,##0\);_(* "-"??_);_(@_)' );
define( 'XL_PER_NEG', '0.00%;[Red]-0.00%.' );
define( 'XL_DOL', '_("$"* #,##0.00_);_("$"* \(#,##0.00\);_("$"* "-"??_);_(@_)' );
define( 'XL_INT', '_(* #,##0_);_(* \(#,##0\);_(* "-"??_);_(@_)' );
define( 'XL_NEG', '#,##0_);[RED]_(* \(#,##0\);_(* "-"??_);_(@_)' );
define( 'XL_DEC', '_(* #,##0.00_);_(* \(#,##0\);_(* "-"??_);_(@_)' );