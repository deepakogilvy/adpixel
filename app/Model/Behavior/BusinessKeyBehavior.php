<?php

App::uses( 'ModelBehavior', 'Model' );

/**
 * This behavior perform's 2 task.
 * 1. Manages incremetal revisions of records. 
 *    Only single version of record is active at any given time. Old records are marked as is_active NULL.
 * 2. Generate history based find-sql so that reports can be generated based 
 *    on past version of data.
 * 3. Model should have a column with name "business_key VARCHAR(100) NOT NULL" 
 *    and "is_active INT(NULL)". 
 *    This 2 columns togather should have a unique index. 
 *
 * 
 * @todo Model save is not setting auto setting created_by, created. Need to fix this.
 * @copyright (c) 2015, ogilvy.com
 * @author Tushar Takkar <tushar.takkar@ogilvy.com>
 * @example behavior 
 * class MyModel extends AppModel {
 *   public $actsAs = [ 
 *            'BusinessKey' => [ 
 *                  'business_columns' => ['created_by', 'funding_type', 'market_id', 'month', 'property_id', 'year' ], 
 *                  'system_columns' => ['created_by', 'modified_by', 'created', 'modified']     
 *             ]
 *          ];
 *
 * }
 */
class BusinessKeyBehavior extends ModelBehavior {

    public $defaults = [
        'business_columns' => [ ],
        'data_columns' => [ ],
        'system_columns' => ["is_confirm" => 1, "previous_version_id" => 1, "is_active" => 1, "modified_by" => 1, "created" => 1, "modified" => 1 ]
    ];

    /**
     * All model columns which are used for generating business column value
     * @var array 
     */
    private $businessColumns = [ ];

    /**
     * Model Column name used as business key.
     * @var string 
     */
    private $businessKeyName = "business_key";

    /**
     * Model columns which will be ingnored while computing diff. 
     * @var array 
     */
    private $systemColumns = [ ];
    //array( "is_confirm" => 1, "previous_version_id" => 1, "is_active" => 1, "modified_by" => 1, "created" => 1, "modified" => 1 )
    /**
     * Model Column name used as active column.
     * @var string 
     */
    private $isActiveColumn = "is_active";
    private $dataColumns = [ ];

    /**
     * This method initialises model behavior.
     * 
     * @author Tushar Takkar <tushar.takkar@ogilvy.com>
     * @param object $model
     * @param array $settings
     */
    public function setup( &$model, $settings ) {
        if( !isset( $this->settings[$model->alias] ) ) {
            $this->settings[$model->alias] = $this->defaults;
        }
        if( isset( $settings['business_columns'] ) ) {
            $this->settings[$model->alias]['business_columns'] = array_flip( $settings['business_columns'] );
        }
        if( isset( $settings['system_columns'] ) ) {
            $this->settings[$model->alias]['system_columns'] = array_flip( $settings['system_columns'] );
        }
        if( isset( $settings['data_columns'] ) ) {
            $this->settings[$model->alias]['data_columns'] = array_flip( $settings['data_columns'] );
        }

        $businessColumns = $this->settings[$model->alias]['business_columns'];
        $systemColumns = $this->settings[$model->alias]['system_columns'];
        $dataColumns = $this->settings[$model->alias]['data_columns'];
    }

