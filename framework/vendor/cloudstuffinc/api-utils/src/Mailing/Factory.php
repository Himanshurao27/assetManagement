<?php
namespace Cloudstuff\ApiUtil\Mailing;

/**
 * Mailing Factory which will create Mailjet object
 * 
 * @package Cloudstuff Standard Shared Libraries
 * @author Rohit <rohit@trackier.com>
 */
class Factory {
    const MAILJET = 'mailjet';
    const MAILGUN = 'mailgun';
    public static function make(string $type, Config $conf) {
        switch($type) {
            case self::MAILJET:
                $mailer = new MailjetClient($conf);
                break;
            
            case self::MAILGUN:
                break;
            
            default:
                throw new \Cloudstuff\ApiUtil\Exception\Core("Invalid client type");
        }
        return $mailer;
    }
}