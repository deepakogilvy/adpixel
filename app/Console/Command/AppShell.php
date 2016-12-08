<?php
/**
 * AppShell file
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         CakePHP(tm) v 2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Shell', 'Console');

/**
 * Application Shell
 *
 * Add your application-wide methods in the class below, your shells
 * will inherit them.
 *
 * @package       app.Console.Command
 */
class AppShell extends Shell {

    public function perform() {
        $this->initialize();
        $this->loadTasks();
        return $this->runCommand($this->args[0], $this->args);
    }

    public $workflowId = null;
    public $workflow = false;
    public $success = true;
    public $callFunction = null;

    protected function _setVariables() {
        $this->workflow = $this->Workflow->find( 'first', [ 'conditions' => [ 'workflow_id' => $this->workflowId ] ] );
        if( !isset( $this->workflow['Workflow'] ) || in_array( $this->workflow['Workflow']['status'], [ 'COMPLETED', 'TERMINATED' ] ) ) {
            $this->out( 'Workflow ' . $this->workflow['Workflow']['status'] . ' SKIPPING :: ' . $this->command . ' workflow # ' . $this->workflow_id );
            return false;
        }
        $this->workflow = $this->workflow['Workflow'];
        return true;
    }

    protected function _error( $message = null ) {
        $this->out( __d( 'cake_console', '<error>' . $message . '</error>') );
        return true;
    }

    public function initialize() {
        if( !$this->db ) $this->db = ConnectionManager::getDataSource( 'default' );
        if( !$this->Neo ) {
            App::import('Component', 'Neo');
            $this->Neo = new NeoComponent(new ComponentCollection());
        }
        if( !$this->workflow ) {
            if( isset( $this->args[2] ) ) {
                $this->workflowId = intval( $this->args[2] );
                $this->callFunction = $this->args[1];
                $this->command = $this->args[0];
                if( !$this->_setVariables() ) {
                    $this->_error( "Invalid Workflow ID: " . $this->workflowId . " for " . ucfirst( $this->command ) . " Report" );
                    $this->Log->push( [ 'message' => 'Projection Workbook shell executed with incorrect workflow id', 'object_id' => $this->workflowId, 'object' => 'workflow' ], 'workflow' );
                    exit;
                }
            }
        }
    }

    public function startup() {
        $this->workflowId = intval( $this->args[1] );
        $this->callFunction = $this->args[0];
        if( !$this->_setVariables() ) {
            $this->_error( "Invalid Workflow ID: " . $this->workflow_id . " for " . ucfirst( $this->command ) . " Workbook" );
            $this->Log->push( [ 'message' => 'Projection Workbook shell executed with incorrect workflow id', 'object_id' => $this->workflow_id, 'object' => 'workflow' ], 'workflow' );
            exit;
        }
    }

    public function report() {
        $workflowStep = $this->workflow['current_step'];

        if( $this->callFunction == null || !method_exists( $this, $this->callFunction ) ) {
            $this->_error( "Invalid Function Name Provided: " . $this->callFunction );
            $this->Log->push( [ 'message' => 'Workflow shell executed with invalid Function Name', 'object_id' => $this->workflowId, 'function requested' => $this->callFunction, 'object' => 'workflow' ], 'workflow' );
            $this->Workflow->updateAll( ['$set' => ['status' => 'FAILED']], [ 'workflow_id' => intval( $this->workflowId ) ] );
            $this->_setWorkflowStatus( $workflowStep, $this->callFunction, 'FAILED');
            return false;
        }

        if( $this->workflow['status'] == 'FAILED' ) {
                $this->out('WORKFLOW ERROR IN OTHER STEP TASKS - SKIPPING  ' . $this->callFunction . ' of Projection Workbook workflow . # ' . $this->workflowId );        
                $this->Workflow->updateAll( ['$set' => ['status' => 'FAILED']], [ 'workflow_id' => intval( $this->workflowId ) ] );
                return false;
        }

        $this->db->query("START TRANSACTION;");
        try {
            call_user_func( [ $this, $this->callFunction ] );
            $this->success = true;
        } catch( Exception $e ) {
            $this->success = false;
            $this->_setWorkflowStatus( $workflowStep, $this->callFunction, 'FAILED');
            $this->Workflow->updateAll( ['$set' => ['status' => 'FAILED']], [ 'workflow_id' => intval( $this->workflowId ) ] );

            $msg =  $this->callFunction .' FAILED for workflow ID : ' . $this->workflowId . ' :: ' . $e->getMessage();
            $_log = [ 'message' => $msg, 'object_id' => $this->workflowId, 'step' => $workflowStep, 'task' => $this->callFunction, 'object' => 'workflow' ];
            $this->Log->push( $_log , 'workflow' );
            $this->out( $msg );
            $this->db->query("ROLLBACK;");
            $this->out( __d( 'cake_console', '<error>' . $this->callFunction . ' Failed</error>' ) );
        }
        if( $this->success ) {
            $this->db->query("COMMIT;");
            $this->_setWorkflowStatus( $workflowStep, $this->callFunction, "SUCCESS" );
        }
    }

    protected function _setWorkflowStatus( $currentStep, $task, $status) {
        return $this->Workflow->updateAll( [ '$set' => [ $currentStep . '.' . $task => [ 'status' => $status ] ] ], [ 'workflow_id' => intval( $this->workflowId ) ] );
    }

    protected function _execute( $sql ) {
        $this->out();
        $query_pre = microtime(true);
        $this->out( $sql );
        $result = $this->db->query( $sql );
        $rowCount = $this->db->lastAffected();
        $query_post = microtime(true);
        $this->out( $query_post - $query_pre );
        $this->out( $rowCount );
        $this->out();
        $this->hr();
        return true;
    }

}
