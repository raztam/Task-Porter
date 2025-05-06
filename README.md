# Task-Porter for Moodle

Task-Porter is a Moodle local plugin that automatically syncs newly created assignments from subscribed courses to students' **Google Calendars**. It's designed to help learners stay organized by keeping due dates and start dates visible in their personal calendars.

## Features

- Subscribable per-course Google Calendar sync
- Automatically detects new assignments via event observer
- Uses an ad-hoc task to add events for each subscriber
- Minimal performance impact on Moodle
- Secure integration with Google OAuth 2.0

## Requirements

- Moodle 4.1 or higher (tested on 4.4)
- Google account for calendar integration
- OAuth 2 service configured in Moodle
- Composer installed

## Installation

1. Clone or download this repository into your Moodle's `/local` directory:
   ```bash
   git clone https://github.com/raztam/Task-Porter.git local/taskporter
   cd local/taskporter
   composer install
   ```

## Google API Configuration

1. Create a Google Cloud Console project:

   - Go to [Google Cloud Console](https://console.cloud.google.com/)
   - Create a new project
   - Enable the Google Calendar API and Gmail API for your project

2. Configure OAuth 2.0 credentials:

   - Go to "Credentials" in the API & Services section
   - Create an OAuth 2.0 Client ID (Web application type)
   - Add your Moodle site domain to the authorized redirect URIs
   - Note your Client ID and Client Secret

3. Create or edit the `config.php` file in the plugin directory:

   ```php
   <?php
   defined('MOODLE_INTERNAL') || die();

   $googleapiconfig = [
       'google_client_id' => 'YOUR_CLIENT_ID_HERE',
       'google_client_secret' => 'YOUR_CLIENT_SECRET_HERE',
   ];
   ```

4. Set the correct permissions:

   - Make sure your Google OAuth 2.0 application has the following scopes:
     - `https://www.googleapis.com/auth/calendar`
     - `https://www.googleapis.com/auth/calendar.events`
     - `https://www.googleapis.com/auth/userinfo.email`

5. Complete the Moodle plugin installation to use your Google API configuration

## License

This plugin is licensed under the GNU General Public License v3.0.
See the [LICENSE.txt](LICENSE.txt) file for details.
