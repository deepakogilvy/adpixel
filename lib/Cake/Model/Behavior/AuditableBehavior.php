<?php

class AuditableBehavior extends ModelBehavior {

    private $_original = array();
    public $changeId = false;

    public function setup( Model $Model, $settings = array() ) {
        $ignore = array_merge( array( 'Audit' ), (array) Configure::read( 'audit.ignore' ) );
        if( !is_array( $ignore ) ) $ignore = array();
        $cModal = $Model->alias;
        if( in_array( $cModal, $ignore ) ) return true;
        if( !isset( $this->settings[$Model->alias] ) ) {
            $this->settings[$Model->alias] = array( 'ignore' => array( 'created', 'updated', 'modified' ),
                'habtm' => count( $Model->hasAndBelongsToMany ) > 0 ? array_keys( $Model->hasAndBelongsToMany ) : array()
            );
        }
        if( !is_array( $settings ) ) {
            $settings = array();
        }
        $this->settings[$Model->alias] = array_merge_recursive( $this->settings[$Model->alias], $settings );

        foreach( $this->settings[$Model->alias]['habtm'] as $index => $model_name ) {
            if( !array_key_exists( $model_name, $Model->hasAndBelongsToMany ) || ( is_array( $Model->$model_name->actsAs ) && array_search( 'Auditable', $Model->$model_name->actsAs ) === true ) ) {
                unset( $this->settings[$Model->alias]['habtm'][$index] );
            }
        }
    }

    public function beforeSave( Model $Model ) {
        $ignore = array_merge( array( 'Audit' ), (array) Configure::read( 'audit.ignore' ) );
        if( !is_array( $ignore ) ) $ignore = array();

        $cModal = $Model->alias;
        if( in_array( $cModal, $ignore ) ) return true;
        if( !empty( $Model->id ) ) {
            $this->_original[$Model->alias] = $this->_getModelData( $Model );
        }

        return true;
    }

    public function beforeDelete( Model $Model, $cascade = true ) {
        $ignore = array_merge( array( 'Audit' ), (array) Configure::read( 'audit.ignore' ) );
        if( !is_array( $ignore ) ) $ignore = array();

        $cModal = $Model->alias;
        if( in_array( $cModal, $ignore ) ) return true;
        $original = $Model->find( 'first', array( 'contain' => false, 'conditions' => array( $Model->alias . '.' . $Model->primaryKey => $Model->id ), ) );
        $this->_original[$Model->alias] = $original[$Model->alias];

        return true;
    }

    public function afterSave( Model $Model, $created ) {
        $ignore = array_merge( array( 'Audit' ), (array) Configure::read( 'audit.ignore' ) );
        if( !is_array( $ignore ) ) $ignore = array();

        $cModal = $Model->alias;
        $auditPrimaryLabel = $Model->data[$cModal][$Model->displayField];
        if( method_exists( $Model, 'auditPrimaryLabel' ) ) {
            $auditPrimaryLabel = $Model->auditPrimaryLabel();
        }
        $auditSecondaryLabelValue = '';
        $auditSecondaryLabelName = '';
        if( method_exists( $Model, 'auditSecondaryLabel' ) ) {
            $auditSecondary = $Model->auditSecondaryLabel();
            if( isset( $auditSecondary[0] ) ) {
                $auditSecondaryLabelValue = $auditSecondary[0];
            }
            if( isset( $auditSecondary[1] ) ) {
                $auditSecondaryLabelName = $auditSecondary[1];
            }
        }
        if( in_array( $cModal, $ignore ) ) return true;
        $audit = array( $Model->alias => $this->_getModelData( $Model ) );
        $audit[$Model->alias][$Model->primaryKey] = $Model->id;
        $Model->bindModel( array( 'hasMany' => array( 'Audit' ) ) );

        $source = $Model->currentUser();

        $data = array(
            'Audit' => array(
                'change_id' => $this->changeId, 'event' => $created ? 'CREATE' : 'EDIT', 'model' => $Model->alias,
                'entity_id' => $Model->id, 'role_id' => isset( $source['role_id'] ) ? $source['role_id'] * 1 : 'N.A',
                'user_id' => isset( $source['id'] ) ? $source['id'] * 1 : 'N.A',
                'user_name' => isset( $source['name'] ) ? $source['name'] : 'N.A',
                'label' => $auditPrimaryLabel,
                'secondary_label_value' => $auditSecondaryLabelValue,
                'secondary_label_name' => $auditSecondaryLabelName
            )
        );
        $updates = array();
        $delta = array();
       
        foreach( $audit[$Model->alias] as $property => $value ) {
            if( ( $Model->hasMethod( 'isVirtualField' ) && $Model->isVirtualField( $property ) ) || in_array( $property, $this->settings[$Model->alias]['ignore'] ) ) {
                continue;
            }
            if( !$created ) {
                if( array_key_exists( $property, $this->_original[$Model->alias] ) && $this->_original[$Model->alias][$property] != $value ) {
                    $updates[] = ['property_name' => $property,
                        'old_value' => is_array( $this->_original[$Model->alias][$property] ) ? json_encode( $this->_original[$Model->alias][$property] ) : $this->_original[$Model->alias][$property],
                        'new_value' => is_array( $value ) ? json_encode( $value ) : $value,
                    ];
                }
            }
        }
        if( !$created && !empty( $updates ) ) {
            $data = array(
                'Audit' => array(
                    'event' => $created ? 'CREATE' : 'EDIT',
                    'model' => $Model->alias, 'entity_id' => $Model->id,
                    'role_id' => isset( $source['role_id'] ) ? $source['role_id'] * 1 : 'N.A',
                    'user_id' => isset( $source['id'] ) ? $source['id'] * 1 : 'N.A',
                    'user_name' => isset( $source['name'] ) ? $source['name'] : 'N.A',
                    'label' => $auditPrimaryLabel,
                    'secondary_label_value' => $auditSecondaryLabelValue,
                    'secondary_label_name' => $auditSecondaryLabelName,
                    'updates' => $updates
                )
            );
            $Model->Audit->create();
            $Model->Audit->save( $data );
            if( $created ) {
                if( $Model->hasMethod( 'afterAuditCreate' ) ) {
                    $Model->afterAuditCreate( $Model );
                }
            } else {
                if( $Model->hasMethod( 'afterAuditUpdate' ) ) {
                    $Model->afterAuditUpdate( $Model, $this->_original, $updates, $Model->Audit->id );
                }
            }
        }

        if( $created ) {
            $Model->Audit->create();
            $Model->Audit->save( $data );

            if( $created ) {
                if( $Model->hasMethod( 'afterAuditCreate' ) ) {
                    $Model->afterAuditCreate( $Model );
                }
            } else {
                if( $Model->hasMethod( 'afterAuditUpdate' ) ) {
                    $Model->afterAuditUpdate( $Model, $this->_original, $updates, $Model->Audit->id );
                }
            }
        }

        $Model->unbindModel( array( 'hasMany' => array( 'Audit' ) ) );
        if( isset( $this->_original ) ) {
            unset( $this->_original[$Model->alias] );
        }
        return true;
    }

