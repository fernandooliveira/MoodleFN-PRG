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
$mform->addElement('static', 'grade_report_progress_sum_heading', get_string('settingsumname', 'gradereport_progress' ), get_string('settingprogressfulldescription', 'gradereport_progress' ));

//selectyesno
$mform->addElement('selectyesno', 'report_grade_progress_sum_enabled', get_string('settingsumenabled', 'gradereport_progress' ));
$mform->addHelpButton('report_grade_progress_sum_enabled', 'settingsumenableddescription', 'gradereport_progress');
$defaultvalue = isset($CFG->report_grade_progress_sum_enabled)? $CFG->report_grade_progress_sum_enabled : 0;
$mform->setDefault('report_grade_progress_sum_enabled', $defaultvalue);

//text enter weight
/*
$mform->addElement('text', 'report_grade_progress_sum_weight', get_string('settingsumweight', 'gradereport_progress' ));
$mform->setType('report_grade_progress_sum_weight', PARAM_INT);
$mform->addRule('report_grade_progress_sum_weight', null, 'numeric', null, 'client');
$mform->addHelpButton('report_grade_progress_sum_weight', 'settingsumweightdescription', 'gradereport_progress');
*/
//select weight
$weightchoices = array();
for ($i = 0; $i <= 100; $i++) {
    $weightchoices[$i] = $i;
}
$mform->addElement('select', 'report_grade_progress_sum_weight', get_string('settingsumweight', 'gradereport_progress'), $weightchoices);
$defaultvalue = isset($CFG->report_grade_progress_sum_weight)? $CFG->report_grade_progress_sum_weight : 0;
$mform->setDefault('report_grade_progress_sum_weight', $defaultvalue);
$mform->addHelpButton('report_grade_progress_sum_weight', 'settingsumweightdescription', 'gradereport_progress');


//show total activities in course
$mform->addElement('selectyesno', 'report_grade_progress_sum_show_total', get_string('settingsumtotalactivities', 'gradereport_progress' ));
$mform->addHelpButton('report_grade_progress_sum_show_total', 'settingsumtotalactivitiesdescription', 'gradereport_progress');
$defaultvalue = isset($CFG->report_grade_progress_sum_show_total)? $CFG->report_grade_progress_sum_show_total : 0;
$mform->setDefault('report_grade_progress_sum_show_total', $defaultvalue);

//show total activities completed so far
$mform->addElement('selectyesno', 'report_grade_progress_sum_show_completed', get_string('settingsumtotalcomplete', 'gradereport_progress' ));
$mform->addHelpButton('report_grade_progress_sum_show_completed', 'settingsumtotalcompletedescription', 'gradereport_progress');
$defaultvalue = isset($CFG->report_grade_progress_sum_show_completed)? $CFG->report_grade_progress_sum_show_completed : 0;
$mform->setDefault('report_grade_progress_sum_show_completed', $defaultvalue);

//show activities not completed yet
$mform->addElement('selectyesno', 'report_grade_progress_sum_show_incomplete', get_string('settingsumtotalincoomplete', 'gradereport_progress' ));
$mform->addHelpButton('report_grade_progress_sum_show_incomplete', 'settingsumtotalincoompletedescription', 'gradereport_progress');
$defaultvalue = isset($CFG->report_grade_progress_sum_show_incomplete)? $CFG->report_grade_progress_sum_show_incomplete : 0;
$mform->setDefault('report_grade_progress_sum_show_incomplete', $defaultvalue);

//show grade from submitted
$mform->addElement('selectyesno', 'report_grade_progress_sum_show_grade_submitted', get_string('settingsumgradesubmitted', 'gradereport_progress' ));
$mform->addHelpButton('report_grade_progress_sum_show_grade_submitted', 'settingsumgradesubmitteddescription', 'gradereport_progress');
$defaultvalue = isset($CFG->report_grade_progress_sum_show_grade_submitted)? $CFG->report_grade_progress_sum_show_grade_submitted : 0;
$mform->setDefault('report_grade_progress_sum_show_grade_submitted', $defaultvalue);

//show grade from available
$mform->addElement('selectyesno', 'report_grade_progress_sum_show_grade_available', get_string('settingsumgradeavailable', 'gradereport_progress' ));
$mform->addHelpButton('report_grade_progress_sum_show_grade_available', 'settingsumgradeavailabledescription', 'gradereport_progress');
$defaultvalue = isset($CFG->report_grade_progress_sum_show_grade_available)? $CFG->report_grade_progress_sum_show_grade_available : 0;
$mform->setDefault('report_grade_progress_sum_show_grade_available', $defaultvalue);

//show last course login
$mform->addElement('selectyesno', 'report_grade_progress_sum_show_last_login', get_string('settingsumlastlogin', 'gradereport_progress' ));
$mform->addHelpButton('report_grade_progress_sum_show_last_login', 'settingsumlastlogindescription', 'gradereport_progress');
$defaultvalue = isset($CFG->report_grade_progress_sum_show_last_login)? $CFG->report_grade_progress_sum_show_last_login : 0;
$mform->setDefault('report_grade_progress_sum_show_last_login', $defaultvalue);

//show logins since course start
$mform->addElement('selectyesno', 'report_grade_progress_sum_show_total_logins', get_string('settingsumtotallogin', 'gradereport_progress' ));
$mform->addHelpButton('report_grade_progress_sum_show_total_logins', 'settingsumtotallogindescription', 'gradereport_progress');
$defaultvalue = isset($CFG->report_grade_progress_sum_show_total_logins)? $CFG->report_grade_progress_sum_show_total_logins : 0;
$mform->setDefault('report_grade_progress_sum_show_total_logins', $defaultvalue);

//show number of weeks left to complete course
$mform->addElement('selectyesno', 'report_grade_progress_sum_show_weeks_left', get_string('settingsumweeksleft', 'gradereport_progress' ));
$mform->addHelpButton('report_grade_progress_sum_show_weeks_left', 'settingsumweeksleftdescription', 'gradereport_progress');
$defaultvalue = isset($CFG->report_grade_progress_sum_show_weeks_left)? $CFG->report_grade_progress_sum_show_weeks_left : 0;
$mform->setDefault('report_grade_progress_sum_show_weeks_left', $defaultvalue);