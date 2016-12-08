<?php
App::uses( 'Component', 'Controller' );
class SamlComponent extends Component {
    private $path = NULL;
    private $authSource = 'default-sp';
    private $as;

    public function __construct( ComponentCollection $collection, array $settings, string $authSource = NULL ) {
        if( !is_null( Configure::read('Saml.SimpleSamlPath') ) ) {
            $this->path = Configure::read( 'Saml.SimpleSamlPath' );
        } else {
            throw new Exception( 'Parameter Saml.SimpleSamlPath is missing from the configuration file.' );
        }
        
        if( !is_null( $authSource ) ) {
            $this->authSource = $authSource;
        } elseif( !is_null( Configure::read( 'Saml.AuthSource' ) ) ) {
            $this->authSource = Configure::read( 'Saml.AuthSource' );
        }
        require_once( $this->path.'/lib/_autoload.php' );
        $this->as = new SimpleSAML_Auth_Simple( $this->authSource );
    }

    public function getAttributes() {
        return $this->as->getAttributes();
    }
    
    public function getAuthData( $name ) {
        return $this->as->getAuthData( $name );
    }
    
    public function getLoginURL( $returnTo = null ) {
        return $this->as->getLoginURL( $returnTo );
    }

    public function getLogoutURL( $returnTo = null ) {
        return $this->as->getLogoutURL( $returnTo );
    }

    public function isAuthenticated() {
        return $this->as->isAuthenticated();
    }

    public function login( $url ) {
        $this->as->login( $url );
    }

    public function logout( $url ) {
        $this->as->logout( $url );
    }

    public function requireAuth( $params = array() ) {
        $this->as->requireAuth( $params );
    }
}