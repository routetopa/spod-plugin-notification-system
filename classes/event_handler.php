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

    public function baseAddComment(OW_Event $event)
    {
        $params = $event->getParams();

        $entityId = $params['entityId'];
        $userId = $params['userId'];
        $commentId = $params['commentId'];

        $comment = BOL_CommentService::getInstance()->findComment($commentId);

        try
        {
            $client = new Client(new Version1X('http://localhost:3000'));
            $client->initialize();
            //$client->emit('chat message', ['chat message' => 'bar']);

            $tChatController = new SPODTCHAT_CTRL_Ajax();
            $commentListRendered = $tChatController->getCommentListRendered();

            $client->emit('chat message', [$commentListRendered]);
            $client->close();
        }
        catch(Exception $e)
        {}

    }

    public function emitNotification($map){
        try
        {
            $client = new Client(new Version1X('http://localhost:3000'));
            $client->initialize();
            $client->emit('realtime_notification', $map);
            $client->close();
        }
        catch(Exception $e)
        {}
    }

    public function addNotification(OW_Event $event){
        $params = $event->getParams();
        SPODNOTIFICATION_BOL_Service::getInstance()->addNotification(
            $params['plugin'],
            $params['type'],
            $params['action'],
            $params['data']
        );

        $this->sendEmailNotificationProcess(SPODNOTIFICATION_CLASS_Consts::FREQUENCY_IMMEDIATELY);
    }

    /*EMAIL NOTIFICATION STUFF*/
    private function getEmailContentHtml($userId, $content)
    {
        $date = getdate();
        $time = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);

        //SET EMAIL TEMPLETE
        $template = OW::getPluginManager()->getPlugin('spodnotification')->getCmpViewDir() . 'email_notification_template_html.html';
        $this->setTemplate($template);

        //USER AVATAR FOR THE NEW MAIL
        $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array(OW::getUser()->getId()))[OW::getUser()->getId()];
        $this->assign('userName', BOL_UserService::getInstance()->getDisplayName($userId));
        $this->assign('string', $content);
        $this->assign('avatar', $avatar);
        $this->assign('time', $time);

        return parent::render();
    }

    private function getEmailContentText($message){
        $date = getdate();
        $time = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);

        $template = OW::getPluginManager()->getPlugin('spodnotification')->getCmpViewDir() . 'email_notification_template_text.html';
        $this->setTemplate($template);

        $this->assign('nl', '%%%nl%%%');
        $this->assign('tab', '%%%tab%%%');
        $this->assign('space', '%%%space%%%');
        $this->assign('string', $message);

        $content = parent::render();
        $search = array('%%%nl%%%', '%%%tab%%%', '%%%space%%%');
        $replace = array("\n", '    ', ' ');

        return str_replace($search, $replace, $content);
    }

    public function sendEmailNotificationProcess($frequency)
    {
        $mail_ready_to_send = array();
        $notifications = SPODNOTIFICATION_BOL_Service::getInstance()->getAllNotifications();
        foreach($notifications as $notification){
            $users = SPODNOTIFICATION_BOL_Service::getInstance()->getRegisteredByPluginAndAction($notification->plugin ,$notification->action, $frequency);
            foreach($users as $user){
                $user = BOL_UserService::getInstance()->findUserById($user);
                if ( empty($user) ) continue;
                $data= json_decode($notification->data);
                $mail = OW::getMailer()->createMail()
                    ->addRecipientEmail($user->email)
                    ->setHtmlContent($this->getEmailContentHtml($user->id, $data->message))
                    ->setTextContent($this->getEmailContentText($data->message))
                    ->setSubject($data->subject);

                $ready = new stdClass();
                $ready->mail = $mail;
                $ready->notificationId = $notification->id;
                array_push($mail_ready_to_send, $ready);
            }
        }

        foreach($mail_ready_to_send as $mail){
            try
            {
                //OW::getMailer()->send($mail->mail);
                BOL_MailService::getInstance()->send($mail->mail);
                SPODNOTIFICATION_BOL_Service::getInstance()->deleteNotificationById(intval($mail->notificationId));
            }
            catch ( Exception $e )
            {
                //Skip invalid notification
            }
        }
    }
}