    /**
     * This method computes diff, if record is modified then invalidates 
     * curret record and create new record.
     * 
     * @author Tushar Takkar <tushar.takkar@ogilvy.com>
     * @param object $model
     * @return boolean
     * @throws Exception
     */
    public function beforeSave( &$model, $options = [ ] ) {
        $businessColumns = $this->settings[$model->alias]['business_columns'];
        $systemColumns = $this->settings[$model->alias]['system_columns'];
        $dataColumns = $this->settings[$model->alias]['data_columns'];

        if( empty( $businessColumns ) ) {
            throw new Exception( sprintf( 'Please configure "business columns" for model["%s"] behaviour', $model->alias ) );
        }
        $columns = array_keys( $businessColumns );
        if( !$model->hasField( $columns ) ) {
            throw new Exception( sprintf( 'Some of "business columns" for model["%s"] are missing, model should have columns(%s)', $model->alias, implode( ", ", $columns ) ) );
        }
        if( !$model->hasField( $this->isActiveColumn ) ) {
            throw new Exception( sprintf( 'Column "%s" should be present in model["%s"] ', $this->isActiveColumn, $model->alias ) );
        }
        if( !empty( $model->id ) ) {
            $model->data[$model->alias][$model->primaryKey] = $model->id;
        }

        // Allow row to be deleted.
        if( array_key_exists( $this->isActiveColumn, $model->data[$model->alias] ) ) {
            if( is_null( $model->data[$model->alias][$this->isActiveColumn] ) ) {
                return true;
            }
        }

        // if deleted then retun true;
        if( isset( $model->data[$model->alias] ) && isset( $model->data[$model->alias]['is_deleted'] ) && $model->data[$model->alias]['is_deleted'] == 1 ) {
            return true;
        }

        $old = $new = array();
        $model->data[$model->alias][$this->isActiveColumn] = 1;
        $findOldFromBusinessKey = !array_diff_key( $businessColumns, $model->data[$model->alias] );
        $previousVersionID = null;
        $oldRecord = [ ];
        if( isset( $model->data[$model->alias][$model->primaryKey] ) && !empty( $model->data[$model->alias][$model->primaryKey] ) ) {
            // If primary Key is set.
            $oldRecord = $old = current( $model->findById( $model->data[$model->alias][$model->primaryKey] ) );
            if( !is_array( $old ) ) {
                $oldRecord = $old = [ ];
            }
            if( empty( $oldRecord ) ) {
                $queryResult = $model->query( "select {$model->alias}.* from {$model->useTable} AS {$model->alias} where {$model->alias}.{$model->primaryKey}={$model->data[$model->alias][$model->primaryKey] } LIMIT 1" );
                if( !empty( $queryResult ) && $queryResult[0][$model->alias]['is_deleted'] == 1 ) {
                    return true;
                }
            }
            if( isset( $old[$model->primaryKey] ) ) {
                $previousVersionID = $old[$model->primaryKey];
                unset( $old[$model->primaryKey] );
            }
        } elseif( $findOldFromBusinessKey ) {
            // Compute old from business key
            $businessKeyValue = array_intersect_key( $model->data[$model->alias], $businessColumns );
            ksort( $businessKeyValue );
            $businessKeyValue = implode( "", $businessKeyValue );
            $model->data[$model->alias][$this->businessKeyName] = $businessKeyValue;
            $old = $model->find( 'first', array( 'conditions' => array( $this->businessKeyName => $businessKeyValue, $this->isActiveColumn => 1 ) ) );
            if( !is_array( $old ) ) {
                $oldRecord = $old = [ ];
            }
            if( isset( $old[$model->alias] ) ) {
                $oldRecord = $old = $old[$model->alias];
                if( isset( $old[$model->primaryKey] ) ) {
                    $previousVersionID = $old[$model->primaryKey];
                    unset( $old[$model->primaryKey] );
                }
            }
        }
        if( isset( $model->oldRecord ) ) {
            $model->oldRecord = $oldRecord;
        }
        $new = array_merge( $old, $model->data[$model->alias] );
        if( !array_diff_key( $businessColumns, $new ) ) {
            if( !empty( $oldRecord ) && isset( $oldRecord['created_by'] ) ) {
                $new['created_by'] = $oldRecord['created_by'];
            }
            $businessKeyValue = array_intersect_key( $new, $businessColumns );
            ksort( $businessKeyValue );
            $model->data[$model->alias][$this->businessKeyName] = $new[$this->businessKeyName] = implode( "", $businessKeyValue );

            // Compute if ID exists.
            if( !empty( $old ) ) {
                // check if record is modified.
                $systemColumnValues = array_intersect_key( $new, $systemColumns );
                $newCompare = $new = array_diff_key( $new, $systemColumns + array( $model->primaryKey => 1 ) );
                $oldCompare = $old = array_diff_key( $old, $systemColumns + array( $model->primaryKey => 1 ) );
                if( !empty( $dataColumns ) ) {
                    $oldCompare = array_intersect_key( $oldCompare, $dataColumns );
                    $newCompare = array_intersect_key( $newCompare, $dataColumns );
                }
                if( $newCompare != $oldCompare ) {
                    // mark current active as null. 
                    $updated_records = $model->find( 'list', ['fields' => ["{$model->alias}.{$model->primaryKey}", "{$model->alias}.{$model->primaryKey}" ],
                        'conditions' => array(
                            $this->businessKeyName => [ $old[$this->businessKeyName], $new[$this->businessKeyName] ]
                            , $this->isActiveColumn => 1
                        )
                            ] );
                    if( !is_array( $updated_records ) ) {
                        $updated_records = [ ];
                    }
                    if( !empty( $updated_records ) and is_array( $updated_records ) ) {
                        $model->updateAll(
                                array( $this->isActiveColumn => NULL ), array( "{$model->alias}.{$model->primaryKey}" => $updated_records )
                        );
                    }
                    //create new record.
                    App::uses( 'CakeSession', 'Model/Datasource' );
                    $logged_user_id = CakeSession::read( $this->user_id_key );
                    $userID = 0;
                    if( is_array( $logged_user_id ) && isset( $logged_user_id['Auth'] ) && isset( $logged_user_id['Auth']['User'] ) && isset( $logged_user_id['Auth']['User']['id'] ) ) {
                        $userID = $logged_user_id['Auth']['User']['id'];
                    }
                    $model->id = null;
                    $model->create();
                    $model->data[$model->alias] = $new;
                    $model->data[$model->alias]['replaced_records'] = (is_array( $updated_records ) ? implode( ",", $updated_records ) : "");
                    $model->data[$model->alias]['is_active'] = 1;
                    if( !empty( $oldRecord ) && isset( $oldRecord['created_by'] ) ) {
                        $model->data[$model->alias]['created_by'] = $oldRecord['created_by'];
                        $model->data[$model->alias]['created'] = $oldRecord['created'];
                        $model->data[$model->alias]['modified_by'] = $userID;
                        $model->data[$model->alias]['modified'] = $systemColumnValues['modified'];
                    } else {
                        $model->data[$model->alias]['created_by'] = $userID;
                        $model->data[$model->alias]['created'] = $systemColumnValues['modified'];
                    }
                    $model->data[$model->alias]['previous_version_id'] = $previousVersionID;
                } else {
                    // transform current request as insert...
                    if( isset( $model->data[$model->alias]['created'] ) ) {
                        unset( $model->data[$model->alias]['created'] );
                        unset( $model->data[$model->alias]['created_by'] );
                    }
                    $model->exists = true;
                    $model->id = $oldRecord[$model->primaryKey];
                    if( !empty( $previousVersionID ) && $previousVersionID != $model->id ) {
                        $model->data[$model->alias]['previous_version_id'] = $previousVersionID;
                    }

                    $arrayUnique = array_unique( [ $old[$this->businessKeyName], $new[$this->businessKeyName] ] );
                    $updated_records = $model->find( 'list', ['fields' => ["{$model->alias}.{$model->primaryKey}", "{$model->alias}.{$model->primaryKey}" ],
                        'conditions' => array(
                            $this->businessKeyName => $arrayUnique
                            , $this->isActiveColumn => 1
                            , " {$model->primaryKey} != " => $oldRecord[$model->primaryKey]
                        )
                            ] );
                    if( !is_array( $updated_records ) ) {
                        $updated_records = [ ];
                    }
                    if( !empty( $updated_records ) and is_array( $updated_records ) ) {
                        $model->updateAll(
                                array( $this->isActiveColumn => NULL ), array( "{$model->alias}.{$model->primaryKey}" => $updated_records )
                        );
                    }

                    if( isset( $oldRecord['replaced_records'] ) && !empty( $oldRecord['replaced_records'] ) ) {
                        $rr = explode( ",", $oldRecord['replaced_records'] );
                        $updated_records = array_unique( array_merge( $updated_records, $rr ) );
                    }
                    $model->data[$model->alias]['replaced_records'] = (is_array( $updated_records ) ? implode( ",", $updated_records ) : "");
                }
            }
        } else {
            $message = sprintf( "Record to be saved dosent contain fields %s", implode( ", ", array_keys( array_diff_key( $businessColumns, $new ) ) ) );
            throw new Exception( $message );
        }
        return true;
    }

