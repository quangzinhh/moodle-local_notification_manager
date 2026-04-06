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

require_once(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();
require_capability('local/notification_manager:manage', $context);

$userid = optional_param('userid', 0, PARAM_INT);
$trashids = optional_param_array('ids', [], PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);

require_sesskey();

$response = ['success' => false, 'message' => ''];

if (empty($trashids)) {
    $response['message'] = 'No notifications selected.';
    echo json_encode($response);
    exit;
}

list($inorsql, $params) = $DB->get_in_or_equal($trashids, SQL_PARAMS_NAMED, 'trash');

// User id is no longer strictly required for global management, 
// but we append it to the query ONLY if a specific user filter was intentionally provided.
$sql = "id $inorsql";
if ($userid > 0) {
    $sql .= " AND useridto = :useridto";
    $params['useridto'] = $userid;
}

$count = $DB->count_records_select('local_notification_trash', $sql, $params);

if ($count > 0) {
    try {
        if ($action === 'restore') {
            $records = $DB->get_records_select('local_notification_trash', $sql, $params);
            foreach ($records as $rec) {
                // Restore the JSON data as array and insert to normal table
                $raw = (array)json_decode($rec->rawdata);
                // Remove the old ID to let DB auto-increment
                unset($raw['id']); 
                $DB->insert_record('notifications', (object)$raw);
            }
            $DB->delete_records_select('local_notification_trash', $sql, $params);
            
            $response['success'] = true;
            $response['count'] = $count;
            $response['message'] = get_string('success_restored', 'local_notification_manager', $count);
        } else if ($action === 'hard') {
            $DB->delete_records_select('local_notification_trash', $sql, $params);
            $response['success'] = true;
            $response['count'] = $count;
            $response['message'] = get_string('success_deleted', 'local_notification_manager', $count);
        } else {
             $response['message'] = 'Unknown action.';
        }
    } catch (Exception $e) {
        $response['message'] = get_string('error_delete', 'local_notification_manager') . ' ' . $e->getMessage();
    }
} else {
    $response['message'] = 'No matching notifications found for this user.';
}

header('Content-Type: application/json');
echo json_encode($response);
