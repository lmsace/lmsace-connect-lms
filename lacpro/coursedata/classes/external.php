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
 * External functions to export the course section and module title to wordpress connector.
 *
 * @package   lacpro_coursedata
 * @copyright 2023 LMSACE Dev Team <https://www.lmsace.com>.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace lacpro_coursedata;

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . "/course/externallib.php");

use html_writer;
use external_api;
use external_files;
use external_value;
use external_format_value;
use external_single_structure;
use external_multiple_structure;

/**
 * External functions to export the course section and module title to wordpress connector.
 */
class external extends external_api {

    /**
     * Paramters defined for the external function - get the courses by course field.
     *
     * @return core_course_external
     */
    public static function get_courses_detail_by_field_parameters() {
        return \core_course_external::get_courses_by_field_parameters();
    }

    /**
     * Get courses by field with course structure.
     *
     * @param string $field
     * @param mixed $value
     * @return array
     */
    public static function get_courses_detail_by_field($field, $value) {
        global $CFG;

        $data = \core_course_external::get_courses_by_field($field, $value);

        foreach ($data['courses'] as $key => $course) {
            $modinfo = get_fast_modinfo($course['id']);
            $sections = [];
            foreach ($modinfo->get_section_info_all() as $section => $thissection) {
                $showsection = $thissection->uservisible;
                if (!$showsection) {
                    continue;
                }
                $sectionname = html_writer::tag('span', get_section_name($thissection->course, $thissection));
                $cmlist = [];
                foreach ($modinfo->sections[$thissection->section] as $modnumber) {
                    $mod = $modinfo->cms[$modnumber];
                    $modname = $mod->get_formatted_name() ?: $mod->name;
                    $modicon = $CFG->wwwroot.'/mod/'.$mod->modname.'/pix/monologo.png';
                    if (!file_exists($CFG->dirroot.'/mod/'.$mod->modname.'/pix/monologo.png')) {
                        $modicon = $CFG->wwwroot.'/mod/'.$mod->modname.'/pix/icon.png';
                    }
                    $cmlist[] = ['name' => $modname, 'modicon' => $modicon, 'modname' => $mod->modname];
                }
                $sections[] = ['name' => $sectionname, 'cmlist' => $cmlist];
            }
            $data['courses'][$key]['details'] = json_encode(['sections' => $sections]);
        }

        return $data;
    }

    /**
     * Returns the course structure data.
     *
     * @return external_single_structure
     */
    public static function get_courses_detail_by_field_returns() {
        // Course structure, including not only public viewable fields.
        return new \external_single_structure(
            array(
                'courses' => new \external_multiple_structure(self::get_course_structure(false), 'Course'),
                'warnings' => new \external_warnings()
            )
        );
    }

