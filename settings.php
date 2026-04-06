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

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category(
        'local_notification_manager',
        get_string('pluginname', 'local_notification_manager')
    ));

    $ADMIN->add('local_notification_manager', new admin_externalpage(
        'local_notification_manager_dashboard',
        get_string('dashboardtitle', 'local_notification_manager'),
        new moodle_url('/local/notification_manager/index.php'),
        'local/notification_manager:manage'
    ));

    $ADMIN->add('local_notification_manager', new admin_externalpage(
        'local_notification_manager_manage',
        get_string('tab_manage', 'local_notification_manager'),
        new moodle_url('/local/notification_manager/manage.php'),
        'local/notification_manager:manage'
    ));

    $ADMIN->add('local_notification_manager', new admin_externalpage(
        'local_notification_manager_trash',
        get_string('tab_trash', 'local_notification_manager'),
        new moodle_url('/local/notification_manager/trash.php'),
        'local/notification_manager:manage'
    ));
}
