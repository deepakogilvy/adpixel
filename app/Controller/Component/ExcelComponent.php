<?php
App::uses('Component', 'Controller');
class ExcelComponent extends Component {
    public $components = [ 'Session' ];
    public $_xls;
    protected $_row = 1;
    protected $_tableParams;
    protected $_maxRow = 0;
    public $_activeSheet;

    public function createWorksheet() {
        App::import('Vendor', 'PHPExcel', [ 'file' => 'Excel'.DS.'PHPExcel.php' ] );
        $this->_xls = new PHPExcel();
        $this->_activeSheet = $this->_xls->getActiveSheet();
        $this->_row = 1;
        return $this;
    }

    public function loadWorksheet($file) {
        App::import( 'Vendor', 'PHPExcel_IOFactory', [ 'file' => 'Excel'.DS.'PHPExcel'.DS.'IOFactory.php' ] );
        $this->_xls = PHPExcel_IOFactory::load($file);
        $this->setActiveSheet(0);
        $this->_activeSheet = $this->_xls->getActiveSheet();
        $this->_row = 1;
        return $this;
    }

    public function addSheet($name) {
        $index = $this->_xls->getSheetCount();
        $this->_xls->createSheet($index)->setTitle($name);
        $this->setActiveSheet($index);
        $this->_activeSheet = $this->_xls->getActiveSheet();
        return $this;
    }

    public function setActiveSheet($sheet) {
        $this->_maxRow = $this->_xls->setActiveSheetIndex($sheet)->getHighestRow();
        $this->_row = 1;
        $this->_activeSheet = $this->_xls->getActiveSheet();
        return $this;
    }

    public function setSheetName($name) {
        $this->_activeSheet->setTitle($name);
        return $this;
    }

    public function __call($name, $arguments) {
        return call_user_func_array( [ $this->_xls, $name ], $arguments);
    }

    public function setDefaultFont($name, $size) {
        $this->_xls->getDefaultStyle()->getFont()->setName($name);
        $this->_xls->getDefaultStyle()->getFont()->setSize($size);

        return $this;
    }

    public function setRow($row) {
        $this->_row = (int)$row;
        return $this;
    }

    public function freezePane($cell) {
        $this->_activeSheet->freezePane($cell);
        return $this;
    }

    public function setFillColor($cell, $colorCode = '000000') {
        $this->fillColor( $cell, $colorCode );
        return $this;
    }

    public function addTableHeader( $data, $params = [ 'bold' => false, 'center' => false ] ) {
        if( isset( $params['hrow'] ) ) $this->setRow($params['hrow']);        
        $offset = 0;
        if (isset($params['offset'])) $offset = is_numeric($params['offset']) ? (int)$params['offset'] : PHPExcel_Cell::columnIndexFromString($params['offset']);
        if (isset($params['font'])) $this->_activeSheet->getStyle($this->_row)->getFont()->setName($params['font']);
        if (isset($params['size'])) $this->_activeSheet->getStyle($this->_row)->getFont()->setSize($params['size']);
        if (isset($params['bold'])) $this->_activeSheet->getStyle($this->_row)->getFont()->setBold($params['bold']);
        if (isset($params['italic'])) $this->_activeSheet->getStyle($this->_row)->getFont()->setItalic($params['italic']);
        if (isset($params['setHeight']['value'])) $this->setHeight( $this->_row, $params['setHeight']['value'] );
        if ( isset( $params['center'] ) && $params['center'] == true ) $this->center( $this->_row );
        if ( isset( $params['wrap'] ) && $params['wrap'] == true ) $this->wordWrap( $this->_row );
        $this->_tableParams = [ 'header_row' => $this->_row, 'offset' => $offset, 'row_count' => 0, 'auto_width' => [], 'filter' => [], 'wrap' => [] ];

        foreach ($data as $d) {
            $this->_activeSheet->setCellValueByColumnAndRow($offset, $this->_row, $d['label']);
            if (isset($d['width']) && is_numeric($d['width']))
                $this->_activeSheet->getColumnDimensionByColumn($offset)->setWidth((float)$d['width']);
            else
                $this->_tableParams['auto_width'][] = $offset;

            if (isset($d['filter']) && $d['filter']) $this->_tableParams['filter'][] = $offset;
            if (isset($d['wrap']) && $d['wrap']) $this->_tableParams['wrap'][] = $offset;
            if ( isset( $d['colorCode'] ) ) $this->fillColor( $d['cell'], $d['colorCode'] );

            $offset++;
        }
        $this->_row++;

        return $this;
    }

