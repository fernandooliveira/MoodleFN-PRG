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
 * Strings for component 'gradereport_progress', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   gradereport_progress
 * @copyright 2011 MoodleFN
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
global $CFG;
require_once($CFG->dirroot . '/grade/report/progress/lib.engine.php');

///global
$string['pluginname'] = 'Progress report generator';
$string['progress:viewall'] = 'View all user progress reports';
//global errors
$string['errorinitfailiure'] = 'unspecific failure';
$string['errorinitcourseid'] = 'error: course id parameter missing';
$string['errornoselectableusers'] = 'error: no users available to select';
$string['errorinvaliduserselected'] = 'error: selected user is not in set of selectable users';
$string['errornovalidreports'] = 'error: no reports enabled.  reports can be enabled in settings.';
$string['errorillegalformat'] = 'error: selected format is not in set of available formats, or has been disabled.';
$string['errorunknown'] = 'error: unknown cause';
//global settings.php
$string['settingprogressname'] = 'Progress report generator settings';
$string['settingprogressfulldescription'] = 'These settings apply to the progress report generator as a whole.';
$string['settingallowstudents'] = 'Allow students to use the progress report generator.';
$string['settingallowstudents_help'] = 'Allow students to use the progress report generator.';
$string['settingallowstudentsdescription'] = 'Checking this box will allow students to use the progress report generator.  They will be able to select themselves and check various reports.';
$string['settingreportsheader'] = 'Detected reports';
$string['settingreportsheaderfulldescription'] = 'The following subreport types have been detected and are available for configuration (where applicable)';
$string['settingformatssheader'] = 'Detected Formats';
$string['settingformatssheaderfulldescription'] = 'The following formats have been detected and are available for configuration (where applicable)';
//global lib.php (prg class)
$string['corereportselectuiheading'] = 'Select a user to report on, and (if applicable) a format to export your report in.';
$string['corereportselectuihiddenweight'] = 'Enter a numeric weight.  Lower weights place reports at the top';
$string['corereportselectuihiddenuser'] = 'Select a user to report on';
$string['corereportselectuiuserselect'] = 'Select a user';
$string['corereportselectuisubmit'] = 'Submit';
$string['corereportformatlistheading'] = 'Choose a format';
$string['coreinstructionsheading'] = 'Instructions';
$string['coreinstructionsparagraph'] = 'The progress report generator can be configured to deliver sorted subreports for a specific user.  These reports can be exported in various formats.';
$string['coreinstruction1'] = 'Select a student.';
$string['coreinstruction4'] = 'The order of subreports and the specific subreports included in the generated progress report are configured in site settings and further customized in course grade settings.';
$string['coreinstruction3'] = 'Click the submit button.';
$string['coreinstruction2'] = 'If a report has been loaded, click on a format link (opens in new window) to export the loaded report in the desired format.';

$prg_subplugin_manager_instance = progress_report_subplugin_manager::get_instance();

if ($files = $prg_subplugin_manager_instance->get_subplugin_files('formats','lang/en/gradereport_progress.php')) {
    foreach ($files as $file) {
        require_once($file);
    }
}

if ($files = $prg_subplugin_manager_instance->get_subplugin_files('reports','lang/en/gradereport_progress.php')) {
    foreach ($files as $file) {
        require_once($file);
    }
}