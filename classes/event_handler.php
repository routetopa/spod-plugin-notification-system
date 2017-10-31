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
        if($frequency != SPODNOTIFICATION_CLASS_Consts::FREQUENCY_IMMEDIATELY)
        {
            $notifications = SPODNOTIFICATION_BOL_Service::getInstance()->getAllNotificationsByFrequency($frequency);
        }

        foreach($notifications as $notification){
            $users = SPODNOTIFICATION_BOL_Service::getInstance()->getRegisteredUsersForNotification($notification, $frequency);
            $notification->send($users);
        }
    }

    public function sendNotificationBatchProcess__($frequency)
    {
        $notification_ready_to_send        = array();
        $notification_delayed_messages     = array();

        $notifications = SPODNOTIFICATION_BOL_Service::getInstance()->getAllNotificationsByFrequency($frequency);

        foreach($notifications as $notification){

            $notification->type = json_decode($notification->type);
            $notification->data = json_decode($notification->data);

            $users = SPODNOTIFICATION_BOL_Service::getInstance()->getRegisteredUsersForNotification($notification, $frequency);

            foreach($users as $user){

                if($user->userId == $notification->data->owner_id)
                    continue;

                $user = BOL_UserService::getInstance()->findUserById($user->userId);
                if ( empty($user) ) continue;

                switch($frequency){
                    case SPODNOTIFICATION_CLASS_Consts::FREQUENCY_IMMEDIATELY:
                        $ready = new stdClass();;
                        $ready->notification = $notification;
                        $notification_ready_to_send = $this->fillNotificationStructure($notification_ready_to_send, $user, $ready);
                        break;
                    default:
                        $ready = new stdClass();
                        $ready->notification = $notification;;
                        $notification_delayed_messages = $this->fillNotificationStructure($notification_delayed_messages, $user, $ready);
                        break;
                }
            }
        }

        foreach (array_keys($notification_delayed_messages) as $userId){
            $notification_content = "";
            foreach ($notification_delayed_messages[$userId] as $delayed_message) {
                $notification_content .= $delayed_message->data . "<br><br><br>";
                array_push($notificationIds, $delayed_message->notificationId);
            }
            $user = BOL_UserService::getInstance()->findUserById($userId);

            $ready = new stdClass();
            $data = json_decode($ready->notification);
            $data->message  = $notification_content;
            $data->subject = OW::getLanguage()->text('spodnotification','email_notifications_subject_delayed');
            $ready->notification->data = json_encode($data);
            $notification_ready_to_send = $this->fillNotificationStructure($notification_ready_to_send, $user, $ready);
        }

        foreach(array_keys($notification_ready_to_send) as $userId){
            try
            {
                foreach ($notification_ready_to_send[$userId][0]->notification->type as $type){
                    call_user_func(array($this, $this->sendNotificationFunctions[$type]), $userId, $notification_ready_to_send[$userId][0]->notification);
                }
            }
            catch ( Exception $e )
            {
                //Skip invalid notification
                error_log($e->getMessage());
            }
        }
    }
}