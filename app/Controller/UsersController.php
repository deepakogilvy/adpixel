<?php
App::uses( 'AppController', 'Controller' );

class UsersController extends AppController {

    public $name = 'Users';
    public $uses = [ 'User', 'Role', 'Pages', 'ValidationLog', 'Campaign', 'ProcessId' ];
    public $current_user = false;
    public $components = [ 'TableSearch' ];

    public function beforeFilter() {
        parent::beforeFilter();
        $this->Auth->allow( 'login', 'oops', 'acs', 'slogin', 'getPages', 'postPages' );
        $this->Auth->authError = 'Please login to continue.';
    }

    public function index( $param ) {

    }

    public function logout( $error = null ) {
        $this->Session->write( 'Core', false );
        $this->Session->write( 'Auth', false );
        $this->Session->destroy();
        $this->Auth->logout();
        $this->redirect( "/" );
    }

    public function login() {
        $this->layout = 'login';
        if( $this->request->is( 'post' ) ) {
            $user = $this->User->find( 'first', array(
                'conditions' => array( 'email' => strtolower( $this->request->data['email'] ), 'password' => md5( $this->request->data['password'] ), 'User.is_active' => 1 ) ) );
            if( count( $user ) > 0 ) {
                $this->Session->write( 'Auth', $user );
                $this->User->save( [ 'id' => $this->Session->read( 'Auth.User.id' ), 'last_login' => date( "Y-m-d H:i:s" ) ] );
                $this->redirect( [ 'controller' => 'campaigns', 'action' => 'index' ] );
            } else {
                $this->redirect( array( 'action' => 'oops', 'notfound' ) );
            }
        }
    }

    public function oops( $page ) {
        $this->layout = 'error';
        $this->render( $page );
    }

    public function ajaxData() {
        $this->autoRender = false;
        $query = [
            'fields' => [ 'name', 'email', 'Role.role_name', 'is_active', 'id' ]
        ];
        if( $this->Session->read( 'Auth.User.role_id' ) > 0 ) {
            $query['conditions']['role_id >'] = $this->Session->read( 'Auth.User.role_id' );
        }
        $conditions = '';

        $sortByColumns = [ 'name', 'email', 'Role.role_name', 'is_active' ];
        $searchWordMap = [ "is_active" => [ 'active' => 1, 'in-active' => 0, 'inactive' => 0, 'in active' => 0 ] ];
        $output = $this->TableSearch->data( $query, $sortByColumns, $searchWordMap, '', $conditions );
        $data = $output['aaData'];
        $output['aaData'] = [ ];
        $modelClass = $this->modelClass;
        $isDeletable = $this->isAllowed( $this->request->controller, 'delete' );
        if( $data ) {
            foreach( $data as $d ) {
                $deleted = '';
                if( $isDeletable ) {
                    $deleted = '<a data-target="#deletePopup" data-toggle="modal"   data-original-title="Delete ' . $this->modelClass . '" type="button" data-id="' . $d[$modelClass]["id"] . '" class="btn btn-xs btn-danger delete-record glyphicon glyphicon-remove js-tt" ></a>';
                }
                $temp = [ ];
                $temp[] = $d[$modelClass]['name'];
                $temp[] = $d[$modelClass]['email'];
                $temp[] = $d['Role']['role_name'];

                if( $d[$modelClass]['is_active'] ) {
                    $temp[] = '<span id="label_' . $d[$modelClass]['id'] . '"class="label label-success">Active</span>';
                } else {
                    $temp[] = '<span id="label_' . $d[$modelClass]['id'] . '"class="label label-danger">Inactive</span>';
                }
                $temp[] = '<div class="btn-group">'
                        . '<a href="' . Router::url( [ 'controller' => $this->request->controller, 'action' => 'edit', $d[$modelClass]["id"] ] ) . '" class="btn btn-xs btn-default glyphicon glyphicon-pencil js-tt" type="button" data-toggle="tooltip" title="" data-original-title="Edit ' . $this->modelClass . '"></a>'
                        . '<a class="btn btn-xs btn-default setStatus fa fa-exclamation js-tt" data-controller="' . $this->request->controller . '" rel="' . $d[$modelClass]['is_active'] . '" id="' . $d[$modelClass]["id"] . '" type="button" data-toggle="tooltip" title="" data-original-title="Change Status"></a>'
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
                echo "{$this->modelClass} deleted.";
            } else {
                echo "{$this->modelClass} could not be deleted.";
            }
        }
    }

    public function acs() {
        $data = $this->request->data;
        if( $data ) {
            $xml = simplexml_load_string( base64_decode( $data['SAMLResponse'] ) );
            $node = $xml->children( 'saml', true )->Assertion->children( 'saml', true )->AttributeStatement->children( 'saml', true )->Attribute->children( 'saml', true )->AttributeValue;
            $email = trim( $node->{0} );
            $user = $this->User->find( 'first', [ 'conditions' => [ 'email' => strtolower( $email ) ] ] );
            if( count( $user ) > 0 ) {
                if( $user['User']['is_active'] == 1 ) {
                    $this->Session->write( 'Auth', $user );
                    $this->User->save( [ 'id' => $this->Session->read( 'Auth.User.id' ), 'last_login' => date( "Y-m-d H:i:s" ) ] );
                    $this->redirect( [ 'controller' => 'home', 'action' => 'index' ] );
                } else {
                    $this->redirect( [ 'action' => 'oops', 'inactive' ] );
                }
            } else {
                $this->redirect( [ 'action' => 'oops', 'notfound' ] );
            }
        } else {
            $this->redirect( Configure::read( 'loginUrl' ) );
        }
    }

