<?php

App::uses( 'AppModel', 'Model' );

class User extends AppModel {

    public $belongsTo = [ 'Role' ];
    public $virtualFields = [ 'name' => 'CONCAT(User.first_name, " ", User.last_name)' ];
    public $loggedUser = false;

    public function isAllowed( $controller, $action = 'index' ) {
        return true;
    }

    public function loggedUser() {
        if( CakeSession::read( 'Auth.User.id' ) ) {
            $this->loggedUser = $this->find( 'first', [ 'conditions' => [ 'User.id' => CakeSession::read( 'Auth.User.id' ), 'User.is_active' => 1 ] ] );
        }
        return $this;
    }

    public function auditPrimaryLabel() {
        return "{$this->data[$this->alias]['first_name']}  {$this->data[$this->alias]['last_name']}";
    }

    public function auditSecondaryLabel() {
        return [$this->data[$this->alias]['first_name'] . ' ' . $this->data[$this->alias]['first_name'], "Name" ];
    }
}