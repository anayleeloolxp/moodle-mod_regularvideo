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
 * Regular Video module version information
 *
 * @package mod_regularvideo
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

global $DB;
global $USER;

$moduleid = $_REQUEST['cm'];

$userid = $USER->id;

if (isset($moduleid) && isset($moduleid) != '' && isset($userid) && isset($userid) != '') {
    $check_completion = $DB->get_record_sql('SELECT COUNT(*) as iscompleted FROM {course_modules_completion} WHERE `coursemoduleid` = ' . $moduleid . ' AND `userid` = ' . $userid);

    $iscompleted = $check_completion->iscompleted;

    if ($iscompleted == 0) {
        $object = new stdClass;
        $object->coursemoduleid = $moduleid;
        $object->userid = $userid;
        $object->completionstate = 1;
        $object->viewed = 1;
        $object->timemodified = time();

        $DB->insert_record('course_modules_completion', $object);
    }
}

die;