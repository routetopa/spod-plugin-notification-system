<?php

OW::getPluginManager()->addPluginSettingsRouteName('spodnotification', 'notification-settings');

$authorization = OW::getAuthorization();
$groupName = 'spodnotification';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'view', true);
$authorization->addAction($groupName, 'add_comment');