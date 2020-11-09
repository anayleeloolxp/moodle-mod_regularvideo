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
 * Regular Video external API
 *
 * @package    mod_regularvideo
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");

/**
 * Regular Video external functions
 *
 * @package    mod_regularvideo
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */
class mod_regularvideo_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 3.0
     */
    public static function view_regularvideo_parameters() {
        return new external_function_parameters(
            array(
                'regularvideoid' => new external_value(PARAM_INT, 'regularvideo instance id')
            )
        );
    }

    /**
     * Simulate the regularvideo/view.php web interface regularvideo: trigger events, completion, etc...
     *
     * @param int $regularvideoid the regularvideo instance id
     * @return array of warnings and status result
     * @since Moodle 3.0
     * @throws moodle_exception
     */
    public static function view_regularvideo($regularvideoid) {
        global $DB, $CFG;
        require_once($CFG->dirroot . "/mod/regularvideo/lib.php");

        $params = self::validate_parameters(self::view_regularvideo_parameters(),
                                            array(
                                                'regularvideoid' => $regularvideoid
                                            ));
        $warnings = array();

        // Request and permission validation.
        $regularvideo = $DB->get_record('regularvideo', array('id' => $params['regularvideoid']), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($regularvideo, 'regularvideo');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        require_capability('mod/regularvideo:view', $context);

        // Call the regularvideo/lib API.
        regularvideo_view($regularvideo, $course, $cm, $context);

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 3.0
     */
    public static function view_regularvideo_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings()
            )
        );
    }

}
