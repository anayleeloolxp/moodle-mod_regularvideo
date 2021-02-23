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
require_once($CFG->dirroot . '/mod/regularvideo/locallib.php');
require_once($CFG->libdir . '/completionlib.php');

global $CFG;
require_once($CFG->libdir . '/filelib.php');

$leeloolxplicense = get_config('mod_regularvideo')->license;
$url = 'https://leeloolxp.com/api_moodle.php/?action=page_info';
$postdata = [
    'license_key' => $leeloolxplicense,
];

$curl = new curl;

$options = array(
    'CURLOPT_RETURNTRANSFER' => true,
    'CURLOPT_HEADER' => false,
    'CURLOPT_POST' => count($postdata),
);

if (!$output = $curl->post($url, $postdata, $options)) {
    notice(get_string('nolicense', 'mod_regularvideo'));
}

$infoleeloolxp = json_decode($output);

if ($infoleeloolxp->status != 'false') {
    $leeloolxpurl = $infoleeloolxp->data->install_url;
} else {
    notice(get_string('nolicense', 'mod_regularvideo'));
}

$url = $leeloolxpurl . '/admin/Theme_setup/get_vimeo_videos_settings';

$postdata = [
    'license_key' => $leeloolxplicense,
];

$curl = new curl;

$options = array(
    'CURLOPT_RETURNTRANSFER' => true,
    'CURLOPT_HEADER' => false,
    'CURLOPT_POST' => count($postdata),
);

$show = 1;

if (!$output = $curl->post($url, $postdata, $options)) {
    notice(get_string('nolicense', 'mod_regularvideo'));
    $show = 0;
}

$id = optional_param('id', 0, PARAM_INT); // Course Module ID
$p = optional_param('p', 0, PARAM_INT); // Page instance ID
$inpopup = optional_param('inpopup', 0, PARAM_BOOL);

if ($p) {
    if (!$regularvideo = $DB->get_record('regularvideo', array('id' => $p))) {
        print_error('invalidaccessparameter');
    }
    $cm = get_coursemodule_from_instance('regularvideo', $regularvideo->id, $regularvideo->course, false, MUST_EXIST);
} else {
    if (!$cm = get_coursemodule_from_id('regularvideo', $id)) {
        print_error('invalidcoursemodule');
    }
    $regularvideo = $DB->get_record('regularvideo', array('id' => $cm->instance), '*', MUST_EXIST);
}

$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

require_course_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/regularvideo:view', $context);

// Trigger module viewed event.
$event = \mod_regularvideo\event\course_module_viewed::create(array(
    'objectid' => $regularvideo->id,
    'context' => $context,
));
$event->add_record_snapshot('course_modules', $cm);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('regularvideo', $regularvideo);
$event->trigger();

// Update 'viewed' state if required by completion system
require_once($CFG->libdir . '/completionlib.php');
$completion = new completion_info($course);
// $completion->set_module_viewed($cm);

$PAGE->set_url('/mod/regularvideo/view.php', array('id' => $cm->id));

$options = empty($regularvideo->displayoptions) ? array() : unserialize($regularvideo->displayoptions);

if ($inpopup and $regularvideo->display == RESOURCELIB_DISPLAY_POPUP) {
    $PAGE->set_pagelayout('popup');
    $PAGE->set_title($course->shortname . ': ' . $regularvideo->name);
    $PAGE->set_heading($course->fullname);
} else {
    $PAGE->set_title($course->shortname . ': ' . $regularvideo->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($regularvideo);
    $_SESSION['regularvideo'] = $regularvideo;
}
echo $OUTPUT->header();
if (!isset($options['printheading']) || !empty($options['printheading'])) {
    echo $OUTPUT->heading(format_string($regularvideo->name), 2);
}

if (!empty($options['printintro'])) {
    if (trim(strip_tags($regularvideo->intro))) {
        echo $OUTPUT->box_start('mod_introbox', 'regularvideointro');
        echo format_module_intro('regularvideo', $regularvideo, $cm->id);
        echo $OUTPUT->box_end();
    }
}

if ($regularvideo->vimeo_video_id && $show == 1) {
    echo '<iframe id="vimeoiframe" src="https://player.vimeo.com/video/' . $regularvideo->vimeo_video_id . '" width="' . $regularvideo->width . '" height="' . $regularvideo->height . '" frameborder="' . $regularvideo->border . '" allow="' . $regularvideo->allow . '" allowfullscreen=""></iframe>';
}

$content = file_rewrite_pluginfile_urls($regularvideo->content, 'pluginfile.php', $context->id, 'mod_regularvideo', 'content', $regularvideo->revision);
$formatoptions = new stdClass;
$formatoptions->noclean = true;
$formatoptions->overflowdiv = true;
$formatoptions->context = $context;
$content = format_text($content, $regularvideo->contentformat, $formatoptions);
echo $OUTPUT->box($content, "generalbox center clearfix");
global $USER;
if ($show == 1) {
    echo '<script src="https://player.vimeo.com/api/player.js"></script>
    <script>
        var iframe = document.querySelector("#vimeoiframe");
        var player = new Vimeo.Player(iframe);

        player.on("ended", function() {
            console.log("ended the video!");
            /*$.ajax( {
                url:"' . $CFG->wwwroot . '/mod/regularvideo/mark_complete.php?cm=' . $cm->id . '",
                success:function(data) {console.log("marked complete");}
            });

            $.post("' . $CFG->wwwroot . '/course/togglecompletion.php", {id:"' . $cm->id . '", completionstate:"1", fromajax:"1", sesskey:"' . $USER->sesskey . '"}, function(response){
                console.log("marked complete");
            });*/

            $.post("' . $CFG->wwwroot . '/mod/regularvideo/markcomplete.php", {id:"' . $cm->id . '", completionstate:"1", fromajax:"1", sesskey:"' . $USER->sesskey . '"}, function(response){
                console.log("marked complete");
            });

        });

        player.on("play", function() {
            console.log("played the video!");
        });

        player.getVideoTitle().then(function(title) {
            //console.log("title:", title);
        });
    </script>';
}
// LUDO: REMOVE LAST MODIFIED
// $strlastmodified = get_string("lastmodified");
// echo "<div class=\"modified\">$strlastmodified: ".userdate($regularvideo->timemodified)."</div>";

echo $OUTPUT->footer();
