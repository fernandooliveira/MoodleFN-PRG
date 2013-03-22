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
$string['settingallowstudentsdescription'] = 'Checking this box will allow students to use the progress report generator.  They will be able to select themselves and check various reports.';
//global lib.php (prg class)
$string['corereportselectuiheading'] = 'Select a user to report on, which subreports should be included, and (if applicable) a format to export a report in';
$string['corereportselectuihiddenweight'] = 'Enter a numeric weight.  Lower weights place reports at the top';
$string['corereportselectuihiddenuser'] = 'Select a user to report on';
$string['corereportselectuiuserselect'] = 'Select a user';
$string['corereportselectuisubmit'] = 'Submit';
$string['corereportformatlistheading'] = 'Choose a format';
$string['coreinstructionsheading'] = 'Instructions';
$string['coreinstructionsparagraph'] = 'The progress report generator can be configured to deliver sorted subreports for a specific user.  These reports can be exported in various formats.';
$string['coreinstruction1'] = 'Select a student.';
$string['coreinstruction2'] = 'Weight subreports.  Lower weights appear before heavier ones.  a weight of -1 excludes the report.';
$string['coreinstruction3'] = 'Click the submit button.';
$string['coreinstruction4'] = 'If a report has been loaded, click on a format link (opens in new window) to export the loaded report in the desired format.';
///subreports
//subreport activity class strings
$string['activity'] = 'activity';
$string['activitytitle'] = 'Activity report';
$string['activityselectlabel'] = 'Sort order';
$string['activitysubmitbutton'] = 'Sort activities';
$string['activitysortweightincomplete'] = 'Weight - incomplete first';
$string['activitysortweight'] = 'Weight';
$string['activitysortcourseincomplete'] = 'Course order - incomplete first';
$string['activitysortcourse'] = 'Course order';
$string['activitytableheaderactivity'] = 'Activity';
$string['activitytableheaderpercentage'] = 'Percent';
//subreport activity settings strings
$string['settingactname'] = 'Activity subreport settings';
$string['settingactfulldescription'] = 'The activity subreport lists all activities in a course along with their associated grade.  Activities can be sorted by weight, course order and by complete/incomplete.';
$string['settingactenabled'] = 'Enable activity subreport';
$string['settingactenableddescription'] = 'Check box to allow users to select the activity subreport/uncheck to remove the activity report as an option.';
$string['settingactweight'] = 'Default weight';
$string['settingactweightdescription'] = 'The default weight of the activity subreport as a numeric value.  Lower values place the activity subreport earlier in the sequence of selected subreports.  note that a value of -1 should disable the subreport.';
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
$string['settingcatweight'] = 'Default weight';
$string['settingcatweightdescription'] = 'The default weight of the category subreport as a numeric value.  Lower values place the category subreport earlier in the sequence of selected subreports.  note that a value of -1 should disable the subreport.';
$string['settingcatdepth'] = 'Maximum category depth';
$string['settingcatdepthdescription'] = 'A depth of 0, includes only courses sharing the selected course\'s parent.  Greater depths will include courses in child categories of the parent category of the selected course.  For example a depth of two will include courses with the same parent as the selected course.  Courses in child and grand child categories will also be included.';
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
$string['settingsecweight'] = 'Default weight';
$string['settingsecweightdescription'] = 'The default weight of the section subreport as a numeric value.  Lower values place the section subreport earlier in the sequence of selected subreports.  note that a value of -1 should disable the subreport.';
//subreport summary class strings
$string['summary'] = 'summary';
$string['summarytitle'] = 'Summary subreport';
$string['summarytotalgradeitemheading'] = 'Grade item totals';
$string['summarytotalgradeitems'] = 'Total number of grade items';
$string['summarytotalcomplete'] = 'Total complete';
$string['summarytotalincomplete'] = 'Total incomplete';
$string['summarytotalhidden'] = 'Total hidden';
$string['summarytotallocked'] = 'Total locked';
$string['summarytotalungraded'] = 'Total ungraded';
$string['summarytotalnograde'] = 'Total no grade';
$string['summaryoverallheading'] = 'Overall grades';
$string['summaryoverallmoodle'] = 'Overall grade from Moodle';
$string['summaryoverallsubmitted'] = 'Overall grade based on submitted assignments';
$string['summaryoverallavailable'] = 'Overall grade based on available assignments';
$string['summarylastaccess'] = 'Last logged in';
$string['summaryoveralldummyuserlocked'] = 'Overall dummy user locked';
$string['summaryusageheading'] = 'Usage';
$string['summaryhourslogged'] = 'Approximate hours spent in course';
$string['summaryweeksleft'] = 'Weeks left';
$string['summarynotapplicable'] = 'Not applicable';
//subreport summary settings strings
$string['settingsumname'] = 'Summary subreport settings';
$string['settingsumfulldescription'] = 'The summary subreport gives statistics derived from counting grade items filtered on the state of their completeness.  Overall grade stats and estimates of attendance are also provided.';
$string['settingsumenableddescription'] = 'Check box to allow users to select the summary subreport/ uncheck to remove the summary report as an option.';
$string['settingsumenabled'] = 'Summary subreport settings';
$string['settingsumweight'] = 'Default weight';
$string['settingsumweightdescription'] = 'The default weight of the summary subreport as a numeric value.  Lower values place the summary subreport earlier in the sequence of selected subreports.  note that a value of -1 should disable the subreport.';
///formats
//format base class strings
$string['basereportheading'] = 'Base format';
$string['base'] = 'base';
//format base settings strings - none
//format email class strings
$string['emailreportheading'] = 'email';
$string['email'] = 'email';
$string['emailconfigformheading'] = '';
$string['emailsubjectline'] = 'Moodle progress report';
$string['emailstatusheading'] = 'Email sent status';
$string['emailstatusnotsent'] = 'email not sent';
$string['emailstatussent'] = 'email sent';
$string['emailrecipientsuser'] = 'Recipient - user';
$string['emailrecipientsmentor'] = 'Recipient(s) - user\'s mentors';
$string['emailrecipientsteacher'] = 'Recipient(s) - user\'s teachers';
$string['emailfromlegend'] = 'Check box to make email from your account.  Leave unchecked to send email from the Moodle system.  Sending email with a from address allows for users to reply to you personally.';
$string['emailfromlabel'] = 'send email \'from\' my address ';
$string['emailadditionalmessagelegend'] = 'Write an additional message to appear before the progress report';
$string['emailadditionalmessagetitle'] = 'additional message';
$string['emailsubmitbutton'] = 'Send';
//format email settings strings
$string['settingemailname'] = 'Email';
$string['settingemailfulldescription'] = 'Allow users to email progress reports to the subject of the report, teachers and mentors.';
$string['settingenableemail'] = 'Enable email';
$string['settingenableemaildescription'] = 'Check box to allow users to email generated reports';
//format pdf class strings
$string['pdfreportheading'] = 'pdf';
$string['pdffilename'] = 'prg_';
$string['pdf'] = 'pdf';
//format pdf settings strings
$string['settingpdfname'] = 'PDF';
$string['settingpdffulldescription'] = 'Allow users to generate PDF versions of progress report generator outputs. (Uses TCPDF library)';
$string['settingenablepdf'] = 'Enable PDF generation';
$string['settingenablepdfdescription'] = 'Check box to allow users to generate PDF versions of reports';
//format print class strings
$string['print'] = 'print';
$string['printreportheading'] = 'print';
//format print settings strings - none
///renderers
//renderer print strings
$string['printbutton'] = 'Print';