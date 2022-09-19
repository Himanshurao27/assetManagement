<?php

namespace Framework;

use Framework\Base as Base;
use Framework\Cache as Cache;
use Framework\Events as Events;
use Framework\Registry as Registry;
use Framework\Cache\Exception as Exception;

/**
 * Factory Class which accepts initialization options, and also selects the type of returned object, based on the internal $_type property.
 * It raises Cache\Exception\Argument exceptions for invalid/unsupported types, and only supports one type of cache driver, Cache\Driver\Memcached.
 * @author Hemant Mann <hemant.mann@vnative.com>
 * @author Faizan Ayubi <faizan@vnative.com>
 */
class Cache extends Base {

	/**
	 * @readwrite
	 */
	protected $_type;

	/**
	 * @readwrite
	 */
	protected $_options;

	protected function _getExceptionForImplementation($method) {
		return new Exception\Implementation("{$method} method not implemented");
	}

	public function initialize() {
		Events::fire("framework.cache.initialize.before", array($this->type, $this->options));

		$configuration = Registry::getConfiguration();
		$configuration = $configuration->initialize();
		$parsed = $configuration->parse("configuration/cache");

		if (!$this->type) {
			if (!empty($parsed->cache->default) && !empty($parsed->cache->default->type)) {
				$this->type = $parsed->cache->default->type;
				unset($parsed->cache->default->type);
				$this->options = (array) $parsed->cache->default;
			}
		} else {
			$type = $this->type;
			if (isset($parsed->cache->$type)) {
				$this->options = (array) $parsed->cache->$type;
			}
		}

		if (!$this->type) {
			throw new Exception\Argument("Invalid type");
		}

		Events::fire("framework.cache.initialize.after", array($this->type, $this->options));

		switch ($this->type) {
			case "memcached":
				return new Cache\Driver\Memcached($this->options);

			case "redis":
				return new Cache\Driver\Redis($this->options);

			case "mongod":
				return new Cache\Driver\Mongod($this->options);

			default:
				throw new Exception\Argument("Invalid type");
				break;
		}
	}

}
