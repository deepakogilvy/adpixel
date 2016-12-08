<?php

App::uses( 'Component', 'Controller' );
App::uses( 'MongodbSource', 'Model/Datasource' );

class TableSearchComponent extends Component {

    public $controller = null;
    public $months = [ '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec' ];

    public function initialize( Controller $controller ) {
        $this->controller = $controller;
    }

    private function formatColumn( $model, $column, $columnsExp ) {
        if( isset( $columnsExp[$column] ) ) {
            return $columnsExp[$column];
        } else if( stripos( $column, "." ) === false ) {
            return $model->alias . '.' . $column;
        } else {
            return $column;
        }
    }

    /**
     * 
     * @param Array $query 
     * @param array $sortByColumns
     * @param array $searchWordMap
     * @return array
     */
    private function formatColumnMongo( $model, $column, $columnsExp ) {
        if( isset( $columnsExp[$column] ) ) {
            return $columnsExp[$column];
        } else if( stripos( $column, "." ) === false ) {
            return $column;
        } else {
            return $column;
        }
    }

    public function data( $query, $sortByColumns, $searchWordMap = [ ], $columnsExp = [ ], $cond = [ ], $forceLikeSearchOn = [ ] ) {
        $output = $options = [ ];
        $model = $this->controller->{$this->controller->modelClass};
        $options = array_merge( $options, $query );
        if( isset( $_GET['start'] ) && $_GET['length'] != '-1' && !isset( $options['limit'] ) ) {
            $options['offset'] = intval( $_GET['start'] );
            $options['limit'] = intval( $_GET['length'] );
        }
        if( isset( $_GET['order'] ) && is_array( $_GET['order'] ) && isset( $_GET['order'][0] ) ) {
            $index = $_GET['order'][0]['column'];
            if( isset( $sortByColumns[$index] ) ) {
                $orderColumn = $this->formatColumn( $model, $sortByColumns[$index], $columnsExp );
                $orderDirection = $_GET['order'][0]['dir'];
                $options['order'] = [ $orderColumn . ' ' . $orderDirection ];
            }
        }
        $conditions = [ ];
        if( isset( $_GET['columns'] ) && is_array( $_GET['columns'] ) ) {
            if( isset( $_GET['search'] ) && !empty( $_GET['search']['value'] ) ) {
                $where = [ ];
                $searchValue = strtolower( $_GET['search']['value'] );
                $not = "";
                $notEqual = "";
                if( !empty( $searchValue ) && $searchValue{0} == "!" ) {
                    $not = " NOT ";
                    $notEqual = " != ";
                    $searchValue = substr( $searchValue, 1 );
                }
                foreach( $_GET['columns'] as $key => $columnArray ) {
                    if( isset( $columnArray['searchable'] ) && $columnArray['searchable'] == 'true' && !empty( $columnArray['name'] ) ) {
                        $columnName = $this->formatColumn( $model, $columnArray['name'], $columnsExp );
                        if( isset( $searchWordMap[$columnArray['name']] ) && is_array( $searchWordMap[$columnArray['name']] ) ) {
                            foreach( $searchWordMap[$columnArray['name']] as $searchValueKey => $searchValueValue ) {
                                if( strcasecmp( $searchValue, $searchValueKey ) == 0 ) {
                                    if( in_array( $columnArray['name'], $forceLikeSearchOn ) ) {
                                        $where[] = [ $columnName . ' ' . $not . ' LIKE ' => "%{$searchValueValue}%" ];
                                    } else {

                                        if( isset( $searchWordMap[$columnArray['name']]['alt_conditions'] ) && is_array( $searchWordMap[$columnArray['name']]['alt_conditions'] ) ) {
                                            foreach( $searchWordMap[$columnArray['name']]['alt_conditions'] as $xxx => $yyy ) {
                                                if( is_array( $yyy ) ) {
                                                    foreach( $yyy as $xxxx => $yyyy ) {
                                                        if( $yyyy === $columnArray['name'] ) {
                                                            unset( $yyy[$xxxx] );
                                                            $yyy[$xxxx . $notEqual] = $searchValueValue;
                                                        }
                                                    }
                                                    $where[] = $yyy;
                                                }
                                            }
                                        } else {
                                            $where[][$columnName . $notEqual] = $searchValueValue;
                                        }
                                    }
                                    break;
                                }
                            }
                        } else {
                            $where[] = [ $columnName . ' ' . $not . ' LIKE ' => "%{$searchValue}%" ];
                        }
                    }
                }
                if( trim( $not ) == 'NOT' ) {
                    $conditions['AND'] = $where;
                } else {
                    $conditions['OR'] = $where;
                }
            } else {
                foreach( $_GET['columns'] as $key => $columnArray ) {
                    if( isset( $columnArray['searchable'] ) && $columnArray['searchable'] == 'true' && !empty( $columnArray['name'] ) && isset( $columnArray['search']['value'] ) && !empty( $columnArray['search']['value'] ) ) {
                        $columnName = $this->formatColumn( $model, $columnArray['name'], $columnsExp );
                        $searchValue = $columnArray['search']['value'];
                        $not = "";
                        $notEqual = "";
                        if( !empty( $searchValue ) && $searchValue{0} == "!" ) {
                            $not = " NOT ";
                            $notEqual = " != ";
                            $searchValue = substr( $searchValue, 1 );
                        }
                        if( isset( $searchWordMap[$columnArray['name']] ) && is_array( $searchWordMap[$columnArray['name']] ) ) {
                            foreach( $searchWordMap[$columnArray['name']] as $searchValueKey => $searchValueValue ) {
                                if( strcasecmp( $searchValue, $searchValueKey ) == 0 ) {
                                    if( in_array( $columnArray['name'], $forceLikeSearchOn ) ) {
                                        $where[] = [ $columnName . ' ' . $not . '  LIKE ' => "%{$searchValueValue}%" ];
                                    } else {
                                        $conditions[][$columnName . $notEqual] = $searchValueValue;
                                    }
                                    break;
                                }
                            }
                        } else {
                            $conditions[] = [$columnName . ' ' . $not . ' LIKE ' => "%$searchValue%" ];
                        }
                    }
                }
            }
            if( $cond ) {
                $conditions[] = $cond;
            }
        }
        $output = [
            "draw" => intval( $_GET['draw'] ),
            "iTotalRecords" => 0,
            "iTotalDisplayRecords" => 0,
            "aaData" => [ ],
            "iTotalRecords" => false
        ];
        if( !isset( $options['conditions'] ) ) {
            $options['conditions'] = [ ];
        }
        $countByDistinct = isset( $options['count_by_distinct'] ) && !empty( $options['count_by_distinct'] ) ? 1 : 0;
        if( !empty( $conditions ) ) {
            if( $countByDistinct ) {
                $listQuery = array_merge( ['fields' => ["{$model->alias}.$model->primaryKey" ] ], array_intersect_key( $options, ['joins' => 1, 'conditions' => 1, 'group' => 1 ] ) );
                if( isset( $query['count_by_distinct_joins'] ) && !empty( $query['count_by_distinct_joins'] ) ) {
                    if( !isset( $listQuery['joins'] ) ) {
                        $listQuery['joins'] = [ ];
                    }
                    $listQuery['joins'] = array_merge( $listQuery['joins'], $query['count_by_distinct_joins'] );
                }
                $iTotalRecords = $model->find( 'list', $listQuery );
                if( !empty( $iTotalRecords ) ) {
                    $output['iTotalRecords'] = count( $iTotalRecords );
                }
            } else {
                $output['iTotalRecords'] = (int) $model->find( 'count', array_intersect_key( $options, ['joins' => 1, 'conditions' => 1, 'group' => 1 ] ) );
            }
        }
        $options['conditions'] = array_merge( $options['conditions'], $conditions );
        if( $countByDistinct ) {
            $listQuery = array_merge( ['fields' => ["{$model->alias}.$model->primaryKey" ] ], array_intersect_key( $options, ['joins' => 1, 'conditions' => 1, 'group' => 1 ] ) );
            if( isset( $query['count_by_distinct_joins'] ) && !empty( $query['count_by_distinct_joins'] ) ) {
                if( !isset( $listQuery['joins'] ) ) {
                    $listQuery['joins'] = [ ];
                }
                $listQuery['joins'] = array_merge( $listQuery['joins'], $query['count_by_distinct_joins'] );
            }
            $iTotalDisplayRecords = $model->find( 'list', $listQuery );
            if( !empty( $iTotalDisplayRecords ) ) {
                $output['iTotalDisplayRecords'] = count( $iTotalDisplayRecords );
            }
        } else {
            $output['iTotalDisplayRecords'] = (int) $model->find( 'count', array_intersect_key( $options, ['joins' => 1, 'conditions' => 1, 'group' => 1 ] ) );
        }
        if( $output['iTotalRecords'] === false ) {
            $output['iTotalRecords'] = $output['iTotalDisplayRecords'];
        }

        $data = $model->find( 'all', $options );
        if( is_array( $data ) ) {
            foreach( $data as $k => $v ) {
                $output["aaData"][$k] = $v;
            }
        }
        return $output;
    }

