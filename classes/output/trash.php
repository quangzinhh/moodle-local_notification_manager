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
 * Trash output class.
 *
 * @package    local_notification_manager
 * @copyright  2024 Developer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_notification_manager\output;

use renderable;
use renderer_base;
use templatable;

/**
 * Trash output class.
 */
class trash implements renderable, templatable {
    /** @var int $userid The user id. */
    private int $userid;
    /** @var string $userlabel The user label. */
    private string $userlabel;
    /** @var int $page The page number. */
    private int $page;
    /** @var string $search Search query. */
    private string $search;
    /** @var int $perpage Items per page. */
    private int $perpage = 20;

    /**
     * Constructor for trash class.
     *
     * @param int $userid
     * @param string $userlabel
     * @param int $page
     * @param string $search
     */
    public function __construct(int $userid = 0, string $userlabel = '', int $page = 0, string $search = '') {
        $this->userid = $userid;
        $this->userlabel = $userlabel;
        $this->page = max(0, $page);
        $this->search = trim($search);
    }

    /**
     * Export data for template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        global $DB;

        $data = [
            'sesskey' => sesskey(),
            'is_user_selected' => ($this->userid > 0),
            'userid' => $this->userid,
            'userlabel' => s($this->userlabel),
            'search' => s($this->search),
            // Strings.
            'str' => [
                'searchusers' => get_string('searchusers', 'local_notification_manager'),
                'pleaseselectuser' => get_string('pleaseselectuser', 'local_notification_manager'),
                'search' => get_string('search', 'local_notification_manager'),
                'col_subject' => get_string('col_subject', 'local_notification_manager'),
                'col_message' => get_string('col_message', 'local_notification_manager'),
                'col_component' => get_string('col_component', 'local_notification_manager'),
                'col_timedeleted' => get_string('col_timedeleted', 'local_notification_manager'),
                'delete_selected_permanently' => get_string('delete_selected_permanently', 'local_notification_manager'),
                'restore_selected' => get_string('restore_selected', 'local_notification_manager'),
                'no_trash_notifications' => get_string('no_trash_notifications', 'local_notification_manager'),
                'select_all' => get_string('select_all', 'local_notification_manager'),
            ],
        ];

        $where = [];
        $params = [];

        if ($this->userid > 0) {
            $user = $DB->get_record('user', ['id' => $this->userid], '*', MUST_EXIST);
            $fullname = fullname($user);
            $data['notificationsfor'] = get_string('notificationsfor', 'local_notification_manager', format_string($fullname));
            $where[] = 't.useridto = :useridto';
            $params['useridto'] = $this->userid;
            $data['is_user_selected'] = true;
        } else {
            $data['notificationsfor'] = get_string('tab_trash', 'local_notification_manager'); // Or something generic.
            $data['is_user_selected'] = false;
        }

        if ($this->search !== '') {
            $searchlike = '%' . $DB->sql_like_escape($this->search) . '%';
            $where[] = '(' . $DB->sql_like('t.subject', ':search1', false) . ' OR ' .
                       $DB->sql_like('t.component', ':search2', false) . ')';
            $params['search1'] = $searchlike;
            $params['search2'] = $searchlike;
        }

        $sqlwhere = '';
        if (!empty($where)) {
            $sqlwhere = 'WHERE ' . implode(' AND ', $where);
        }

        $totalcountsql = "SELECT COUNT(t.id) FROM {local_notification_manager_trash} t $sqlwhere";
        $totalcount = $DB->count_records_sql($totalcountsql, $params);

        $offset = $this->page * $this->perpage;
        $sql = "SELECT t.*, u.firstname, u.lastname, u.id as uuid
                FROM {local_notification_manager_trash} t
                LEFT JOIN {user} u ON u.id = t.useridto
                $sqlwhere
                ORDER BY t.timedeleted DESC";

        $records = $DB->get_records_sql($sql, $params, $offset, $this->perpage);

        $notifications = [];
        foreach ($records as $record) {
            $raw = json_decode($record->rawdata);
            $messagepreview = '-';
            if ($raw) {
                $messagepreview = format_text($raw->smallmessage ?? $raw->fullmessage, FORMAT_HTML);
            }

            $uname = 'Unknown';
            if (!empty($record->uuid)) {
                $u = new \stdClass();
                $u->firstname = $record->firstname;
                $u->lastname = $record->lastname;
                $uname = fullname($u);
            }

            $notifications[] = [
                'id' => $record->id,
                'subject' => format_string($record->subject),
                'message' => $messagepreview,
                'component' => $record->component,
                'timedeleted' => userdate($record->timedeleted),
                'username' => $uname,
            ];
        }

        $data['has_notifications'] = !empty($notifications);
        $data['notifications'] = $notifications;

        $baseurl = new \moodle_url('/local/notification_manager/trash.php', [
            'userid' => $this->userid,
            'userlabel' => $this->userlabel,
            'search' => $this->search,
        ]);
        $data['pagingbar'] = $output->paging_bar($totalcount, $this->page, $this->perpage, $baseurl->out(false), 'page');

        return $data;
    }
}