    public function setHeaderStyle( $data, $row = null ){
        if( !is_null( $row ) ) {
            $i = 'A';
        }

        foreach( $data as $d ) {
            if( !isset( $d['merge'] ) ) {
                if( isset( $d['len'] ) ) {
                    $d['merge'] = PHPExcel_Cell::stringFromColumnIndex( $d['offset'] ) . $row . ':' . PHPExcel_Cell::stringFromColumnIndex( $d['offset'] - 1 + $d['len'] ) . $row;
                } else {
                    $d['merge'] = $i . $row . ':' . $i . $row;
                    $i++;
                }
            }
            $this->mergeCells( $d['merge'] );
            if( isset( $d['colorCode'] ) ) $this->fillColor( $d['merge'], $d['colorCode'] );
            if( isset( $d['fontColor'] ) ) $this->fontColor($d['merge'], $d['fontColor']);
            $this->center( $d['merge'] );
            $this->wordWrap( $d['merge'] );
        }
        return $this;
    }

    public function mergeCells( $cells ) {
        $this->_activeSheet->mergeCells($cells);
        return $this;
    }

    public function fillColor( $cells, $colorCode ) {
        $this->_activeSheet->getStyle($cells)->getFill()->applyFromArray( [ 'type' => PHPExcel_Style_Fill::FILL_SOLID, 'startcolor' => [ 'rgb' => $colorCode ] ] );
        return $this;
    }

    public function setHeight( $row, $height ) {
        $this->_activeSheet->getRowDimension( $row )->setRowHeight( $height );
        return $this;
    }

    public function fontColor( $row, $colorCode = 'FFFFFF' ) {
        $this->_activeSheet->getStyle( $row )->getFont()->applyFromArray( [ 'color' => [ 'rgb' => $colorCode ] ] );
        return $this;
    }

    public function center( $cell ) {
        $this->_activeSheet->getStyle( $cell )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        return $this;
    }
    
    public function right( $cell ) {
        $this->_activeSheet->getStyle( $cell )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        return $this;
    }
    
    public function left( $cell ) {
        $this->_activeSheet->getStyle( $cell )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        return $this;
    }

    public function boldCell( $cell ) {
        $this->_activeSheet->getStyle( $cell )->getFont()->setBold(true);
        return $this;
    }

    public function wordWrap( $cell ) {
        $this->_activeSheet->getStyle( $cell )->getAlignment()->setWrapText(true)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        return $this;
    }

    public function setFilter( $cell ) {
        $this->_activeSheet->setAutoFilter( $cell );
        return $this;
    }
    
    public function fillBorder($cell, $borderType, $borderLayout, $color = '000000') {
        $style = '';
        switch ($borderType) {
            case 'BORDER_THIN':
                $style = PHPExcel_Style_Border::BORDER_THIN;
                break;
            case 'BORDER_THICK':
                $style = PHPExcel_Style_Border::BORDER_THICK;
                break;
        }
        $borderArray = [ 'borders' => [ $borderLayout => [ 'style' => $style, 'color' => [ 'argb' => $color ] ] ] ];
        $this->_activeSheet->getStyle($cell)->applyFromArray($borderArray);
        return $this;
    }
    
