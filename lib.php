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
 * Create webservice automatically. General functions.
 *
 * @package   local_lmsace_connect
 * @copyright 2023 LMSACE Dev Team <https://www.lmsace.com>.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Automatically enable the webservice and create token for selected user.
 *
 * @return void
 */
function lmsace_connect_auto_create_webservice() {
    global $CFG, $DB;
    require_once($CFG->dirroot.'/lib/weblib.php');
    require_once($CFG->dirroot.'/webservice/lib.php');

    require_once($CFG->dirroot.'/lib/externallib.php');

    // Fetch the lacconnect webservice service id.
    $service = $DB->get_record('external_services', ['shortname' => 'local_lmsace_connect']);
    if (!empty($service)) {
        $serviceid = $service->id; // Webservice service id.
        // Check token already created or not.
        $technicaluser = get_config('local_lmsace_connect', 'technicaluser'); // Webservice user.
        if (!empty($technicaluser) && $DB->record_exists('user', ['id' => $technicaluser]) ) {

            if ( !$DB->record_exists('external_tokens', ['externalserviceid' => $serviceid, 'userid' => $technicaluser]) ) {
                // Selected technical user for the webservice to generate token.
                $userid = $technicaluser; // Current main admin user id.
                // Check the selected user has capaility to generate token for webservice.
                // Generate the token for the plugin webservice.
                $webservicemanager = new webservice();
                $serviceuser = new stdClass();
                $serviceuser->externalserviceid = $serviceid;
                $serviceuser->userid = $userid;
                $webservicemanager->add_ws_authorised_user($serviceuser);

                $token = external_generate_token(EXTERNAL_TOKEN_PERMANENT, $serviceid, $userid, context_system::instance());
                if ($token != '') {
                    \core\notification::success(get_string('tokengenerated', 'local_lmsace_connect'));
                }
            }
        }
    }
}

/**
 * Find the token user has any missing capabilities.
 *
 * @return bool|array List of missing capabilities.
 */
function lmsace_connect_check_token_user() {
    global $DB, $OUTPUT;
    $user = get_config('local_lmsace_connect', 'technicaluser'); // Webservice user.
    if (empty($user)) {
        return [];
    }
    $service = $DB->get_record('external_services', ['shortname' => 'local_lmsace_connect']);
    if (!empty($service)) {
        $serviceid = $service->id;
        $assignableroles = get_assignable_roles(context_course::instance(1), ROLENAME_ALIAS, false, $user);
        $rolemissing = '';
        if (empty($assignableroles)) {
            $rolemissing = html_writer::start_tag('div', ['class' => 'missing-assign-roles']);
            $rolemissing .= html_writer::tag('p', get_string('assignrolemissing', 'local_lmsace_connect'));
            $rolemissing .= html_writer::end_div();
        }

        // Make up list of capabilities that the user is missing for the given webservice.
        $webservicemanager = new \webservice();

        $usermissingcaps = $webservicemanager->get_missing_capabilities_by_users([['id' => $user]], $serviceid);
        $caps = [
            'moodle/site:viewuseridentity',
            'moodle/role:assign',
            'moodle/course:viewparticipants',
            'moodle/course:viewhiddenuserfields',
            'enrol/manual:manage',
            'enrol/manual:config',
            'moodle/user:viewalldetails',
            'webservice/rest:use',
        ];
        foreach ($caps as $cap) {
            if (!has_capability($cap, context_system::instance(), $user) ) {
                $usermissingcaps[$user][] = $cap;
            }
        }
        if (empty($usermissingcaps[$user])) {
            return $rolemissing;
        }
        asort($usermissingcaps[$user]);

        if (!is_siteadmin($user)
                && array_key_exists($user, $usermissingcaps)) {
            $count = \html_writer::span(count($usermissingcaps[$user]), 'badge badge-danger');
            $links = array_map(function($capname) {
                return get_capability_docs_link((object)['name' => $capname]) . \html_writer::div($capname, 'text-muted');
            }, $usermissingcaps[$user]);
            $list = \html_writer::alist($links);
            $help = $OUTPUT->help_icon('missingcaps', 'webservice');
            $content = print_collapsible_region(
                \html_writer::div($list . $help, 'missingcaps'),
                'small mt-2',
                \html_writer::random_id('usermissingcaps'),
                get_string('usermissingcaps', 'webservice', $count),
                '',
                true,
                true
            );
            return $rolemissing.$content;
        }
    }
    return false;
}
