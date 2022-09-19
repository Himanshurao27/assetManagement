<?php
namespace Cloudstuff\ApiUtil\Messaging;

/**
 * Messaging Factory which will create corresponding messaging class object
 *
 * @package Cloudstuff Standard Shared Libraries
 * @author Hemant Mann <hemant.mann@cloudstuff.tech>
 * @since 1.0.0
 */
class Factory {
    const TYPE_GOOGLE_PUBSUB = 'google_pubsub';

    public static function make(string $type, Config $opts) {
        switch ($type) {
            case self::TYPE_GOOGLE_PUBSUB:
                $obj = new GoogleClient($opts);
                break;
            
            default:
                throw new \Cloudstuff\ApiUtil\Exception\Core("Invalid client type");
        }
        return $obj;
    }
}
