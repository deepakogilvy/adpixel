<?php

App::uses( 'Component', 'Controller' );
App::uses( 'CakeEmail', 'Network/Email' );

class NeoComponent extends Component {

    public function sendMail( $var = null, $template = null, $to = null, $subject = null, $bcc = array(), $fromEmail = 'neo_support@ogilvy.com', $fromName = 'Neo@Ogilvy', $provider = 'smtp', $success = 'sent', $replyToEmail = 'neo_support@ogilvy.com', $replyToName = 'Neo@Ogilvy', $attachments = [ ], $layout = 'default' ) {
        $semail = Configure::read( 'override_email' );
        if( Configure::read( 'override_email' ) ) {
            $subject = "{$subject} ({$to})";
            $to = Configure::read( 'override_email_with' );
            if( !empty( $bcc ) ) {
                $bcc = array();
            }
        }
        $email = new CakeEmail( $provider );

        $email->viewVars( array( 'var' => $var ) );
        try {
            if( is_string( $to ) ) {
                $to = trim( $to );
            }
            $email->template( $template, $layout )->emailFormat( 'html' )->to( $to )->from( $fromEmail, $fromName )->subject( $subject )->replyTo( $replyToEmail, $replyToName );
            if( !empty( $bcc ) ) {
                $email->bcc( $bcc );
            }
            if( !empty( $attachments ) ) {
                $email->attachments( $attachments );
            }
            $email->send();
            $success = "Mail sent";
        } catch( Exception $e ) {
            
        }
        return $success;
    }

}
