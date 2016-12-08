<?php
App::uses('AppModel', 'Model');
class Audit extends AppModel {
    public $useDbConfig = 'mongo';
    public $primaryKey = '_id';    
    public $mongoNoSetOperator = true;
}