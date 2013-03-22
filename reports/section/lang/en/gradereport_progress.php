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
 * String definitions of subplugin
 *
 * @package    gradebook
 * @subpackage progress
 * @copyright  2011 MoodleFN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
//subreport section class strings
$string['section'] = 'section';
$string['sectiontitle'] = 'Section subreport';
$string['sectionnotapplicable'] = 'na';
$string['sectiontotal'] = 'Total items';
$string['sectioncomplete'] = 'Complete items';
$string['sectionincomplete'] = 'Incomplete items';
$string['sectionsaved'] = 'Items saved but not submitted';
$string['sectionnotattempted'] = 'Items not attempted';
$string['sectionwaitingforgrade'] = 'Items waiting for grade';
//subreport section settings strings
$string['settingsecname'] = 'Section subreport settings';
$string['settingsecfulldescription'] = 'The section subreport lists all sections in the course the report is generated from.  For each section, counts of incomplete, complete, saved but ungraded, waiting for grade, and unattempted assignments are given.';
$string['settingsecenabled'] = 'Enable the section subreport';
$string['settingsecenableddescription'] = 'Check box to allow users to select the section subreport/ uncheck to remove the section report as an option.';
$string['settingsecenableddescription_help'] = 'Check box to allow users to select the section subreport/ uncheck to remove the section report as an option.';
$string['settingsecweight'] = 'Default weight';
$string['settingsecweightdescription'] = 'The default weight of the section subreport as a numeric value.  Lower values place the section subreport earlier in the sequence of selected subreports.  note that a value of -1 should disable the subreport.';
$string['settingsecweightdescription_help'] = 'The default weight of the section subreport as a numeric value.  Lower values place the section subreport earlier in the sequence of selected subreports.  note that a value of -1 should disable the subreport.';