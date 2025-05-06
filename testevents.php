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

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/eventslib.php');

use core\event\manager;

echo "<pre>";

$reflection = new ReflectionClass('core\\event\\manager');
$method = $reflection->getMethod('load_observers');
$method->setAccessible(true);
$observers = $method->invoke(null);

foreach ($observers as $eventname => $handlers) {
    if (str_contains($eventname, 'course_module_created')) {
        echo "Event: $eventname\n";
        print_r($handlers);
    }
}

echo "</pre>";
