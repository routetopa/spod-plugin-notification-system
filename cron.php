<?php

class SPODNOTIFICATION_Cron extends OW_Cron
{
    public function __construct()
    {
        parent::__construct();

        //$this->addJob('chechServerStatus', 60);
        $this->addJob('sendEveryDayEmailNotification'  , 60 * 24);//OneDay
        $this->addJob('sendEveryMouthEmailNotification', 60 * 24 * 30);//OneMonth
    }

    public function run()
    {
        // TODO: Implement run() method.
    }

    private function chechServerStatus()
    {
        $connection = @fsockopen('localhost', '3000');
        $preference = BOL_PreferenceService::getInstance()->findPreference('spodnotification_admin_run_status');
        $spodnotification_admin_run_status = empty($preference) ? "" : $preference->defaultValue;

        if (!is_resource($connection) && $spodnotification_admin_run_status == "RUN")
        {
            //chdir(OW::getPluginManager()->getPlugin('spodnotification')->getRootDir() . '/lib');
            //shell_exec("sh ./run_server.sh");
            shell_exec("service spod-notification-service start");
        }
    }

    private function sendEveryDayEmailNotification(){
        SPODNOTIFICATION_CLASS_EventHandler::getInstance()->sendEmailNotificationProcess(SPODNOTIFICATION_CLASS_Consts::FREQUENCY_EVERYDAY);
    }

    private function sendEveryMouthEmailNotification(){
        SPODNOTIFICATION_CLASS_EventHandler::getInstance()->sendEmailNotificationProcess(SPODNOTIFICATION_CLASS_Consts::FREQUENCY_EVERYMONTH);
    }


}