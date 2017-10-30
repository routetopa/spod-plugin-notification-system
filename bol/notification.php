<?php

class SPODNOTIFICATION_BOL_Notification extends OW_Entity
{
    /**
     * @var string
     */
    public $data;
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $plugin;
    /**
     * @var string
     */
    public $action;
    /**
     * @var string
     */
    public $subAction;
    /**
     * @var integer
     */
    public $targetUserId;
    /**
     * @var integer
     */
    public $timestamp;
}
