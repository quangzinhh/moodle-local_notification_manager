<?php

namespace local_notification_manager\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;

class dashboard implements renderable, templatable {
    private string $timerange;

    public function __construct(string $timerange = '30') {
        $validranges = ['7', '30', '90', 'all'];
        if (!in_array($timerange, $validranges)) {
            $timerange = '30';
        }
        $this->timerange = $timerange;
    }

    public function export_for_template(renderer_base $output): array {
        global $DB, $PAGE;

        $data = [
            'timerange' => $this->timerange,
            'is_7' => $this->timerange === '7',
            'is_30' => $this->timerange === '30',
            'is_90' => $this->timerange === '90',
            'is_all' => $this->timerange === 'all',
            'str' => [
                'time_range' => get_string('time_range', 'local_notification_manager'),
                'time_7_days' => get_string('time_7_days', 'local_notification_manager'),
                'time_30_days' => get_string('time_30_days', 'local_notification_manager'),
                'time_90_days' => get_string('time_90_days', 'local_notification_manager'),
                'time_all' => get_string('time_all', 'local_notification_manager'),
                'analytic_engagement' => get_string('analytic_engagement', 'local_notification_manager'),
                'analytic_unread_rate' => get_string('analytic_unread_rate', 'local_notification_manager'),
                'analytic_total' => get_string('analytic_total', 'local_notification_manager'),
                'analytic_read' => get_string('analytic_read', 'local_notification_manager'),
                'analytic_unread' => get_string('analytic_unread', 'local_notification_manager'),
                'analytic_top_users' => get_string('analytic_top_users', 'local_notification_manager'),
                'analytic_popular_types' => get_string('analytic_popular_types', 'local_notification_manager')
            ]
        ];

        $where = '';
        $params = [];

        if ($this->timerange !== 'all') {
            $days = (int)$this->timerange;
            $cutoff = time() - ($days * 24 * 60 * 60);
            $where = 'timecreated >= :cutoff';
            $params['cutoff'] = $cutoff;
        }

        // 1. Engagement Analytics
        $sqlwhere = $where ? "WHERE $where" : "";
        $engagementsql = "SELECT COUNT(id) as total, 
                          SUM(CAST(CASE WHEN timeread IS NULL THEN 1 ELSE 0 END AS INTEGER)) as unread,
                          SUM(CAST(CASE WHEN timeread IS NOT NULL THEN 1 ELSE 0 END AS INTEGER)) as read
                          FROM {notifications} $sqlwhere";
        
        // Moodle DML does not support standard SUM CASE well across all DBs smoothly, so it's safer to just fetch the totals separately or use a simpler pivot, or get them with two count queries.
        $total_notifications = $DB->count_records_select('notifications', $where, $params);
        
        $unread_where = $where ? "$where AND timeread IS NULL" : "timeread IS NULL";
        $unread_notifications = $DB->count_records_select('notifications', $unread_where, $params);
        
        $read_notifications = $total_notifications - $unread_notifications;
        
        $unread_rate = 0;
        if ($total_notifications > 0) {
            $unread_rate = round(($unread_notifications / $total_notifications) * 100, 1);
        }

        $data['engagement'] = [
            'total' => $total_notifications,
            'read' => $read_notifications,
            'unread' => $unread_notifications,
            'unread_rate' => $unread_rate,
            'read_rate' => 100 - $unread_rate,
            'chart_arc' => $unread_rate * 3.6 // 360 degrees
        ];

        // 2. User Analytics (Top 5)
        // Group by user, count, order by count desc
        $usersqlwhere = $sqlwhere ? "$sqlwhere AND useridto > 0" : "WHERE useridto > 0";
        $usersql = "SELECT useridto, COUNT(id) as notifcount 
                    FROM {notifications} 
                    $usersqlwhere 
                    GROUP BY useridto 
                    ORDER BY notifcount DESC";
        $topuserrecords = $DB->get_records_sql($usersql, $params, 0, 5);
        $topusers = [];
        
        if (!empty($topuserrecords)) {
            $userids = array_keys($topuserrecords);
            list($userinsql, $userparams) = $DB->get_in_or_equal($userids);
            // Fetch users
            $users = $DB->get_records_select('user', "id $userinsql", $userparams, '', 'id, firstname, lastname, picture, email');
            
            // Generate user details
            foreach ($topuserrecords as $userid => $record) {
                if (isset($users[$userid])) {
                    $u = $users[$userid];
                    $userpic = new \user_picture($u);
                    $userpic->size = 50;
                    $topusers[] = [
                        'userid' => $userid,
                        'fullname' => fullname($u),
                        'count' => $record->notifcount,
                        'profileimageurl' => $userpic->get_url($PAGE, $output)->out(false)
                    ];
                }
            }
        }
        $data['topusers'] = $topusers;
        $data['has_topusers'] = !empty($topusers);

        // 3. Notification Type Analytics Component Breakdown
        $typesql = "SELECT component, COUNT(id) as notifcount 
                    FROM {notifications} 
                    $sqlwhere 
                    GROUP BY component 
                    ORDER BY notifcount DESC";
        $typerecords = $DB->get_records_sql($typesql, $params, 0, 10);
        $types = [];
        $maxtypecount = 0;
        
        foreach ($typerecords as $record) {
            if ($record->notifcount > $maxtypecount) {
                $maxtypecount = $record->notifcount;
            }
        }

        foreach ($typerecords as $record) {
            $component = $record->component ?: 'moodle';
            $percent = 0;
            if ($maxtypecount > 0) {
                $percent = round(($record->notifcount / $maxtypecount) * 100, 1);
            }
            // Generate a random-ish color based on component name for the bar
            $hash = md5($component);
            $color = '#' . substr($hash, 0, 6);
            
            $types[] = [
                'component' => $component,
                'count' => $record->notifcount,
                'percent' => $percent,
                'color' => $color
            ];
        }
        $data['populartypes'] = $types;
        $data['has_populartypes'] = !empty($types);

        return $data;
    }
}
