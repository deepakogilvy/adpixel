<?php

App::uses( 'AppController', 'Controller' );

class CampaignsController extends AppController {

    public $name = 'Campaign';
    public $uses = [ 'Campaign', 'Page', 'ValidationLog' ];
    public $components = [ 'Utility', 'TableSearch', 'DataTable.DataTable' => [ 'Campaign' => [ 'columns' => [ 'id', 'name', 'start_date', 'end_date', 'validation_week_days', 'created', 'Actions' => null ] ] ] ];
    public $helpers = [ 'DataTable.DataTable' ];

    public function index() {
        
    }

    public function add() {
        if( $this->request->is( 'post' ) ) {
            $data = $this->request->data;
            $data['validation_week_days'] = implode( ",", $data['validation_week_days'] );
            $data['start_date'] = toDB( $data['start_date'] );
            $data['end_date'] = toDB( $data['end_date'] );
            $exists = $this->Campaign->find( 'count', [ 'conditions' => [ 'name' => $data['name'] ] ] );
            if( $exists ) {
                $campaign['Campaign'] = $data;
                $this->Session->setFlash( __( 'Campaign already exists.' ), 'default', [ 'class' => 'danger' ] );
            } else {
                $this->Campaign->save( $data );
                $campaign_id = $this->Campaign->getInsertID();
                if( is_uploaded_file( $this->request->params['form']['pixel_file']['tmp_name'] ) ) {
                    $filepath = $this->request->params['form']['pixel_file']['tmp_name'];
                    $filename = $this->request->params['form']['pixel_file']['name'];
                    $pixel = $this->__extractPixelData( $campaign_id, $filepath, $filename, 'add' );
                    if( $pixel['valid_file'] ) {
                        if(!empty($pixel['data']))
                            $this->Page->saveAll( $pixel['data'] );
                    } else {
                        $this->Campaign->id = $campaign_id;
                        $this->Campaign->delete();
                        $errorMessage = __( $pixel['error_message'] );
                        $this->Session->setFlash( $errorMessage, 'default', [ 'class' => 'danger' ] );
                        $this->redirect( [ 'controller' => 'campaigns', 'action' => 'index' ] );
                    }
                } else {
                    $errorMessage = __( 'The pixel code was not uploaded, please try to upload again.' );
                }
                $this->Session->setFlash( __( 'Campaign information has been saved.' ), 'default', [ 'class' => 'success' ] );
                $this->redirect( [ 'controller' => 'campaigns', 'action' => 'index' ] );
            }
        }
    }

    public function edit( $id ) {
        if( $id ) {
            $campaign = $this->Campaign->findById( $id );
        }
        if( !$id && !$campaign ) {
            $this->Session->setFlash( __( 'Sorry! we could not locate this campaign in our database' ), 'default', [ 'class' => 'info' ] );
            $this->redirect( [ 'controller' => 'campaigns', 'action' => 'index' ] );
        }
        if( $this->request->is( 'post' ) ) {
            $data = $this->request->data;
            $data['validation_week_days'] = implode( ",", $data['validation_week_days'] );
            $data['start_date'] = toDB( $data['start_date'] );
            $data['end_date'] = toDB( $data['end_date'] );
            $this->Campaign->id = $id;
            $exists = $this->Campaign->find( 'count', [ 'conditions' => [ 'name' => $data['name'] ] ] );
            if( $exists && strcasecmp( $campaign['Campaign']['name'], $data['name'] ) != 0 ) {
                $campaign['Campaign'] = $data;
                $this->Session->setFlash( __( "The Campaign name " . $data['name'] . " already exists." ), 'default', [ 'class' => 'danger' ] );
            } else {
                $this->Campaign->save( $data );
                if( is_uploaded_file( $this->request->params['form']['pixel_file']['tmp_name'] ) ) {
                    $filepath = $this->request->params['form']['pixel_file']['tmp_name'];
                    $filename = $this->request->params['form']['pixel_file']['name'];
                    $pixel = $this->__extractPixelData( $id, $filepath, $filename, 'edit' );
                    if( $pixel['valid_file'] ) {
                        if(!empty($pixel['data']))
                            $this->Page->saveAll( $pixel['data'] );
                    } else {
                        $errorMessage = __( $pixel['error_message'] );
                        $this->Session->setFlash( $errorMessage, 'default', [ 'class' => 'danger' ] );
                        $this->redirect( [ 'controller' => 'campaigns', 'action' => 'index' ] );
                    }
                } else {
                    $errorMessage = __( 'The pixel code was not uploaded, please try to upload again.' );
                }
                $this->Session->setFlash( __( 'Campaign information has been updated.' ), 'default', [ 'class' => 'success' ] );
                $this->redirect( [ 'controller' => 'campaigns', 'action' => 'index' ] );
            }
        }
        $this->set( 'campaign', $campaign );
    }

