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
 * To add progress report settings to the admin block
 *
 * @package    gradebook
 * @subpackage progress
 * @copyright  2011 MoodleFN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    ///Global
    //Progress Report Generator heading
    $settings->add(new admin_setting_heading('grade_report_progress', get_string('settingprogressname', 'gradereport_progress'),
                       get_string('settingprogressfulldescription', 'gradereport_progress')));    
    //Progress Report Generator: can students use the progress report generator
    $settings->add(new admin_setting_configcheckbox('grade_report_progress_allow_students', get_string('settingallowstudents', 'gradereport_progress' ), 
                                                get_string('settingallowstudentsdescription', 'gradereport_progress' ), null,1,0));
                                             
    ///Subreports
    //Activity Subreport heading
    $settings->add(new admin_setting_heading('grade_report_progress', get_string('settingactname', 'gradereport_progress'),
                       get_string('settingactfulldescription', 'gradereport_progress')));    
    //activity subreport enabled
    $settings->add(new admin_setting_configcheckbox('grade_report_progress_act_enabled', get_string('settingactenabled', 'gradereport_progress' ), 
                                                get_string('settingactenableddescription' , 'gradereport_progress' ), null,1,0));
    //activity subreport default weight
    $settings->add(new admin_setting_configtext('grade_report_progress_act_weight', get_string('settingactweight', 'gradereport_progress' ), 
                                                get_string('settingactweightdescription' , 'gradereport_progress' ), 0, PARAM_INT));
                                                
    //Category Subreport heading
    $settings->add(new admin_setting_heading('grade_report_progress', get_string('settingcatname', 'gradereport_progress'),
                       get_string('settingcatfulldescription', 'gradereport_progress')));    
    //category report enabled
    $settings->add(new admin_setting_configcheckbox('grade_report_progress_cat_enabled', get_string('settingcatenabled', 'gradereport_progress' ), 
                                                get_string('settingcatenableddescription' , 'gradereport_progress' ), null,1,0));
    //category report default weight
    $settings->add(new admin_setting_configtext('grade_report_progress_cat_weight', get_string('settingcatweight', 'gradereport_progress' ), 
                                                get_string('settingcatweightdescription' , 'gradereport_progress' ), 0, PARAM_INT));
    //Category report max depth                                            
    $settings->add(new admin_setting_configtext('grade_report_progress_cat_weight', get_string('settingcatdepth', 'gradereport_progress' ), 
                                                get_string('settingcatdepthdescription' , 'gradereport_progress' ), 0, PARAM_INT));

 
    //Section Subreport heading
    $settings->add(new admin_setting_heading('grade_report_progress', get_string('settingsecname', 'gradereport_progress'),
                       get_string('settingsecfulldescription', 'gradereport_progress')));    
    //section report enabled
    $settings->add(new admin_setting_configcheckbox('grade_report_progress_sec_enabled', get_string('settingsecenabled', 'gradereport_progress' ), 
                                                get_string('settingsecenableddescription' , 'gradereport_progress' ), null,1,0));
    //section report default weight
    $settings->add(new admin_setting_configtext('grade_report_progress_sec_weight', get_string('settingsecweight', 'gradereport_progress' ), 
                                                get_string('settingsecweightdescription' , 'gradereport_progress' ), 0, PARAM_INT));
                                                
    //Summary Subreport heading
    $settings->add(new admin_setting_heading('gradereport_progress', get_string('settingsumname', 'gradereport_progress'),
                       get_string('settingsumfulldescription', 'gradereport_progress')));    
    //summary report enabled
    $settings->add(new admin_setting_configcheckbox('grade_report_progress_sum_enabled', get_string('settingsumenabled', 'gradereport_progress' ), 
                                                get_string('settingsumenableddescription' , 'gradereport_progress' ), null,1,0));
    //summary report default weight
    $settings->add(new admin_setting_configtext('grade_report_progress_sum_weight', get_string('settingsumweight', 'gradereport_progress' ), 
                                                get_string('settingsumweightdescription' , 'gradereport_progress' ), 0, PARAM_INT));
    
    ///Formats
    // Base Format - no settings because required, so always enabled
    
    //Email Format heading
    $settings->add(new admin_setting_heading('grade_report_progress', get_string('settingemailname', 'gradereport_progress'),
                           get_string('settingemailfulldescription', 'gradereport_progress'))); 
    //email format enabled
    $settings->add(new admin_setting_configcheckbox('grade_report_progress_email_enabled', get_string('settingenableemail', 'gradereport_progress' ), 
                          get_string('settingenableemaildescription' , 'gradereport_progress' ), null,1,0));    
    
    //PDF Format heading
    $settings->add(new admin_setting_heading('grade_report_progress', get_string('settingpdfname', 'gradereport_progress'),
                       get_string('settingpdffulldescription', 'gradereport_progress'))); 
    //pdf format enabled
    $settings->add(new admin_setting_configcheckbox('grade_report_progress_pdf_enabled', get_string('settingenablepdf', 'gradereport_progress' ), 
                                                get_string('settingenablepdfdescription' , 'gradereport_progress' ), null,1,0));
    
    //Print Format - no settings because browsers allow this, so always enabled

}