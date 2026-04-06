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
$notificationids = optional_param_array('ids', [], PARAM_INT);

require_sesskey();

$response = ['success' => false, 'message' => ''];

if ($userid <= 0) {
    $response['message'] = 'Invalid user ID.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

if (empty($notificationids)) {
    $response['message'] = 'No notifications selected.';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$action = optional_param('action', 'hard', PARAM_ALPHA); // soft or hard

// Ensure the notifications actually belong to the user
list($inorsql, $params) = $DB->get_in_or_equal($notificationids, SQL_PARAMS_NAMED, 'notif');
$params['useridto'] = $userid;

$sql = "useridto = :useridto AND id $inorsql";
$count = $DB->count_records_select('notifications', $sql, $params);

if ($count > 0) {
    try {
        if ($action === 'soft') {
            $records = $DB->get_records_select('notifications', $sql, $params);
            foreach ($records as $rec) {
                $trash = new \stdClass();
                $trash->originalid = $rec->id;
                $trash->useridto = $rec->useridto;
                $trash->subject = $rec->subject;
                $trash->component = $rec->component;
                $trash->timecreated = $rec->timecreated;
                $trash->timeread = $rec->timeread;
                $trash->timedeleted = time();
                $trash->rawdata = json_encode($rec);
                
                $DB->insert_record('local_notification_trash', $trash);
            }
        }
        
        $DB->delete_records_select('notifications', $sql, $params);
        $response['success'] = true;
        $response['count'] = $count;
        $msgkey = ($action === 'soft') ? 'success_trashed' : 'success_deleted';
        $response['message'] = get_string($msgkey, 'local_notification_manager', $count);
    } catch (Exception $e) {
        $response['message'] = get_string('error_delete', 'local_notification_manager') . ' ' . $e->getMessage();
    }
} else {
    $response['message'] = 'No matching notifications found for this user.';
}

header('Content-Type: application/json');
echo json_encode($response);