    /**
     * Return structure of export the course structure to WP.
     *
     * @param bool $onlypublicdata
     * @return array
     */
    protected static function get_course_structure($onlypublicdata = true) {
        $coursestructure = array(
            'id' => new external_value(PARAM_INT, 'course id'),
            'fullname' => new external_value(PARAM_RAW, 'course full name'),
            'displayname' => new external_value(PARAM_RAW, 'course display name'),
            'shortname' => new external_value(PARAM_RAW, 'course short name'),
            'categoryid' => new external_value(PARAM_INT, 'category id'),
            'categoryname' => new external_value(PARAM_RAW, 'category name'),
            'sortorder' => new external_value(PARAM_INT, 'Sort order in the category', VALUE_OPTIONAL),
            'summary' => new external_value(PARAM_RAW, 'summary'),
            'summaryformat' => new external_format_value('summary'),
            'summaryfiles' => new external_files('summary files in the summary field', VALUE_OPTIONAL),
            'overviewfiles' => new external_files('additional overview files attached to this course'),
            'showactivitydates' => new external_value(PARAM_BOOL, 'Whether the activity dates are shown or not'),
            'showcompletionconditions' => new external_value(PARAM_BOOL,
                'Whether the activity completion conditions are shown or not'),
            'contacts' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'contact user id'),
                        'fullname'  => new external_value(PARAM_NOTAGS, 'contact user fullname'),
                    )
                ),
                'contact users'
            ),
            'enrollmentmethods' => new external_multiple_structure(
                new external_value(PARAM_PLUGIN, 'enrollment method'),
                'enrollment methods list'
            ),
            'customfields' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'name' => new external_value(PARAM_RAW, 'The name of the custom field'),
                        'shortname' => new external_value(PARAM_RAW,
                            'The shortname of the custom field - to be able to build the field class in the code'),
                        'type'  => new external_value(PARAM_ALPHANUMEXT,
                            'The type of the custom field - text field, checkbox...'),
                        'valueraw' => new external_value(PARAM_RAW, 'The raw value of the custom field'),
                        'value' => new external_value(PARAM_RAW, 'The value of the custom field'),
                    )
                ), 'Custom fields', VALUE_OPTIONAL),
            'details' => new external_value(PARAM_RAW, 'Course section and modules details')
        );

        if (!$onlypublicdata) {
            $extra = array(
                'idnumber' => new external_value(PARAM_RAW, 'Id number', VALUE_OPTIONAL),
                'format' => new external_value(PARAM_PLUGIN, 'Course format: weeks, topics, social, site,..', VALUE_OPTIONAL),
                'showgrades' => new external_value(PARAM_INT, '1 if grades are shown, otherwise 0', VALUE_OPTIONAL),
                'newsitems' => new external_value(PARAM_INT, 'Number of recent items appearing on the course page', VALUE_OPTIONAL),
                'startdate' => new external_value(PARAM_INT, 'Timestamp when the course start', VALUE_OPTIONAL),
                'enddate' => new external_value(PARAM_INT, 'Timestamp when the course end', VALUE_OPTIONAL),
                'maxbytes' => new external_value(PARAM_INT, 'Largest size of file that can be uploaded into', VALUE_OPTIONAL),
                'showreports' => new external_value(PARAM_INT, 'Are activity report shown (yes = 1, no =0)', VALUE_OPTIONAL),
                'visible' => new external_value(PARAM_INT, '1: available to student, 0:not available', VALUE_OPTIONAL),
                'groupmode' => new external_value(PARAM_INT, 'no group, separate, visible', VALUE_OPTIONAL),
                'groupmodeforce' => new external_value(PARAM_INT, '1: yes, 0: no', VALUE_OPTIONAL),
                'defaultgroupingid' => new external_value(PARAM_INT, 'default grouping id', VALUE_OPTIONAL),
                'enablecompletion' => new external_value(PARAM_INT, 'Completion enabled? 1: yes 0: no', VALUE_OPTIONAL),
                'completionnotify' => new external_value(PARAM_INT, '1: yes 0: no', VALUE_OPTIONAL),
                'lang' => new external_value(PARAM_SAFEDIR, 'Forced course language', VALUE_OPTIONAL),
                'theme' => new external_value(PARAM_PLUGIN, 'Fame of the forced theme', VALUE_OPTIONAL),
                'marker' => new external_value(PARAM_INT, 'Current course marker', VALUE_OPTIONAL),
                'legacyfiles' => new external_value(PARAM_INT, 'If legacy files are enabled', VALUE_OPTIONAL),
                'calendartype' => new external_value(PARAM_PLUGIN, 'Calendar type', VALUE_OPTIONAL),
                'timecreated' => new external_value(PARAM_INT, 'Time when the course was created', VALUE_OPTIONAL),
                'timemodified' => new external_value(PARAM_INT, 'Last time  the course was updated', VALUE_OPTIONAL),
                'requested' => new external_value(PARAM_INT, 'If is a requested course', VALUE_OPTIONAL),
                'cacherev' => new external_value(PARAM_INT, 'Cache revision number', VALUE_OPTIONAL),
                'filters' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'filter'  => new external_value(PARAM_PLUGIN, 'Filter plugin name'),
                            'localstate' => new external_value(PARAM_INT, 'Filter state: 1 for on, -1 for off, 0 if inherit'),
                            'inheritedstate' => new external_value(PARAM_INT, '1 or 0 to use when localstate is set to inherit'),
                        )
                    ),
                    'Course filters', VALUE_OPTIONAL
                ),
                'courseformatoptions' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_RAW, 'Course format option name.'),
                            'value' => new external_value(PARAM_RAW, 'Course format option value.'),
                        )
                    ),
                    'Additional options for particular course format.', VALUE_OPTIONAL
                ),
            );
            $coursestructure = array_merge($coursestructure, $extra);
        }

        return new external_single_structure($coursestructure);
    }
}
