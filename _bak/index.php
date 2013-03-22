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
 * Progress Report Generator file accessed through web browser          (1)
 *
 * @package    grade                                                    (2)
 * @subpackage report
 * @copyright  2011 MoodleFN                                            (3)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later (4)
 */
 
require_once('../../../config.php');
require_once($CFG->dirroot . '/grade/report/progress/lib.php');
require_once($CFG->libdir . '/gradelib.php');

$courseid   = required_param('id', PARAM_INT); //the course that the report is on

$PAGE->set_url(new moodle_url('/grade/report/progress/index.php', array('id'=>$courseid)));
$PAGE->set_pagelayout('report');
/// basic access checks
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('nocourseid');
}
require_login($course);

$context = get_context_instance(CONTEXT_COURSE, $course->id);
require_capability('gradereport/progress:view', $context);

grade_regrade_final_grades($courseid);

/// progress report engine
$opts['course'] = $course;
$opts['courseid'] = $courseid;
$opts['format'] = optional_param('format', 'base', PARAM_ALPHANUM);
$opts['context'] = $context;
$opts['userid'] = optional_param('userid', -1 , PARAM_INT);
$progress = new progress_report_generator($opts);

if (!$progress->report()) { //generator returns false if fails
    print_error('errorinitfailiure', 'gradereport_prgoress', $CFG->wwwroot.'/grade/report/progress/index.php?' . $course->id); //general initialization error
}