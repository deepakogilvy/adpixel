<?php

App::uses( 'ModelBehavior', 'Model' );

class DeletedBehavior extends ModelBehavior {

    public $mapMethods = array( '/findDeleted/' => 'findDeleted', '/findNon_deleted/' => 'findNonDeleted', );

    public function beforeFind( Model $model, $query ) {
        $deleteCol = 'is_deleted';
        if( $model->hasField( $deleteCol ) ) {
            if( isset( $query['conditions']['is_deleted'] ) ) {
                return true;
            } else {
                $query['conditions']["{$model->alias}.is_deleted"] = 0;
                return $query;
            }
        }
        return true;
    }

    public function setup( Model $model, $config = array() ) {
        $model->findMethods['deleted'] = true;
        $model->findMethods['non_deleted'] = true;
    }

    public function findDeleted( Model $model, $functionCall, $state, $query, $results = array() ) {
        if( $state == 'before' ) {
            if( empty( $query['conditions'] ) ) {
                $query['conditions'] = array();
            }
            $query['conditions']["{$model->alias}.deleted <>"] = null;
            return $query;
        }
        return $results;
    }

    public function findNonDeleted( Model $model, $functionCall, $state, $query, $results = array() ) {
        if( $state == 'before' ) {
            if( empty( $query['conditions'] ) ) {
                $query['conditions'] = array();
            }
            $query['conditions']["{$model->alias}.deleted"] = null;
            return $query;
        }
        return $results;
    }

    public function softdelete( Model $model, $id = null ) {
        if( $id ) {
            $model->id = $id;
        }
        if( !$model->id ) {
            return false;
        }
        $deleteCols = array( 'is_deleted', 'deleted' );
        foreach( $deleteCols as $deleteCol ) {
            if( !$model->hasField( $deleteCol ) ) {
                return false;
            }
        }
        $db = $model->getDataSource();
        $now = time();
        $default = array( 'formatter' => 'date' );
        $colType = array_merge( $default, $db->columns[$model->getColumnType( $deleteCols[1] )] );
        $time = $now;
        if( array_key_exists( 'format', $colType ) ) {
            $time = call_user_func( $colType['formatter'], $colType['format'] );
        }
        foreach( $deleteCols as $deleteCol ) {
            if( !empty( $model->whitelist ) ) {
                $model->whitelist[] = $deleteCol;
            }
        }
        $model->set( $deleteCols[0], 1 );
        $return[$deleteCols[0]] = $model->saveField( $deleteCols[0], 1 );
        $model->set( $deleteCols[1], $time );
        $return[$deleteCols[1]] = $model->saveField( $deleteCols[1], $time );
        if( $model->hasField( 'is_active' ) ) {
            $model->set( 'is_active', NULL );
            $return['is_active'] = $model->saveField( 'is_active', NULL );
        }
        return $return;
    }

    public function undelete( Model $model, $id = null ) {
        if( $id ) {
            $model->id = $id;
        }
        if( !$model->id ) {
            return false;
        }
        $deleteCols = array( 'is_deleted', 'deleted' );
        foreach( $deleteCols as $deleteCol ) {
            if( !$model->hasField( $deleteCol ) ) {
                return false;
            }
        }
        foreach( $deleteCols as $deleteCol ) {
            $model->set( $deleteCol, null );
            $return[$deleteCol] = $model->saveField( $deleteCol, null );
        }
        return $return;
    }

}
