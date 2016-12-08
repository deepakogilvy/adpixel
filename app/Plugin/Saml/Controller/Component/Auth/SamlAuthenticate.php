<?php 
App::uses('Component', 'Controller');
class SamlAuthenticate extends Component {
	public $components = array( 'Saml.Saml' );

	public function authenticate( CakeRequest $request, CakeResponse $response ) {
		return $this->Saml->getAttributes();
	}

	public function getUser() {
		return $this->Saml->getAttributes();
	}
	
	public function logout() {
		
	}
	
	public function unauthenticated( Controller $controller ) {
		return $this->Saml->isAuthenticated();
	}
}