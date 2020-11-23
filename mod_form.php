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
 * Regular Video configuration form
 *
 * @package mod_regularvideo
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/regularvideo/locallib.php');
require_once($CFG->libdir.'/filelib.php');

class mod_regularvideo_mod_form extends moodleform_mod {
    function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        require_once($CFG->libdir . '/filelib.php');

        $leeloolxplicense = get_config('mod_regularvideo')->license;
        $url = 'https://leeloolxp.com/api_moodle.php/?action=page_info';
        $postdata = '&license_key=' . $leeloolxplicense;

        $curl = new curl;

        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => 1,
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
        
        $postdata = '&license_key=' . $leeloolxplicense;
        
        $curl = new curl;
        
        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => 1,
        );
        
        if (!$output = $curl->post($url, $postdata, $options)) {
            notice(get_string('nolicense', 'mod_regularvideo'));
        }
        
        $resposedata = json_decode($output);
        $settingleeloolxp = $resposedata->data->vimeo_videos;
        $config = $settingleeloolxp;

        //$config = get_config('regularvideo');

        //-------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $this->standard_intro_elements();

        //-------------------------------------------------------
        $mform->addElement('header', 'contentsection', get_string('contentheader', 'regularvideo'));

        $mform->addElement('float', 'vimeo_video_id', get_string('regular_vimeo_video_id', 'regularvideo'), array('size'=>'48'));
        $mform->addElement('float', 'width', get_string('regular_width', 'regularvideo'), array('size'=>'48'));
        $mform->addElement('float', 'height', get_string('regular_height', 'regularvideo'), array('size'=>'48'));
        //$mform->addElement('text', 'border', get_string('regular_border'), array('size'=>'48'));
        $mform->addElement('advcheckbox', 'border', get_string('regular_border', 'regularvideo'));
        $mform->addElement('text', 'allow', get_string('regular_allow', 'regularvideo'), array('size'=>'48'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('allow', PARAM_TEXT);
        } else {
            $mform->setType('allow', PARAM_CLEANHTML);
        }

        $mform->addElement('editor', 'regularvideo', get_string('content', 'regularvideo'), null, regularvideo_get_editor_options($this->context));
        $mform->addRule('regularvideo', get_string('required'), 'required', null, 'client');

        //-------------------------------------------------------
        $mform->addElement('header', 'appearancehdr', get_string('appearance'));

        if ($this->current->instance) {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions), $this->current->display);
        } else {
            $options = resourcelib_get_displayoptions(explode(',', $config->displayoptions));
        }
        if (count($options) == 1) {
            $mform->addElement('hidden', 'display');
            $mform->setType('display', PARAM_INT);
            reset($options);
            $mform->setDefault('display', key($options));
        } else {
            $mform->addElement('select', 'display', get_string('displayselect', 'regularvideo'), $options);
            $mform->setDefault('display', $config->display);
        }

        if (array_key_exists(RESOURCELIB_DISPLAY_POPUP, $options)) {
            $mform->addElement('text', 'popupwidth', get_string('popupwidth', 'regularvideo'), array('size'=>3));
            if (count($options) > 1) {
                $mform->disabledIf('popupwidth', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupwidth', PARAM_INT);
            $mform->setDefault('popupwidth', $config->popupwidth);

            $mform->addElement('text', 'popupheight', get_string('popupheight', 'regularvideo'), array('size'=>3));
            if (count($options) > 1) {
                $mform->disabledIf('popupheight', 'display', 'noteq', RESOURCELIB_DISPLAY_POPUP);
            }
            $mform->setType('popupheight', PARAM_INT);
            $mform->setDefault('popupheight', $config->popupheight);
        }

        $mform->addElement('advcheckbox', 'printheading', get_string('printheading', 'regularvideo'));
        $mform->setDefault('printheading', $config->printheading);
        $mform->addElement('advcheckbox', 'printintro', get_string('printintro', 'regularvideo'));
        $mform->setDefault('printintro', $config->printintro);

        // add legacy files flag only if used
        if (isset($this->current->legacyfiles) and $this->current->legacyfiles != RESOURCELIB_LEGACYFILES_NO) {
            $options = array(RESOURCELIB_LEGACYFILES_DONE   => get_string('legacyfilesdone', 'regularvideo'),
                             RESOURCELIB_LEGACYFILES_ACTIVE => get_string('legacyfilesactive', 'regularvideo'));
            $mform->addElement('select', 'legacyfiles', get_string('legacyfiles', 'regularvideo'), $options);
            $mform->setAdvanced('legacyfiles', 1);
        }

        //-------------------------------------------------------
        $this->standard_coursemodule_elements();

        //-------------------------------------------------------
        $this->add_action_buttons();

        //-------------------------------------------------------
        $mform->addElement('hidden', 'revision');
        $mform->setType('revision', PARAM_INT);
        $mform->setDefault('revision', 1);
    }

    function data_preprocessing(&$default_values) {
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('regularvideo');
            $default_values['regularvideo']['format'] = $default_values['contentformat'];
            $default_values['regularvideo']['text']   = file_prepare_draft_area($draftitemid, $this->context->id, 'mod_regularvideo', 'content', 0, regularvideo_get_editor_options($this->context), $default_values['content']);
            $default_values['regularvideo']['itemid'] = $draftitemid;
        }
        if (!empty($default_values['displayoptions'])) {
            $displayoptions = unserialize($default_values['displayoptions']);
            if (isset($displayoptions['printintro'])) {
                $default_values['printintro'] = $displayoptions['printintro'];
            }
            if (isset($displayoptions['printheading'])) {
                $default_values['printheading'] = $displayoptions['printheading'];
            }
            if (!empty($displayoptions['popupwidth'])) {
                $default_values['popupwidth'] = $displayoptions['popupwidth'];
            }
            if (!empty($displayoptions['popupheight'])) {
                $default_values['popupheight'] = $displayoptions['popupheight'];
            }
        }
    }
}

