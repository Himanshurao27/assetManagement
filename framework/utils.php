<?php
namespace Framework;

class Utils {
	/**
	 * Get Class of an object
	 * @param  object  $obj  Any object
	 * @param  boolean $full Whether full name is required (with namespace as prefix)
	 * @return string        Name of the class of the object
	 */
	public static function getClass($obj, $full = false) {
		$cl = get_class($obj);

		if (!$full) {
			$parts = explode("\\", $cl);
			$cl = array_pop($parts);
		}
		return $cl;
	}

	/**
	 * @deprecated should not be used
	 * @param  boolean $numbers Whether numbers are required in the string
	 * @return string           [a-zA-Z0-9]+
	 */
	public static function randomPass($numbers = true) {
		$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

		if (!$numbers) {
			$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		}
		$pass = array(); //remember to declare $pass as an array
		$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
		for ($i = 0; $i < 8; $i++) {
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		return implode($pass); //turn the array into a string
	}
	
	public static function getConfig($name, $property = null) {
		$config = Registry::get("configuration")->parse("configuration/{$name}");

		if ($property && property_exists($config, $property)) {
			return $config->$property;
		}
		return $config;
	}

	public static function getAppConfig() {
		return static::getConfig('app')->app;
	}
	
	public static function getRedisService() {
		$redis = Registry::getRedis();
		return $redis->getService();
	}

	/**
	 * Set Cache in Memcache
	 * @param string  $key      Name of the Key
	 * @param mixed  $value    The value to be stored
	 * @param integer $duration No of seconds for which the value should be stored
	 */
	public static function setCache($key, $value, $duration = 300) {
        /** @var \Framework\Cache\Driver\Memcached $memCache */
		$memCache = Registry::getCache();
		return $memCache->set($key, $value, $duration);
	}

	/**
	 * Get Cache Value from the key
	 * @param  string $key Name of the key
	 * @return mixed      Corresponding value in the key
	 */
	public static function getCache($key, $default = null) {
	    /** @var \Framework\Cache\Driver\Memcached $memCache */
		$memCache = Registry::getCache();
		return $memCache->get($key, $default);
	}

	public static function removeCache($key) {
		/** @var \Framework\Cache\Driver\Memcached $memCache */
		$memCache = Registry::getCache();
		return $memCache->erase($key);
	}

	public static function getSmartCache($date, $resourceUid) {
		$cacheKey = sprintf("Date:%s_ID:%s", $date, $resourceUid);
		return static::getCache($cacheKey);
	}

	public static function setSmartCache($date, $resourceUid, $resource) {
		$cacheKey = sprintf("Date:%s_ID:%s", $date, $resourceUid);
		static::setCache($cacheKey, $resource, 86400);
	}

	public static function decodeBase64Image($data, $validTypes = []) {
		if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
		    $data = substr($data, strpos($data, ',') + 1);
		    $type = strtolower($type[1]); // jpg, png, gif

		    if (! in_array($type, $validTypes)) {
		        throw new \Exception('Invalid Image Type');
		    }

		    $data = base64_decode($data);
		    if ($data === false) {
		        throw new \Exception('Base64 Decode failed');
		    }
		    return ['data' => $data, 'extension' => $type];
		} else {
		    throw new \Exception('Invalid Image Data');
		}
	}

	/**
	 * Calculate the difference between two numbers
	 * @param float $old Old value
	 * @param float $new New value
	 * @return float The Change in Number
	 */
	public static function Numdiff($old, $new) {
		$diff = $new - $old;
		if ($old == 0 || $new == 0) {
			return round($diff / 100, 4);
		}
		if ($diff != 0) {
			return round($diff / $old, 4);
		} else {
			return 0;
		}
	}

	/**
	 * Set a message to Session that will only be displayed once
	 * @param  string $msg Message to display
	 * @return null
	 */
	public static function flashMsg($msg) {
		$session = Registry::getSession();
		$session->set('$flashMessage', $msg);
	}

	/**
	 * Get the CDN base URL
	 * @return string
	 */
	public static function getCDN($cloudCdn = true) {
		if ($cloudCdn) {
			return CLOUD_CDN;
		}
		return CDN;
	}

	/**
	 * Get the Image CDN base URL
	 * @return string
	 */
	public static function getImageCdn() {
		$cdn = static::getCDN() . "uploads/images/";
		return $cdn;
	}

	public static function mapObject(array $mapping, $obj) {
		$newObj = [];
		foreach ($mapping as $key => $prop) {
			if (is_array($prop)) {
				$newObj[$key] = static::mapObject($prop, $obj);
			} else {
				$newObj[$key] = static::getObjectProperty($obj, $prop);
			}
		}
		return (object) $newObj;
	}

	public static function getObjectProperty($obj, string $prop) {
		$parts = explode(".", $prop);
		$val = clone $obj;
		foreach ($parts as $p) {
			$val = $val->$p ?? null;
			if (is_null($val)) {
				return $val;
			}
		}
		return $val;
	}

	public static function getUploadFileExt($f) {
		$name = $_FILES[$f]["name"] ?? 'abcd.txt';
		$parts = explode(".", $name);
		$ext = $parts[count($parts) - 1];
		$ext = strtolower($ext);
		return $ext;
	}

	public static function convertToMap($arr, $key) {
		$map = [];
		foreach ($arr as $o) {
			$map[$o->$key] = $o;
		}
		return $map;
	}
}
