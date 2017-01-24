<?php


class SPODNOTIFICATION_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function settings($params)
    {
        $this->setPageTitle('Notification server');
        $this->setPageHeading('Notification server');

        $form = new Form('settings');
        $this->addForm($form);

        $submit = new Submit('add');

        /*$field = new HiddenField('running');
        $form->addElement($field);

        $connection = @fsockopen('localhost', '3000');

        if (is_resource($connection))
        {
            $submit->setValue('STOP');
            $this->assign('running', 'running');
            $field->setValue(1);
        }
        else
        {
            $submit->setValue('START');
            $this->assign('running', 'not running');
            $field->setValue(0);
        }*/

        $validatorPort = new IntValidator(1024, 65535);
        $notification_port = new TextField('notification_port');
        $preference = BOL_PreferenceService::getInstance()->findPreference('notification_port');
        $setting_notification_port = empty($preference) ? "3000" : $preference->defaultValue;
        $notification_port->setValue($setting_notification_port);
        $notification_port->setRequired();
        $notification_port->addValidator($validatorPort);
        $form->addElement($notification_port);

        $form->addElement($submit);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST))
        {
            $data = $form->getValues();

            chdir(OW::getPluginManager()->getPlugin('spodnotification')->getRootDir() . '/lib');
            $config = fopen("./config.js", "w+");
            fwrite($config,"var config = module.exports = {port:". $data['notification_port']."};");

            shell_exec("service spod-notification-service restart");

            /*$data = $form->getValues();

            $preference = BOL_PreferenceService::getInstance()->findPreference('spodnotification_admin_run_status');

            if(empty($preference))
                $preference = new BOL_Preference();

            $preference->key = 'spodnotification_admin_run_status';
            $preference->sortOrder = 1;
            $preference->sectionName = 'general';


            if($data['running'])
            {
                //is running
                $submit->setValue('START');
                $this->assign('running', 'not running');
                $field->setValue(0);

                //chdir(OW::getPluginManager()->getPlugin('spodnotification')->getRootDir() . '/lib');
                //shell_exec("sh ./stop_server.sh");
                shell_exec("service spod-notification-service stop");
                $preference->defaultValue = 'STOP';
            }
            else
            {
                //is not running
                //chdir(OW::getPluginManager()->getPlugin('spodnotification')->getRootDir() . '/lib');
                //shell_exec("sh ./run_server.sh");
                shell_exec("service spod-notification-service start");
                $preference->defaultValue = 'RUN';

                $submit->setValue('STOP');
                $this->assign('running', 'running');
                $field->setValue(1);
            }

            BOL_PreferenceService::getInstance()->savePreference($preference);*/
        }
    }
}