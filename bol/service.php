<?php
class SPODNOTIFICATION_BOL_Service
{
    /**
     * Singleton instance.
     *
     * @var SPODNOTIFICATION_BOL_Service
     */
    private static $classInstance;

    private $defaultRuleList = array();

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return SPODNOTIFICATION_BOL_Service
     */
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
    }

    public function getAllNotifications()
    {
        return SPODNOTIFICATION_BOL_NotificationDao::getInstance()->findAll();
    }

    public function getNotificationByPlugin($plugin)
    {
        $example = new OW_Example();
        $example->andFieldEqual('plugin', $plugin);
        $result = SPODNOTIFICATION_BOL_NotificationDao::getInstance()->findObjectByExample($example);
        return $result;
    }

    public function deleteNotificationById($id)
    {
        SPODNOTIFICATION_BOL_NotificationDao::getInstance()->deleteById($id);
    }

    public function addNotification($plugin, $type, $action, $data){
        $notification         = new SPODNOTIFICATION_BOL_Notification();
        $notification->plugin = $plugin;
        $notification->type   = $type;
        $notification->action = $action;
        $notification->data   = $data;

        SPODNOTIFICATION_BOL_NotificationDao::getInstance()->save($notification);
    }

    public function registerUserForNotification($userId, $plugin, $type, $action, $frequency){

        if($this->isUserRegisteredForAction($userId,$plugin,$action) != null){
            SPODNOTIFICATION_BOL_RegisteredUserDao::getInstance()->updateFrequency($userId,$plugin,$action, $frequency);
            return;
        }

        $reguser             = new SPODNOTIFICATION_BOL_RegisteredUser();
        $reguser->userId     = $userId;
        $reguser->plugin     = $plugin;
        $reguser->type       = $type;
        $reguser->action     = $action;
        $reguser->frequency  = $frequency;
        SPODNOTIFICATION_BOL_RegisteredUserDao::getInstance()->save($reguser);
    }

    public function isUserRegisteredForAction($userId, $plugin, $action){
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('plugin', $plugin);
        $example->andFieldEqual('action', $action);
        $result = SPODNOTIFICATION_BOL_RegisteredUserDao::getInstance()->findObjectByExample($example);
        return $result;

    }

    public function deleteRegisteredUser($userId)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('userId', $userId);
        SPODNOTIFICATION_BOL_RegisteredUserDao::getInstance()->deleteByExample($ex);
    }

    public function deleteUserForNotification($userId, $plugin, $type, $action){
        $ex = new OW_Example();
        $ex->andFieldEqual('userId', $userId);
        $ex->andFieldEqual('plugin', $plugin);
        $ex->andFieldEqual('type', $type);
        $ex->andFieldEqual('action', $action);
        SPODNOTIFICATION_BOL_RegisteredUserDao::getInstance()->deleteByExample($ex);
    }

    public function getRegisteredByPluginAndAction($plugin, $action, $frequency)
    {
        $example = new OW_Example();
        $example->andFieldEqual('plugin', $plugin);
        $example->andFieldEqual('action', $action);
        $example->andFieldEqual('frequency', $frequency);
        $result = SPODNOTIFICATION_BOL_RegisteredUserDao::getInstance()->findListByExample($example);
        return $result;
    }

    public function collectActionList()
    {
        if ( empty($this->defaultRuleList) )
        {
            $event = new BASE_CLASS_EventCollector('spodnotification.collect_actions');
            OW::getEventManager()->trigger($event);

            $eventData = $event->getData();
            foreach ( $eventData as $item )
            {
                $this->defaultRuleList[$item['action']] = $item;
            }
        }

        return $this->defaultRuleList;
    }


}
