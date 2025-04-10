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
 * Google authentication handler for Task Porter plugin.
 *
 * @package    local_taskporter
 * @copyright  2023 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot . '/local/taskporter/classes/google_auth_manager.php');

$courseid = required_param('courseid', PARAM_INT);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = context_course::instance($courseid);

require_login();
require_capability('local/taskporter:view', $context);

// Create instance of the Google Auth Manager
$authmanager = new \local_taskporter\google\google_auth_manager();

$authmanager->get_redirect_uri();

// Check if we're returning from Google with a code
if (isset($_GET['code'])) {
    // Exchange code for tokens
    $authmanager->handle_oauth_callback();

    // If successful, redirect to the data display page
    redirect(new moodle_url('/local/taskporter/show_google_data.php', array('courseid' => $courseid)));
} else {
    // If not returning from Google, initiate auth flow
    $authurl = $authmanager->get_auth_url();

    // Redirect user to Google for authentication
    redirect($authurl);
}
