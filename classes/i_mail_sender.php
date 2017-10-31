<?php

interface SPODNOTIFICATION_CLASS_IMailSender
{
    public function __construct($notification, $targets);
    function send();
}