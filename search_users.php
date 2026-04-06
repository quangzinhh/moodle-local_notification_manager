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

$search = optional_param('q', '', PARAM_RAW_TRIMMED);
require_sesskey();
$items = [];

if ($search !== '') {
    $query = '%' . core_text::strtolower($search) . '%';
    $sql = "SELECT id, firstname, lastname, email
              FROM {user}
             WHERE deleted = 0
               AND (" . $DB->sql_like('LOWER(firstname)', ':search1', false)
                . " OR " . $DB->sql_like('LOWER(lastname)', ':search2', false)
                . " OR " . $DB->sql_like('LOWER(email)', ':search3', false) . ")
          ORDER BY firstname ASC, lastname ASC, id ASC";
    $records = $DB->get_records_sql($sql, [
        'search1' => $query,
        'search2' => $query,
        'search3' => $query,
    ], 0, 20);

    foreach ($records as $user) {
        $fullname = trim(fullname((object)[
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
        ]));
        $items[] = [
            'id' => (int)$user->id,
            'fullname' => $fullname,
            'email' => (string)$user->email,
        ];
    }
}

header('Content-Type: application/json');
echo json_encode(['items' => $items]);
