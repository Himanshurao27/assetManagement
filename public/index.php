<?php
ini_set('session.gc_maxlifetime', 7200);    // server should keep session data for AT LEAST 2 hour
session_set_cookie_params(7200);    // each client should remember their session id for EXACTLY 2 hour

ob_start();
define("DEBUG", True);
define("APP_PATH", str_replace(DIRECTORY_SEPARATOR, "/", dirname(dirname(__FILE__))));
define('FRAMEWORK_PATH', APP_PATH . '/framework');
define("URL", "http://". $_SERVER['HTTP_HOST']. $_SERVER['REQUEST_URI']);
define("CDN", "/assets/");
define("GCDN", "https://static.vnative.com/");

try {

    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: *');
    header('Access-Control-Allow-Headers: *');

    $requestMethod = $_SERVER['REQUEST_METHOD'] ?? '';
    if ($requestMethod == 'OPTIONS') {
        return;
    }

    require_once(APP_PATH . '/application/bootstrap.php');

    // 9. unset global variables
    unset($configuration);
    unset($database);
    unset($cache);
    unset($session);
    unset($router);
    unset($routes);
} catch (Exception $e) {

    // list exceptions
    $exceptions = array(
        "401" => array(
            "Framework\Router\Exception\Inactive"
        ),
        "404" => array(
            "Framework\Router\Exception\Action",
            "Framework\Router\Exception\Controller"
        ),
        "500" => array(
            "Framework\Cache\Exception",
            "Framework\Configuration\Exception",
            "Framework\Controller\Exception",
            "Framework\Core\Exception",

            "Framework\Database\Exception",
            "Framework\Model\Exception",
            "Framework\Request\Exception",
            "Framework\Router\Exception",
            "Framework\Session\Exception",

            "Framework\Template\Exception",
            "Framework\View\Exception",

            "MongoDB\Driver\Exception\Exception"
        )
    );

    $exception = get_class($e);

    // attempt to find the approapriate template, and render
    foreach ($exceptions as $template => $classes) {
        foreach ($classes as $class) {
            if ($class == $exception || is_subclass_of($exception, $class)) {
                header("Content-type: text/html");
                include(APP_PATH . "/application/views/layouts/errors/{$template}.php");
                exit;
            }
        }
    }

    // log or email any error

    // render fallback template
    header("Content-type: text/html");
    include(APP_PATH . "/application/views/layouts/errors/500.php");
    exit;
} catch (Error $e) {
    header("Content-type: text/html");
    include(APP_PATH . "/application/views/layouts/errors/500.php");
    exit;
}
?>
