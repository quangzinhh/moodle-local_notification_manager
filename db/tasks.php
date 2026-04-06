<?php
defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'local_notification_manager\task\cleanup_trash_task',
        'blocking' => 0,
        'minute' => 'X',
        'hour' => '3',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*'
    ],
];
