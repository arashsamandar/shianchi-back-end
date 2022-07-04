<?php
/**
 * Created by PhpStorm.
 * User: wb-admin
 * Date: 19/07/17
 * Time: 15:49
 */

namespace Wego\Services\Notification;


use Maknz\Slack\Client;

class SlackNotifier implements Notifiable
{

    private $client, $message, $receiverId;
    public function __construct()
    {
        $this->client = new Client('https://hooks.slack.com/services/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');
    }

    public function send()
    {
        $this->client->to($this->receiverId)->send($this->message);
    }

    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    public function setReceiver($receiverId)
    {
        $this->receiverId = $receiverId;
        return $this;
    }
}