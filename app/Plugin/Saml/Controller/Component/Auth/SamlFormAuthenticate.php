<?php
App::uses('FormAuthenticate', 'Controller/Component/Auth');
class SamlFormAuthenticate extends FormAuthenticate {
    private $path = NULL;
    private $authSource = 'default-sp';
    private $as;

    public function __construct( ComponentCollection $collection, array $settings, string $authSource = NULL ) {
        if( Configure::read( 'Saml.SimpleSamlPath' ) != NULL ) {
            $this->path = Configure::read( 'Saml.SimpleSamlPath' );
        } else {
            throw new Exception( 'Parameter Saml.SimpleSamlPath is missing from the configuration file.' );
        }
        
        if( !is_null( $authSource ) ) {
            $this->authSource = $authSource;
        } elseif( !is_null( Configure::read( 'Saml.AuthSource' ) ) ) {
            $this->authSource = Configure::read( 'Saml.AuthSource' );
        }
        
        require_once($this->path.'/lib/_autoload.php');
        $this->as = new SimpleSAML_Auth_Simple($this->authSource);
    }

    public function authenticate(CakeRequest $request, CakeResponse $response) {
        $userModel = $this->settings['userModel'];
        list(, $model) = pluginSplit($userModel);

        if( $this->as->isAuthenticated() ) {
            $user = $this->as->getAttributes();
        } else {
            $fields = $this->settings['fields'];
            if( !$this->_checkFields( $request, $model, $fields ) ) {
                return false;
            }

            $user = $this->_findUser( $request->data[$model][$fields['username']], $request->data[$model][$fields['password']] );
        }

        if( !$user ) {
            return false;
        }
        return $user;
    }

    public function logout($user) {
        if( $this->as->isAuthenticated() ) {
            $this->as->logout();
        }
    }
}