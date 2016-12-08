<?php

App::uses( 'AppController', 'Controller' );

class RolesController extends AppController {

    public $name = 'Roles';
    public $uses = [ 'Role', 'User' ];
    public $components = ['TableSearch' ];

    public function index() {
        //$roles = $this->Role->find( 'all', $options );
        //$users = $this->User->find( 'list', [ 'fields' => [ 'id', 'name'] ] );
        //$this->set( 'roles', $roles )->set( 'users', $users );
    }

    public function view( $roleId = null ) {
        $this->set( 'roleId', $roleId );
        $roleName = $this->Role->findById( $roleId );
        $roleName = $roleName['Role']['role_name'];
        $this->set( 'roleName', $roleName );
        $conditions = [ ];
        $conditions['role_id'] = $roleId;
        $privileges = $this->Privilege->find( 'all', [ 'conditions' => [ 'role_id' => $roleId ], 'order' => 'id' ] );
        $this->set( 'privileges', $privileges );
    }

    public function setStatus( $id = null, $status = null ) {
        $this->layout = 'ajax';
        $status = ( $status + 1 ) % 2;
        $this->Role->id = $id;
        $this->Role->save( [ 'is_active' => $status ] );
        $this->User->updateAll( [ 'reload' => 1 ], [ 'role_id' => $id ] );
        echo $status;
        $this->set( 'status', $status );
        $this->set( '_serialized', $status );
    }

    public function edit( $roleId = null ) {
        if( $this->request->is( 'post' ) ) {
            $data = $this->request->data;
            $this->Role->save( $data );
            $this->User->updateAll( [ 'reload' => 1 ], [ 'role_id' => $data['id'] ] );
            $this->Session->setFlash( __( 'Role Updated' ), 'default', [ 'class' => 'success' ] );
            $this->redirect( [ 'controller' => 'roles', 'action' => 'index' ] );
        }
        $role = $this->Role->findById( $roleId );
        $this->set( 'role', $role );
    }

    public function add() {
        if( $this->request->is( 'post' ) ) {
            $data = $this->request->data;
            if( $this->Role->findByRoleName( $data['role_name'] ) ) {
                $this->Session->setFlash( __( 'Role with name ' . $data['role_name'] . ' exists.' ), 'default', [ 'class' => 'danger' ] );
                $this->redirect( [ 'controller' => 'roles', 'action' => 'add' ] );
            }
            $this->Role->save( $data );
            $this->Session->setFlash( __( 'Role Added' ), 'default', [ 'class' => 'success' ] );
            $this->redirect( [ 'controller' => 'roles', 'action' => 'index' ] );
        }
    }

    public function ajaxData() {
        $this->autoRender = false;
        $query = ['fields' => ["id", "role_name", "is_active", "created" ] ];
        $sortByColumns = ['role_name', 'is_active' ];
        $searchWordMap = [
            "is_active" => ['active' => 1, 'in-active' => 0, 'inactive' => 0, 'in active' => 0 ],
        ];
        $columnsExp = [ ];
        $output = $this->TableSearch->data( $query, $sortByColumns, $searchWordMap, $columnsExp );
        $data = $output['aaData'];
        $output['aaData'] = [ ];
        $modelClass = $this->modelClass;
        $isDeletable = $this->isAllowed( $this->request->controller, 'delete' );
        if( $data ) {
            foreach( $data as $d ) {
                $deleted = '';
                if( $isDeletable ) {
                    $deleted = '<a data-target="#deletePopup" data-toggle="modal"   data-original-title="Delete ' . $this->modelClass . '" data-toggle="tooltip" type="button" data-id="' . $d[$modelClass]["id"] . '" class="btn btn-xs btn-danger delete-record" >
                            <i class="fa fa-remove"></i>
                        </a>';
                }
                $temp = [ ];
                $temp[] = '<a href="' . Router::url( [ 'controller' => $this->request->controller, 'action' => 'view', $d[$modelClass]["id"] ] ) . '"> ' . $d[$modelClass]['role_name'] . '</a>';
                if( $d[$modelClass]['is_active'] ) {
                    $temp[] = '<span id="label_' . $d[$modelClass]['id'] . '"class="label label-success">Active</span>';
                } else {
                    $temp[] = '<span id="label_' . $d[$modelClass]['id'] . '"class="label label-danger">Inactive</span>';
                }
                $temp[] = '<div class="btn-group">'
                        . '<a href="' . Router::url( [ 'controller' => $this->request->controller, 'action' => 'edit', $d[$modelClass]["id"] ] ) . '" class="btn btn-xs btn-default" type="button" data-toggle="tooltip" title="" data-original-title="Edit ' . $this->modelClass . '">'
                        . '<i class="fa fa-pencil"></i>'
                        . '</a>'
                        . '<a class="btn btn-xs btn-default setStatus" data-controller="' . $this->request->controller . '" rel="' . $d[$modelClass]['is_active'] . '" id="' . $d[$modelClass]["id"] . '" type="button" data-toggle="tooltip" title="" data-original-title="Change Status">'
                        . '<i class="fa fa-exclamation"></i>'
                        . '</a>'
                        . $deleted
                        . '</div>';

                $output['aaData'][] = $temp;
            }
        }
        echo json_encode( $output );
    }

    public function delete( $id ) {
        $this->autoRender = false;
        if( $this->request->is( 'post' ) ) {
            $data = $this->request->data;
            $id = $data['id'];
            if( $this->{$this->modelClass}->softdelete( $id ) ) {
                echo "{$this->modelClass} has been deleted";
            } else {
                echo "{$this->modelClass} could not be deleted";
            }
        }
    }
}