    public function border( $cell, $borderType = 'BORDER_THICK', $borderLayout = 'outline', $color = '000000' ) {
        $style = '';
        switch ($borderType) {
            case 'BORDER_THIN':
            $style = PHPExcel_Style_Border::BORDER_THIN;
            break;
            case 'BORDER_THICK':
            $style = PHPExcel_Style_Border::BORDER_THICK;
            break;
        }
        $borderArray = [ 'borders' => [ $borderLayout => [ 'style' => $style, 'color' => [ 'argb' => $color ] ] ] ];
        $this->_activeSheet->getStyle($cell)->applyFromArray($borderArray);
        return $this;        
    }
    
    public function setNumberFormat($cell, $format) {
        $this->_activeSheet->getStyle($cell)->getNumberFormat()->setFormatCode($format);
        return $this;
    }

    public function format( $cell, $format ) {
        $this->_activeSheet->getStyle($cell)->getNumberFormat()->setFormatCode($format);
        return $this;
    }

    public function addTableRow($data, $offset = NULL ) {
        if( is_null( $offset ) ) $offset = $this->_tableParams['offset'];

        foreach ($data as $d) {
            $this->_activeSheet->setCellValueByColumnAndRow($offset++, $this->_row, $d);
        }
        $this->_row++;
        $this->_tableParams['row_count']++;

        return $this;
    }

