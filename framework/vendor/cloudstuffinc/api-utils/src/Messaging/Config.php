<?php
namespace Cloudstuff\ApiUtil\Messaging;

/**
 * Config class for creating messaging clients
 *
 * @package Cloudstuff Standard Shared Libraries
 * @author Hemant Mann <hemant.mann@cloudstuff.tech>
 * @since 1.0.1
 */
class Config {
	/**
	 * @var string $projectId
	 */
    public $projectId;

    /**
	 * @var string $topicName
	 */
    public $topicName;

    public function __construct(array $opts = []) {
    	foreach ($opts as $key => $value) {
    		if (property_exists($this, $key)) {
    			$this->$key = $value;
    		}
    	}
    }
}
