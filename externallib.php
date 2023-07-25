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
 * Local lmsace connect - External webservices for connection.
 *
 * @package   local_lmsace_connect
 * @copyright 2023 LMSACE Dev Team <https://www.lmsace.com>.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . "/course/externallib.php");

/**
 * Customised External services - count of courses,
 */
class local_lmsace_connect_external extends external_api {

    /**
     * Get available user roles to assign in course.
     *
     * @return external_function_parameters
     */
    public static function get_user_roles_parameters() {
        return new external_function_parameters(
            array(
                'role' => new external_value( PARAM_TEXT, 'role', VALUE_OPTIONAL )
            )
        );
    }

    /**
     * Fetch the list of course assignable roles.
     *
     * @return array list of roles
     */
    public static function get_user_roles() {
        global $DB;

        $course = $DB->get_records('course', [], '', 'id', 1, 1);
        if (!empty($course)) {
            $context = context_course::instance(1);
            $results = get_assignable_roles($context);
            foreach ($results as $roleid => $rolename) {
                $roles[] = ['id' => $roleid, 'name' => $rolename];
            }
            return $roles;
        } else {

            list($insql, $inparam) = $DB->get_in_or_equal([CONTEXT_COURSE]);
            $sql = "SELECT lvl.id, lvl.roleid, rle.name, rle.shortname FROM {role_context_levels} lvl
            JOIN {role} AS rle ON rle.id = lvl.roleid
            WHERE contextlevel $insql ";
            $result = $DB->get_records_sql($sql, $inparam);
            $result = role_fix_names($result);
        }
        $roles = [];
        // Generate options list for select mform element.
        foreach ($result as $key => $role) {
            $roles[] = [ 'id' => $role->roleid, 'name' => $role->localname]; // Role fullname.
        }
        return $roles;

    }

