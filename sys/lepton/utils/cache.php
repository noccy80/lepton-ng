<?php

using('lepton.utils.datetime');

class CacheException extends Exception { }

class Cache {

	private static $initialized = false;
	private static $mci = null;

	private static function initialize() {
		if (!self::$initialized) {
			self::$mci = new Memcached();
			self::$mci->addServer('127.0.0.1',11211);
			self::$initialized = true;
		}
	}

	private static function check() {
		$rc = self::$mci->getResultCode();
		switch($rc) {
			case Memcached::RES_SUCCESS:
				return true;
			case Memcached::RES_FAILURE:
				throw new CacheException('The operation failed in some fashion.', $rc);
			case Memcached::RES_HOST_LOOKUP_FAILURE:
				throw new CacheException('DNS lookup failed.', $rc);
			case Memcached::RES_UNKNOWN_READ_FAILURE:
				throw new CacheException('Failed to read network data.', $rc);
			case Memcached::RES_PROTOCOL_ERROR:
				throw new CacheException('Bad command in memcached protocol.', $rc);
			case Memcached::RES_CLIENT_ERROR:
				throw new CacheException('Error on the client side.', $rc);
			case Memcached::RES_SERVER_ERROR:
				throw new CacheException('Error on the server side.', $rc);
			case Memcached::RES_WRITE_FAILURE:
				throw new CacheException('Failed to write network data.', $rc);
			case Memcached::RES_DATA_EXISTS:
				throw new CacheException('Failed to do compare-and-swap: item you are trying to store has been modified since you last fetched it.', $rc);
			case Memcached::RES_NOTSTORED:
				throw new CacheException('Item was not stored: but not because of an error. This normally means that either the condition for an "add" or a "replace" command wasn\'t met, or that the item is in a delete queue.', $rc);
			case Memcached::RES_NOTFOUND:
				throw new CacheException('Item with this key was not found (with "get" operation or "cas" operations).', $rc);
			case Memcached::RES_PARTIAL_READ:
				throw new CacheException('Partial network data read error.', $rc);
			case Memcached::RES_SOME_ERRORS:
				throw new CacheException('Some errors occurred during multi-get.', $rc);
			case Memcached::RES_NO_SERVERS:
				throw new CacheException('Server list is empty.', $rc);
			case Memcached::RES_END:
				throw new CacheException('End of result set.', $rc);
			case Memcached::RES_ERRNO:
				throw new CacheException('System error.', $rc);
			case Memcached::RES_BUFFERED:
				throw new CacheException('The operation was buffered.', $rc);
			case Memcached::RES_TIMEOUT:
				throw new CacheException('The operation timed out.', $rc);
			case Memcached::RES_BAD_KEY_PROVIDED:
				throw new CacheException('Bad key.', $rc);
			case Memcached::RES_CONNECTION_SOCKET_CREATE_FAILURE:
				throw new CacheException('Failed to create network socket.', $rc);
			case Memcached::RES_PAYLOAD_FAILURE:
				throw new CacheException('Payload failure: could not compress/decompress or serialize/unserialize the value.', $rc);
			default:
				return false;
		}
	}

	public static function get($key) {
		self::initialize();
		$val = self::$mci->get($key);
		if (self::check()) return $val;
	}

	public static function set($key,$value,$validity = null) {
		self::initialize();
		if ($validity) {
			$sec = duration::toSeconds($validity);
			$expires = $sec;
		} else {
			$expires = 0;
		}
		self::$mci->set($key,$value,$expires);
		self::check();
	}

	public static function clr($key) {
		try {
			self::$mci->delete($key);
			if (self::check()) return true;
		} catch (CacheException $e) {
			if ($e->getCode() == Memcached::RES_NOTFOUND) return false;
			throw $e;
		}
	}

}