    public function addTableFooter() {
        foreach ($this->_tableParams['auto_width'] as $col) {
            $this->_activeSheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        if (count($this->_tableParams['filter'])) {
            $this->_activeSheet->setAutoFilter(PHPExcel_Cell::stringFromColumnIndex($this->_tableParams['filter'][0]) . ($this->_tableParams['header_row']) . ':' . PHPExcel_Cell::stringFromColumnIndex($this->_tableParams['filter'][count($this->_tableParams['filter']) - 1]) . ($this->_tableParams['header_row'] + $this->_tableParams['row_count']));
        }
        foreach ($this->_tableParams['wrap'] as $col) {
            $this->_activeSheet->getStyle(PHPExcel_Cell::stringFromColumnIndex($col) . ($this->_tableParams['header_row'] + 1) . ':' . PHPExcel_Cell::stringFromColumnIndex($col) . ($this->_tableParams['header_row'] + $this->_tableParams['row_count']))->getAlignment()->setWrapText(true);
        }

        return $this;
    }

    public function addData($data, $offset = 0) {
        if (!is_numeric($offset)) $offset = PHPExcel_Cell::columnIndexFromString($offset);
        foreach ($data as $d) {
            $this->_activeSheet->setCellValueByColumnAndRow($offset++, $this->_row, $d);
        }
        $this->_row++;
        return $this;
    }

    public function getTableData($max = 100) {
        if ($this->_row > $this->_maxRow) return false;
        $data = [];
        for ($col = 0; $col < $max; $col++) {
            $data[] = $this->_activeSheet->getCellByColumnAndRow($col, $this->_row)->getValue();
        }

        $this->_row++;
        return $data;
    }

    public function getWriter($writer) {
        return PHPExcel_IOFactory::createWriter($this->_xls, $writer);
    }

    public function save($file, $writer = 'Excel2007') {
        $objWriter = $this->getWriter($writer);
        return $objWriter->save( Configure::read( 'archive_path' ) . $file );
    }

    public function output($filename = 'export.xlsx', $writer = 'Excel2007') {
        ob_end_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $objWriter = $this->getWriter($writer);
        $objWriter->save('php://output');
        exit;
    }

    public function setStyleForHeader( $headerRow ){
        $header = [];
        foreach ( $headerRow as $value ) {
            $headerValue = [];
            if ( isset( $value['merge'] ) ) {
                $headerValue['merge'] = $value['merge'];
            }
            if ( isset( $value['cell'] ) ) {
                $headerValue['cell'] = $value['cell'];
            }
            if ( isset( $value['colorCode'] ) ) {
                $headerValue['colorCode'] = $value['colorCode'];
            }
            if ( isset( $value['fontColor'] ) ) {
                $headerValue['fontColor'] = $value['fontColor'];
            }
            if ( isset( $value['offset'] ) ) {
                $headerValue['offset'] = $value['offset'];
            }
            if ( isset( $value['len'] ) ) {
                $headerValue['len'] = $value['len'];
            }
            $header[] = $headerValue;
        }        
        return $header;
    }
    
    public function setHeaderTitle( $headerRow ) {
        $headerValue = [];
        foreach ( $headerRow as $value ) {
            $headerValue[] = [ 'label' => $value['label'], 'width' => $value['width'] ];
        }        
        return $headerValue;
    }

    public function addHeader( $headerArray, $row = null ) {
        $this->addTableHeader( $this->setHeaderTitle( $headerArray ) )->setHeaderStyle( $this->setStyleForHeader( $headerArray ), $row );
        return $this;
    }

    public function stringFromColumnIndex( $int ) {
        return PHPExcel_Cell::stringFromColumnIndex( $int );
    }

    public function cloneSheet( $sheetName = 'sheet', $cloneSheetIndex = 0 ) {
        $baseSheet = $this->_xls->getSheet( $cloneSheetIndex );
        $clonedSheet = clone $baseSheet;
        $clonedSheet->setTitle($sheetName);
        $this->_xls->addSheet($clonedSheet);
        return $this;
    }

    public function getSheetNames() {
        $sheetNames = $this->_xls->getSheetNames();
        $sheetNames = array_change_key_case ( array_flip( $sheetNames ), CASE_UPPER );
        return $sheetNames;
    }

    public function excelToArrays( $filePath, $sheetIndex = 0 ) {
        App::import('Vendor', 'PHPExcel', [ 'file' => 'Excel'.DS.'PHPExcel.php' ] );
        $this->_xls = new PHPExcel();
        return $this->_xls->excelToArray( $filePath, $sheetIndex );
    }

    public function excelToArray( $sheetIndex = 0, $header=true ) {
        $objWorksheet = $this->_xls->setActiveSheetIndex($sheetIndex);
        if($header) {
            $highestRow = $objWorksheet->getHighestRow();
            $highestColumn = $objWorksheet->getHighestColumn();
            $headingsArray = $objWorksheet->rangeToArray('A1:'.$highestColumn.'1',null, true, true, true);
            $headingsArray = $headingsArray[1];
            $r = -1;
            $namedDataArray = [];
            for ($row = 2; $row <= $highestRow; ++$row) {
                $dataRow = $objWorksheet->rangeToArray('A'.$row.':'.$highestColumn.$row,null, true, true, true);
                if ((isset($dataRow[$row]['A'])) && ($dataRow[$row]['A'] > '')) {
                    ++$r;
                    foreach($headingsArray as $columnKey => $columnHeading) {
                        if( !is_null( $columnHeading ) ) $namedDataArray[$r][$columnHeading] = $dataRow[$row][$columnKey];
                    }
                }
            }
        } else{
            $namedDataArray = $objWorksheet->toArray(null,true,true,true);
        }
        return $namedDataArray;
    }

    public function toExcelDate( $date ) {
        App::import('Vendor', 'PHPExcel', [ 'file' => 'Excel'. DS . 'PHPExcel' . DS . 'Calculation' . DS . 'DateTime.php' ] );
        if( $date == NULL || $date == '0000-00-00' || $date == '' ) {
            return '00/00/0000';
        } else {
            $date = strtotime( $date );
            return PHPExcel_Shared_Date::PHPToExcel($date);
        }
    }

    public function insertNewRowBefore( $pBefore = 1, $pNumRows = 1 ) {
        $this->_xls->insertNewRowBefore( $pBefore, $pNumRows );
    }

    public function removeSheetByIndex( $pIndex ) {
        $this->_xls->removeSheetByIndex( $pIndex );
    }

    public function addTableHeaderWithStyle( $row, $r = 1 ) {
        $row = setOffset( $row );
        foreach( $row as $value ) {
            $this->addTableHeader( [ [ 'label' => $value['label'] ] ], [ 'bold' => false, 'offset' => $value['offset'], 'hrow' => $r ] );
        }
        $this->setHeaderStyle( $this->setStyleForHeader( $row ), $r );
    }

}