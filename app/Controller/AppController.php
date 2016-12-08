<?php

App::uses( 'Controller', 'Controller' );

class AppController extends Controller {

    public $helpers = [ 'Neo' ];
    public $current_user = false;
    public $session_user = false;
    public $uses = [ 'User' ];
    public $components = [ 'Session', 'Excel', 'Cookie', 'RequestHandler', 'Auth' => [  'loginAction' => [ 'controller' => 'users', 'action' => 'login' ] ] ];

    public function beforeRender() {
        if( $this->name == 'CakeError' ) $this->layout = 'error';
    }

    public function beforeFilter() {
        $this->current_user = $this->User->loggedUser();
        if( !$this->session_user ) {
            $this->session_user = $this->Session->read( 'Auth.User' );
        }
        $this->set( 'current_user', $this->current_user );
        $this->Auth->allow( 'ajaxData' );
    }

    public function isAuthorized() {
        return true;
    }

    public function isAllowed( $controller, $action ) {
        return true;
    }
}