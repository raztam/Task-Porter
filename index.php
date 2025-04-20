<?php
use local_taskporter\controller\task_controller;

require_once('../../config.php');
require_once($CFG->dirroot . '/local/taskporter/classes/task_controller.php');
require_login();

$courseid = required_param('courseid', PARAM_INT);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = context_course::instance($courseid);

require_capability('local/taskporter:view', $context);

$PAGE->set_url(new moodle_url('/local/taskporter/index.php', array('courseid' => $courseid)));
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_title(get_string('pluginname', 'local_taskporter'));
$PAGE->set_heading(format_string($course->fullname));

$taskcontroller = new task_controller();
$assignmentsjson = $taskcontroller->get_course_assignments($courseid);
// Pass true as the second parameter to get arrays instead of objects.
$assignments = json_decode($assignmentsjson, true);

// Prepare data for the template.
$templatedata = [
    'has_assignments' => !empty($assignments),
    'assignments' => $assignments,
    'calendar_url' => (new moodle_url('/local/taskporter/add_to_calendar.php', ['courseid' => $courseid]))->out(false),
];

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_taskporter'));


// Render the template.
echo $OUTPUT->render_from_template('local_taskporter/task_list', $templatedata);


echo $OUTPUT->footer();
