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

use local_taskporter\google\google_auth_manager;
use local_taskporter\constants;
use local_taskporter\redirect_manager;

require_once('../../config.php');


$courseid = required_param('courseid', PARAM_INT);
// Optional parameter to determine where to redirect after authentication.
$returnto = optional_param('returnto', constants::RETURN_DEFAULT, PARAM_ALPHA);

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$context = context_course::instance($courseid);

require_login();
require_capability('local/taskporter:view', $context);

$authmanager = new google_auth_manager();

if ($authmanager->is_authenticated()) {
    // If already authenticated, redirect based on the returnto parameter.
    redirect_manager::redirect_to_destination($courseid, $returnto);
}

$authmanager->get_redirect_uri();

// Not authenticated, start the OAuth flow by redirecting to Google.
$state = [
    'courseid' => $courseid,
    'returnto' => $returnto,
];
$authurl = $authmanager->get_auth_url($state);

// Redirect user to Google for authentication.
redirect($authurl);
