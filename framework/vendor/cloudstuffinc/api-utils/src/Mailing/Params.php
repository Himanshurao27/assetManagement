<?php
namespace Cloudstuff\ApiUtil\Mailing;

/**
 * Param class for sending parameters to the mailing client
 * 
 * @package Cloudstuff Standard Shared Libraries
 * @author Rohit <rohit@trackier.com>
 */

class Params {
    /**
     * @var string $senderEmail
     */
    public $senderEmail;

    /**
     * @var string $senderEmail
     */
    public $senderName;

    /**
     * @var string $senderEmail
     */
    public $replyToEmail;

    /**
     * @var string[] $recipientEmails
     */
    public $recipientEmails;

    /**
     * @var string[] $cc
     */
    public $cc;

    /**
     * @var string[] $bcc
     */
    public $bcc;

    /**
     * @var string $subject
     */
    public $subject;

    /**
     * @var string $textPart
     */
    public $textPart;

    /**
     * @var string $htmlPart
     */
    public $htmlPart;
    
    public function __construct(array $params = []) {
        foreach ($params as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}