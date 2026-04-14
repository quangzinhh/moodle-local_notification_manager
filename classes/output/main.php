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
 * Main output class.
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
 * Main output class.
 */
class main implements renderable, templatable {
    /** @var int $userid The user id. */
    private int $userid;
    /** @var string $userlabel The user label. */
    private string $userlabel;
    /** @var int $page The page number. */
    private int $page;
    /** @var string $search Search query. */
    private string $search;
    /** @var string $filter Filter type definition. */
    private string $filter;
    /** @var int $perpage Items per page. */
    private int $perpage = 20;

    /**
     * Constructor for main class.
     *
     * @param int $userid
     * @param string $userlabel
     * @param int $page
     * @param string $search
     * @param string $filter
     */
    public function __construct(
        int $userid = 0,
        string $userlabel = '',
        int $page = 0,
        string $search = '',
        string $filter = 'all'
    ) {
        $this->userid = $userid;
        $this->userlabel = $userlabel;
        $this->page = max(0, $page);
        $this->search = trim($search);
        $this->filter = $filter;
    }

    /**
     * Export data for template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        global $DB, $USER;

        $data = [
            'sesskey' => sesskey(),
            'is_user_selected' => ($this->userid > 0),
            'userid' => $this->userid,
            'userlabel' => s($this->userlabel),
            'search' => s($this->search),
            'filter_all_selected' => ($this->filter === 'all'),
            'filter_read_selected' => ($this->filter === 'read'),
            'filter_unread_selected' => ($this->filter === 'unread'),
            // Strings.
            'str' => [
                'searchusers' => get_string('searchusers', 'local_notification_manager'),
                'pleaseselectuser' => get_string('pleaseselectuser', 'local_notification_manager'),
                'search' => get_string('search', 'local_notification_manager'),
                'filter_all' => get_string('filter_all', 'local_notification_manager'),
                'filter_read' => get_string('filter_read', 'local_notification_manager'),
                'filter_unread' => get_string('filter_unread', 'local_notification_manager'),
                'col_subject' => get_string('col_subject', 'local_notification_manager'),
                'col_message' => get_string('col_message', 'local_notification_manager'),
                'col_component' => get_string('col_component', 'local_notification_manager'),
                'col_timecreated' => get_string('col_timecreated', 'local_notification_manager'),
                'col_timeread' => get_string('col_timeread', 'local_notification_manager'),
                'delete_selected' => get_string('delete_selected', 'local_notification_manager'),
                'no_notifications' => get_string('no_notifications', 'local_notification_manager'),
                'select_all' => get_string('select_all', 'local_notification_manager'),
            ],
        ];

        if ($this->userid > 0) {
            $user = $DB->get_record('user', ['id' => $this->userid], '*', MUST_EXIST);
            $fullname = fullname($user);
            $data['notificationsfor'] = get_string('notificationsfor', 'local_notification_manager', format_string($fullname));

            $where = ['useridto = :useridto'];
            $params = ['useridto' => $this->userid];

            if ($this->search !== '') {
                $searchlike = '%' . $DB->sql_like_escape($this->search) . '%';
                $where[] = '(' . $DB->sql_like('subject', ':search1', false) . ' OR ' .
                           $DB->sql_like('smallmessage', ':search2', false) . ' OR ' .
                           $DB->sql_like('fullmessage', ':search3', false) . ')';
                $params['search1'] = $searchlike;
                $params['search2'] = $searchlike;
                $params['search3'] = $searchlike;
            }

            if ($this->filter === 'read') {
                $where[] = 'timeread IS NOT NULL';
            } else if ($this->filter === 'unread') {
                $where[] = 'timeread IS NULL';
            }

            $sqlwhere = implode(' AND ', $where);

            $totalcount = $DB->count_records_select('notifications', $sqlwhere, $params);

            $offset = $this->page * $this->perpage;
            $records = $DB->get_records_select(
                'notifications',
                $sqlwhere,
                $params,
                'timecreated DESC',
                '*',
                $offset,
                $this->perpage
            );

            $notifications = [];
            foreach ($records as $record) {
                $notifications[] = [
                    'id' => $record->id,
                    'subject' => format_string($record->subject),
                    'message' => format_text($record->smallmessage ?? $record->fullmessage, FORMAT_HTML),
                    'component' => $record->component,
                    'timecreated' => userdate($record->timecreated),
                    'timeread' => $record->timeread ? userdate($record->timeread) : '-',
                    'is_read' => !empty($record->timeread),
                ];
            }

            $data['has_notifications'] = !empty($notifications);
            $data['notifications'] = $notifications;

            $baseurl = new \moodle_url('/local/notification_manager/manage.php', [
                'userid' => $this->userid,
                'userlabel' => $this->userlabel,
                'search' => $this->search,
                'filter' => $this->filter,
            ]);
            $data['pagingbar'] = $output->paging_bar($totalcount, $this->page, $this->perpage, $baseurl->out(false), 'page');
        }

        return $data;
    }
}
