<?php


// 1. load the Core class that includes an autoloader
require_once(FRAMEWORK_PATH. "/core.php");
Framework\Core::initialize();

// 2. Additional Path's which
Framework\Core::autoLoadPaths([
	"/application/libraries",
	"/application/Command",
	"/application"
]);

// plugins
$path = APP_PATH . "/application/plugins";
$iterator = new DirectoryIterator($path);

foreach ($iterator as $item) {
    if (!$item->isDot() && $item->isDir()) {
        include($path . "/" . $item->getFilename() . "/initialize.php");
    }
}

// 3. load and initialize the Configuration class 
$configuration = new Framework\Configuration(array(
    "type" => "ini"
));
Framework\Registry::set("configuration", $configuration->initialize());

// 4. load and initialize the Database class – does not connect
$database = new Framework\Database();
Framework\Registry::set("database", $database->initialize());

// 5. load and initialize the Cache class – does not connect
$cache = new Framework\Cache(['type' => 'memcached']);
Framework\Registry::set("cache", $cache->initialize());
$redisCache = new Framework\Cache(['type' => 'redis']);
Framework\Registry::set("redis", $redisCache->initialize());

// Load the logger
$logger = new Framework\Logger();
Framework\Registry::setLogger($logger->initialize());

// 6. load and initialize the Session class 
$session = new Framework\Session();
Framework\Registry::set("session", $session->initialize());

// 7. load the Router class and provide the url + extension
$router = new Framework\Router(array(
    "url" => isset($_GET["url"]) ? $_GET["url"] : "auth/login",
    "extension" => !empty($_GET["extension"]) ? $_GET["extension"] : "html"
));
Framework\Registry::set("router", $router);

// include custom routes 
$routes = include(APP_PATH . "/application/routes.php");
// add defined routes
foreach ($routes as $route) {
    $router->addRoute(new Framework\Router\Route\Simple($route));
}

$router->dispatch();