    /**
     * Return values description for fetch roles.
     *
     * @return external_multiple_structure
     */
    public static function get_user_roles_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value( PARAM_INT, 'Role id' ),
                    'name' => new external_value( PARAM_TEXT, 'Role name' ),
                )
            )
        );
    }


    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.2
     */
    public static function get_courses_parameters() {
        return new external_function_parameters(
            array(
                'from' => new external_value(PARAM_INT, 'The value to from record id', VALUE_OPTIONAL, null),
                'limit' => new external_value(PARAM_INT, 'The value to Limit', VALUE_OPTIONAL, null),
            )
        );
    }


    /**
     * Get courses matching a specific field (id/s, shortname, idnumber, category)
     *
     * @param int $from field name to search, or empty for all courses
     * @param int $limit value to search
     * @return array list of courses and warnings
     * @throws  invalid_parameter_exception
     * @since Moodle 3.2
     */
    public static function get_courses($from = 0, $limit = 0) {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/course/lib.php');
        require_once($CFG->libdir . '/filterlib.php');

        $params = self::validate_parameters(self::get_courses_parameters(),
            array(
                'from'  => $from,
                'limit'  => $limit,
            )
        );
        $warnings = array();

        $courses = $DB->get_records('course', [], 'id ASC', '*', $from, $limit);
        if (empty($courses)) {
            return [];
        }

        $coursesdata = array();
        foreach ($courses as $course) {
            $context = context_course::instance($course->id);
            $canupdatecourse = has_capability('moodle/course:update', $context);
            $canviewhiddencourses = has_capability('moodle/course:viewhiddencourses', $context);

            // Check if the course is visible in the site for the user.
            if (!$course->visible and !$canviewhiddencourses and !$canupdatecourse) {
                continue;
            }
            // Get the public course information, even if we are not enrolled.
            $courseinlist = new core_course_list_element($course);

            // Now, check if we have access to the course, unless it was already checked.
            try {
                if (empty($course->contextvalidated)) {
                    core_course_external::validate_context($context);
                }
            } catch (Exception $e) {
                // User can not access the course, check if they can see the public information about the course and return it.
                if (core_course_category::can_view_course_info($course)) {
                    $coursesdata[$course->id] = self::get_course_public_information($courseinlist, $context);
                }
                continue;
            }
            $coursesdata[$course->id] = self::get_course_public_information($courseinlist, $context);
            // Return information for any user that can access the course.
            $coursefields = array('format', 'showgrades', 'newsitems', 'startdate', 'enddate', 'maxbytes', 'showreports', 'visible',
                'groupmode', 'groupmodeforce', 'defaultgroupingid', 'enablecompletion', 'completionnotify', 'lang', 'theme',
                'marker');

            // Course filters.
            $coursesdata[$course->id]['filters'] = filter_get_available_in_context($context);

            // Information for managers only.
            if ($canupdatecourse) {
                $managerfields = array('idnumber', 'legacyfiles', 'calendartype', 'timecreated', 'timemodified', 'requested',
                    'cacherev');
                $coursefields = array_merge($coursefields, $managerfields);
            }

            // Populate fields.
            foreach ($coursefields as $field) {
                $coursesdata[$course->id][$field] = $course->{$field};
            }

            // Clean lang and auth fields for external functions (it may content uninstalled themes or language packs).
            if (isset($coursesdata[$course->id]['theme'])) {
                $coursesdata[$course->id]['theme'] = clean_param($coursesdata[$course->id]['theme'], PARAM_THEME);
            }
            if (isset($coursesdata[$course->id]['lang'])) {
                $coursesdata[$course->id]['lang'] = clean_param($coursesdata[$course->id]['lang'], PARAM_LANG);
            }

            $courseformatoptions = course_get_format($course)->get_config_for_external();
            foreach ($courseformatoptions as $key => $value) {
                $coursesdata[$course->id]['courseformatoptions'][] = array(
                    'name' => $key,
                    'value' => $value
                );
            }
        }

        return array(
            'courses' => $coursesdata,
            'warnings' => $warnings
        );
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 3.2
     */
    public static function get_courses_returns() {
        // Course structure, including not only public viewable fields.
        return core_course_external::get_courses_by_field_returns();
    }

    /**
     * Return the course information that is public (visible by every one)
     *
     * @param  core_course_list_element $course        course in list object
     * @param  stdClass       $coursecontext course context object
     * @return array the course information
     * @since  Moodle 3.2
     */
    protected static function get_course_public_information(core_course_list_element $course, $coursecontext) {

        static $categoriescache = array();

        // Category information.
        if (!array_key_exists($course->category, $categoriescache)) {
            $categoriescache[$course->category] = core_course_category::get($course->category, IGNORE_MISSING);
        }
        $category = $categoriescache[$course->category];

        // Retrieve course overview used files.
        $files = array();
        foreach ($course->get_course_overviewfiles() as $file) {
            $fileurl = moodle_url::make_webservice_pluginfile_url($file->get_contextid(), $file->get_component(),
                                                                    $file->get_filearea(), null, $file->get_filepath(),
                                                                    $file->get_filename())->out(false);
            $files[] = array(
                'filename' => $file->get_filename(),
                'fileurl' => $fileurl,
                'filesize' => $file->get_filesize(),
                'filepath' => $file->get_filepath(),
                'mimetype' => $file->get_mimetype(),
                'timemodified' => $file->get_timemodified(),
            );
        }

        // Retrieve the course contacts,
        // we need here the users fullname since if we are not enrolled can be difficult to obtain them via other Web Services.
        $coursecontacts = array();
        foreach ($course->get_course_contacts() as $contact) {
             $coursecontacts[] = array(
                'id' => $contact['user']->id,
                'fullname' => $contact['username'],
                'roles' => array_map(function($role){
                    return array('id' => $role->id, 'name' => $role->displayname);
                }, $contact['roles']),
                'role' => array('id' => $contact['role']->id, 'name' => $contact['role']->displayname),
                'rolename' => $contact['rolename']
             );
        }

        // Allowed enrolment methods (maybe we can self-enrol).
        $enroltypes = array();
        $instances = enrol_get_instances($course->id, true);
        foreach ($instances as $instance) {
            $enroltypes[] = $instance->enrol;
        }

        // Format summary.
        list($summary, $summaryformat) =
            external_format_text($course->summary, $course->summaryformat, $coursecontext->id, 'course', 'summary', null);

        $categoryname = '';
        if (!empty($category)) {
            $categoryname = external_format_string($category->name, $category->get_context());
        }

        $displayname = get_course_display_name_for_list($course);
        $coursereturns = array();
        $coursereturns['id']                = $course->id;
        $coursereturns['fullname']          = external_format_string($course->fullname, $coursecontext->id);
        $coursereturns['displayname']       = external_format_string($displayname, $coursecontext->id);
        $coursereturns['shortname']         = external_format_string($course->shortname, $coursecontext->id);
        $coursereturns['categoryid']        = $course->category;
        $coursereturns['categoryname']      = $categoryname;
        $coursereturns['summary']           = $summary;
        $coursereturns['summaryformat']     = $summaryformat;
        $coursereturns['summaryfiles']      = external_util::get_area_files($coursecontext->id, 'course', 'summary', false, false);
        $coursereturns['overviewfiles']     = $files;
        $coursereturns['contacts']          = $coursecontacts;
        $coursereturns['enrollmentmethods'] = $enroltypes;
        $coursereturns['sortorder']         = $course->sortorder;
        $coursereturns['showactivitydates'] = $course->showactivitydates;
        $coursereturns['showcompletionconditions'] = $course->showcompletionconditions;

        $handler = core_course\customfield\course_handler::create();
        if ($customfields = $handler->export_instance_data($course->id)) {
            $coursereturns['customfields'] = [];
            foreach ($customfields as $data) {
                $coursereturns['customfields'][] = [
                    'type' => $data->get_type(),
                    'value' => $data->get_value(),
                    'valueraw' => $data->get_data_controller()->get_value(),
                    'name' => $data->get_name(),
                    'shortname' => $data->get_shortname()
                ];
            }
        }

        return $coursereturns;
    }

    /**
     * Get count of courses parameters.
     *
     * @return external_function_parameters
     */
    public static function get_courses_count_parameters() {
        return new external_function_parameters([]);
    }

    /**
     * Fetch the count of courses available in moodle.
     *
     * @return array
     */
    public static function get_courses_count() {
        global $DB;
        $count = $DB->count_records('course');
        return ['count' => $count];
    }

    /**
     * Return description for courses count.
     *
     * @return external_single_structure
     */
    public static function get_courses_count_returns() {
        return new external_single_structure(
            [ 'count' => new external_value(PARAM_INT, 'Count of course records')]
        );
    }
}
