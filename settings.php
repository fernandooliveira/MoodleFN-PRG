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
global $CFG;
require_once($CFG->dirroot . '/grade/report/progress/lib.engine.php');
$prg_subplugin_manager_instance = progress_report_subplugin_manager::get_instance();

//core plugin
if ($ADMIN->fulltree) {
    //Progress Report Generator heading
    $settings->add(new admin_setting_heading('grade_report_progress_core', get_string('settingprogressname', 'gradereport_progress'),
                                             get_string('settingprogressfulldescription', 'gradereport_progress')));
                       
    //Progress Report Generator: can students use the progress report generator
    $settings->add(new admin_setting_configcheckbox('report_grade_progress_allow_students', get_string('settingallowstudents', 'gradereport_progress' ), 
                                                    get_string('settingallowstudentsdescription', 'gradereport_progress' ), null,1,0));
}

//reports
if ($ADMIN->fulltree) {
   $settings->add(new admin_setting_heading('grade_report_progress_all_reports', get_string('settingreportsheader', 'gradereport_progress'),
                                             get_string('settingreportsheaderfulldescription', 'gradereport_progress')));
}
//include report subplugin setting.php files
if ($files = $prg_subplugin_manager_instance->get_subplugin_files('reports','settings.php')) {
    foreach ($files as $file) {
        require_once($file);
    }
}

//formats
if ($ADMIN->fulltree) {    
    $settings->add(new admin_setting_heading('grade_report_progress_all_formats', get_string('settingformatssheader', 'gradereport_progress'),
                                             get_string('settingformatssheaderfulldescription', 'gradereport_progress')));
}
//include format subplugin setting.php files
if ($files = $prg_subplugin_manager_instance->get_subplugin_files('formats','settings.php')) {
    foreach ($files as $file) {
        require_once($file);
    }
}
