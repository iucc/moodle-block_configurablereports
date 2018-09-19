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
 * Configurable Reports - A Moodle block/report for creating customizable reports
 *
 * report_configurablereports version information.
 *
 * @package   report_configurablereports
 * @author:     Juan leyva <http://www.twitter.com/jleyvadelgado>
 * @date:       2013-09-07
 *
 * @copyright  Juan leyva <http://www.twitter.com/jleyvadelgado>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');

$id = required_param('id', PARAM_INT);       // course id
$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
$cmid = optional_param('cmid', null, PARAM_INT);

// needed to setup proper $COURSE
require_login($course);

//setting page url
$PAGE->set_url('/blocks/configurable_reports/report_list.php', array('id' => $id));
//setting page layout to report
$PAGE->set_pagelayout('report');

if ($course->id == SITEID) {
    $context = context_system::instance();
} else {
    $context = context_course::instance($course->id);
}
$PAGE->set_context($context);

//checking if user is capable of viewing this report in $context
require_capability('block/configurable_reports:viewreports', $context);

require_once($CFG->dirroot . "/blocks/configurable_reports/locallib.php");

if ($cmid) {
    $cm = $DB->get_record('course_modules', array('id' => $cmid));
    if ($cm) {
        $module_reports = $DB->get_records('block_configurable_reports', array('coursemodule' => $cm->module), 'name ASC');
    }
    if ($module_reports) {
        $items[] = get_string('modulereports', 'block_configurable_reports');
        $items[] = '<hr/>';
        foreach ($module_reports as $report) {
            if ($report->visible && cr_check_report_permissions($report, $USER->id, $context)) {
                $rname = format_string($report->name);
                $items[] = '<div id="reportlink"><a href= "' . $CFG->wwwroot . '/blocks/configurable_reports/viewreport.php?id=' .
                    $report->id . '&courseid=' . $course->id . '&cmid=' . $cmid . '" alt="' . $rname . '">' . $rname . '</a></div>' .
                    '<div id="reportsummary">' . $report->summary . '</div>';
            }
        }
    }
} else {
    // Site (Shared) reports
    $reports = $DB->get_records('block_configurable_reports', array('courseid' => SITEID), 'name ASC');

    if ($reports) {
        $items[] = get_string('systemreports', 'block_configurable_reports');
        $items[] = '<hr/>';
        foreach ($reports as $report) {
            if ($report->global && $report->visible && cr_check_report_permissions($report, $USER->id, $context)) {
                $rname = format_string($report->name);
                $items[] = '<div id="reportlink"><a href= "' . $CFG->wwwroot . '/blocks/configurable_reports/viewreport.php?id=' .
                    $report->id . '&courseid=' . $course->id . '&cmid=' . $cmid . '" alt="' . $rname . '">' . $rname . '</a></div>' .
                    '<div id="reportsummary">' . $report->summary . '</div>';
            }
        }
    }

// Course reports
    $reports = $DB->get_records('block_configurable_reports', array('courseid' => $course->id), 'name ASC');

    if ($reports) {
        $items[] = '<br/>' . get_string('coursereports', 'block_configurable_reports');
        $items[] = '<hr/>';
        foreach ($reports as $report) {
            if (!$report->global && $report->visible && cr_check_report_permissions($report, $USER->id, $context)) {
                $rname = format_string($report->name);
                $items[] = '<div id="reportlink"><a href= "' . $CFG->wwwroot . '/blocks/configurable_reports/viewreport.php?id=' .
                    $report->id . '&courseid=' . $course->id . '" alt="' . $rname . '">' . $rname . '</a></div>' .
                    '<div id="reportsummary">' . $report->summary . '</div>';
            }
        }
    }
}

// Manage reports link (admin)
if (has_capability('block/configurable_reports:managereports', $context)
    || has_capability('block/configurable_reports:manageownreports', $context)) {
    $items[] = '<br/><div id="managereports"><a class="linkbutton" href="' . $CFG->wwwroot . '/blocks/configurable_reports/managereport.php?courseid=' .
        $course->id . '">' . (get_string('managereports', 'block_configurable_reports')) . '</a></div>';
}

//making log entry
// todo: migrate to events
//add_to_log($course->id, 'course', 'report configurable_reports', "blocks/configurable_reports/report_list.php?id=$course->id", $course->id);

//setting page title and page heading
$PAGE->set_title($course->shortname . ': ' . get_string('pluginname', 'block_configurable_reports'));
$PAGE->set_heading($course->fullname);

//Displaying header and heading
echo $OUTPUT->header();
//echo $OUTPUT->heading(get_string('systemreports','report_configurablereports'));

// Display list of reports that are available to the user
// (based on permissions defined on the configurable reports block, in general and per report)
if (!empty($items)) {
    foreach ($items as $report) {
        echo "$report<br/>";
    }
} else {
    echo $OUTPUT->heading(get_string('noreportsavailable', 'block_configurable_reports'));
}

//display page footer
echo $OUTPUT->footer();
