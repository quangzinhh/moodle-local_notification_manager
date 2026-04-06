<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_local_notification_manager_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2024040606) {

        $table = new xmldb_table('local_notification_trash');

        if (!$dbman->table_exists($table)) {

            // Adding fields to table local_notification_trash.
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('originalid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('useridto', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('subject', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('component', XMLDB_TYPE_CHAR, '100', null, null, null, null);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('timeread', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
            $table->add_field('timedeleted', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('rawdata', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);

            // Adding keys to table local_notification_trash.
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

            // Adding indexes to table local_notification_trash.
            $table->add_index('useridto_idx', XMLDB_INDEX_NOTUNIQUE, ['useridto']);
            $table->add_index('timedeleted_idx', XMLDB_INDEX_NOTUNIQUE, ['timedeleted']);

            // Conditionally launch create table for local_notification_trash.
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2024040606, 'local', 'notification_manager');
    }

    return true;
}