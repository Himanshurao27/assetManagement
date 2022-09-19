<?php
namespace Cloudstuff\ApiUtil\Messaging;

/**
 * All the messaging classes should implement IClient interafce
 *
 * @package Cloudstuff Standard Shared Libraries
 * @author Hemant Mann <hemant.mann@cloudstuff.tech>
 * @since 1.0.1
 */
interface IClient {
    public function send(string $data);
}
