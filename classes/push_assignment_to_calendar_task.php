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
 * Task to push an assignment to subscribers' calendars.
 *
 * @package    local_taskporter
 * @copyright  2025 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_taskporter\task;

use local_taskporter\google\google_calendar_handler;
use local_taskporter\google\google_auth_manager;
use local_taskporter\controller\task_controller;

/**
 * Class for pushing a new assignment to subscribers' calendars
 */
class push_assignment_to_calendar_task extends \core\task\adhoc_task {

    /**
     * Execute the task.
     */
    public function execute() {
        global $DB;

        $controller = new task_controller();

        $data = $this->get_custom_data();
        if (empty($data->courseid) || empty($data->instanceid)) {
            mtrace('Missing required data for push_assignment_to_calendar_task');
            return;
        }

        $courseid = $data->courseid;
        $instanceid = $data->instanceid;
        $moduleid = $data->moduleid;

        mtrace("Processing calendar sync for assignment {$instanceid} in course {$courseid}");

        // Get the assignment details.
        $task = $controller->get_assignment_by_id($courseid, $instanceid, $moduleid);
        if (empty($task)) {
            mtrace("Could not find assignment data for instance {$instanceid}");
            return;
        }

        // Get subscribers for this course.
        $subscribers = $DB->get_records('local_taskporter_subscriptions', ['courseid' => $courseid]);

        if (empty($subscribers)) {
            mtrace("No subscribers found for course {$courseid}");
            return;
        }

        mtrace("Found " . count($subscribers) . " subscribers for course {$courseid}");

        // Process each subscriber.
        foreach ($subscribers as $subscription) {
            $this->process_subscriber($subscription->userid, $task);
        }

        mtrace("Completed calendar sync for assignment {$instanceid}");
    }

    /**
     * Process a single subscriber
     *
     * @param int $userid User ID of the subscriber
     * @param array $task Assignment data
     * @return bool True if successful
     */
    protected function process_subscriber($userid, $task) {
        mtrace("Processing calendar sync for user {$userid}");

        // Initialize auth manager with subscriber's user ID.
        $authmanager = new google_auth_manager($userid);

        // Check if the user is authenticated with Google.
        if (!$authmanager->is_authenticated()) {
            mtrace("User {$userid} is not authenticated with Google, skipping");
            return false;
        }

        try {
            // Initialize calendar handler with the user's auth.
            $calendarhandler = new google_calendar_handler($authmanager->get_client());

            // Add task to calendar.
            $result = $calendarhandler->add_task_to_calendar($task);

            if ($result) {
                mtrace("Successfully added task '{$task['name']}' to calendar for user {$userid}");
                return true;
            } else {
                mtrace("Failed to add task '{$task['name']}' to calendar for user {$userid}");
                return false;
            }
        } catch (\Exception $e) {
            mtrace("Error syncing calendar for user {$userid}: " . $e->getMessage());
            return false;
        }
    }
}
