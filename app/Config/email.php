<?php
class EmailConfig {
    public $default = array(
        'transport' => 'Mail',
        'from' => 'alerts@ogilvy.com',
        'charset' => 'utf-8',
        'headerCharset' => 'utf-8',
    );

    public $smtp = array(
        'transport' => 'Smtp',
        'from' => array('alerts@ogilvy.com' => 'Ogilvy GBMT'),
        'host' => 'smtp.mandrillapp.com',
        'port' => 587,
        'timeout' => 30,
        'username' => 'dav.ogilvy.com@gmail.comwrongemail',
        'password' => 'M2gxyBIElJwJDCBLctMXXwwrongpassword',
        'client' => null,
        'log' => false,
        //'charset' => 'utf-8',
        //'headerCharset' => 'utf-8',
    );

    /*
    public $smtp = array(
        'transport' => 'Smtp',
        'from' => array('alerts@ogilvy.com' => 'Ogilvy DAV'),
        'host' => 'smtp.ogilvy.com',
        'port' => 25,
        'timeout' => 30,
        'username' => 'neo_mg@ogilvy.com',
        'password' => 'Welcome123',
        'client' => null,
        'log' => false,
        //'charset' => 'utf-8',
        //'headerCharset' => 'utf-8',
    );
    */

    public $fast = array(
        'from' => 'you@localhost',
        'sender' => null,
        'to' => null,
        'cc' => null,
        'bcc' => null,
        'replyTo' => null,
        'readReceipt' => null,
        'returnPath' => null,
        'messageId' => true,
        'subject' => null,
        'message' => null,
        'headers' => null,
        'viewRender' => null,
        'template' => false,
        'layout' => false,
        'viewVars' => null,
        'attachments' => null,
        'emailFormat' => null,
        'transport' => 'Smtp',
        'host' => 'localhost',
        'port' => 25,
        'timeout' => 30,
        'username' => 'user',
        'password' => 'secret',
        'client' => null,
        'log' => true,
        //'charset' => 'utf-8',
        //'headerCharset' => 'utf-8',
    );

}
