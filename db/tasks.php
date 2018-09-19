<?php

/**
 * In this file are specified the timings that Moodle uses with cron in order to
 * periodically launch tasks.
 *
 */
$tasks = array(
    /**
     * Export data
     */
    array(
        'classname' => 'block_configurable_reports\task\scheduled_queries',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '22',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    )
);