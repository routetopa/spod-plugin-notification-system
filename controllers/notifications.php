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

        OW::getDocument()->setHeading(OW::getLanguage()->text('spodnotification', 'setup_page_heading'));
        OW::getDocument()->setHeadingIconClass('ow_ic_mail');
        OW::getDocument()->setTitle(OW::getLanguage()->text('spodnotification', 'setup_page_title'));

        $actions = SPODNOTIFICATION_BOL_Service::getInstance()->collectActionList();

        $tplActions = array();

        foreach ( $actions as $action )
        {
            $result = SPODNOTIFICATION_BOL_Service::getInstance()->isUserRegisteredForAction(OW::getUser()->getId(), $action['section'], $action['action']);
            $action['registered'] = ($result != null) ? true : false;
            $action['frequency']  = $result->frequency;
            if ( empty($tplActions[$action['section']]) )
            {
                $tplActions[$action['section']] = array(
                    'label' => $action['sectionLabel'],
                    'icon' => empty($action['sectionIcon']) ? '' : $action['sectionIcon'],
                    'actions' => array()
                );
            }

            $tplActions[$action['section']]['actions'][$action['action']] = $action;

            $js = UTIL_JsGenerator::composeJsString('
                NOTIFICATION                                            = {}
                NOTIFICATION.userId                                     = {$userId}
                NOTIFICATION.ajax_notification_register_user_for_action = {$ajax_notification_register_user_for_action}
            ', array(
                'userId'                                     => OW::getUser()->getId(),
                'ajax_notification_register_user_for_action' => OW::getRouter()->urlFor('SPODNOTIFICATION_CTRL_Ajax', 'registerUserForAction'),
            ));
            OW::getDocument()->addOnloadScript($js);
        }

        $this->assign('actions', $tplActions);
    }
}