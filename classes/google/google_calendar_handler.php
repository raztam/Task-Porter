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
 * Google Calendar handler for Task Porter plugin.
 *
 * @package    local_taskporter
 * @copyright  2025 Your Name <your.email@example.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_taskporter\google;

/**
 * Class responsible for handling Google Calendar API interactions
 */
class google_calendar_handler {
    /** @var \Google_Client The Google API client */
    protected $client;

    /** @var \Google_Service_Calendar Google Calendar service */
    protected $calendarservice;

    /** @var \local_taskporter\google\google_auth_manager Auth manager instance */
    protected $authmanager;

    /** @var string The calendar ID to use (default: primary) */
    protected $calendarid;

    /**
     * Constructor.
     *
     * @param string $calendarid Optional calendar ID (default: primary)
     */
    public function __construct($calendarid = 'primary') {
        global $CFG;
        require_once($CFG->dirroot . '/local/taskporter/vendor/autoload.php');

        $this->calendarid = $calendarid;
        $this->authmanager = new google_auth_manager();
        $this->client = $this->authmanager->get_client();

        // Initialize the calendar service if authenticated
        if ($this->authmanager->is_authenticated()) {
            $this->calendarservice = new \Google_Service_Calendar($this->client);
        }
    }

    /**
     * Check if the user is authenticated and we can use the Calendar API
     *
     * @return bool True if authenticated and service is available
     */
    public function is_ready() {
        return $this->authmanager->is_authenticated() && $this->calendarservice !== null;
    }

