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
 * LMSACE Connect coursedata - Add the external functions to lmsace connect service.
 *
 * @package   lacpro_coursedata
 * @copyright 2023 LMSACE Dev Team <https://www.lmsace.com>.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 /**
  * Added the external service methods to lmsace connect webservice, if already generated.
  *
  * @return bool
  */
function xmldb_lacpro_coursedata_install() {

    global $DB;

    if ($serviceid = $DB->get_field('external_services', 'id', ['shortname' => 'local_lmsace_connect'])) {
        $data = array(
            'externalserviceid' => $serviceid,
            'functionname' => 'lacpro_coursedata_get_courses_detail_by_field'
        );
        if (!$DB->record_exists('external_services_functions', $data)) {
            $DB->insert_record('external_services_functions', $data);
        }
    }

    return true;
}
