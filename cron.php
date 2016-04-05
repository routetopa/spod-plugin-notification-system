<?php

class SPODNOTIFICATION_Cron extends OW_Cron
{
    public function __construct()
    {
        parent::__construct();

        $this->addJob('chechServerStatus', 2);
    }

    public function run()
    {
        // TODO: Implement run() method.
    }

    public function chechServerStatus()
    {
        $connection = @fsockopen('localhost', '3000');
        $preference = BOL_PreferenceService::getInstance()->findPreference('spodnotification_admin_run_status');
        $spodnotification_admin_run_status = empty($preference) ? "" : $preference->defaultValue;

        if (!is_resource($connection) && $spodnotification_admin_run_status == "RUN")
        {
            chdir(OW::getPluginManager()->getPlugin('spodnotification')->getRootDir() . '/lib');
            shell_exec("sh ./run_server.sh");
        }
    }
}