<?php

namespace local_notification_manager\task;

use core\task\scheduled_task;

defined('MOODLE_INTERNAL') || die();

class cleanup_trash_task extends scheduled_task {

    public function get_name() {
        return get_string('cleanup_trash_task', 'local_notification_manager');
    }

    public function execute() {
        global $DB;
        
        mtrace("Starting Notification Manager trash cleanup task...");
        
        $days = 30;
        $cutoff = time() - ($days * DAYSECS);
        
        $sql = "timedeleted < :cutoff";
        $params = ['cutoff' => $cutoff];
        
        $count = $DB->count_records_select('local_notification_trash', $sql, $params);
        if ($count > 0) {
            mtrace("Found {$count} notifications to permanently delete.");
            $DB->delete_records_select('local_notification_trash', $sql, $params);
            mtrace("Deleted successfully.");
        } else {
            mtrace("No notifications to delete.");
        }
        
        mtrace("Cleanup task finished.");
    }
}
