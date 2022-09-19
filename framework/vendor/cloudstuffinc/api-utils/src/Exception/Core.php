<?php
namespace Cloudstuff\ApiUtil\Exception;

/**
 * Base Exception, all the custom exceptions will extend this class
 * making it easier for the caller to catch only the library specific exceptions
 *
 * @package Cloudstuff Standard Shared Libraries
 * @author Hemant Mann <hemant.mann@cloudstuff.tech>
 * @since 1.0.0
 */
class Core extends \Exception {}
