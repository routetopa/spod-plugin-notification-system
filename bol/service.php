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

    public function getAllNotificationsByFrequency($frequency)
    {
        $result = null;
        switch($frequency){
            case SPODNOTIFICATION_CLASS_Consts::FREQUENCY_IMMEDIATELY:
                $example = new OW_Example();
                $example->setOrder('timestamp DESC');
                $result = SPODNOTIFICATION_BOL_NotificationDao::getInstance()->findListByExample($example);
                /*$result = SPODNOTIFICATION_BOL_NotificationDao::getInstance()->findAll();
                $result = array($result[count($result) - 1]);*/
                $result = array($result[0]);
                break;
            case SPODNOTIFICATION_CLASS_Consts::FREQUENCY_EVERYDAY:
                $today_timestamp     = strtotime('today midnight');
                $tomorrow_timestamp  = strtotime('tomorrow midnight');

                $example = new OW_Example();
                $example->andFieldBetween('timestamp', $today_timestamp, $tomorrow_timestamp);
                $result = SPODNOTIFICATION_BOL_NotificationDao::getInstance()->findListByExample($example);
                break;
            case SPODNOTIFICATION_CLASS_Consts::FREQUENCY_EVERYMONTH:
                $current_month_timestamp = strtotime('first day of this month', time());
                $next_month_timestamp    = strtotime('first day of next month', time());

                $example = new OW_Example();
                $example->andFieldBetween('timestamp', $current_month_timestamp, $next_month_timestamp);
                $result = SPODNOTIFICATION_BOL_NotificationDao::getInstance()->findListByExample($example);
                break;
        }
        return $result;
    }

    public function getNotificationByPlugin($plugin)
    {
        $example = new OW_Example();
        $example->andFieldEqual('plugin', $plugin);
        $result = SPODNOTIFICATION_BOL_NotificationDao::getInstance()->findListByExample($example);
        return $result;
    }

    public function deleteNotificationById($id)
    {
        SPODNOTIFICATION_BOL_NotificationDao::getInstance()->deleteById($id);
    }

    public function deleteExpiredNotifications()
    {
        $current_month_timestamp = strtotime('first day of this month', time());

        $example = new OW_Example();
        $example->andFieldLessThan('timestamp', $current_month_timestamp);
        SPODNOTIFICATION_BOL_NotificationDao::getInstance()->deleteByExample($example);
    }

    public function addNotification($plugin, $type, $action, $data){
        $notification            = new SPODNOTIFICATION_BOL_Notification();
        $notification->plugin    = $plugin;
        $notification->type      = $type;
        $notification->action    = $action;
        $notification->data      = $data;
        $notification->timestamp = time();

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

    public function addUserRegistrationId($userId, $registrationId)
    {
        if($this->getRegistrationIdForUser($userId) != null){
           SPODNOTIFICATION_BOL_UserRegistrationIdDao::getInstance()->updateRegistrationId($userId, $registrationId);
        }else{
            $r                 = new SPODNOTIFICATION_BOL_UserRegistrationId();
            $r->userId         = $userId;
            $r->registrationId = $registrationId;
            $r->timestamp      = time();
            SPODNOTIFICATION_BOL_UserRegistrationIdDao::getInstance()->save($r);
        }
    }

    public function getRegistrationIdForUser($userId){
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $result = SPODNOTIFICATION_BOL_UserRegistrationIdDao::getInstance()->findObjectByExample($example);
        return !empty($result) ? $result->registrationId : null;
    }


}
