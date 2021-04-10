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
 * Backup file.
 *
 * @package   mod_regularvideo
 * @category  backup
 * @copyright 2020 Leeloo LXP (https://leeloolxp.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Define all the backup steps that will be used by the backup_regularvideo_activity_task
 */

/**
 * Define the complete regularvideo structure for backup, with file and id annotations
 */
class backup_regularvideo_activity_structure_step extends backup_activity_structure_step {

    /**
     * Define Structure
     */
    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $regularvideo = new backup_nested_element('regularvideo', array('id'), array(
            'name', 'vimeo_video_id', 'width', 'height', 'border', 'allow', 'intro', 'introformat', 'content', 'contentformat',
            'legacyfiles', 'legacyfileslast', 'display', 'displayoptions',
            'revision', 'timemodified'));

        // Build the tree
        // (love this)

        // Define sources
        $regularvideo->set_source_table('regularvideo', array('id' => backup::VAR_ACTIVITYID));

        // Define id annotations
        // (none)

        // Define file annotations
        $regularvideo->annotate_files('mod_regularvideo', 'intro', null); // This file areas haven't itemid
        $regularvideo->annotate_files('mod_regularvideo', 'content', null); // This file areas haven't itemid

        // Return the root element (regularvideo), wrapped into standard activity structure
        return $this->prepare_activity_structure($regularvideo);
    }
}
