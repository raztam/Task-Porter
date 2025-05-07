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
 * Plugin settings for local_taskporter
 *
 * @package    local_taskporter
 * @copyright  2025 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Create the new settings page.
    $settings = new admin_settingpage('local_taskporter', get_string('pluginname', 'local_taskporter'));
    $ADMIN->add('localplugins', $settings);

    // Add the Google API client ID setting.
    $settings->add(new admin_setting_configtext(
        'local_taskporter/google_client_id',
        get_string('google_client_id', 'local_taskporter'),
        get_string('google_client_id_desc', 'local_taskporter'),
        '',  // Default value
        PARAM_RAW,
        60
    ));

    // Add the Google API client secret setting.
    $settings->add(new admin_setting_configpasswordunmask(
        'local_taskporter/google_client_secret',
        get_string('google_client_secret', 'local_taskporter'),
        get_string('google_client_secret_desc', 'local_taskporter'),
        '',  // Default value
        PARAM_RAW
    ));

    // Get the redirect URI for the current site.
    $redirecturi = $CFG->wwwroot . '/local/taskporter/google_oauth_callback.php';

    // Add instructions for creating Google API credentials.
    $settings->add(new admin_setting_heading(
        'local_taskporter/setup_instructions',
        get_string('setup_instructions', 'local_taskporter'),
        get_string('setup_instructions_desc', 'local_taskporter', $redirecturi)
    ));
}
