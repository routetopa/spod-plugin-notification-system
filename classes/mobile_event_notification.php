<?php

class SPODNOTIFICATION_CLASS_MobileEventNotification extends SPODNOTIFICATION_CLASS_BaseEventNotification
{
    public static $TYPE = 'mobile';

    private $title;
    private $message;
    private $data;

    public function __construct($plugin, $action, $subAction, $targetUserId = null, $title, $message, $data)
    {
        parent::__construct($plugin, $action, $subAction, $targetUserId);

        $this->title   = $title;
        $this->message = $message;
        $this->data    = $data;
    }

    public function send($targets)
    {
        return;
        //$mail = new SPODNOTIFICATION_CLASS_ElasticMailSender($this, $targets);
        //$mail->send();
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getData()
    {
        return $this->data;
    }
}