    /**
     * Add a single Moodle task to Google Calendar
     *
     * @param array $task Task data from task_controller
     * @return array|bool The created calendar event or false on failure
     */
    public function add_task_to_calendar($task) {
        if (!$this->is_ready()) {
            debugging('Cannot add task to calendar: Not authenticated with Google Calendar', DEBUG_DEVELOPER);
            return false;
        }

        try {
            // Get the due date
            $duedate = !empty($task['duedate']) ? $task['duedate'] : null;
            if (empty($duedate)) {
                debugging('Cannot add task to calendar: No due date for task ' . $task['name'], DEBUG_DEVELOPER);
                return false;
            }

             // Check if task already exists in calendar.
            $existingevent = $this->find_existing_task($task);
            if ($existingevent) {
                debugging('Task already exists in calendar: ' . $task['name'] . ' (' . $existingevent->getId() . ')');
                return [
                 'id' => $existingevent->getId(),
                    'htmlLink' => $existingevent->getHtmlLink(),
                    'taskName' => $task['name'],
                    'success' => true,
                    'alreadyExists' => true,
                ];
            }

            // Create an event.
            $event = $this->create_event_from_task($task, $duedate);

            // Insert the event.
            $createdevent = $this->calendarservice->events->insert($this->calendarid, $event);

            // Return the created event.
            return [
                'id' => $createdevent->getId(),
                'htmlLink' => $createdevent->getHtmlLink(),
                'taskName' => $task['name'],
                'success' => true,
            ];

        } catch (\Exception $e) {
            debugging('Error adding task to calendar: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }
    }

    /**
     * Add multiple Moodle tasks to Google Calendar
     *
     * @param array $tasks Array of tasks from task_controller
     * @return array Results of the calendar additions
     */
    public function add_all_tasks_to_calendar($tasks) {
        $results = [
            'success' => true,
            'total' => count($tasks),
            'added' => 0,
            'skipped' => 0,
            'failed' => 0,
            'events' => [],
        ];

        if (empty($tasks)) {
            $results['success'] = false;
            $results['message'] = 'No tasks to add';
            return $results;
        }

        if (!$this->is_ready()) {
            $results['success'] = false;
            $results['message'] = 'Not authenticated with Google Calendar';
            return $results;
        }

        foreach ($tasks as $task) {
            $event = $this->add_task_to_calendar($task);

            if ($event) {
                if (!empty($event['alreadyExists'])) {
                    // Task already exists in calendar.
                    $results['skipped']++;
                    $results['events'][] = [
                        'taskName' => $task['name'],
                        'success' => true,
                        'message' => 'Task already exists in calendar',
                        'id' => $event['id'],
                        'htmlLink' => $event['htmlLink'],
                    ];
                } else {
                    $results['added']++;
                    $results['events'][] = $event;
                }
            } else {
                $results['failed']++;
                $results['events'][] = [
                    'taskName' => $task['name'],
                    'success' => false,
                    'message' => 'Failed to add to calendar',
                ];
            }
        }

        return $results;
    }

    /**
     * Get list of available calendars
     *
     * @return array List of calendars or empty array on failure
     */
    public function get_calendars() {
        if (!$this->is_ready()) {
            return [];
        }

        try {
            $calendars = [];
            $calendarlist = $this->calendarservice->calendarList->listCalendarList();

            foreach ($calendarlist->getItems() as $calendaritem) {
                $calendars[] = [
                    'id' => $calendaritem->getId(),
                    'summary' => $calendaritem->getSummary(),
                    'description' => $calendaritem->getDescription(),
                    'primary' => $calendaritem->getPrimary(),
                ];
            }

            return $calendars;
        } catch (\Exception $e) {
            debugging('Error getting calendar list: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return [];
        }
    }

    /**
     * Set the calendar ID to use
     *
     * @param string $calendarid The calendar ID
     */
    public function set_calendar_id($calendarid) {
        $this->calendarid = $calendarid;
    }

    /**
     * Creates a Google Calendar event from a Moodle task
     *
     * @param array $task Task data from task_controller
     * @param int $duedate The due date timestamp for the task
     * @return \Google_Service_Calendar_Event The created event object
     */
    private function create_event_from_task($task, $duedate) {
        // Random color ID between 1 and 11 (Google Calendar color IDs).
        $colorid = rand(1, 11);

        return new \Google_Service_Calendar_Event([
        'summary' => '[Assignment] ' . $task['name'],
        'description' => 'Course: ' . $task['coursename'] . "\n\n" .
                         $task['description'] . "\n\n" .
                         "View in Moodle: " . $task['url'],
        'start' => [
            'dateTime' => date('c', $duedate - (3 * 3600)), // 3 hours before due
            'timeZone' => date_default_timezone_get(),
        ],
        'end' => [
            'dateTime' => date('c', $duedate),
            'timeZone' => date_default_timezone_get(),
        ],
        'reminders' => [
            'useDefault' => false,
            'overrides' => [
                ['method' => 'popup', 'minutes' => 1440], // 24 hours before
                ['method' => 'popup', 'minutes' => 60],     // 1 hour before
            ],
        ],
        'colorId' => (string)$colorid, // Random color for events.
        ]);
    }


    /**
     * Find if a task already exists in the calendar
     *
     * @param array $task Task data from task_controller
     * @return \Google_Service_Calendar_Event|null The existing event or null if not found
     */
    private function find_existing_task($task) {
        try {
            // Search within a reasonable timeframe (±1 day from due date).
            $duedate = $task['duedate'];
            $timemin = date('c', $duedate - (24 * 3600)); // 1 day before
            $timemax = date('c', $duedate + (24 * 3600)); // 1 day after

            // Search for events with the same name
            $events = $this->calendarservice->events->listEvents($this->calendarid, [
            'timeMin' => $timemin,
            'timeMax' => $timemax,
            'q' => $task['name'],
            'singleEvents' => true,
            ]);

            // Loop through events to find a match with exact name and due time.
            foreach ($events->getItems() as $event) {
                // Check if the event has the same title.
                if ($event->getSummary() == '[Assignment] ' . $task['name']) {
                    // Check if end time matches due date (within 5 minutes).
                    $eventendtime = strtotime($event->getEnd()->getDateTime());
                    if (abs($eventendtime - $duedate) < 300) { // 5 minutes tolerance.
                        return $event;
                    }
                }
            }

            return null;
        } catch (\Exception $e) {
            debugging('Error searching for existing events: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return null;
        }
    }

}
