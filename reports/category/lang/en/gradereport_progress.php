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
 
//subreport category class strings
$string['category'] = 'category';
$string['categorytitle'] = 'Category report';
$string['categorytotalgradeitems'] = 'Total number of grade items';
$string['categorytotalcomplete'] = 'Completed grade items';
$string['categoryoverallmoodle'] = 'Overall grade according to Moodle';
$string['categoryoverallsubmitted'] = 'Overall grade (submitted items only)';
$string['categoryoverallavailable'] = 'Overall grade (all available items)';
$string['categorynotauthorized'] = 'You are not authorized to use the progress report generator in the context of this course';
$string['categorynotenrolled'] = 'Selected user is not enrolled in this course.';
//subreport category settings strings
$string['settingcatname'] = 'Category subreport settings';
$string['settingcatfulldescription'] = '(Expensive to generate) the category subreport lists all courses in the same category as the course the report is generated from.  For each course, the grade based on submitted and available grade items is given along with total complete/incomplete.';
$string['settingcatenabled'] = 'Enable the category subreport';
$string['settingcatenableddescription'] = 'Check box to allow users to select the category subreport/ uncheck to remove the category report as an option.';
$string['settingcatenableddescription_help'] = 'Check box to allow users to select the category subreport/ uncheck to remove the category report as an option.';
$string['settingcatweight'] = 'Default weight';
$string['settingcatweightdescription'] = 'The default weight of the category subreport as a numeric value.  Lower values place the category subreport earlier in the sequence of selected subreports.  note that a value of -1 should disable the subreport.';
$string['settingcatweightdescription_help'] = 'The default weight of the category subreport as a numeric value.  Lower values place the category subreport earlier in the sequence of selected subreports.  note that a value of -1 should disable the subreport.';
$string['settingcatdepth'] = 'Maximum category depth';
$string['settingcatdepthdescription'] = 'A depth of 0, includes only courses sharing the selected course\'s parent.  Greater depths will include courses in child categories of the parent category of the selected course.  For example a depth of two will include courses with the same parent as the selected course.  Courses in child and grand child categories will also be included.';
$string['settingcatdepthdescription_help'] = 'A depth of 0, includes only courses sharing the selected course\'s parent.  Greater depths will include courses in child categories of the parent category of the selected course.  For example a depth of two will include courses with the same parent as the selected course.  Courses in child and grand child categories will also be included.';
$string['settingcatshowsubmitted'] = 'Show grades on submitted';
$string['settingcatshowsubmitteddescription'] = 'Display a course grade based on all grade items present in the course.  This is a standard Moodle course grade.';
$string['settingcatshowsubmitteddescription_help'] = 'Display a course grade based on all grade items present in the course.  This is a standard Moodle course grade.';
$string['settingcatshowavailable'] = 'Show grades on available';
$string['settingcatshowavailabledescription'] = 'Display a course grade based only on grade items that are available.  Unavailable activities and non-activity grades such as participation are excluded.';
$string['settingcatshowavailabledescription_help'] = 'Display a course grade based only on grade items that are available.  Unavailable activities and non-activity grades such as participation are excluded.';
$string['settingcatdisclaim'] = '*Courses to be displayed must be configured at the course level.*';
$string['settingcatdisclaimdescription'] = 'Courses to be displayed must be configured at the course level.  Presenting a default option at the site level could impede performance on large sites.';
$string['settingcatchoosecourses'] = 'Check courses which will be considered for category report.';