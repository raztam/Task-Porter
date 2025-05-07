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

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Task Porter';
$string['taskporter:manage'] = 'Manage Task Porter';
$string['taskporter:view'] = 'View Task Porter';

// Google account status strings.
$string['connect'] = 'Connect';
$string['logout'] = 'Logout';
$string['googleaccount'] = 'Google Account';

// Subscription status strings.
$string['subscription_success'] = 'Successfully subscribed for course updates.';
$string['unsubscription_success'] = 'Successfully unsubscribed from course updates.';
$string['subscription_failed'] = 'Failed to update subscription.';

// Admin settings strings
$string['google_client_id'] = 'Google Client ID';
$string['google_client_id_desc'] = 'The Client ID from your Google API Console project.';
$string['google_client_secret'] = 'Google Client Secret';
$string['google_client_secret_desc'] = 'The Client Secret from your Google API Console project.';
$string['setup_instructions'] = 'Google API Setup Instructions';
$string['setup_instructions_desc'] = '
<ol>
    <li>Go to the <a href="https://console.developers.google.com/" target="_blank">Google API Console</a></li>
    <li>Create a new project or select an existing one</li>
    <li>Enable the Google Calendar API</li>
    <li>Go to "Credentials" and create an OAuth 2.0 Client ID</li>
    <li>Set the application type to "Web application"</li>
    <li>Add the following Authorized redirect URI: <strong>{$a}</strong></li>
    <li>Under "OAuth consent screen", add the following scopes:
        <ul>
            <li><code>https://www.googleapis.com/auth/calendar</code></li>
            <li><code>https://www.googleapis.com/auth/userinfo.email</code></li>
        </ul>
    </li>
    <li>Copy the resulting Client ID and Client Secret into the fields above</li>
</ol>';
