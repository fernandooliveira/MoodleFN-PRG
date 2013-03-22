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
    //Email Format heading
    $settings->add(new admin_setting_heading('grade_report_progress_email', get_string('settingemailname', 'gradereport_progress'),
                           get_string('settingemailfulldescription', 'gradereport_progress'))); 
    //email format enabled
    $settings->add(new admin_setting_configcheckbox('report_grade_progress_email_enabled', get_string('settingenableemail', 'gradereport_progress' ), 
                          get_string('settingenableemaildescription' , 'gradereport_progress' ), null,1,0));    
    //allow students to send emails       
    $settings->add(new admin_setting_configcheckbox('report_grade_progress_email_enabled_students', get_string('settingenableemailstudents', 'gradereport_progress' ), 
                          get_string('settingenableemailstudentsdescription' , 'gradereport_progress' ), null,1,0));                   
}    