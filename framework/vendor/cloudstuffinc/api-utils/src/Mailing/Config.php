<?php
namespace Cloudstuff\ApiUtil\Mailing;

/**
 * Config class for creating mailing client
 * 
 * @package Cloudstuff Standard Shared Libraries
 * @author Rohit <rohit@trackier.com>
 */

class Config {
    /**
     * @var string $publicKey
     */
    public $publicKey;

    /**
     * @var string $privateKey
     */
    public $privateKey;

    public function __construct(array $conf = []) {
        foreach ($conf as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}