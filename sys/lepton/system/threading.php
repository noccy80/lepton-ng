<?php

    /**
     * PHP threading for Lepton
     *
     * Based on code by Tudor Barbu.
     *
     * @author Christopher Vagnetoft
     * @author Tudor Barbu <miau@motane.lu>
     * @license MIT
     */

    interface IRunnable {
        function threadmain();
    }

    abstract class Runnable implements IRunnable {
        public $pid;
    }


    class Thread {
        const FUNCTION_NOT_CALLABLE     = 10;
        const COULD_NOT_FORK            = 15;

        /**
         * possible errors
         *
         * @var array
         */
        private $errors = array(
            Thread::FUNCTION_NOT_CALLABLE   => 'You must specify a valid function name that can be called from the current scope.',
            Thread::COULD_NOT_FORK          => 'pcntl_fork() returned a status of -1. No new process was created',
        );

        /**
         * callback for the function that should
         * run as a separate thread
         *
         * @var callback
         */
        protected $runnable;

        /**
         * holds the current process id
         *
         * @var integer
         */
        private $pid;

        /**
         * checks if threading is supported by the current
         * PHP configuration
         *
         * @return boolean
         */
        public static function available() {
            return ( function_exists( 'pcntl_fork' ) );
        }

        /**
         * class constructor - you can pass
         * the callback function as an argument
         *
         * @param callback $_runnable
         */
        public function __construct( Runnable $_runnable = null ) {
            if( $_runnable !== null ) {
                $this->setRunnable( $_runnable );
            }
        }

        /**
         * sets the callback
         *
         * @param callback $_runnable
         * @return callback
         */
        public function setRunnable( Runnable $_runnable ) {
            $this->runnable = $_runnable;
        }

        /**
         * gets the callback
         *
         * @return callback
         */
        public function getRunnable() {
            return $this->runnable;
        }

        /**
         * returns the process id (pid) of the simulated thread
         *
         * @return int
         */
        public function getPid() {
            return $this->pid;
        }

        /**
         * checks if the child thread is alive
         *
         * @return boolean
         */
        public function isAlive() {
            $pid = pcntl_waitpid( $this->pid, $status, WNOHANG );
            return ( $pid === 0 );

        }

        /**
         * starts the thread, all the parameters are
         * passed to the callback function
         *
         * @return void
         */
        public function start() {
            $pid = @ pcntl_fork();
            if( $pid == -1 ) {
                throw new Exception( $this->getError( Thread::COULD_NOT_FORK ), Thread::COULD_NOT_FORK );
            }
            if( $pid ) {
                // parent
                $this->pid = $pid;
                Console::debug("Thread forked with pid %d", $pid);
		return $pid;
            }
            else {
                // child
                $this->runnable->pid = posix_getpid();
                pcntl_signal( SIGTERM, array( &$this, 'signalHandler' ) );
                exit (call_user_func( array(&$this->runnable,'threadmain') ));
            }
        }

        /**
         * attempts to stop the thread
         * returns true on success and false otherwise
         *
         * @param integer $_signal - SIGKILL/SIGTERM
         * @param boolean $_wait
         */
        public function stop( $_signal = SIGKILL, $_wait = false ) {
            if( $this->isAlive() ) {
                posix_kill( $this->pid, $_signal );
                if( $_wait ) {
                    pcntl_waitpid( $this->pid, $status = 0 );
                }
            }
        }

        /**
         * alias of stop();
         *
         * @return boolean
         */
        public function kill( $_signal = SIGKILL, $_wait = false ) {
            return $this->stop( $_signal, $_wait );
        }

        /**
         * gets the error's message based on
         * its id
         *
         * @param integer $_code
         * @return string
         */
        public function getError( $_code ) {
            if ( isset( $this->errors[$_code] ) ) {
                return $this->errors[$_code];
            }
            else {
                return 'No such error code ' . $_code . '! Quit inventing errors!!!';
            }
        }

        /**
         * signal handler
         *
         * @param integer $_signal
         */
        protected function signalHandler( $_signal ) {
            switch( $_signal ) {
                case SIGTERM:
                    exit( 0 );
                break;
            }
        }
    }

// EOF
?>
