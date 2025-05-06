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
 * Task Controller for managing course assignments
 *
 * @package    local_taskporter
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_taskporter\controller;

defined('MOODLE_INTERNAL') || die();

/**
 * Task Controller class
 *
 * This class handles fetching and formatting assignment data
 */
class task_controller {

    /**
     * Get all assignments for a specific course
     *
     * @param int $courseid Course ID to get assignments for, 0 for all courses
     * @return bool|string Array of assignments with due dates
     */
    public function get_course_assignments($courseid = 0) {
        global $DB;

        $assignments = [];

        // Build the query.
        $sql = "SELECT cm.id as cmid, a.id, a.name, a.intro, a.duedate, a.allowsubmissionsfromdate,
                       a.course, c.fullname as coursename, cm.deletioninprogress
                FROM {assign} a
                JOIN {course_modules} cm ON cm.instance = a.id
                JOIN {modules} m ON m.id = cm.module
                JOIN {course} c ON c.id = a.course
                WHERE m.name = 'assign'";

        $params = [];

        if ($courseid > 0) {
            $sql .= " AND a.course = :courseid";
            $params['courseid'] = $courseid;
        }

        // Add sorting by due date.
        $sql .= " ORDER BY a.duedate ASC";

        $results = $DB->get_records_sql($sql, $params);

        foreach ($results as $result) {
            // Process results into a cleaner format.
            // Only include assignments where deletion is not in progress.
            if ($result->cmid && !$result->deletioninprogress) {
                $assignments[] = [
                    'id' => $result->id,
                    'cmid' => $result->cmid,
                    'name' => $result->name,
                    'description' => strip_tags($result->intro),
                    'duedate' => $result->duedate,
                    'duedateformatted' => ($result->duedate) ? userdate($result->duedate) : 'No due date',
                    'startdate' => $result->allowsubmissionsfromdate,
                    'startdateformatted' => ($result->allowsubmissionsfromdate)
                        ? userdate($result->allowsubmissionsfromdate)
                        : 'No start date',
                    'courseid' => $result->course,
                    'coursename' => $result->coursename,
                    'url' => (new \moodle_url('/mod/assign/view.php', ['id' => $result->cmid]))->out(false),
                ];
            }
        }
        return json_encode($assignments);
    }

     /**
      * Get a single assignment by its instance ID and course module ID
      *
      * @param int $courseid Course ID
      * @param int $instanceid Assignment instance ID
      * @param int $moduleid Course module ID
      * @return array|null Assignment data array or null if not found
      */
    public function get_assignment_by_id($courseid, $instanceid, $moduleid) {
        global $DB;

        // Build the query for a specific assignment.
        $sql = "SELECT cm.id as cmid, a.id, a.name, a.intro, a.duedate, a.allowsubmissionsfromdate,
                       a.course, c.fullname as coursename, cm.deletioninprogress
                FROM {assign} a
                JOIN {course_modules} cm ON cm.instance = a.id
                JOIN {modules} m ON m.id = cm.module
                JOIN {course} c ON c.id = a.course
                WHERE m.name = 'assign'
                AND a.id = :instanceid
                AND cm.id = :moduleid
                AND a.course = :courseid";

        $params = [
            'instanceid' => $instanceid,
            'moduleid' => $moduleid,
            'courseid' => $courseid,
        ];

        $result = $DB->get_record_sql($sql, $params);

        if (!$result || $result->deletioninprogress) {
            return null;
        }

        // Format the assignment data.
        return [
            'id' => $result->id,
            'cmid' => $result->cmid,
            'name' => $result->name,
            'description' => strip_tags($result->intro),
            'duedate' => $result->duedate,
            'duedateformatted' => ($result->duedate) ? userdate($result->duedate) : 'No due date',
            'startdate' => $result->allowsubmissionsfromdate,
            'startdateformatted' => ($result->allowsubmissionsfromdate)
                ? userdate($result->allowsubmissionsfromdate)
                : 'No start date',
            'courseid' => $result->course,
            'coursename' => $result->coursename,
            'url' => (new \moodle_url('/mod/assign/view.php', ['id' => $result->cmid]))->out(false),
        ];
    }
}