    public function dataAudit( $query, $sortByColumns, $searchWordMap = [ ], $columnsExp = [ ] ) {
        if( isset( $query['customConditions'] ) ) {
            $customConditions = $query['customConditions'];
            unset( $query['customConditions'] );
        } else {
            $customConditions = [ ];
        }
        $output = $options = [ ];
        $model = $this->controller->{$this->controller->modelClass};
        $options = array_merge( $options, $query );
        if( isset( $_GET['start'] ) && $_GET['length'] != '-1' && !isset( $options['limit'] ) ) {
            $options['offset'] = intval( $_GET['start'] );
            $options['limit'] = intval( $_GET['length'] );
        }
        if( isset( $_GET['order'] ) && is_array( $_GET['order'] ) && isset( $_GET['order'][0] ) ) {
            $index = $_GET['order'][0]['column'];
            if( isset( $sortByColumns[$index] ) ) {
                $orderColumn = $this->formatColumnMongo( $model, $sortByColumns[$index], $columnsExp );
                $orderDirection = $_GET['order'][0]['dir'];
                if( $orderDirection == 'asc' ) {
                    $orderDirection = 1;
                    $orderDirectionFlag = false;
                } else {
                    $orderDirection = -1;
                    $orderDirectionFlag = true;
                }
                unset( $options['order'] );
                $options['order'][$orderColumn] = $orderDirection;
            }
        }
        $conditions = [ ];
        if( isset( $_GET['columns'] ) && is_array( $_GET['columns'] ) ) {
            if( isset( $_GET['search'] ) && !empty( $_GET['search']['value'] ) ) {
                $where = [ ];
                $searchValue = $_GET['search']['value'];
                foreach( $_GET['columns'] as $key => $columnArray ) {
                    if( isset( $columnArray['searchable'] ) && $columnArray['searchable'] == 'true' && !empty( $columnArray['name'] ) ) {
                        $columnName = $columnArray['name'];
                        if( isset( $searchWordMap[$columnArray['name']] ) ) {
                            if( in_array( $searchValue, array_keys( $searchWordMap[$columnArray['name']] ) ) ) {
                                $where[][$columnName] = $searchWordMap[$columnArray['name']][$searchValue];
                            }
                        } else {
                            $where[] = [ $columnName => new MongoRegex( "/^" . $searchValue . "/i" ) ];
                        }
                    }
                }
                $conditions['$or'] = $where;
            } else {
                foreach( $_GET['columns'] as $key => $columnArray ) {
                    if( isset( $columnArray['searchable'] ) && $columnArray['searchable'] == 'true' && !empty( $columnArray['name'] ) && isset( $columnArray['search']['value'] ) && !empty( $columnArray['search']['value'] ) ) {
                        $columnName = $columnArray['name'];
                        $searchValue = $columnArray['search']['value'];
                        if( isset( $searchWordMap[$columnArray['name']] ) ) {
                            if( in_array( $searchValue, array_keys( $searchWordMap[$columnArray['name']] ) ) ) {
                                $conditions[][$columnName] = $searchWordMap[$columnArray['name']][$searchValue];
                            }
                        } else {
                            $conditions[] = [$columnName => new MongoRegex( "/^" . $searchValue . "/i" ) ];
                        }
                    }
                }
            }
        }

        $output = [
            "draw" => intval( $_GET['draw'] ),
            "iTotalRecords" => 0,
            "iTotalDisplayRecords" => 0,
            "aaData" => [ ],
            "iTotalRecords" => false
        ];
        if( !isset( $options['conditions'] ) ) {
            $options['conditions'] = [ ];
        }
        $countByDistinct = isset( $options['count_by_distinct'] ) && !empty( $options['count_by_distinct'] ) ? 1 : 0;
        if( !empty( $conditions ) ) {
            if( $countByDistinct ) {
                $listQuery = array_merge( ['fields' => ["{$model->alias}.$model->primaryKey" ] ], array_intersect_key( $options, ['joins' => 1, 'conditions' => 1, 'group' => 1 ] ) );
                if( isset( $query['count_by_distinct_joins'] ) && !empty( $query['count_by_distinct_joins'] ) ) {
                    if( !isset( $listQuery['joins'] ) ) {
                        $listQuery['joins'] = [ ];
                    }
                    $listQuery['joins'] = array_merge( $listQuery['joins'], $query['count_by_distinct_joins'] );
                }
                $iTotalRecords = $model->find( 'list', $listQuery );
                if( !empty( $iTotalRecords ) ) {
                    $output['iTotalRecords'] = count( $iTotalRecords );
                }
            } else {
                $output['iTotalRecords'] = (int) $model->find( 'count', array_intersect_key( $options, ['joins' => 1, 'conditions' => 1, 'group' => 1 ] ) );
            }
        }
        $options['conditions'] = array_merge( $options['conditions'], $customConditions );
        
        $options['conditions'] = array_merge( $options['conditions'], $conditions );
        if( $countByDistinct ) {
            $listQuery = array_merge( ['fields' => ["{$model->alias}.$model->primaryKey" ] ], array_intersect_key( $options, ['joins' => 1, 'conditions' => 1, 'group' => 1 ] ) );
            if( isset( $query['count_by_distinct_joins'] ) && !empty( $query['count_by_distinct_joins'] ) ) {
                if( !isset( $listQuery['joins'] ) ) {
                    $listQuery['joins'] = [ ];
                }
                $listQuery['joins'] = array_merge( $listQuery['joins'], $query['count_by_distinct_joins'] );
            }
            $iTotalDisplayRecords = $model->find( 'list', $listQuery );
            if( !empty( $iTotalDisplayRecords ) ) {
                $output['iTotalDisplayRecords'] = count( $iTotalDisplayRecords );
            }
        } else {
            $output['iTotalDisplayRecords'] = (int) $model->find( 'count', array_intersect_key( $options, ['joins' => 1, 'conditions' => 1, 'group' => 1 ] ) );
        }
        if( $output['iTotalRecords'] === false ) {
            $output['iTotalRecords'] = $output['iTotalDisplayRecords'];
        }
        
        $data = $model->find( 'all', $options );
        
        if( is_array( $data ) ) {
            foreach( $data as $k => $v ) {
                $output["aaData"][$k] = $v;
            }
        }
        
        return $output;
    }

