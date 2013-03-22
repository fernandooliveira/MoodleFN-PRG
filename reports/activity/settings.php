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
    $settings->add(new admin_setting_heading('grade_report_progress_act', get_string('settingactname', 'gradereport_progress'),
                       get_string('settingactfulldescription', 'gradereport_progress')));    
    //activity subreport enabled
    $settings->add(new admin_setting_configcheckbox('report_grade_progress_act_enabled', get_string('settingactenabled', 'gradereport_progress' ), 
                                                get_string('settingactenableddescription' , 'gradereport_progress' ), null,1,0));
    //activity subreport default weight
    $weightchoices = array();
    for ($i = 0; $i <= 100; $i++) {
        $weightchoices[$i] = $i;
    }
    //$settings->add(new admin_setting_configtext('report_grade_progress_act_weight', get_string('settingactweight', 'gradereport_progress' ), 
                                                //get_string('settingactweightdescription' , 'gradereport_progress' ), 0, PARAM_INT));

    $settings->add(new admin_setting_configselect('report_grade_progress_act_weight', get_string('settingactweight', 'gradereport_progress' ),
                                                get_string('settingactweightdescription' , 'gradereport_progress' ), 0, $weightchoices));                                                 
    //default sort
    $settings->add(new admin_setting_configselect('report_grade_progress_act_sort_default', get_string('settingactsortdefault', 'gradereport_progress' ),
                                                get_string('settingactsortdefaultdescription' , 'gradereport_progress' ), 0,
                                                array(0=>get_string('activitysortcourse', 'gradereport_progress'),
                                                      1=>get_string('activitysortcourseincomplete', 'gradereport_progress'),
                                                      2=>get_string('activitysortweight', 'gradereport_progress'),
                                                      3=>get_string('activitysortweightincomplete', 'gradereport_progress'))));
}