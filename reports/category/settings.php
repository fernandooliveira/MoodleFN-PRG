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
    //Category Subreport heading
    $settings->add(new admin_setting_heading('grade_report_progress_cat', get_string('settingcatname', 'gradereport_progress'),
                       get_string('settingcatfulldescription', 'gradereport_progress')));    
    //category report enabled
    $settings->add(new admin_setting_configcheckbox('report_grade_progress_cat_enabled', get_string('settingcatenabled', 'gradereport_progress' ), 
                                                get_string('settingcatenableddescription' , 'gradereport_progress' ), null,1,0));
    //category report default weight
    //$settings->add(new admin_setting_configtext('report_grade_progress_cat_weight', get_string('settingcatweight', 'gradereport_progress' ), 
    //                                            get_string('settingcatweightdescription' , 'gradereport_progress' ), 0, PARAM_INT));
    $weightchoices = array();
    for ($i = 0; $i <= 100; $i++) {
        $weightchoices[$i] = $i;
    }
    $settings->add(new admin_setting_configselect('report_grade_progress_cat_weight', get_string('settingcatweight', 'gradereport_progress' ),
                                                get_string('settingcatweightdescription' , 'gradereport_progress' ), 0, $weightchoices));     
         
    /*
    //Category report max depth                                            
    $settings->add(new admin_setting_configtext('report_grade_progress_cat_depth', get_string('settingcatdepth', 'gradereport_progress' ), 
                                                get_string('settingcatdepthdescription' , 'gradereport_progress' ), 0, PARAM_INT));
    */
    
    //Show grade based on submitted activities
    $settings->add(new admin_setting_configcheckbox('report_grade_progress_cat_show_submitted', get_string('settingcatshowsubmitted', 'gradereport_progress' ), 
                                                get_string('settingcatshowsubmitteddescription' , 'gradereport_progress' ), null,1,0));
    //Show grade based on all activities in unit
    $settings->add(new admin_setting_configcheckbox('report_grade_progress_cat_show_available', get_string('settingcatshowavailable', 'gradereport_progress' ), 
                                                get_string('settingcatshowavailabledescription' , 'gradereport_progress' ), null,1,0));
    //add a selection to checkbox all categories...

    //Category Subreport heading
    $settings->add(new admin_setting_heading('grade_report_progress', get_string('settingcatdisclaim', 'gradereport_progress'),
        get_string('settingcatdisclaimdescription', 'gradereport_progress')));
}