    /**
     * This method returns difference by comparing record with its last version 
     * before modification.
     * SAMPLE GENERATDE SQL
     * SELECT IF(old.id != Actual.id,CONCAT(old.id,'->',Actual.id),'') AS id, 
     * IF(old.market != Actual.market,CONCAT(old.market,'->',Actual.market),'') AS market, 
     * IF(old.market_id != Actual.market_id,CONCAT(old.market_id,'->',Actual.market_id),'') AS market_id, 
     * IF(old.media_owner != Actual.media_owner,CONCAT(old.media_owner,'->',Actual.media_owner),'') AS media_owner, 
     * IF(old.media_owner_id != Actual.media_owner_id,CONCAT(old.media_owner_id,'->',Actual.media_owner_id),'') AS media_owner_id, 
     * IF(old.property != Actual.property,CONCAT(old.property,'->',Actual.property),'') AS property, 
     * IF(old.property_id != Actual.property_id,CONCAT(old.property_id,'->',Actual.property_id),'') AS property_id, 
     * IF(old.media_class != Actual.media_class,CONCAT(old.media_class,'->',Actual.media_class),'') AS media_class, 
     * IF(old.digital_channel != Actual.digital_channel,CONCAT(old.digital_channel,'->',Actual.digital_channel),'') AS digital_channel, 
     * IF(old.year != Actual.year,CONCAT(old.year,'->',Actual.year),'') AS year, 
     * IF(old.month != Actual.month,CONCAT(old.month,'->',Actual.month),'') AS month, 
     * IF(old.funding_type != Actual.funding_type,CONCAT(old.funding_type,'->',Actual.funding_type),'') AS funding_type, 
     * IF(old.currency != Actual.currency,CONCAT(old.currency,'->',Actual.currency),'') AS currency, 
     * IF(old.spend != Actual.spend,CONCAT(old.spend,'->',Actual.spend),'') AS spend, 
     * IF(old.is_active != Actual.is_active,CONCAT(old.is_active,'->',Actual.is_active),'') AS is_active, 
     * IF(old.business_key != Actual.business_key,CONCAT(old.business_key,'->',Actual.business_key),'') AS business_key 
     * FROM `gbm`.`actuals` AS `Actual` 
     * left JOIN `gbm`.`actuals` AS `old` ON (`old`.`business_key` = `Actual`.`business_key`) 
     * WHERE `old`.`id` < `Actual`.`id` AND `Actual`.`id` = 9 ORDER BY `old`.`id` desc LIMIT 1
     *  
     * 
     * @author Tushar Takkar <tushar.takkar@ogilvy.com>
     * @param object $model
     * @param int $id
     * @return array
     */
    public function diff( $model, $id ) {
        $businessColumns = $this->settings[$model->alias]['business_columns'];
        $systemColumns = $this->settings[$model->alias]['system_columns'];
        $dataColumns = $this->settings[$model->alias]['data_columns'];

        $fields = array();
        foreach( array_diff_key( $model->schema(), $systemColumns ) as $columnName => $columnInfo ) {
            $fields[] = "IF(old.{$columnName} != {$model->alias}.$columnName,CONCAT(old.$columnName,'->',{$model->alias}.$columnName),'') AS {$columnName}";
        }
        return $model->find( 'first', array(
                    'joins' => array(
                        array(
                            'table' => $model->table,
                            'alias' => 'old',
                            'type' => 'left',
                            'conditions' => array(
                                "old.{$this->businessKeyName} = {$model->alias}.{$this->businessKeyName}"
                            )
                        )
                    ),
                    'conditions' => array(
                        "old.{$model->primaryKey} < {$model->alias}.{$model->primaryKey}",
                        "{$model->alias}.{$model->primaryKey}" => $id
                    ),
                    'fields' => $fields,
                    'order' => "old.{$model->primaryKey} desc",
                    'limit' => 1
                        )
        );
    }

