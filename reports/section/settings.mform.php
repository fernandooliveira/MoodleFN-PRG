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
 
//static
$mform->addElement('static', 'grade_report_progress_sec_heading', get_string('settingsecname', 'gradereport_progress' ), get_string('settingprogressfulldescription', 'gradereport_progress' ));

//selectyesno
$mform->addElement('selectyesno', 'report_grade_progress_sec_enabled', get_string('settingsecenabled', 'gradereport_progress' ));
$mform->addHelpButton('report_grade_progress_sec_enabled', 'settingsecenableddescription', 'gradereport_progress');
$defaultvalue = isset($CFG->report_grade_progress_sec_enabled)? $CFG->report_grade_progress_sec_enabled : 0;
$mform->setDefault('report_grade_progress_sec_enabled', $defaultvalue);

//text enter weight
/*
$mform->addElement('text', 'report_grade_progress_sec_weight', get_string('settingsecweight', 'gradereport_progress' ));
$mform->setType('report_grade_progress_sec_weight', PARAM_INT);
$mform->addRule('report_grade_progress_sec_weight', null, 'numeric', null, 'client');
$mform->addHelpButton('report_grade_progress_sec_weight', 'settingsecweightdescription', 'gradereport_progress');
*/
//select weight
$weightchoices = array();
for ($i = 0; $i <= 100; $i++) {
    $weightchoices[$i] = $i;
}
$mform->addElement('select', 'report_grade_progress_sec_weight', get_string('settingsecweight', 'gradereport_progress'), $weightchoices);
$defaultvalue = isset($CFG->report_grade_progress_sec_weight)? $CFG->report_grade_progress_sec_weight : 0;
$mform->setDefault('report_grade_progress_sec_weight', $defaultvalue);
$mform->addHelpButton('report_grade_progress_sec_weight', 'settingsecweightdescription', 'gradereport_progress');

