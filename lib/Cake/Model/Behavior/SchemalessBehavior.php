<?php
App::uses('ModelBehavior', 'Model');
class SchemalessBehavior extends ModelBehavior {
    public $name = 'Schemaless';
    public $settings = array();
    protected $_defaultSettings = array();

    public function setup(&$Model, $config = array()) {
        //$this->settings[$Model->alias] = array_merge($this->_defaultSettings, $config);
    }

   public function beforeSave(&$Model) {
        $Model->cacheSources = false;
        $Model->schema(true);
        return true;
    }
}