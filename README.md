# Notification system

### About this plugin

Notification system is a plugin for synchronous notification on SPOD. The notifications are use to get real time feedback about users activities in some
SPOD area for instance:

* Cocreation :
   - Users add/remove a new datalet in a room
   - Users modify metadata information related to the dataset
   - Users add comment in the discussion area

* Agora :
   - Users add comment in the discussion
   - Update information in the graph area

In the *Notification system plugin admin panel* the admin user can start/stop the notification server. **Note that by stopping the server the system will not provide a real time
feedback to the users activities**

### Installation guide

To install *Notification system* plugin:

* Clone this project by following the github instruction on *SPOD_INSTALLATION_DIR/ow_plugins*
* Install the plugin on SPOD by *admin plugins panel*
* Install SPOD Notification server :
  - Run the installation script in *SPOD_INSTALLATION_DIR/NOTIFYCATION_SYSTEM_PLUGIN_INSTALLATION_DIR/static/script/install_spod_notification_service.sh* and select *All*.
    **This script must be run as root**

### Start the service
1. Copy the **static/scripts/spod-notification-service** file into **/etc/systemd/system/**
2. Reboot the system
3. **systemctl daemon-reload**
4. **service spod-notification start**

### Copy the plugin
Pay attention to the plugin name:
1. Rename plugin to **notification_system**
2. If plugin is already installed, uninstall it and then re-install
3.  **service spod-notification restart**
4.  Check key from the SPOD admin panel
