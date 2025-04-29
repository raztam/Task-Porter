<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

use local_taskporter\controller\task_controller;
use local_taskporter\google\google_auth_manager;
use local_taskporter\subscription_manager;
use local_taskporter\constants;

require_once('../../config.php');
require_once($CFG->dirroot . '/local/taskporter/classes/task_controller.php');
require_once($CFG->dirroot . '/local/taskporter/classes/google_auth_manager.php');
require_once($CFG->dirroot . '/local/taskporter/classes/constants.php');
require_login();

$courseid = required_param('courseid', PARAM_INT);
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($courseid);

require_capability('local/taskporter:view', $context);

$PAGE->set_url(new moodle_url(constants::URL_MAIN_PAGE, ['courseid' => $courseid]));
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_title(get_string('pluginname', 'local_taskporter'));
$PAGE->set_heading(format_string($course->fullname));

// Check for Google authentication status.
$authmanager = new google_auth_manager();
$isauthenticated = $authmanager->is_authenticated();
$useremail = $authmanager->get_user_email();

// Check if the user is subscribed to the course.
$issubscribed = subscription_manager::is_subscribed($USER->id, $courseid);

$taskcontroller = new task_controller();
$assignmentsjson = $taskcontroller->get_course_assignments($courseid);
// Pass true as the second parameter to get arrays instead of objects.
$assignments = json_decode($assignmentsjson, true);

// Prepare data for the template.
$templatedata = [
    'has_assignments' => !empty($assignments),
    'assignments' => $assignments,
    'calendar_url' => (new moodle_url(constants::URL_GOOGLE_CALENDAR_PAGE, ['courseid' => $courseid]))->out(false),
     // Subscription data.
     'is_subscribed' => $issubscribed,
     'toggle_subscription_url' => (new moodle_url(constants::URL_TOGGLE_SUBSCRIPTION, ['courseid' => $courseid]))->out(false),
    // Google account status data.
    'is_authenticated' => $isauthenticated,
    'google_email' => $useremail,
    'login_url' => (new moodle_url(constants::URL_GOOGLE_AUTH, ['courseid' => $courseid]))->out(false),
    'logout_url' => (new moodle_url(constants::URL_GOOGLE_LOGOUT, ['courseid' => $courseid]))->out(false),
];

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_taskporter'));

// Render the Google account status partial.
echo $OUTPUT->render_from_template('local_taskporter/google_account_status', $templatedata);

// Render the assignment list template.
echo $OUTPUT->render_from_template('local_taskporter/task_list', $templatedata);

echo $OUTPUT->footer();
