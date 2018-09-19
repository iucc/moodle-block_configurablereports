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
 * The block_configurable_reports report hidden event.
 *
 * @package    block_configurable_reports
 *             A Moodle block for creating Configurable Reports
 * @package blocks
 * @author: Juan leyva <http://www.twitter.com/jleyvadelgado>
 * @date: 2009
 */

namespace block_configurable_reports\event;

defined('MOODLE_INTERNAL') || die();

class report_hidden extends \core\event\base {
    /**
     * Create instance of event.
     *
     * @since Moodle 2.7
     *
     * @param \stdClass $report
     * @param \context_course $context
     * @return report_hidden
     */
    public static function create_from_report(\stdClass $report, \context_course $context) {
        $data = array(
            'context' => $context,
            'objectid' => $report->id
        );
        /** @var report_hidden $event */
        $event = self::create($data);
        $event->add_record_snapshot('block_configurable_reports', $report);
        return $event;
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {
        return "The user with id '$this->userid' hidden the report with id '$this->objectid' " .
        "for the course id '$this->contextinstanceid' ";
    }

    /**
     * Return the legacy event log data.
     *
     * @return array|null
     */
    protected function get_legacy_logdata() {
        return array($this->courseid, 'configurable_reports', 'view', '/blocks/configurable_reports/editreport.php?id=' . $this->objectid
            , $this->objectid, $this->contextinstanceid);
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventreporthidden', 'block_configurable_reports');
    }

    /**
     * Get URL related to the action.
     *
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/blocks/configurable_reports/editreport.php', array('id' => $this->objectid));
    }

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'block_configurable_reports';
    }
}
