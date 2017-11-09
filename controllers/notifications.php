<?php

class SPODNOTIFICATION_CTRL_Notifications extends OW_ActionController
{

    public function settings()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('spodnotification')->getStaticJsUrl() . 'notification_system.js');
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('spodnotification')->getStaticCssUrl() . 'notification_settings.css');

        OW::getDocument()->getMasterPage()->setTemplate(OW::getPluginManager()->getPlugin('spodnotification')->getRootDir() . 'master_pages/main.html');

        $actions = SPODNOTIFICATION_BOL_Service::getInstance()->collectActionList();

        $tplActions = array();

        foreach ( $actions as $action )
        {
            $result = SPODNOTIFICATION_BOL_Service::getInstance()->isUserRegisteredForAction(OW::getUser()->getId(), $action['section'], $action['action'], SPODNOTIFICATION_CLASS_MailEventNotification::$TYPE);

            $action['registered'] = ($result != null) ? true : false;
            $action['frequency']  = ($result != null) ? $result->frequency : '';
            if ( empty($tplActions[$action['section']]) )
            {
                $tplActions[$action['section']] = array(
                    'label' => $action['sectionLabel'],
                    'icon' => empty($action['sectionIcon']) ? '' : $action['sectionIcon'],
                    'actions' => array()
                );
            }

            if($action['sectionClass'] == 'action')
                $tplActions[$action['section']]['actions'][$action['action']] = $action;
            else
                $tplActions[$action['section']]['actions'][$action['parentAction']]["subAction"][] = $action;
        }

        $js = UTIL_JsGenerator::composeJsString('
                NOTIFICATION                                            = {}
                NOTIFICATION.userId                                     = {$userId}
                NOTIFICATION.ajax_notification_register_user_for_action = {$ajax_notification_register_user_for_action}
            ', array(
            'userId'                                     => OW::getUser()->getId(),
            'ajax_notification_register_user_for_action' => OW::getRouter()->urlFor('SPODNOTIFICATION_CTRL_Ajax', 'registerUserForAction'),
        ));
        OW::getDocument()->addOnloadScript($js);

        $this->assign('actions', $tplActions);
    }
}