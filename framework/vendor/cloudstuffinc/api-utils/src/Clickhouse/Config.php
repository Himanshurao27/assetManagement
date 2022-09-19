<?php
namespace Cloudstuff\ApiUtil\Clickhouse;

/**
 * Config class for working with clickhouse cluster
 *
 * @package Cloudstuff Standard Shared Libraries
 * @author Hemant Mann <hemant.mann@cloudstuff.tech>
 * @since 1.0.2
 */
class Config {
	/**
	 * @var string $host
	 */
	public $host;

	/**
	 * @var string $database
	 */
	public $database;

	/**
	 * @var string $port
	 */
	public $port;

	/**
	 * @var string
	 */
	public $username;

	/**
	 * @var string
	 */
	public $password;

	/**
	 * Max Query execution time
	 * @var string
	 */
	public $maxTime = 60;

	public function __construct(array $opts = []) {
		foreach ($opts as $key => $value) {
			if (property_exists($this, $key)) {
				$this->$key = $value;
			}
		}
	}
}
