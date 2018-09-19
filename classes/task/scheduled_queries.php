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
 * The block_configurable_reports report created event.
 *
 * @package    block_configurable_reports
 *             A Moodle block for creating Configurable Reports
 * @package blocks
 * @author: Eugene Gurvich <gurvich@post.tau.ac.il>
 * @date: 2016
 */

namespace block_configurable_reports\task;


class scheduled_queries extends \core\task\scheduled_task {

    public function get_name() {
        return get_string('scheduledqueries', 'block_configurable_reports');
    }

    public function execute() {
        global $CFG, $DB;

        require_once($CFG->dirroot."/blocks/configurable_reports/locallib.php");
        require_once($CFG->dirroot.'/blocks/configurable_reports/report.class.php');
        require_once($CFG->dirroot.'/blocks/configurable_reports/reports/sql/report.class.php');

        mtrace("\nConfigurable report (block)");

        $reports = $DB->get_records('block_configurable_reports');
        $DB->execute("SET SESSION wait_timeout=31536000");
        if ($reports) {
            foreach ($reports as $report) {
                // Running only SQL reports. $report->type == 'sql'
                if ($report->type == 'sql' AND (!empty($report->cron) AND $report->cron == '1')) {
                    $reportclass = new \report_sql($report);

                    // Execute it using $remoteDB
                    $starttime = microtime(true);
                    mtrace("\nExecuting query '$report->name'");
                    //$results = $reportclass->create_report();
                    $components = cr_unserialize($reportclass->config->components);
                    $config = (isset($components['customsql']['config']))? $components['customsql']['config'] : new stdclass;
                    $sql = $reportclass->prepare_sql($config->querysql);
                    //if (strpos($sql, ';') !== false) {
                    $sqlqueries = explode(';',$sql);
                    //} else
                    foreach ($sqlqueries as $sql) {
                        mtrace(substr($sql,0,60)); // Show some SQL
                        $results = $reportclass->execute_query($sql);
                        mtrace(($results==1) ? '...OK time='.round((microtime(true) - $starttime) * 1000).'mSec' : 'Some SQL Error'.'\n');
                    }
                    unset($reportclass);
                }
            }
        }
        return true; // Finished OK.

    }

}