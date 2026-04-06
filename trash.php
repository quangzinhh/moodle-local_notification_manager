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
$userlabel = optional_param('userlabel', '', PARAM_RAW_TRIMMED);
$page = max(0, optional_param('page', 0, PARAM_INT));
$search = optional_param('search', '', PARAM_RAW_TRIMMED);

if ($userlabel !== '') {
    if (preg_match('/^\s*(\d+)\s*-/', $userlabel, $matches)) {
        $userid = (int)$matches[1];
    }
}

$params = [
    'userid' => $userid,
    'userlabel' => $userlabel,
    'page' => $page,
    'search' => $search
];

$PAGE->set_url(new moodle_url('/local/notification_manager/trash.php', $params));
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('tab_trash', 'local_notification_manager'));
$PAGE->set_heading(get_string('pluginname', 'local_notification_manager'));
$PAGE->requires->js_call_amd('local_notification_manager/main', 'initTrash');

$renderer = $PAGE->get_renderer('local_notification_manager');

echo $renderer->header();
echo $OUTPUT->heading(get_string('tab_trash', 'local_notification_manager'));

$tabs = [
    new tabobject('dashboard', new moodle_url('/local/notification_manager/index.php'), get_string('tab_dashboard', 'local_notification_manager')),
    new tabobject('manage', new moodle_url('/local/notification_manager/manage.php'), get_string('tab_manage', 'local_notification_manager')),
    new tabobject('trash', new moodle_url('/local/notification_manager/trash.php'), get_string('tab_trash', 'local_notification_manager'))
];
echo $OUTPUT->tabtree($tabs, 'trash');

require_once(__DIR__ . '/classes/output/trash.php');
echo $renderer->render(new \local_notification_manager\output\trash($userid, $userlabel, $page, $search));

echo $renderer->footer();
