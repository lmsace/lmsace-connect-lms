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
 * LMSACE Connect - Generate token form and moodle webservice enable options.
 *
 * @package   local_lmsace_connect
 * @copyright 2023 LMSACE Dev Team <https://www.lmsace.com>.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die('No dirct access');

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * Create details for the connection.
 */
class connection_form extends moodleform {

    /**
     * Form definitions.
     *
     * @return void
     */
    public function definition() {
        global $DB;
        // Moodle form object.
        $mform = $this->_form;
        // Techinical user selector dropdown.
        $userlist = get_admins();
        array_walk($userlist, function($user) {
            return fullname($user);
        });
        $users = $DB->get_records('user', ['confirmed' => 1, 'deleted' => 0]);
        foreach ($users as $user) {
            if ($user->id != 1) {
                $userlist[$user->id] = fullname($user);
            }
        }
        $mform->addElement('autocomplete', 'technicaluser', get_string('selectuser', 'local_lmsace_connect'), $userlist);
        $mform->setType('technicaluser', PARAM_INT);
        $mform->setDefault('technicaluser', $this->_customdata['technicaluser']);
        $mform->addElement('static', '', '', get_string('assignuserdescription', 'local_lmsace_connect') );

        if (isset($this->_customdata['token'] ) && $this->_customdata['token']) {
            $mform->addElement('text', 'token', get_string('webservicetoken', 'local_lmsace_connect'));
            $mform->setType('token', PARAM_TEXT);
            $mform->setDefault('token', $this->_customdata['token']);
            $mform->addElement('static', '', '', get_string('copytokendescription', 'local_lmsace_connect'));
        }

        $this->add_action_buttons(true, get_string('generatetoken', 'local_lmsace_connect'));
    }
}
