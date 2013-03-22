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
 * Subplugin form fields for course settings
 *
 * @package    gradebook
 * @subpackage progress
 * @copyright  2011 MoodleFN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;
 
//static
$mform->addElement('static', 'grade_report_progress_act_heading', get_string('settingactname', 'gradereport_progress' ), get_string('settingprogressfulldescription', 'gradereport_progress' ));

//selectyesno
$mform->addElement('selectyesno', 'report_grade_progress_act_enabled', get_string('settingactenabled', 'gradereport_progress' ));
$mform->addHelpButton('report_grade_progress_act_enabled', 'settingactenableddescription', 'gradereport_progress');
$defaultvalue = isset($CFG->report_grade_progress_act_enabled)? $CFG->report_grade_progress_act_enabled : 0;
$mform->setDefault('report_grade_progress_act_enabled', $defaultvalue);

//select weight
$weightchoices = array();
for ($i = 0; $i <= 100; $i++) {
    $weightchoices[$i] = $i;
}
$mform->addElement('select', 'report_grade_progress_act_weight', get_string('settingactweight', 'gradereport_progress'), $weightchoices);
$defaultvalue = isset($CFG->report_grade_progress_act_weight)? $CFG->report_grade_progress_act_weight : 0;
$mform->setDefault('report_grade_progress_act_weight', $defaultvalue);
$mform->addHelpButton('report_grade_progress_act_weight', 'settingactweightdescription', 'gradereport_progress');

//text enter weight
//$mform->addElement('text', 'report_grade_progress_act_weight', get_string('settingactweight', 'gradereport_progress' ));
//$mform->setType('report_grade_progress_act_weight', PARAM_INT);
//$mform->addRule('report_grade_progress_act_weight', null, 'numeric', null, 'client');
//$mform->addHelpButton('report_grade_progress_act_weight', 'settingactweightdescription', 'gradereport_progress');

//select default sort method
$sortchoices = array(0=>get_string('activitysortcourse', 'gradereport_progress'),
    1=>get_string('activitysortcourseincomplete', 'gradereport_progress'),
    2=>get_string('activitysortweight', 'gradereport_progress'),
    3=>get_string('activitysortweightincomplete', 'gradereport_progress'));
$mform->addElement('select', 'report_grade_progress_act_sort_default', get_string('settingactsortdefault', 'gradereport_progress'), $sortchoices);
$defaultvalue = isset($CFG->report_grade_progress_act_sort_default)? $CFG->report_grade_progress_act_sort_default : 0;
$mform->setDefault('report_grade_progress_act_sort_default', $defaultvalue);