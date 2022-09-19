<?php
namespace Cloudstuff\ApiUtil\Messaging;
use Google\Cloud\PubSub\PubSubClient;

/**
 * Google Pub Sub Messaging client
 *
 * @package Cloudstuff Standard Shared Libraries
 * @author Hemant Mann <hemant.mann@cloudstuff.tech>
 * @since 1.0.1
 */
class GoogleClient implements IClient {
	/**
	 * @var Config Options object
	 */
	protected $opts;

	/**
	 * @var object Pub/sub topic object
	 */
	protected $topic;

	/**
	 * GoogleClient constructor
	 * @param Config $opts Client Options
	 */
	public function __construct(Config $opts) {
		$pubsub = new PubSubClient([
	        'projectId' => $opts->projectId
	    ]);
	    $this->opts = $opts;
	    $this->topic = $pubsub->topic($opts->topicName);
	}

    public function send(string $data) {
    	return $this->topic->publish(['data' => $data]);
    }
}
