<?php

OW::getPluginManager()->addPluginSettingsRouteName('spodnotification', 'notification-settings');

$sql = 'CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'spod_notification_notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `data` text,
  `type` text,
  `plugin` text,
  `action` text,
  `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'spod_notification_registered_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `type` text,
  `plugin` text,
  `action` text,
  `frequency` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;
';

OW::getDbo()->query($sql);

/*$authorization = OW::getAuthorization();
$groupName = 'spodnotification';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'view', true);
$authorization->addAction($groupName, 'add_comment');*/