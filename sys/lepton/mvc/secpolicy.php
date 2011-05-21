<?php

class SystemSecurityPolicy {

	private static $cache = null;

	private static function initPolicyProviderCache() {
		if (self::$cache != null) return;
		$cd = getDescendants('SecurityPolicyProvider');
		foreach($cd as $cc) {
			self::$cache[] = new $cc();
		}
	}

	static function inAllowedPath($file) {
		self::initPolicyProviderCache();
		foreach(self::$cache as $p) {
			if ($p->getPolicyRecord(SecurityPolicyProvider::SPT_FILEPOLICY, $file) == true) {
				return true;
			}
		}
		return false;
	}

}

interface ISecurityPolicyProvider {
	function getPolicyRecord($type,$key);
}
abstract class SecurityPolicyProvider implements ISecurityPolicyProvider {
	const SPT_FILEPOLICY = 'spt.filepolicy';
	const SPT_NETWORKPOLICY = 'spt.networkpolicy';
}

class BaseSecurityPolicyProvider extends SecurityPolicyProvider {

	function getPolicyRecord($type,$key) {
		if ($type == self::SPT_FILEPOLICY) {
			$ap = base::appPath();
			$ap_allowed = array('res');
			$key = realpath($key);
			foreach($ap_allowed as $apext) {
				$apt = realpath($ap.'/'.$apext);
				if (substr($key,0,strlen($apt)) == $apt) {
					return true;
				}
			}
			return false;
		}
		return null;
	}

}
