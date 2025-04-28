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
 * Course subscription manager
 *
 * @package    local_taskporter
 * @copyright  2025 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_taskporter;

/**
 * Class for managing course subscriptions
 */
class subscription_manager {

    /**
     * Subscribe a user to a course
     *
     * @param int $userid The user ID
     * @param int $courseid The course ID
     * @param string $provider Calendar provider (default: 'google')
     * @return bool True if successful, false otherwise
     */
    public static function subscribe_to_course($userid, $courseid) {
        global $DB;

        // Check if subscription already exists.
        if (self::is_subscribed($userid, $courseid)) {
            return true; // Already subscribed.
        }

        $subscription = new \stdClass();
        $subscription->userid = $userid;
        $subscription->courseid = $courseid;
        $subscription->timecreated = time();
        $subscription->timemodified = time();

        return $DB->insert_record('local_taskporter_subscriptions', $subscription) > 0;
    }

    /**
     * Unsubscribe a user from a course
     *
     * @param int $userid The user ID
     * @param int $courseid The course ID
     * @param string $provider Calendar provider (default: 'google')
     * @return bool True if successful, false otherwise
     */
    public static function unsubscribe_from_course($userid, $courseid) {
        global $DB;

        return $DB->delete_records('local_taskporter_subscriptions', [
            'userid' => $userid,
            'courseid' => $courseid,
        ]);
    }

    /**
     * Check if a user is subscribed to a course
     *
     * @param int $userid The user ID
     * @param int $courseid The course ID
     * @param string $provider Calendar provider (default: 'google')
     * @return bool True if subscribed, false otherwise
     */
    public static function is_subscribed($userid, $courseid) {
        global $DB;

        return $DB->record_exists('local_taskporter_subscriptions', [
            'userid' => $userid,
            'courseid' => $courseid,
        ]);
    }

    /**
     * Get subscription count for a course
     *
     * @param int $courseid The course ID
     * @param string $provider Optional provider filter
     * @return int Number of subscriptions
     */
    public static function get_course_subscription_count($courseid) {
        global $DB;

        return $DB->count_records('local_taskporter_subscriptions', ['courseid' => $courseid]);
    }
}
