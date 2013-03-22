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
 * Subplugin settings
 *
 * @package    gradebook
 * @subpackage progress
 * @copyright  2011 MoodleFN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    //Summary Subreport heading
    $settings->add(new admin_setting_heading('gradereport_progress_sum', get_string('settingsumname', 'gradereport_progress'),
                       get_string('settingsumfulldescription', 'gradereport_progress')));    
    //summary report enabled
    $settings->add(new admin_setting_configcheckbox('report_grade_progress_sum_enabled', get_string('settingsumenabled', 'gradereport_progress' ), 
                                                get_string('settingsumenableddescription' , 'gradereport_progress' ), null,1,0));
    
    //activity subreport default weight
    $weightchoices = array();
    for ($i = 0; $i <= 100; $i++) {
        $weightchoices[$i] = $i;
    }
    $settings->add(new admin_setting_configselect('report_grade_progress_sum_weight', get_string('settingsumweight', 'gradereport_progress' ),
                                                get_string('settingsumweightdescription' , 'gradereport_progress' ), 0, $weightchoices));     
                                                
    //show total activities in course
    $settings->add(new admin_setting_configcheckbox('report_grade_progress_sum_show_total', get_string('settingsumtotalactivities', 'gradereport_progress' ), 
                                                get_string('settingsumtotalactivitiesdescription' , 'gradereport_progress' ), null,1,0));
    //show total activities completed so far
    $settings->add(new admin_setting_configcheckbox('report_grade_progress_sum_show_completed', get_string('settingsumtotalcomplete', 'gradereport_progress' ), 
                                                get_string('settingsumtotalcompletedescription' , 'gradereport_progress' ), null,1,0));
    //show activities not completed yet
    $settings->add(new admin_setting_configcheckbox('report_grade_progress_sum_show_incomplete', get_string('settingsumtotalincoomplete', 'gradereport_progress' ), 
                                                get_string('settingsumtotalincoompletedescription' , 'gradereport_progress' ), null,1,0));
    //show grade from submitted
    $settings->add(new admin_setting_configcheckbox('report_grade_progress_sum_show_grade_submitted', get_string('settingsumgradesubmitted', 'gradereport_progress' ), 
                                                get_string('settingsumgradesubmitteddescription' , 'gradereport_progress' ), null,1,0));
    //show grade from available
    $settings->add(new admin_setting_configcheckbox('report_grade_progress_sum_show_grade_available', get_string('settingsumgradeavailable', 'gradereport_progress' ), 
                                                get_string('settingsumgradeavailabledescription' , 'gradereport_progress' ), null,1,0));
    //show last course login
    $settings->add(new admin_setting_configcheckbox('report_grade_progress_sum_show_last_login', get_string('settingsumlastlogin', 'gradereport_progress' ), 
                                                get_string('settingsumlastlogindescription' , 'gradereport_progress' ), null,1,0));
    //show logins since course start
    $settings->add(new admin_setting_configcheckbox('report_grade_progress_sum_show_total_logins', get_string('settingsumtotallogin', 'gradereport_progress' ), 
                                                get_string('settingsumtotallogindescription' , 'gradereport_progress' ), null,1,0));
    //show number of weeks left to complete course
    $settings->add(new admin_setting_configcheckbox('report_grade_progress_sum_show_weeks_left', get_string('settingsumweeksleft', 'gradereport_progress' ), 
                                                get_string('settingsumweeksleftdescription' , 'gradereport_progress' ), null,1,0));
}