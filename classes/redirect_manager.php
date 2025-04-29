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
 * Redirect manager for Task Porter plugin.
 *
 * @package    local_taskporter
 * @copyright  2025 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace local_taskporter;


defined('MOODLE_INTERNAL') || die();

/**
 * Class handling redirects for the taskporter plugin.
 */
class redirect_manager {

    /**
     * Redirect based on the returnto parameter.
     *
     * @param int $courseid The course ID
     * @param string $returnto The return destination
     * @param string|null $message Optional message to display
     * @param string|null $messagetype Optional message type(success/error/warning)
     * @return void
     */
    public static function redirect_to_destination($courseid, $returnto, $message = null, $messagetype = null) {
        $url = match($returnto) {
            constants::RETURN_GOOGLE_CALENDAR => new \moodle_url(constants::URL_GOOGLE_CALENDAR_PAGE, ['courseid' => $courseid]),
            constants::RETURN_SUBSCRIPTION => new \moodle_url(constants::URL_TOGGLE_SUBSCRIPTION, ['courseid' => $courseid]),
            constants::RETURN_DEFAULT => new \moodle_url(constants::URL_MAIN_PAGE, ['courseid' => $courseid]),
            default => new \moodle_url(constants::URL_MAIN_PAGE, ['courseid' => $courseid]),
        };

        redirect($url, $message, $messagetype);
    }

}

