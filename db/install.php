<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_local_taskporter_install() {
    global $DB;

    // Ensure the capability is properly registered
    if (!get_capability_info('local/taskporter:view')) {
        return false;
    }

    // Get the student role ID
    $studentrole = $DB->get_record('role', array('shortname' => 'student'));
    if ($studentrole) {
        // Assign the view capability to student role
        role_change_permission($studentrole->id, context_system::instance(), 'local/taskporter:view', CAP_ALLOW);
    }

    // Get teacher roles
    $teacherrole = $DB->get_record('role', array('shortname' => 'teacher'));
    $editingteacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));

    // Assign capabilities to teacher roles
    if ($teacherrole) {
        role_change_permission($teacherrole->id, context_system::instance(), 'local/taskporter:view', CAP_ALLOW);
    }
    if ($editingteacherrole) {
        role_change_permission($editingteacherrole->id, context_system::instance(), 'local/taskporter:view', CAP_ALLOW);
    }

    return true;
}
