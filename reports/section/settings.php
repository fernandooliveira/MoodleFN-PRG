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
 
    //Section Subreport heading
    $settings->add(new admin_setting_heading('grade_report_progress_sec', get_string('settingsecname', 'gradereport_progress'),
                       get_string('settingsecfulldescription', 'gradereport_progress')));    
    //section report enabled
    $settings->add(new admin_setting_configcheckbox('report_grade_progress_sec_enabled', get_string('settingsecenabled', 'gradereport_progress' ), 
                                                get_string('settingsecenableddescription' , 'gradereport_progress' ), null,1,0));
    
    //activity subreport default weight
    $weightchoices = array();
    for ($i = 0; $i <= 100; $i++) {
        $weightchoices[$i] = $i;
    }
    $settings->add(new admin_setting_configselect('report_grade_progress_sec_weight', get_string('settingsecweight', 'gradereport_progress' ),
                                                get_string('settingsecweightdescription' , 'gradereport_progress' ), 0, $weightchoices));     
     
}