    /**
     * This callback method modifies $query based on timeshot_conditions. 
     * Specify a condition based on datetime. 
     * $options['timeshot_conditions']=array(
     *      'year'=>2015, 
     *      'month <=' => 4,
     *      'created <='=> '2015-05-30 01:01:01'
     * );
     * 
     * SAMPLE GENERATED SQL:
     * SELECT 
     * `Actual`.`id`, `Actual`.`market`, `Actual`.`market_id`, `Actual`.`media_owner`, 
     * `Actual`.`media_owner_id`, `Actual`.`property`, `Actual`.`property_id`, `Actual`.`media_class`, 
     * `Actual`.`digital_channel`, `Actual`.`year`, `Actual`.`month`, `Actual`.`funding_type`, 
     * `Actual`.`currency`, `Actual`.`spend`, `Actual`.`is_active`, `Actual`.`created_by`, 
     * `Actual`.`modified_by`, `Actual`.`created`, `Actual`.`modified`, `Actual`.`business_key` 
     *  FROM `gbm`.`actuals` AS `Actual` INNER JOIN 
     * ( 
     *   SELECT max(id) as id FROM actuals WHERE 
     *    `year` = 2015 AND `month` <= '4' AND `created` <= '2015-05-30 01:01:01' 
     *    GROUP BY business_key 
     * ) AS `ActualTimeshot` ON (`ActualTimeshot`.`id` = `Actual`.`id`) 
     * WHERE `market_id` = 1 AND `year` = 2015 ORDER BY `created` DESC

     * 
     * @param object $model
     * @param array $query
     * @return array
     */
    public function beforeFind( $model, $query ) {
        $businessColumns = $this->settings[$model->alias]['business_columns'];
        $systemColumns = $this->settings[$model->alias]['system_columns'];
        $dataColumns = $this->settings[$model->alias]['data_columns'];


        if( isset( $query['timeshot_conditions'] ) ) {
            if( empty( $businessColumns ) ) {
                throw new Exception( sprintf( 'Please configure "business columns" for model["%s"] behaviour', $model->alias ) );
            }
            $columns = array_keys( $businessColumns );
            if( !$model->hasField( $columns ) ) {
                throw new Exception( sprintf( 'Some of "business columns" for model["%s"] are missing, model should have columns(%s)', $model->alias, implode( ", ", $columns ) ) );
            }
            if( !$model->hasField( $this->businessKeyName ) ) {
                throw new Exception( sprintf( 'Column "%s" should be present in model["%s"] ', $this->businessKeyName, $model->alias ) );
            }
            if( !$model->hasField( $this->isActiveColumn ) ) {
                throw new Exception( sprintf( 'Column "%s" should be present in model["%s"] ', $this->isActiveColumn, $model->alias ) );
            }
            $db = $model->getDataSource();

            if( !isset( $query['joins'] ) ) {
                $query['joins'] = array();
            }
            $query['joins'][] = array(
                'table' => "(
                    SELECT max({$model->primaryKey}) as {$model->primaryKey} FROM {$model->table}
                    " . $db->conditions( $query['timeshot_conditions'], true, true, $model ) . "
                    GROUP BY {$this->businessKeyName}
                    )",
                'alias' => "{$model->alias}Timeshot",
                'type' => 'INNER',
                'conditions' => array(
                    "{$model->alias}Timeshot.{$model->primaryKey} = {$model->alias}.{$model->primaryKey}"
                )
            );
        }
        // Read previous version of record when requested.
        if( $model->hasField( 'previous_version_id' ) && stripos( json_encode( $query['fields'] ), 'PreviousVersion' ) != false ) {
            if( !isset( $query['joins'] ) ) {
                $query['joins'] = array();
            }
            if( empty( $query['fields'] ) ) {
                $query['fields'][] = "{$model->alias}.*";
            }
            $query['fields'][] = 'PreviousVersion.*';
            $query['joins'][] = array(
                'table' => $model->table,
                'alias' => 'PreviousVersion',
                'type' => 'left',
                'conditions' => array(
                    " PreviousVersion.{$model->primaryKey} = {$model->alias}.previous_version_id"
                )
            );
        }

        return $query;
    }

}