    public function delete( $id ) {
        $this->autoRender = false;
        if( $this->request->is( 'post' ) ) {
            $data = $this->request->data;
            $id = $data['id'];
            if( $this->{$this->modelClass}->softdelete( $id ) ) {
                echo "Business Unit has been deleted.";
            } else {
                echo "Business Unit could not be deleted.";
            }
        }
    }

    protected function __extractPixelData( $campaign_id, $filepath, $filename, $type ) {
        copy( $filepath, WWW_ROOT . 'uploads/' . $filename );

        $requiredHeader = [ 'Business Unit' => 'business_unit', 'Campaign' => 'campaign', 'Vendor' => 'vendor', 'Pixel Tag Name' => 'pixel_tag_name', 'Pixel ID Code' => 'pixel_id_code', 'Sizmek Activity ID / TTD Pixel Code' => 'pixel_code', 'Pixel Location/URL' => 'url' ];
        $validFile = false;
        $templateSheetName = 'PIXEL SHEET';
        $errorMessage = "";
        $pixelCode = [];
        $uploadedFile = $filepath;
        $extension = explode( '.', $filename );
        $fileExtension = end( $extension );
        $sheetIndex = null;
        $costerData = [];
        $duplicateCosterData = [];
        $headerRow = [];
        $requiredHeaderFound = [];
        if( in_array( $fileExtension, [ 'xlsx', 'xls' ] ) ) {
            $templateObj = $this->Excel->loadWorksheet( $uploadedFile );
            $sheetNames = $this->Excel->getSheetNames();
            $invalid = [];
            if( !isset( $sheetNames[$templateSheetName] ) ) {
                $errorMessage = __( 'The file was not uploaded because the sheet named PIXEL SHEET was not found. Please make sure that this sheet comes in the second position if you have multiple tabs, and try to upload again.' );
            } else {
                $validFile = true;
                $sheetIndex = $sheetNames[$templateSheetName];
                $rows = $this->Excel->excelToArray( $sheetIndex, false );
                $requiredHeaderFound = array();
                $requiredHeaderKeys = array_keys( $requiredHeader );
                foreach( $rows as $rowIndex => $row ) {
                    if( empty( $headerRow ) ) {
                        foreach( $row as $rowKey => $rowValue ) {
                            $row[$rowKey] = trim( $rowValue );
                        }
                        if( empty( array_diff( $requiredHeaderKeys, $row ) ) ) {
                            $headerRow = array_intersect( $row, $requiredHeaderKeys );
                        }
                        $requiredHeaderFound = array_merge( array_intersect( $row, $requiredHeaderKeys ), $requiredHeaderFound );
                    } else {
                        $temp = [];
                        foreach( $headerRow as $excelColumn => $column ) {
                            $column = isset( $requiredHeader[$column] ) ? $requiredHeader[$column] : $column;
                            $temp[$column] = $row[$excelColumn];
                        }
                        if( array_intersect( array( 'Key:', 'KEY:', 'key:' ), $temp ) ) {
                            $errorMessage = __( 'The file was not uploaded because of the data block input below the last data row. Please remove and try to upload again.' );
                            $validFile = false;
                            break;
                        }
                        if( count( array_filter( $temp ) ) > 0 ) {
                            if( trim( $temp['campaign'] ) != '' ) {
                                $temp['url'] = trim( $temp['url'] );
                                $temp['pixel_code'] = trim( $temp['pixel_code'] );
                                $temp['campaign_id'] = $campaign_id;
                                $tempx['business_unit'] = trim( $temp['business_unit'] );
                                $tempx['campaign'] = trim( $temp['campaign'] );
                                $tempx['vendor'] = trim( $temp['vendor'] );
                                $tempx['pixel_tag_name'] = trim( $temp['pixel_tag_name'] );
                                $tempx['pixel_id_code'] = trim( $temp['pixel_id_code'] );
                                $temp['row_data'] = json_encode( $tempx, true );
                                $temp['data_row_number'] = $rowIndex;
                                $arrayData[] = $temp;
                                $costerData[trim( $temp['pixel_code'] ).'-'.$temp['url']] = $temp;
                                if( $type == 'edit' )
                                {
                                    $links = $this->Page->find( 'all', [ 'fields' => [ 'id', 'pixel_code', 'url', 'row_data'], 'conditions' => [ 'pixel_code' => trim( $temp['pixel_code'] ), 'url' => trim( $temp['url'] ), 'campaign_id' => $campaign_id, 'is_deleted' => 0] ] );                              
                                    if( !empty( $links ) )                                       
                                        unset($costerData[trim( $temp['pixel_code'] ).'-'.$temp['url']]);
                                }
                            }
                        }
                    }
                }
                if( $type == 'add' ){  
                    $duplicateCosterData = array_filter($arrayData, function ($element) use ($costerData) {
                        return !in_array($element, $costerData);
                    });
                }
                
                $costerData = array_values($costerData);
                if(!empty($duplicateCosterData)){
                    $pixelCodeStr =  implode(",",array_column($duplicateCosterData,'pixel_code'));
                    $errorMessage = __( "The file is not uploaded because of duplicate pixel codes i.e $pixelCodeStr " );
                    $validFile = false;
                }
                if( empty( $headerRow ) ) {
                    $validFile = false;
                    $missingHeaders = array_diff( $requiredHeaderKeys, $requiredHeaderFound );
                    $errorMessage = __( 'The file is not uploaded because headers are missing or input in wrong column(s) in the Pixel tab: ' . implode( ', ', $missingHeaders ) . ', etc. Please correct and try to upload again.' );
                }
            }
        } else {
            $errorMessage = __( 'The file is not uploaded because the file format is invalid: please save as .xls or .xlsx only, and try to upload again.' );
            $validFile = false;
        }
        return [ 'file' => $filename, 'data' => $costerData, 'valid_file' => $validFile, 'found_header' => $requiredHeaderFound, 'required_header' => $requiredHeader, 'error_message' => $errorMessage, 'coster_position' => $sheetIndex ];
    }

