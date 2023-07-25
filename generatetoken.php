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
 * Local lmsace connect - Assign user and generate token.
 *
 * @package   local_lmsace_connect
 * @copyright 2023 LMSACE Dev Team <https://www.lmsace.com>.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/local/lmsace_connect/lib.php');
require_once($CFG->dirroot.'/local/lmsace_connect/form.php');

$pageurl = new moodle_url('/local/lmsace_connect/generatetoken.php');
$context = context_system::instance();
$header = get_string('generate_token', 'local_lmsace_connect');

$PAGE->set_url($pageurl);
$PAGE->set_context($context);
$PAGE->set_heading($header);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($SITE->shortname.': '.$header);

require_login();

// Check logged in user has permission to change the site config.
require_capability('moodle/site:config', $context);

if ($data = data_submitted()) {
    if (isset($data->action)) {
        if ($data->action == 'webservice') {
            $status = get_config('core', 'enablewebservices');
            set_config('enablewebservices', !$status);
        }

        if ($data->action == 'protocol') {
            // Enable the rest api protocol for webservice.
            // List of already enabled webservices.
            $activewebservices = empty($CFG->webserviceprotocols)
                ? array() : explode(',', $CFG->webserviceprotocols);
            $webservice = 'rest';
            if (!in_array($webservice, $activewebservices)) {
                $activewebservices[] = $webservice;
                $activewebservices = array_unique($activewebservices);
                // Set the activated webserives in config.
            } else {
                if (($key = array_search($webservice, $activewebservices)) !== false) {
                    unset($activewebservices[$key]);
                }
            }
            set_config('webserviceprotocols', implode(',', $activewebservices));
        }
    }
    if (isset($data->technicaluser) && $data->technicaluser != '') {
        set_config('technicaluser',  $data->technicaluser, 'local_lmsace_connect');
        // Generate token for the webservice user.
        $result = lmsace_connect_auto_create_webservice();
        // Redirect to clear the form cached data. Used to change the generated tokens for multiple users.
        redirect($pageurl);
    }
}

echo $OUTPUT->header();

$newdata = ['technicaluser' => 0, 'token' => ''];
$techuserid = get_config('local_lmsace_connect', 'technicaluser');
$newdata['technicaluser'] = $techuserid;
// Set generate token to copy.
$tokencreated = false;
$service = $DB->get_record('external_services', ['shortname' => 'local_lmsace_connect']);
if (!empty($service)) {
    $serviceid = $service->id;
    if ( $result = $DB->get_record('external_tokens', ['externalserviceid' => $serviceid, 'userid' => $techuserid ]) ) {
        $newdata['token'] = $result->token;
        $tokencreated = (!empty($newdata['token'])) ? true : false;
    }
}

$activewebservices = empty($CFG->webserviceprotocols)
    ? array() : explode(',', $CFG->webserviceprotocols); // List of already enabled webservices.
$webservice = 'rest';

$protocol = (in_array($webservice, $activewebservices)) ? true : false;
$protocolurl = new moodle_url('/local/lmsace_connect/generatetoken.php', ['action' => 'protocol']);
$protocoltext = ($protocol == true) ? get_string('disable', 'core')  : get_string('enable', 'core');
$protocolbutton = $OUTPUT->render(new single_button($protocolurl, $protocoltext, 'post', true));

$webservice = get_config('core', 'enablewebservices');
$webserviceurl = new moodle_url('/local/lmsace_connect/generatetoken.php', ['action' => 'webservice']);
if ($webservice) {
    $webservicebutton = $OUTPUT->render(new single_button($webserviceurl, get_string('disable', 'core'), 'post', true));
} else {
    $webservicebutton = $OUTPUT->render(new single_button($webserviceurl, get_string('enable', 'core'), 'post', true));
}
$webservicehelp = $OUTPUT->help_icon('configenablewebservices', 'local_lmsace_connect');
$connectionform = new connection_form(null, $newdata);

$missingcapability = lmsace_connect_check_token_user();

// Find the status of connection.
$status = ($protocol && $webservice && empty($missingcapability) && $tokencreated ) ? true : false;
$tabstatus = [
    'general' => ($status) ? 'circle-success' : 'circle-error',
    'token' => (isset($newdata['token']) && !empty($newdata['token'])) ? 'circle-success' : 'circle-error'
];

$data = [
    'connectionform' => $connectionform->render(),
    'webserviceenabled' => $webservice,
    'protocolenabled' => $protocol,
    'webservicebutton' => $webservicebutton,
    'webservicehelp' => $webservicehelp,
    'protocolbutton' => $protocolbutton,
    'iscapmissing' => (!empty($missingcapability)) ? true : false,
    'missingcapability' => $missingcapability,
    'webtoken' => ($newdata['token']) ?: '',
    'siteurl' => $CFG->wwwroot,
    'tabstatus' => $tabstatus
];

echo $OUTPUT->render_from_template('local_lmsace_connect/generate_token', $data);

echo $OUTPUT->footer();
