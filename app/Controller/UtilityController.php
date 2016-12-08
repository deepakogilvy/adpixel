<?php
App::uses('Sanitize', 'Utility');

class UtilityController extends AppController {
    
    public $name = 'Utility';
    public $uses = [ 'User' ];
    public $current_user = false;

    public function beforeFilter() {

    }

    public function getJson() {
        $this->layout = 'ajax';
        $query = $this->request->query;
        unset( $query['url'] );
        
        $model = Inflector::classify( $this->request->params['pass'][0] );
        $this->loadModel( $model );

        $conditions = [];
        $modelFields = array_keys( $this->{$model}->schema() );
        $fields = array_diff( $modelFields, [ 'created', 'modified', 'created_by', 'modified_by' ] );

        if( !empty( $query ) ) {
            foreach( $query as $k => $v ) {
                if( in_array( $k, $modelFields ) ) $conditions[$k] = $v;
            }
        }
        $result = Hash::extract( $this->{$model}->find( 'all', [ 'fields' => $fields, 'conditions' => $conditions ] ), '{n}.' . $model );
        $this->set( 'result', $result )->set( '_serialize', 'result' );
    }
}