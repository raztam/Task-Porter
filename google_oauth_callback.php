<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/local/taskporter/classes/google_auth_manager.php');

require_login();

$code = optional_param('code', '', PARAM_RAW);
$error = optional_param('error', '', PARAM_RAW);
$state = optional_param('state', '', PARAM_RAW);

// Extract courseid from state.
$courseid = 0;
if (!empty($state) && is_numeric($state)) {
    $courseid = (int)$state;
}

// Create auth manager
$authmanager = new \local_taskporter\google\google_auth_manager();

// Check for errors
if (!empty($error)) {
    redirect(
        new moodle_url('/local/taskporter/index.php', ['courseid' => $courseid]),
        'Authentication failed: ' . $error,
        null,
        \core\output\notification::NOTIFY_ERROR
    );
}

// Process the authorization code
if (!empty($code)) {
    $success = $authmanager->store_token_from_code($code);

    if ($success) {
        // Redirect to test page to show the results
        redirect(
            new moodle_url('/local/taskporter/show_google_data.php', ['courseid' => $courseid]),
            'Authentication successful!',
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    } else {
        redirect(
            new moodle_url('/local/taskporter/index.php', ['courseid' => $courseid]),
            'Failed to store authentication token.',
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }
} else {
    redirect(
        new moodle_url('/local/taskporter/index.php', ['courseid' => $courseid]),
        'No authentication code received.',
        null,
        \core\output\notification::NOTIFY_ERROR
    );
}
