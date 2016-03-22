<?php

OW::getRouter()->addRoute(new OW_Route('notification-settings', '/spodnotification/settings', 'SPODNOTIFICATION_CTRL_Admin', 'settings'));
OW::getRouter()->addRoute(new OW_Route('spodnotification.test', '/spodnotification/test', "SPODNOTIFICATION_CTRL_Test", 'index'));

SPODNOTIFICATION_CLASS_EventHandler::getInstance()->init();