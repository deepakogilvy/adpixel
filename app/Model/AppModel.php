<?php

App::uses( 'Model', 'Model' );
App::uses( 'CakeSession', 'Model/Datasource' );

class AppModel extends Model {

    public $actsAs = [ 'Auditable' => [ 'ignore' => [ 'active', 'name', 'updated', 'modified', 'modified_by', 'reload' ], 'habtm' => [ 'Type', 'Project' ] ], 'Deleted' ];
    public $scoped = false;
    public $marketScoped = true;

    public function currentUser() {
        $user = CakeSession::read( 'Auth.User' );
        return $user;
    }
}