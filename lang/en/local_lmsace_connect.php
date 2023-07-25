<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Local lmsace connect - Language strings.
 *
 * @package   local_lmsace_connect
 * @copyright 2023 LMSACE Dev Team <https://www.lmsace.com>.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'LMSACE Connect';
$string['generate_token'] = 'LMSACE Connect';
$string['connectiondetails'] = 'Connection details';
$string['enablewebprotocal'] = 'Enable REST protocol';
$string['enabled'] = 'Enabled';
$string['disabled'] = 'Disabled';
$string['generatetoken'] = 'Generate Token';

$string['copytokendescription'] = 'Copy and paste this token into LMSACE Connect admin setting page on your wordpress shop';
$string['assignuserdescription'] = 'Select user and click the generate token, it will generates the token for the selected user.';
$string['selectuser'] = 'Select User';
$string['enable'] = 'Enable';
$string['tokengenerated'] = 'Token generated successfully';
$string['siteurl'] = 'Site URL';
$string['webservicetoken'] = 'Webservice Token';
$string['missingcapabilities'] = 'Missing capabilities';
$string['missingcapabilitiesmsg'] = 'Add the following capabilities to the user account which is used to generate the token';

$string['configenablewebservices'] = 'Enable webservice';
$string['configenablewebservices_help'] = 'Web services enable other systems, such as the Moodle app, to log in to the site and perform operations. For extra security, the setting should be disabled if you are not using the app, or an external tool/service that requires integration via web services.';
$string['tokeningeneratetoken'] = 'Create a token for the web services user in "<b>Generate Token</b>" tab.';

$string['settingstoconnect'] = 'Enable the below configs and generate the token.';
$string['assignrolemissing'] = "<span class='badge badge-danger'> User doesn't not have any assignable roles.</span>
<p>User should have role to assign the users to the course who purchase the course on woocommerce</p>";