    public function afterDelete( Model $Model ) {
        $ignore = array_merge( array( 'Audit' ), (array) Configure::read( 'audit.ignore' ) );
        if( !is_array( $ignore ) ) $ignore = array();

        $cModal = $Model->alias;
        if( in_array( $cModal, $ignore ) ) return true;
        $source = $Model->currentUser();
        if( !$this->changeId ) $this->changeId = $this->createId();
        $audit = array( $Model->alias => $this->_original[$Model->alias] );
        $data = array(
            'Audit' => array(
                'change_id' => $this->changeId, 'event' => 'DELETE', 'model' => $Model->alias, 'entity_id' => $Model->id,
                'json_object' => json_encode( $audit ), 'source_id' => isset( $source['id'] ) ? $source['id'] * 1 : null
            )
        );

        $this->Audit = ClassRegistry::init( 'Audit' );
        $this->Audit->create();
        $this->Audit->save( $data );
    }

    private function _getModelData( Model $Model ) {
        $ignore = array_merge( array( 'Audit' ), (array) Configure::read( 'audit.ignore' ) );
        if( !is_array( $ignore ) ) $ignore = array();

        $cModal = $Model->alias;
        if( in_array( $cModal, $ignore ) ) return true;

        $data = $Model->find( 'first', array( 'contain' => !empty( $this->settings[$Model->alias]['habtm'] ) ? array_values( $this->settings[$Model->alias]['habtm'] ) : array(), 'conditions' => array( $Model->alias . '.' . $Model->primaryKey => $Model->id ) ) );
        if( $data ) {
            $audit_data = array( $Model->alias => $data[$Model->alias] );
        } else {
            $audit_data = array( $Model->alias => $Model->alias );
        }

        foreach( $this->settings[$Model->alias]['habtm'] as $habtm_model ) {
            if( array_key_exists( $habtm_model, $Model->hasAndBelongsToMany ) && isset( $data[$habtm_model] ) ) {
                $habtm_ids = Set::combine( $data[$habtm_model], '{n}.id', '{n}.id' );
                $habtm_ids = array_values( $habtm_ids );
                sort( $habtm_ids );
                $audit_data[$Model->alias][$habtm_model] = implode( ',', $habtm_ids );
            }
        }

        return $audit_data[$Model->alias];
    }

    public function createId( $intOnly = true ) {
        $fixed = 1440390700123456;
        $t = explode( " ", microtime() );
        $uid = ($t[1] + $t[0]) * 1000000 - $fixed;
        return ( $intOnly ) ? (int) $uid : uniqid();
    }

}
