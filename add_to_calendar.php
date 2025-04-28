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
 * Add tasks to Google Calendar
 *
 * @package    local_taskporter
 * @copyright  2025 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_taskporter\controller\task_controller;
use local_taskporter\google\google_calendar_handler;
use local_taskporter\google\google_auth_manager;
use local_taskporter\constants;

require_once('../../config.php');
require_once($CFG->dirroot . '/local/taskporter/classes/task_controller.php');
require_once($CFG->dirroot . '/local/taskporter/classes/google_calendar_handler.php');
require_once($CFG->dirroot . '/local/taskporter/classes/google_auth_manager.php');
require_once($CFG->dirroot . '/local/taskporter/classes/constants.php');

$courseid = required_param('courseid', PARAM_INT);
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($courseid);

require_login();
require_capability('local/taskporter:view', $context);

$PAGE->set_url(new moodle_url('/local/taskporter/add_to_calendar.php', ['courseid' => $courseid]));
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_title('Add Tasks to Calendar');
$PAGE->set_heading(format_string($course->fullname));

// Check authentication - if not authenticated, this will redirect to the auth flow.
$authmanager = new google_auth_manager();
if (!$authmanager->is_authenticated()) {
    redirect(
        new moodle_url(constants::URL_GOOGLE_AUTH, [
            'courseid' => $courseid,
            'returnto' => constants::RETURN_GOOGLE_CALENDAR,
        ]),
    );
}

// Get authentication status and email for the template.
$isauthenticated = $authmanager->is_authenticated();
$useremail = $authmanager->get_user_email();

// Create calendar handler and get tasks from controller.
$calendarhandler = new google_calendar_handler();
$taskcontroller = new task_controller();
$assignmentsjson = $taskcontroller->get_course_assignments($courseid);
$assignments = json_decode($assignmentsjson, true);

// Add tasks to calendar.
$results = $calendarhandler->add_all_tasks_to_calendar($assignments);

echo $OUTPUT->header();
echo $OUTPUT->heading('Add Tasks to Google Calendar');

// Prepare data for Google account status template.
$accountdata = [
    'is_authenticated' => $isauthenticated,
    'google_email' => $useremail,
    'login_url' => (new moodle_url('/local/taskporter/google_auth.php', ['courseid' => $courseid, 'returnto' => 'calendar']))->out(false),
    'logout_url' => (new moodle_url('/local/taskporter/google_logout.php', ['courseid' => $courseid, 'returnto' => 'calendar']))->out(false),
];

// Render the Google account status partial.
echo $OUTPUT->render_from_template('local_taskporter/google_account_status', $accountdata);

// Display results.
$templatedata = [
    'success' => $results['success'],
    'added' => $results['added'] ?? 0,
    'skipped' => $results['skipped'] ?? 0,
    'failed' => $results['failed'] ?? 0,
    'events' => $results['events'] ?? [],
    'message' => $results['message'] ?? '',
    'courseUrl' => (new moodle_url('/local/taskporter/index.php', ['courseid' => $courseid]))->out(false),
];

echo $OUTPUT->render_from_template('local_taskporter/calendar_results', $templatedata);

echo $OUTPUT->footer();
