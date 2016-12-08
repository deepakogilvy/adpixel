<?php

App::uses( 'Component', 'Controller' );

/**
 * This component contain generic utility functions.
 * 
 * @copyright (c) 2015, ogilvy.com
 * @author Tushar Takkar <tushar.takkar@ogilvy.com>
 */
class UtilityComponent extends Component {

    /**
     * This method removes spaces at start and end of string.
     * If string is multi word then it will reduce multi spaces into single space between words.
     * 
     * @author Tushar Takkar <tushar.takkar@ogilvy.com>
     * @param string $string Input string to be transformed
     * @return string
     */
    public function trim( $string ) {
        return trim( preg_replace( "/\s+/", " ", $string ) );
    }

}
