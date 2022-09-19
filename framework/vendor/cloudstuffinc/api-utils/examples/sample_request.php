<?php

require dirname(__DIR__) . '/vendor/autoload.php';

try {
	$api = new Cloudstuff\ApiUtil\Api([
		'defaultHeaders' => [
			'Authorization' => 'Token 580b0c7f95851db06a048293ea8d92be3ee89504'
		]
	]);

	$resp = $api->get('https://httpbin.org/get', ['query' => ['foo' => 'bar']]);
	var_dump($resp);
} catch (Cloudstuff\ApiUtil\Exception\Core $e) {
	var_dump($e);
}