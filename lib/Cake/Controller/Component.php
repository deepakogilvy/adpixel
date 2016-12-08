<?php
App::uses('ComponentCollection', 'Controller');
class Component extends Object {
    protected $_Collection;
    public $settings = array();
    public $components = array();
    protected $_componentMap = array();
    public function __construct(ComponentCollection $collection, $settings = array()) {
        $this->_Collection = $collection;
        $this->settings = $settings;
        $this->_set($settings);
        if (!empty($this->components)) {
            $this->_componentMap = ComponentCollection::normalizeObjectArray($this->components);
        }
    }

    public function __get($name) {
        if (isset($this->_componentMap[$name]) && !isset($this->{$name})) {
            $settings = array('enabled' => false) + (array)$this->_componentMap[$name]['settings'];
            $this->{$name} = $this->_Collection->load($this->_componentMap[$name]['class'], $settings);
        }
        if (isset($this->{$name})) {
            return $this->{$name};
        }
    }

    public function initialize(Controller $controller) {
    }

    public function startup(Controller $controller) {
    }

    public function beforeRender(Controller $controller) {
    }

    public function shutdown(Controller $controller) {
    }

    public function beforeRedirect(Controller $controller, $url, $status = null, $exit = true) {
    }

    public function csv( $array, $fileName, $addTime = true, $cols = false ) {
        if( $addTime ) $fileName = $fileName . date('m/d/Y_H:i') . '.csv';
        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename="'.$fileName.'"');

        if( is_array( $array ) ) {
            $i = 0;
            $out = '';
            foreach( $array as $key => $value ) {
                $header = !$cols ? array_keys( $value ) : $cols;
                if( $i < 1 ) {
                    $out .= '"' . implode( '","', $header ) . '"' . "\n";
                    $i = 1;
                }
                if( $cols ) {
                    $value = array_intersect_key( $value, array_flip( $cols ) );
                }
                $out .= '"' . implode( '","', $value ) . '"' . "\n";
            }
            echo $out;
            exit;
        } else {
            return false;
        }
    }

    public function csv_out( $array ) {
        if( is_array( $array ) ) {
            $i = 0;
            $out = '';
            foreach( $array as $key => $value ) {
                $header = !$cols ? array_keys( $value ) : $cols;
                if( $i < 1 ) {
                    $out .= '"' . implode( '","', $header ) . '"' . "\n";
                    $i = 1;
                }
                if( $cols ) {
                    $value = array_intersect_key( $value, array_flip( $cols ) );
                }
                $out .= '"' . implode( '","', $value ) . '"' . "\n";
            }
            return $out;
        } else {
            return "There was no data found.";
        }
    }
}