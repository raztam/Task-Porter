# Task-Porter for Moodle

Task-Porter is a Moodle local plugin that automatically syncs newly created assignments from subscribed courses to students' **Google Calendars**. It’s designed to help learners stay organized by keeping due dates and start dates visible in their personal calendars.

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

## License

This plugin is licensed under the GNU General Public License v3.0.
See the [LICENSE.txt](LICENSE.txt) file for details.