    function record_sort($records, $field, $reverse=false)
    {
        $hash = array();
       
        foreach($records as $key => $record)
        {
            $hash[$record[$field].$key] = $record;
        }
       
        ($reverse)? krsort($hash) : ksort($hash);
       
        $records = array();
       
        foreach($hash as $record)
        {
            $records []= $record;
        }
       
        return $records;
    }
    public function nf( $number, $precision = 2 ) {
        return is_numeric( $number ) ? number_format( $number, $precision ) : number_format( 0, $precision );
    }

    public function beautify( $date = null, $time = false ) {
        if( !is_int( $date ) && ( date( 'Y', strtotime( $date ) ) == '-1' || date( 'Y', strtotime( $date ) ) == 1970 ) ) return "<span class='badge'>N.A</span>";
        if( $time ) {
            if( is_int( $date ) ) return date( 'M d, Y \a\t h:i a ', $date );
            return date( 'M d, Y \a\t h:i a', strtotime( $date ) );
        } else {
            if( is_int( $date ) ) return date( 'M d, Y', $date );
            else return date( 'M d, Y', strtotime( $date ) );
        }
    }

    function currencySymbol( $currency = null ) {
        return ( $currency == 'USD' || $currency == '' ) ? $currency = '$' : $currency;
    }

    function monthYear( $string ) {
        if( strpos( $string, '-' ) ) {
            $period = explode( '-', $string );
        } else {
            $period = explode( ',', $string );
        }
        $period[0] = trim( $period[0] );
        $period[1] = trim( $period[1] );
        $period[0] = array_flip( $this->months )[$period[0]];
        return $period;
    }

}
