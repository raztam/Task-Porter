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
 * Event observer for assignment creation and updates.
 *
 * @package    local_taskporter
 * @copyright  2025 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_taskporter\observers;

use core\event\course_module_created;
use local_taskporter\task\push_assignment_to_calendar_task;

/**
 * Event observer for assignment events
 */
class assignment_observer {

    /**
     * Observer for assignment creation event
     *
     * @param course_module_created $event The course module created event
     * @return bool True if successfully queued
     */
    public static function assignment_created(course_module_created $event) {
        return self::queue_assignment_sync($event);
    }

    /**
     * Queue an adhoc task to sync this assignment to subscribers' calendars
     *
     * @param \core\event\base $event The event object
     * @return bool True if successfully queued
     */
    protected static function queue_assignment_sync(\core\event\base $event) {
        global $DB;

        // Only proceed if this is an assignment module.
        $other = $event->other;
        if (empty($other['modulename']) || $other['modulename'] !== 'assign') {
            return false;
        }

        $data = $event->get_data();
        $courseid = $data['courseid'];

        $cmid = $data['objectid']; // Course module ID.
        $cm = get_coursemodule_from_id('assign', $cmid, $courseid);
        if (!$cm) {
            return false; // Course module not found.
        }

        // Check if this course has any subscribers.
        $subscribers = $DB->get_records('local_taskporter_subscriptions', ['courseid' => $courseid]);
        if (empty($subscribers)) {
            return false; // No subscribers, no need to queue a task.
        }

        // Create a new adhoc task.
        $task = new push_assignment_to_calendar_task();
        $task->set_custom_data([
            'courseid' => $courseid,
            'moduleid' => $cm->module,
            'instanceid' => $cm->instance,
            'eventtime' => time(),
        ]);

        // Queue the task.
        return \core\task\manager::queue_adhoc_task($task);
    }
}