    public function ajaxData() {
        $this->autoRender = false;
        $query = [ 'fields' => [ 'Campaign.name',
                'Campaign.start_date', 'Campaign.end_date',
                'Campaign.created', 'Campaign.id', 'Campaign.validation_week_days' ] ];
        $sortByColumns = [ 'name', 'start_date', 'end_date', 'created' ];
        $searchWordMap = [];
        $output = $this->TableSearch->data( $query, $sortByColumns, $searchWordMap );
        $data = $output['aaData'];
        $output['aaData'] = [];
        $modelClass = $this->modelClass;
        if( $data ) {
            foreach( $data as $d ) {
                $validation_days = explode( ",", $d[$modelClass]['validation_week_days'] );
                $vdays = AppConstants::$validationDays;
                $days = '';
                foreach( $validation_days as $k => $v ) {
                    $days .= $vdays[$v] . ",";
                }
                $days = substr( $days, 0, -1 );
                $deleted = '<a data-target="#deletePopup" data-toggle="modal" data-original-title="Delete Business Unit" type="button" data-id="' . $d[$modelClass]["id"] . '" class="btn btn-xs btn-danger delete-record glyphicon glyphicon-remove js-tt" ></a>';
                $temp = $urlArray = [];
                $temp[] = $d[$modelClass]['name'];
                $temp[] = date( 'M d, Y', strtotime( $d[$modelClass]['start_date'] ) );
                $temp[] = date( 'M d, Y', strtotime( $d[$modelClass]['end_date'] ) );
                $temp[] = $days;
                $temp[] = date( 'M d, Y', strtotime( $d[$modelClass]['created'] ) );
                $temp[] = '<div class="btn-group">'
                        . '<a href="' . Router::url( [ 'controller' => $this->request->controller, 'action' => 'edit', $d[$modelClass]["id"] ] ) . '" class="btn btn-xs btn-default glyphicon glyphicon-pencil" type="button" data-toggle="tooltip" title="" data-original-title="Edit Business Unit"></a>' . $deleted . ''
                        . '<a href="' . Router::url( [ 'controller' => $this->request->controller, 'action' => 'downloadCampaign', $d[$modelClass]["id"] ] ) . '" class="btn btn-xs btn-success glyphicon glyphicon-download" type="button" data-toggle="tooltip" title="" data-original-title="Download Report"></a></div>';
                $output['aaData'][] = $temp;
            }
        }
        echo json_encode( $output );
    }

