<?php


class SPODNOTIFICATION_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function settings($params)
    {
        $this->setPageTitle('Notification server monitor');
        $this->setPageHeading('Notification server monitor');

        $form = new Form('settings');
        $this->addForm($form);

        $submit = new Submit('add');

        $field = new HiddenField('running');
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
        }

        $form->addElement($submit);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST))
        {
            $data = $form->getValues();

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

            BOL_PreferenceService::getInstance()->savePreference($preference);
        }
    }
}