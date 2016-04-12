<?php

require_once OW::getPluginManager()->getPlugin('spodnotification')->getRootDir()
    . 'lib/vendor/autoload.php';

use ElephantIO\Client;
use ElephantIO\Engine\SocketIO\Version1X;

class SPODNOTIFICATION_CLASS_EventHandler
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
        //OW::getEventManager()->bind('base_add_comment', array($this, 'baseAddComment'));
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
}