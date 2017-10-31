<?php

class SPODNOTIFICATION_CLASS_BaseEventNotification
{
    public $plugin;
    public $action;
    public $subAction;
    public $targetUserId;

    public function __construct($plugin, $action, $subAction, $targetUserId=null)
    {
        $this->plugin       = $plugin;
        $this->action       = $action;
        $this->subAction    = $subAction;
        $this->targetUserId = $targetUserId;
    }

    public function save()
    {
        SPODNOTIFICATION_BOL_Service::getInstance()->addNotification(serialize($this));
    }

}