LMSACE Connect Moodle Side Plugin.
=========================================

LMSACE Connect plugin helps moodle course creators to sell their moodle courses as product in popular e-commerce WooCommerce.
This plugin helps the moodle administrators to generate the webservice tokens and other details which is esstential for create connection with woocommerce.

This plugin will enable the webservice, enable the rest protocol and create token for the webservice.
Also, it will create a new service by adding needed external function.

You can copy the webservice token on
> Go to 'Site administration' -> 'server' -> 'Web services' -> 'Generate LAConnect Connection', here on 'General' tab you will see the "**Site URl and Site Tokens**" in textbox..

### Version

Plugin version: 2.0

Released on: 28 March 2022

Authors: https://lmsace.com, LMSACE Dev Team

### Git Repository

Public Git Repository

https://github.com/lmsace/lmsace-connect-moodle


## Documenation

The official documentation to setup connections and SSO between Moodle and Woocommerce using LMSACE Connect can be found at https://github.com/lmsace/lmsace-connect-woocommerce/wiki


### Steps to generate connection data.

1. Login as site administrator.
2. Go to '*Site administration*' -> '*server*' -> '*Web services*' -> '*Generate LAConnect Connection*'
3. Go to "**General**" tab, Enable the webservice by clicking the "*Enable*" button.
4. Enable the reset webprotocal by clicking the "*Enable*" button.
5. On here, go to '**Generate Token**' tab. Select any of the user you want to assign webservices.
> Note: Selected user must have assign role capability and other capabilities. When the token user doesn't have the needed capabilities, that missing capabilities are listed here. Please assign all the listed capabilities to that selected user.
6. Finally, you can copy the "*Site URL*" and "*Webservice Token*" to WordPress side LMSACE connect plugin.

### Installation steps using ZIP file.

1. Clone LMSACE Connect Git repository in any of your prefered location.
2. Rename the folder name into '**lmsace_connect**'. And zip the folder.
3. Next login as Site administrator
4. Go to '*Site Administration*' -> '*Plugins*' -> '*Upload Plugin*', On here upload the plugin zip '**lmsace_connect.zip**'.
4. Go to ‘Site administration’ -> ‘Notifications’ , here on ‘Plugins check’ page you will see the lmsace_connect Local plugin in listing.
5. Click the “Upgrade Moodle database now” button displayed on bottom of the page
> You will get success message once the plugin installed successfully.
6. By clicking “Continue” button on success page. you will redirect to the admin notification page.


### Installation steps using Git.

1. Clone LMSACE Connect plugin Git repository into the folder '*local*'.
2. Rename the folder name into '**lmsace_connect**'.
3. Go to ‘Site administration’ -> ‘Notifications’ , here on ‘Plugins check’ page you will see the lmsace_connect Local plugin in listing.
4. Click the “Upgrade Moodle database now” button displayed on bottom of the page.
> You will get success message once the plugin installed successfully.
5. By clicking “Continue” button on success page. You will redirect to the admin notification page.


## Support

If you need any assistance with the LMSACE Connect SSO plugin, please contact LMSACE support at info@lmsace.com.

### Review

You feel LMSACE Connect helps you to sell courses easier. **Give a review and rating on wordpress.org**, it means lot for us. We are looking for your comments.


### Changes log

1. **Version 2.0**
    * Improved course imports.
    * Implemented SSO between wordpress and moodle
    * Included course data with course imports.
    * Bug fixes in user creation.

