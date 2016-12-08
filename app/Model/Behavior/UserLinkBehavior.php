<?php
App::uses('ModelBehavior', 'Model');

class UserLinkBehavior extends ModelBehavior {
    var $user_id_key = 'Auth.User.id';

    public function setup( &$model, $settings ) {
        if( isset( $settings['user_id_key'] ) ) {
            $this->user_id_key = $settings['user_id_key'];
        }
    }

    public function beforeSave( &$model ) {
        App::uses( 'CakeSession', 'Model/Datasource' );
        $logged_user_id = CakeSession::read( $this->user_id_key );
        if( isset( $logged_user_id ) ) {
            $this->setUserOnCurrentModel( $model, $logged_user_id );
        }
        return true;
    }

    private function setUserOnCurrentModel( &$model, $logged_user_id ) {
        if( isset( $logged_user_id ) ) {
            if (!empty($model->id)) {
                $model->data[$model->alias][$model->primaryKey] = $model->id;
            }
            if( $model->hasField( 'created_by' ) && ( !isset( $model->data[$model->alias]['id'] ) || empty( $model->data[$model->alias]['id'] ) ) ) {
                if( !isset( $model->data[$model->alias]['created_by'] ) ) {
                    $model->data[$model->alias]['created_by'] = $logged_user_id;
                    if( !empty( $model->whitelist ) && !in_array( 'created_by', $model->whitelist ) ) {
                        $model->whitelist[] = 'created_by';
                    }
                }
            }
            if( $model->hasField( 'modified_by' ) && isset( $model->data[$model->alias]['id'] ) && !empty( $model->data[$model->alias]['id'] ) ) {
                $model->data[$model->alias]['modified_by'] = $logged_user_id;
                if( !empty( $model->whitelist ) && !in_array( 'modified_by', $model->whitelist ) ) {
                    $model->whitelist[] = 'modified_by';
                }
            }
        }
    }
}