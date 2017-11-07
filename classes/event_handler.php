<?php

require_once OW::getPluginManager()->getPlugin('spodnotification')->getRootDir()
    . 'lib/vendor/autoload.php';

use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version1X;

class SPODNOTIFICATION_CLASS_EventHandler extends OW_ActionController
{
    private static $classInstance;

    public static function getInstance()
    {
        if(self::$classInstance === null)
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    // Handle event
    public function init()
    {
        OW::getEventManager()->bind('notification_system.add_notification', array($this, 'addNotification'));
    }

    public function emitNotification($map){
        try
        {
            $client = new Client(new Version1X('http://localhost:3000/realtime_notification'));
            $client->initialize();
            $client->emit('realtime_notification', $map);
            $client->close();
        }
        catch(Exception $e)
        {}
    }

    public function fillNotificationStructure($structure, $user, $notification){

        if(array_key_exists($user->id, $structure))
            array_push($structure[$user->id], $notification);
        else
            $structure[$user->id] = array($notification);
        return $structure;
    }

    public function addNotification(OW_Event $event)
    {
        foreach ($event->getParams()['notifications'] as $notification)
            $notification->save();

        $this->sendNotificationBatchProcess(SPODNOTIFICATION_CLASS_Consts::FREQUENCY_IMMEDIATELY, $event->getParams()['notifications']);
    }

    public function sendNotificationBatchProcess($frequency, $notifications=null)
    {
        $notifications = ($frequency == SPODNOTIFICATION_CLASS_Consts::FREQUENCY_IMMEDIATELY)
            ? $notifications
            : SPODNOTIFICATION_BOL_Service::getInstance()->getAllNotificationsByFrequency($frequency);

        $grouped_notifications = array();
        foreach($notifications as $notification){
            $users = SPODNOTIFICATION_BOL_Service::getInstance()->getRegisteredUsersForNotification($notification->notification, $frequency);
            if($frequency == SPODNOTIFICATION_CLASS_Consts::FREQUENCY_IMMEDIATELY)
               $notification->send($users);
            else{
                //For each users save the last notification, by type, related to each plugins
                foreach ( $users as $user )
                {
                    $grouped_notifications[$user->userId][$notification->notification->plugin]['count'] += 1;
                    $grouped_notifications[$user->userId]['user'] = $user;
                    $message =
                        "<i>" .
                        str_replace(
                            ["#N#", "#PLUGIN#"],
                            [$grouped_notifications[$user->userId][$notification->notification->plugin]['count'], $notification->notification->plugin],
                            OW::getLanguage()->text('spodnotification','email_notifications_delayed_news_on_plugin')) .
                        "</i>" .
                        "<br><br>" .
                        $notification->notification->getBasicMessage();

                    $type = (new ReflectionClass(get_class($notification->notification)))->getStaticPropertyValue("TYPE");
                    $grouped_notifications[$user->userId][$notification->notification->plugin]['message'][$type] = $message;
                }
            }
        }
        //send grouped notifications (EVERY_DAY, EVERY_MONTH)
        foreach ($grouped_notifications as $gnotification)
        {
            $mail_message   = $this->getMessageForAllPluginAndNotificationType($gnotification, SPODNOTIFICATION_CLASS_MailEventNotification::$TYPE);
            if(!empty($mail_message)) {
                $mail = new SPODNOTIFICATION_CLASS_MailEventNotification(
                    SPODNOTIFICATION_CLASS_Consts::GLOABL_ACTION_PLUGIN,
                    SPODNOTIFICATION_CLASS_Consts::GLOBAL_ACTION,
                    SPODNOTIFICATION_CLASS_Consts::GLOBAL_ACTION,
                    null,
                    OW::getLanguage()->text('spodnotification','email_notifications_subject_delayed'),
                    $mail_message,
                    ""
                );
                $mail->send([$gnotification['user']]);
            }

            $mobile_message = $this->getMessageForAllPluginAndNotificationType($gnotification, SPODNOTIFICATION_CLASS_MobileEventNotification::$TYPE);
            if(!empty($mobile_message))
            {
                $mobile = new SPODNOTIFICATION_CLASS_MobileEventNotification(
                    SPODNOTIFICATION_CLASS_Consts::GLOABL_ACTION_PLUGIN,
                    SPODNOTIFICATION_CLASS_Consts::GLOBAL_ACTION,
                    SPODNOTIFICATION_CLASS_Consts::GLOBAL_ACTION,
                    null,
                    "SPOD",
                    $mobile_message,
                    []
                );
                $mobile->send([$gnotification['user']]);
            }
        }

    }

    private function getMessageForAllPluginAndNotificationType($messages, $notification_type)
    {
        $message = "";
        array_walk_recursive
            ($messages,
                function($item, $key) use (&$message, $notification_type)
                {
                   if($key == $notification_type)
                   {
                       $message .= $item . "<br><br><br><br>";
                   }
                }
            );

        return $message;

    }
}