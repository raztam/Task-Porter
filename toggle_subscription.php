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
 * Toggle course subscription
 *
 * @package    local_taskporter
 * @copyright  2025 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_taskporter\subscription_manager;
use local_taskporter\google\google_auth_manager;
use local_taskporter\constants;

require_once('../../config.php');
require_once($CFG->dirroot . '/local/taskporter/classes/subscription_manager.php');
require_once($CFG->dirroot . '/local/taskporter/classes/google_auth_manager.php');
require_once($CFG->dirroot . '/local/taskporter/classes/constants.php');

$courseid = required_param('courseid', PARAM_INT);
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($courseid);

require_login();
require_capability('local/taskporter:view', $context);

// Check if user is authenticated with Google for subscriptions.
$authmanager = new google_auth_manager();
if (!$authmanager->is_authenticated()) {
    // Redirect to Google auth with subscription return.
    redirect(
        new moodle_url(constants::URL_GOOGLE_AUTH, [
            'courseid' => $courseid,
            'returnto' => constants::RETURN_SUBSCRIPTION,
        ])
    );
}

// Check current subscription status and toggle it.
$issubscribed = subscription_manager::is_subscribed($USER->id, $courseid);
$success = false;
$message = '';

if ($issubscribed) {
    // Unsubscribe.
    $success = subscription_manager::unsubscribe_from_course($USER->id, $courseid);
    $message = get_string('unsubscription_success', 'local_taskporter');
} else {
    // Subscribe.
    $success = subscription_manager::subscribe_to_course($USER->id, $courseid);
    $message = get_string('subscription_success', 'local_taskporter');
}

// Redirect back to the course page.
redirect(
    new moodle_url('/local/taskporter/index.php', ['courseid' => $courseid]),
    $success ? $message : get_string('subscription_failed', 'local_taskporter'),
    $success ? \core\notification::SUCCESS : \core\notification::ERROR
);
