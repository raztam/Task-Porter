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

function local_taskporter_extend_navigation($navigation) {
    global $COURSE, $PAGE;

    // Only extend navigation within course pages, and not on the site homepage
    if ($COURSE->id === SITEID) {
        return;
    }

    // Check if we're in a course context
    if ($coursenode = $navigation->find('currentcourse', navigation_node::TYPE_COURSE)) {
        // Add the menu item to the course navigation
        $coursenode->add(
            get_string('pluginname', 'local_taskporter'),
            new moodle_url('/local/taskporter/index.php', ['courseid' => $COURSE->id]),
            navigation_node::TYPE_CUSTOM,
            null,
            'taskporter',
            new pix_icon('i/import', '')
        );
    }
}

function local_taskporter_extend_settings_navigation(settings_navigation $navigation) {
    global $PAGE, $COURSE;

    // Only add to course pages
    if ($PAGE->context->contextlevel != CONTEXT_COURSE) {
        return;
    }

    // Find the course administration node
    if ($coursenode = $navigation->find('courseadmin', navigation_node::TYPE_COURSE)) {
        $coursenode->add(
            get_string('pluginname', 'local_taskporter'),
            new moodle_url('/local/taskporter/index.php', ['courseid' => $COURSE->id]),
            navigation_node::TYPE_SETTING,
            null,
            'taskporter',
            new pix_icon('i/import', '')
        );
    }
}

