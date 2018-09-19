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
 * Version details
 *
 * Configurable Reports - A Moodle block for creating customizable reports
 *
 * @package     block_configurable_reports
 * @author:     Juan leyva <http://www.twitter.com/jleyvadelgado>
 * @date:       2013-09-07
 *
 * @copyright  Juan leyva <http://www.twitter.com/jleyvadelgado>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * This function extends the course settings navigation block, if in a course
 * and have correct permissions a link to configurable_reports block page
 * will be added.
 */
function block_configurable_reports_extend_navigation_course($navigation, $course, $context) {
    global $PAGE;
    if (!isloggedin()) {
        return;
    }

    if (is_null($navigation) or is_null($context)) {
        return;
    }

    if (!empty($PAGE->cm->id)) {
        $cmid = $PAGE->cm->id;
    } else {
        $cmid = null;
    }
    if (has_capability('block/configurable_reports:viewreports', $context)) {
        if ($reports = $navigation->get('coursereports')) {
            $url = new moodle_url('/blocks/configurable_reports/report_list.php',
                array('id' => $course->id, 'cmid' => $cmid));
            $reports->add(get_string('pluginname', 'block_configurable_reports'), $url,
                navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
            if (!empty($modsettings = $navigation->parent->find('modulesettings', navigation_node::TYPE_SETTING))) {
                //$modsettings = $PAGE->settingsnav->find('modulesettings', navigation_node::TYPE_SETTING);
                //if ($settingnode = $settingsnav->find('modulesettings', \settings_navigation::TYPE_SETTING)) {
                $modsettings->add(get_string('pluginname', 'block_configurable_reports'), $url,
                    navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
            }
        }
    }
}

/**
 * This function extends the module navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $cm
 */
function block_configurable_reports_extend_navigation_module($navigation, $cm) {
    if ($cm->modname === 'forum' && has_capability('block/configurable_reports:viewreports', context_course::instance($cm->course))) {
        $url = new moodle_url('/blocks/configurable_reports/report_list.php', array('id' => $cm->course, 'cmid' => $cm->id));
        $navigation->add(get_string('pluginname', 'block_configurable_reports'), $url,
            navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}