    public function downloadCampaign( $param ) {
        $options = [ [ 'type' => 'left', 'table' => 'pages', 'alias' => 'page', 'conditions' => [ 'campaign.id = page.campaign_id' ] ],
                [ 'type' => 'left', 'table' => 'validation_logs', 'alias' => 'validation_log', 'conditions' => [ 'page.id = validation_log.page_id' ] ]
        ];
        $campaignPage = $this->Campaign->find( 'all', [ 'fields' => [ 'Campaign.id', 'page.*', 'validation_log.*' ], 'conditions' => [ 'Campaign.id' => $param ], 'joins' => $options ] );
        $output = array();
        foreach( $campaignPage as $key => $value ) {
            foreach( $value as $tableKey => $tableData ) {
                if( $tableKey == 'page' ) {
                    $rowData = json_decode( $tableData['row_data'], true );
                    $output[$tableData['id']]['Business Unit'] = $rowData['business_unit'];
                    $output[$tableData['id']]['Campaign'] = $rowData['campaign'];
                    $output[$tableData['id']]['Vendor'] = $rowData['vendor'];
                    $output[$tableData['id']]['Pixel Tag Name'] = $rowData['pixel_tag_name'];
                    $output[$tableData['id']]['Pixel ID Code'] = $rowData['pixel_id_code'];
                    $output[$tableData['id']]['Sizmek Activity ID / TTD Pixel Code'] = $tableData['pixel_code'];
                    $output[$tableData['id']]['Pixel Location/URL'] = $tableData['url'];
                    $output[$tableData['id']]['Notes'] = '';
                }
                if( $tableKey == 'validation_log' && $tableData['page_id'] != null) {
                    $output[$tableData['page_id']][$tableData['date']] = $tableData['status'];
                }
            }
        }
        $output = array_values( $output );

        $this->Excel->createWorksheet();
        $this->Excel->setSheetName( $output[0]['Business Unit'] );
        $row = 1;
        foreach( range( 0, count( $output[0] ) ) as $columnID ) {
            $this->Excel->_activeSheet->getColumnDimension( $columnID )->setAutoSize( true );
        }

        $dataKeys = array_keys( $output[0] );
        $heading = array();
        $i = 0;
        foreach( $dataKeys as $head ) {
            $heading[$i]['label'] = $head;
            $heading[$i]['width'] = '25';
            $heading[$i]['colorCode'] = 'DCDCDC';
            $heading[$i]['len'] = 1;
            $i++;
        }

        $this->setHeading( $heading, $row );
        foreach( $output as $value ) {
            $row++;
            $k = 0;
            foreach( $heading as $labelValue ) {
                $this->Excel->_activeSheet->setCellValueByColumnAndRow( $k, $row, $value[$labelValue['label']] );
                $k++;
            }
        }
        $this->Excel->setActiveSheet( 0 );
        $this->Excel->output( 'Pixel code verification' . $campaignPage[0]['Campaign']['name'] . '.xlsx' );
    }

    public function setHeading( $heading, $row, $options = [] ) {
        if( isset( $options['height'] ) ) $this->Excel->setHeight( $row, $options['height'] );
        $heading = setOffset( $heading );
        foreach( $heading as $value ) {
            $this->Excel->addTableHeader( [ [ 'label' => $value['label'], 'width' => $value['width'] ] ], [ 'name' => 'Calibri', 'bold' => true, 'offset' => $value['offset'], 'hrow' => $row ] );
        }
        $this->Excel->setHeaderStyle( $this->Excel->setStyleForHeader( $heading ), $row );
    }

}
