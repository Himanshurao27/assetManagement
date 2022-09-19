<?php
namespace Cloudstuff\ApiUtil\Mailing;
use \Mailjet\Resources;

/**
 * Mailjet Client
 * 
 * @package Cloudstuff Standard Shared Libraries
 * @author Rohit <rohit@trackier.com>
 */

class MailjetClient implements IClient {
    /**
     * @var Config Options object
     */
    protected $conf;

    /**
     * @var object Mailjet object
     */
    protected $mj;

    /**
     * MailjetClient constructor
     * @param Config $conf Client Config
     */
    public function __construct(Config $conf) {
        $this->conf = $conf;
        $this->mj = new \Mailjet\Client($conf->publicKey, $conf->privateKey, true, ['version' => 'v3.1']);

    }

    public function send(Params $params) {
        $recipientEmail = []; $ccEmail = []; $bccEmail = [];
        foreach ($params->recipientEmails as $email) {
            $recipientEmail[] = (object) ['Email' => $email];
        }

        foreach ($params->cc as $email) {
            $ccEmail[] = (object) ['Email' => $email];
        }

        foreach ($params->bcc as $email) {
            $bccEmail[] = (object) ['Email' => $email];
        }
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => $params->senderEmail,
                        'Name' => $params->senderName
                    ],
                    'ReplyTo' => [
                        'Email' => $params->replyToEmail,
                    ],
                    'To' => $recipientEmail,
                    'CC' => $ccEmail,
                    'BCC' => $bccEmail,
                    'Subject' => $params->subject,
                    'TextPart' => $params->textPart,
                    'HTMLPart' => $params->htmlPart
                ]
            ]
        ];
        $response = $this->mj->post(Resources::$Email, ['body' => $body]);
        return $response;
    }
}