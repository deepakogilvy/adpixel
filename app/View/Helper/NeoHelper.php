<?php

App::uses( 'AppHelper', 'View/Helper' );

class NeoHelper extends AppHelper {

    public $helpers = [ 'Html' ];

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

    public function u( $controller = 'dashboard', $action = 'index', $param = false ) {
        if( !$param ) {
            return $this->Html->url( [ 'controller' => $controller, 'action' => $action ] );
        } else {
            if( is_array( $param ) ) return $this->Html->url( array_merge( [ 'controller' => $controller, 'action' => $action ], $param ) );
            else return $this->Html->url( [ 'controller' => $controller, 'action' => $action, $param ] );
        }
    }

    public function toTable( $results, $headers = [ ], $table_class = "table table-bordered table-striped table-condensed" ) {
        $init = '<table class="' . $table_class . '" >';
        $head = "<thead><tr>";
        $keys = [ ];
        foreach( $headers as $header ) {
            $keys[] = $header['key'];
            $head .= '<th class="' . $header['class'] . '" >' . strtoupper( $header['label'] ) . '</th>';
        }
        $head .= '</tr></thead>';
        $body = '<tbody>';
        foreach( $results as $result ) {
            $body .= '<tr>';
            foreach( $keys as $k ) {
                $body .= '<td>' . $result[$k] . '</td>';
            };
            $body .= '</tr>';
        }
        $end = '</tbody></table>';
        return $init . $head . $body . $end;
    }

    public function setOptions( $optionsArray, $selected = null, $showEmptyOption = true ) {
        if( isset( $optionsArray[0] ) ) {
            $optionsArray = array_combine( $optionsArray, $optionsArray );
        }
        if( !empty( $selected ) && !is_array( $selected ) && !isset( $optionsArray[$selected] ) ) {
            $optionsArray[$selected] = $selected;
        }

        $options = "";
        if( $showEmptyOption !== false ) {
            $options = '<option value="">Select</option>';
        }
        foreach( $optionsArray as $k => $v ) {
            if( $k ) if( is_array( $selected ) ) $sel = ( in_array( $k, $selected ) ) ? ' selected=selected' : '';
                else $sel = (!is_null( $selected ) && $selected == $k ) ? ' selected=selected' : '';
            $options .= '<option value="' . $k . '"' . $sel . '>' . $v . '</option>';
        }
        return $options;
    }

    public function nf( $number, $precision = 2 ) {
        return is_numeric( $number ) ? number_format( $number, $precision ) : number_format( 0, $precision );
    }

    public function addMenuLink( $linkTitle, $controller, $action, $param = [ 'params' => false ] ) {
        $isActive = $this->params['controller'] == $controller && $this->params['action'] == $action ? ' class="active" ' : '';
        $param['params'] = isset( $param['params'] ) ? $param['params'] : false;
        $current_user = $this->_View->viewVars['current_user'];
        $menuLink = '';
        if( $current_user->isAllowed( $controller, $action ) ) {
            if( is_null( $controller ) && is_null( $action ) ) {
                $menuLink = '<li><a href="#" ' . $isActive . '>';
                if( isset( $param['icon'] ) ) $menuLink .= '<i class="' . $param['icon'] . '"></i>';
                $menuLink .= '<span class="sidebar-mini-hide"> ' . $linkTitle . '</span></a></li>';
            } else {
                $menuLink = '<li><a ' . $isActive . 'href="' . $this->u( $controller, $action, $param['params'] ) . '">';
                if( isset( $param['icon'] ) ) $menuLink .= '<i class="' . $param['icon'] . '"></i>';
                $menuLink .= '<span class="sidebar-mini-hide"> ' . $linkTitle . '</span></a></li>';
            }
        }
        return $menuLink;
    }

    public function addBtn( $title, $controller, $action, $param ) {
        $param['params'] = isset( $param['params'] ) ? $param['params'] : false;
        $class = isset( $param['class'] ) ? $param['class'] : 'btn';
        $type = isset( $param['type'] ) ? $param['type'] : 'button';
        $current_user = $this->_View->viewVars['current_user'];
        $btnLink = '';
        if( $current_user->isAllowed( $controller, $action ) ) {
            if( is_null( $controller ) && is_null( $action ) ) {
                $btnLink = '<a href="#" class="' . $class . '" type="' . $type . '">';
                if( isset( $param['icon'] ) ) $btnLink .= '<i class="' . $param['icon'] . '"></i>';
                $btnLink .= ' ' . $title . '</a>';
            } else {
                $btnLink = '<a href="' . $this->u( $controller, $action, $param['params'] ) . '" class="' . $class . '" type="' . $type . '">';
                if( isset( $param['icon'] ) ) $btnLink .= '<i class="' . $param['icon'] . '"></i>';
                $btnLink .= ' ' . $title . '</a>';
            }
        }
        return $btnLink;
    }

    public function ago( $time, $fromTime = false ) {
        if( !$fromTime ) $fromTime = time();
        $diff = $fromTime - $time;
        if( $diff < 1 ) {
            return 'Now';
        }
        $a = array( 31536000 => 'year', 2592000 => 'month', 86400 => 'day', 3600 => 'hour', 60 => 'min', 1 => 'second' );

        foreach( $a as $secs => $str ) {
            $d = $diff / $secs;
            if( $d >= 1 ) {
                $r = round( $d );
                $str = $r > 1 ? $str . 's' : $str;
                return $r . ' ' . $str . ' ago';
            }
        }
    }

    public function stringTruncate( $string, $length, $dots = "..." ) {
        return ( strlen( $string ) > $length ) ? substr( $string, 0, $length - strlen( $dots ) ) . $dots : $string;
    }

}