    public function validEmail( $email = "" ) {
        if( empty( $email ) ) {
            $email = $this->request->query['email'];
            $id = $this->request->query['id'];
            $this->layout = 'ajax';
            $this->autoRender = false;
            $conditions = [ 'email' => $email, 'User.id !=' => $id ];
        } else {
            $this->layout = 'ajax';
            $this->autoRender = false;
            $conditions = [ 'email' => $email ];
        }

        $aliases = $this->User->find( 'count', [ 'conditions' => $conditions ] );
        echo ( $aliases == 0 ) ? 'YES' : 'NO';
        exit;
    }

    public function slogin( $email ) {
        $user = $this->User->find( 'first', array( 'conditions' => array( 'email' => strtolower( $email ) ) ) );
        if( count( $user ) > 0 ) {
            if( $user['User']['is_active'] == 1 ) {
                $this->Session->write( 'Auth', $user );
                $this->User->save( [ 'id' => $this->Session->read( 'Auth.User.id' ), 'last_login' => date( "Y-m-d H:i:s" ) ] );
                $this->redirect( [ 'controller' => 'campaigns', 'action' => 'index' ] );
            } else {
                $this->redirect( [ 'action' => 'oops', 'inactive' ] );
            }
        } else {
            $this->redirect( array( 'action' => 'oops', 'notfound' ) );
        }
    }

    public static function UUID() {
        return md5( microtime() . rand() );
    }

    public function getPages() {
        $this->autoRender = false;
        $option = [ [ 'type' => 'left', 'table' => 'process_ids', 'alias' => 'pids', 'conditions' => [ 'campaign.id = pids.campaign_id' ] ] ];
        $campaigns = $this->Campaign->find( 'first', [ 'fields' => [ 'id' ], 'conditions' => [ 'is_deleted' => 0, 'start_date <=' => date('Y-m-d'), 'end_date >=' => date('Y-m-d'), 'FIND_IN_SET("' . date('N') . '",validation_week_days)', 'OR' => [ 'pids.created !=' => date('Y-m-d'), 'pids.created' => NULL ] ], 'joins' => $option ] );

        if( !empty( $campaigns ) ) {
            $this->ProcessId->saveAll( [ 'campaign_id' => $campaigns['Campaign']['id'], 'process_id' => $this->UUID(), 'created' => date('Y-m-d') ] );

            $links = $this->Pages->find( 'all', [ 'fields' => [ 'id', 'pixel_code', 'url', 'row_data'], 'conditions' => [ 'campaign_id' => $campaigns['Campaign']['id'], 'is_deleted' => 0 ] ] );
            $links = json_encode( $links );
            return $links;
        }
    }

    public function postPages() {
        $this->autoRender = false;
        $data = $_POST['data1'];
        $data = json_decode( $data, true );

        $requiredData = array();
        $i = 0;
        foreach ( $data as $pageId => $pageStatus ) {
            $requiredData[$i]['page_id'] = $pageId;
            $requiredData[$i]['date'] = date('Y-m-d');
            $requiredData[$i]['status'] = $pageStatus;
            $i++;
        }

        $this->ValidationLog->saveAll( $requiredData );
    }

    private function allowedRoles() {
        $roles = $this->Role->find( 'list', [ 'fields' => [ 'id', 'role_name' ], 'conditions' => ['is_active' => 1 ] ] );
        return $roles;
    }

    public function add() {
        if( $data = $this->request->data ) {
            if( $this->User->findByEmail( $data['email'] ) ) {
                $this->Session->setFlash( __( 'User with email ' . $data['email'] . ' already exists!' ), 'default', [ 'class' => 'danger' ] );
                $this->set( 'data', $data );
            } else {
                $this->User->create();
                $this->User->save( $data );
                $this->Session->setFlash( __( 'User created.' ), 'default', [ 'class' => 'success' ] );
                $this->redirect( [ 'controller' => 'users', 'action' => 'index' ] );
            }
        }
        $roles = $this->allowedRoles();
        $this->set( 'roles', $roles )->set( 'userRole', $this->session_user['role_id'] );
    }

    public function edit( $id = NULL ) {
        if( $data = $this->request->data ) {
            $data['reload'] = 1;
            $data['is_active'] = (bool) $data['is_active'];
            $this->User->save( $data );
            $this->Session->setFlash( __( 'User updated.' ), 'default', [ 'class' => 'success' ] );
            $this->redirect( [ 'controller' => 'users', 'action' => 'index' ] );
        }
        $user = $this->User->findById( $id );
        $roles = $this->allowedRoles();
        $this->set( 'user', $user )->set( 'userRole', $this->session_user['role_id'] )->set( 'roles', $roles );
    }

    public function setStatus( $userId = null, $status = null ) {
        $this->layout = 'ajax';
        $status = ( $status + 1 ) % 2;
        $this->User->id = $userId;
        $this->User->save( [ 'is_active' => $status, 'reload' => 1 ] );
        echo $status;
        $this->set( 'status', $status );
        $this->set( '_serialized', $status );
    }
}