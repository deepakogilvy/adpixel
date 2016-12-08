<?php

App::uses( 'AppModel', 'Model' );

class Role extends AppModel {

    public function auditPrimaryLabel() {
        return "{$this->data[$this->alias]['role_name']}";
    }
}