<?php

require dirname(__DIR__) . '/vendor/autoload.php';

try {
	$config = new Cloudstuff\ApiUtil\Messaging\Config([
		'projectId' => 'tranquil-apogee-150510',
		'topicName' => 'vnative_ads_channel'
	]);
	$api = Cloudstuff\ApiUtil\Messaging\Factory::make(
		Cloudstuff\ApiUtil\Messaging\Factory::TYPE_GOOGLE_PUBSUB,
		$config
	);
	$api->send(json_encode(['_id' => '58551c30b6920d760817f711', 'description' => 'Get Started with doubling your money']));
} catch (Cloudstuff\ApiUtil\Exception\Core $e) {
	var_dump($e);
}

use Google\Cloud\PubSub\PubSubClient;

/**
 * Pulls all Pub/Sub messages for a subscription.
 *
 * @param string $projectId  The Google project ID.
 * @param string $subscriptionName  The Pub/Sub subscription name.
 */
function pull_messages($projectId, $subscriptionName)
{
    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);
    $subscription = $pubsub->subscription($subscriptionName);
    foreach ($subscription->pull() as $message) {
        printf('Message: %s' . PHP_EOL, $message->data());
        // Acknowledge the Pub/Sub message has been received, so it will not be pulled multiple times.
        $subscription->acknowledge($message);
    }
}

pull_messages('tranquil-apogee-150510', 'vnative_ads_sub');