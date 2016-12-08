<?php
class DATABASE_CONFIG {
    public $default = array(
        'datasource' => 'Database/Mysql',
        'persistent' => false,
        'host' => 'localhost',
        'login' => 'root',
        'password' => 'root',
        'database' => 'adpixel',
        'prefix' => '',
    );

    public $mongo = array(
        'datasource' => 'MongodbSource',
        'database' => 'adpixel',
        'host' => 'localhost',
        'port' => 27017,
    );
}
