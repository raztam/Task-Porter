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

namespace local_taskporter;

/**
 * Constants for Task Porter plugin.
 *
 * @package    local_taskporter
 * @copyright  2025 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Class containing all constants used in the taskporter plugin.
 */
class constants {
    /** @var string URL for the main plugin page */
    const URL_MAIN_PAGE = '/local/taskporter/index.php';

    /** @var string URL for the calendar page */
    const URL_GOOGLE_CALENDAR_PAGE = '/local/taskporter/add_to_calendar.php';

    /** @var string URL for Google authentication */
    const URL_GOOGLE_AUTH = '/local/taskporter/google_auth.php';

    /** @var string URL for Google logout */
    const URL_GOOGLE_LOGOUT = '/local/taskporter/google_logout.php';

    /** @var string URL for Google OAuth callback */
    const URL_GOOGLE_OAUTH_CALLBACK = '/local/taskporter/google_oauth_callback.php';

    /** @var string URL for toggling course subscription */
    const URL_TOGGLE_SUBSCRIPTION = '/local/taskporter/toggle_subscription.php';

    /** @var string Default redirect destination parameter value */
    const RETURN_DEFAULT = 'default';

    /** @var string Subscribe return destination parameter value */
    const RETURN_SUBSCRIPTION = 'subscription';

    /** @var string Calendar redirect destination parameter value */
    const RETURN_GOOGLE_CALENDAR = 'calendar';
}
