<?php

    /**
     * @class Mutex
     * @brief Mutex implementation for locking
     *
     * In order to create a mutex, just create a new instance of the class
     * with the name of the lock to use:
     *
     *   $mutex = new Mutex("mylock");
     *
     * When you are done with the critical stuff, just release it again:
     *
     *   unset($mutex);
     *
     * @author Christopher Vagnetoft
     * @license GPL v3
     * @copyright Copyright (c) 2010, Noccy Labs
     */
    final class Mutex {
        private $_lockstate = false;    ///< @var bool The current lock state
        private $_lockname = null;      ///< @var string The lock name
        static $instance = 0;           ///< @var The instance count
        static $resource = 0;           ///< @var The resource handle
        const SHM_KEY = 0xBEEF;         ///< @var The key for shared memory
        const SHM_LOCKS = 1;            ///< @var The locks variable
        const SHM_CRITICAL = 2;         ///< @var The critical lock variable

        /**
         * Voids all the locks. Use to free a deadlock.
         */
        static function void() {
            $res = shm_attach(Mutex::SHM_KEY);
            assert($res != null);
            shm_put_var($res, Mutex::SHM_CRITICAL, false);
            shm_put_var($res,Mutex::SHM_LOCKS,array());
            shm_detach($res);
        }

        /**
         * @private
         * @brief Enters a critical section with exclusive access
         *
         * Remember to call exitCriticalSection afterwards or you will have a
         * deadlock on your hands.
         *
         * @see Mutex::exitCriticalSection
         */
        private function enterCriticalSection() {
            Console::debug("CriticalSection: enter");
            while (shm_get_var(Mutex::$resource, Mutex::SHM_CRITICAL) == true) { usleep(100000); }
            Console::debug("CriticalSection: acquire");
            shm_put_var(Mutex::$resource, Mutex::SHM_CRITICAL, true);
        }

        /**
         * @private
         * @brief Exits a critical section with exclusive access
         *
         * Call after using enterCriticalSection()
         *
         * @see Mutex::enterCriticalSection
         */
        private function exitCriticalSection() {
            Console::debug("CriticalSection: exit");
            shm_put_var(Mutex::$resource, Mutex::SHM_CRITICAL, false);
        }

        /**
         * Constructor
         */
        function __construct($name,$timeoutms = 5000) {
            $this->_lockname = $name;
            // Block until lock can be acquired
            if (Mutex::$instance == 0) {
                Console::debug("Creating mutex manager");
                Mutex::$resource = shm_attach(Mutex::SHM_KEY);
                if (!shm_has_var(Mutex::$resource,Mutex::SHM_LOCKS)) {
                    shm_put_var(Mutex::$resource,Mutex::SHM_LOCKS,array());
                }
            }
            $this->enterCriticalSection();
            Console::debug("Waiting for lock %s", $this->_lockname);
            $t = new timer(true);
            while (true) {
                $ls = shm_get_var(Mutex::$resource,Mutex::SHM_LOCKS);
                if (!isset($ls[$name])) break;
                usleep(100000);
                if ($t->getElapsed() > ($timeoutms / 1000)) {
                	$this->exitCriticalSection();
	                throw new MutexException("Timed out waiting for lock");
                }
            }
            Console::debug("Acquiring lock %s", $this->_lockname);
            $ls = shm_get_var(Mutex::$resource,Mutex::SHM_LOCKS);
            $ls[$name] = true;
            shm_put_var(Mutex::$resource,Mutex::SHM_LOCKS,$ls);
            Mutex::$instance++;
            $this->_lockstate = true;
            $this->exitCriticalSection();
        }

        /**
         * Destructor
         */
        function __destruct() {
            // Release
            if ($this->_lockstate) $this->release();
            Mutex::$instance--;
            if (Mutex::$instance == 0) {
                Console::debug("Destroying mutex manager");
                shm_detach(Mutex::$resource);
            }
        }

        public function release() {
            $this->enterCriticalSection();
            Console::debug("Destroying mutex %s", $this->_lockname);
            $ls = shm_get_var(Mutex::$resource,Mutex::SHM_LOCKS);
            unset($ls[$this->_lockname]);
            shm_put_var(Mutex::$resource,Mutex::SHM_LOCKS,$ls);
            $this->_lockstate = false;
            $this->exitCriticalSection();
        }

    }

	class MutexException extends Exception { }
