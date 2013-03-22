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
 * Subplugin form fields for course settings
 *
 * @package    gradebook
 * @subpackage progress
 * @copyright  2011 MoodleFN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//static
$mform->addElement('static', 'grade_report_progress_cat_heading', get_string('settingcatname', 'gradereport_progress' ), get_string('settingprogressfulldescription', 'gradereport_progress' ));

//selectyesno
$mform->addElement('selectyesno', 'report_grade_progress_cat_enabled', get_string('settingcatenabled', 'gradereport_progress' ));
$mform->addHelpButton('report_grade_progress_cat_enabled', 'settingcatenableddescription', 'gradereport_progress');
$defaultvalue = isset($CFG->report_grade_progress_cat_enabled)? $CFG->report_grade_progress_cat_enabled : 0;
$mform->setDefault('report_grade_progress_cat_enabled', $defaultvalue);

/*
//text enter weight
$mform->addElement('text', 'report_grade_progress_cat_weight', get_string('settingcatweight', 'gradereport_progress' ));
$mform->setType('report_grade_progress_cat_weight', PARAM_INT);
$mform->addRule('report_grade_progress_cat_weight', null, 'numeric', null, 'client');
$mform->addHelpButton('report_grade_progress_cat_weight', 'settingcatweightdescription', 'gradereport_progress');

//category depth is no longer used
$mform->addElement('text', 'report_grade_progress_cat_depth', get_string('settingcatdepth', 'gradereport_progress' ));
$mform->setType('report_grade_progress_cat_depth', PARAM_INT);
$mform->addRule('report_grade_progress_cat_depth', null, 'numeric', null, 'client');
$mform->addHelpButton('report_grade_progress_cat_depth', 'settingcatdepthdescription', 'gradereport_progress');
*/
//select weight
$weightchoices = array();
for ($i = 0; $i <= 100; $i++) {
    $weightchoices[$i] = $i;
}
$mform->addElement('select', 'report_grade_progress_cat_weight', get_string('settingcatweight', 'gradereport_progress'), $weightchoices);
$defaultvalue = isset($CFG->report_grade_progress_cat_weight)? $CFG->report_grade_progress_cat_weight : 0;
$mform->setDefault('report_grade_progress_cat_weight', $defaultvalue);
$mform->addHelpButton('report_grade_progress_cat_weight', 'settingcatweightdescription', 'gradereport_progress');

//selectyesno
$mform->addElement('selectyesno', 'report_grade_progress_cat_show_submitted', get_string('settingcatshowsubmitted', 'gradereport_progress' ));
$mform->addHelpButton('report_grade_progress_cat_show_submitted', 'settingcatshowsubmitteddescription', 'gradereport_progress');
$defaultvalue = isset($CFG->report_grade_progress_cat_show_submitted)? $CFG->report_grade_progress_cat_show_submitted : 0;
$mform->setDefault('report_grade_progress_cat_show_submitted', $defaultvalue);

//selectyesno
$mform->addElement('selectyesno', 'report_grade_progress_cat_show_available', get_string('settingcatshowavailable', 'gradereport_progress' ));
$mform->addHelpButton('report_grade_progress_cat_show_available', 'settingcatshowavailabledescription', 'gradereport_progress');
$defaultvalue = isset($CFG->report_grade_progress_cat_show_available)? $CFG->report_grade_progress_cat_show_available : 0;
$mform->setDefault('report_grade_progress_cat_show_available', $defaultvalue);

$mform->addElement('html', '<div style="clear:both"><strong>'.get_string('settingcatchoosecourses', 'gradereport_progress' ).'</strong></div>');
gradereportcategory_process_categories($mform);

function gradereportcategory_process_categories(&$mform) {
    global $DB;

    //get course/category data
    $query = 'SELECT cou.id AS courseid, cou.fullname AS coursename,
                    cat.name AS catname, cat.id AS catid, cat.parent AS catparent, cat.path AS catpath, cat.coursecount AS catcoursecount, cat.depth AS catdepth
              FROM {course} cou INNER JOIN {course_categories} cat ON cou.category = cat.id
              ORDER BY cat.sortorder, cou.sortorder';

    if (!$courses = $DB->get_records_sql($query, array())) {
        return false;
    }

    //establish max category depth
    $maxdepth = 1;
    foreach ($courses as $course) {
        if ($course->catdepth > $maxdepth) {
            $maxdepth = $course->depth;
        }
    }

    $previous = null;
    $oldcatid = -1;

    foreach ($courses as $course) {

        if ($course->catparent == 0) { //only process top level categories.

            if ($oldcatid != $course->catid) { //check if new category has started

                if ($oldcatid != -1) { //this is not the first pass
                    //tie off the previous category's select group
                    $mform->add_checkbox_controller($oldcatid);

                    if ($previous->catdepth < $maxdepth) {
                        gradereportcategory_process_child_category(&$mform, $courses, $previous, $maxdepth);
                    }

                }

                //create heading for new category
                $mform->addElement('html', '<div style="clear:both"><strong>'.$course->catname.'</strong></div>');

                //change value of old cat id
                $oldcatid = $course->catid;

            }

            //write the current course
            $mform->addElement('advcheckbox', 'report_grade_progress_cat_show_'.$course->courseid, $course->coursename, '', array('group' => $oldcatid), array(0, 1));
            $previous = $course;
        }
    }
    //process the children of the last category
    if ($previous->catdepth < $maxdepth) {
        gradereportcategory_process_child_category(&$mform, $courses, $previous, $maxdepth);
    }
}

function gradereportcategory_process_child_category(&$mform, $courses, $current, $maxdepth) {

    $oldcatid = -1;
    $previous = null;

    //iterate all courses looking for ones with same parent.  If there is depth to go, make a recursive call
    foreach ($courses as $course) {

        if ($course->catparent == $current->catid) { //this is a child course, only process these

            if ($oldcatid != $course->catid) { //check if new category has started

                if ($oldcatid != -1) { //if a real category is being iterated
                    //tie off the previous category's select group
                    $mform->add_checkbox_controller($oldcatid);

                    if ($previous->catdepth < $maxdepth) {
                        gradereportcategory_process_child_category(&$mform, $courses, $previous, $maxdepth);
                    }

                }

                //create heading for new category
                $mform->addElement('html', '<div style="clear:both"><strong>'.$course->catname.'</strong></div>');

                //change value of old cat id
                $oldcatid = $course->catid;

            }

            //write the current course
            $mform->addElement('advcheckbox', 'report_grade_progress_cat_show_'.$course->courseid, $course->coursename, '', array('group' => $oldcatid), array(0, 1));
            $previous = $course;
        }
    }
}