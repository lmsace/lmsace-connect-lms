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
 * Local lmsace connect - Custom functions and services plugin will use.
 *
 * @package   local_lmsace_connect
 * @copyright 2023 LMSACE Dev Team <https://www.lmsace.com>.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'local_lmsace_connect_user_roles' => array(
        'classname'   => 'local_lmsace_connect_external',
        'methodname'  => 'get_user_roles',
        'classpath'   => 'local/lmsace_connect/externallib.php',
        'description' => 'Generate token data to connect the Moodle LMS with woocommerce',
        'type'        => 'write',
    ),

    'local_lmsace_connect_limit_courses' => array(
        'classname'   => 'local_lmsace_connect_external',
        'methodname'  => 'get_courses',
        'classpath'   => 'local/lmsace_connect/externallib.php',
        'description' => 'Get courses with limit',
        'type'        => 'write',
    ),

    'local_lmsace_connect_get_courses_count' => array(
        'classname'   => 'local_lmsace_connect_external',
        'methodname'  => 'get_courses_count',
        'classpath'   => 'local/lmsace_connect/externallib.php',
        'description' => 'Get count of course records',
        'type'        => 'write',
    ),
);

$lafunctions = array(
    'core_course_get_courses',
    'core_user_create_users',
    'core_user_get_users_by_field',
    'enrol_manual_enrol_users',
    'enrol_manual_unenrol_users',
    'core_course_get_categories',
    'core_course_get_courses_by_field',
    'core_enrol_get_users_courses',
    'local_lmsace_connect_user_roles',
    'local_lmsace_connect_limit_courses',
    'local_lmsace_connect_get_courses_count',
);

// Include the external services from auth lmsace_connect if available.
if (class_exists('auth_lmsace_connect\external')) {
    $lafunctions[] = 'auth_lmsace_connect_generate_userloginkey';
    $lafunctions[] = 'auth_lmsace_connect_is_userloggedin';
}

$services = array(
    'LMSACE Connect Service'  => array(
        'functions' => $lafunctions,
        'enabled' => 1,
        'restrictedusers' => 0,
        'shortname' => 'local_lmsace_connect',
        'downloadfiles' => 1,
        'uploadfiles' => 1,
        'restrictedusers' => 1
    ),
);
