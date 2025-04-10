<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/local/taskporter/classes/google_auth_manager.php');

$courseid = required_param('courseid', PARAM_INT);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = context_course::instance($courseid);

require_login();
require_capability('local/taskporter:view', $context);

$PAGE->set_url(new moodle_url('/local/taskporter/show_google_data.php', array('courseid' => $courseid)));
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_title('Google Auth Test');
$PAGE->set_heading('Google Auth Test Results');

echo $OUTPUT->header();
echo $OUTPUT->heading('Google Authentication Test Results');

// Create instance of the Google Auth Manager
$authmanager = new \local_taskporter\google_auth_manager();

if ($authmanager->is_authenticated()) {
    // Get user information to verify authentication works
    try {
        // Check if calendar service is working by listing calendars
        $calendars = $authmanager->list_calendars();

        echo '<div class="alert alert-success">Successfully authenticated with Google!</div>';

        echo '<h3>Your Google Calendars:</h3>';
        echo '<pre>';
        print_r($calendars);
        echo '</pre>';

        // You can add more API calls to test different aspects of your integration

    } catch (Exception $e) {
        echo '<div class="alert alert-danger">Error retrieving data: ' . $e->getMessage() . '</div>';
    }

    echo '<p><a href="' . new moodle_url('/local/taskporter/index.php', array('courseid' => $courseid)) . '" class="btn btn-primary">Return to Task List</a></p>';
} else {
    echo '<div class="alert alert-danger">Not authenticated with Google. Please try again.</div>';
    echo '<p><a href="' . new moodle_url('/local/taskporter/google_auth.php', array('courseid' => $courseid)) . '" class="btn btn-primary">Try Authentication Again</a></p>';
}

echo $OUTPUT->footer();
