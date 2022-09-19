<?php
namespace Cloudstuff\ApiUtil\Mailing;

/**
 * 
 * @package Cloudstuff Standard Shared Libraries
 * @author Rohit <rohit@trackier.com>
 */

 interface ICLient {
     public function send(Params $param);
 }