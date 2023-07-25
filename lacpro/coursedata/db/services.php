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
 * Custom functions and services plugin will use.
 *
 * @package   lacpro_coursedata
 * @copyright 2023 LMSACE Dev Team <https://www.lmsace.com>.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'lacpro_coursedata_get_courses_detail_by_field' => array(
        'classname'   => 'lacpro_coursedata\external',
        'methodname'  => 'get_courses_detail_by_field',
        // 'classpath'   => 'local/lmsace_connect/lacpro/externallib.php',
        'description' => 'Generate token data to connect the Moodle LMS with woocommerce',
        'type'        => 'write',
    )
);
