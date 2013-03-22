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
 * Subplugin class definition
 *
 * @package    gradebook
 * @subpackage progress
 * @copyright  2011 MoodleFN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
require_once($CFG->dirroot . '/grade/report/lib.php');
require_once($CFG->dirroot . '/grade/lib.php');
require_once($CFG->dirroot . '/grade/report/lib.php');

defined('MOODLE_INTERNAL') || die;

/**
 * Summary subreport
 * @package gradebook
 * @subpackage progress
 */
class progress_report_subreport_summary{
    /**
     * consumed by get_string
     * @const string NAME_CODE_STRING
     */
    const NAME_CODE_STRING                      = 'summary';
    /**
     * consumed by get_string
     * @const string TITLE_STRING
     */
    const TITLE_STRING                          = 'summarytitle';

    /**
     * consumed by grade_get_setting
     * @const string SETTING_ENABLED
     */
    const SETTING_ENABLED                       = 'report_grade_progress_sum_enabled';
    /**
     * consumed by grade_get_setting
     * @const string SETTING_WEIGHT
     */
    const SETTING_WEIGHT                        = 'report_grade_progress_sum_weight';

    /**
     * short name for subreport used for tagging URL/POST/GET parameters
     * @const string SHORT_NAME
     */
    const SHORT_NAME                             = 'sum';

    /**
     * consumed by get_string
     * @const string TOTAL_GRADE_ITEM_HEADING
     */
    const TOTAL_GRADE_ITEM_HEADING               = 'summarytotalgradeitemheading';

    /**
     * consumed by get_string
     * @const string TOTAL_GRADE_ITEMS
     */
    const TOTAL_GRADE_ITEMS                      = 'summarytotalgradeitems';

    /**
     * consumed by get_string
     * @const string TOTAL_COMPLETE
     */
    const TOTAL_COMPLETE                         = 'summarytotalcomplete';

    /**
     * consumed by get_string
     * @const string TOTAL_INCOMPLETE
     */
    const TOTAL_INCOMPLETE                       = 'summarytotalincomplete';

    /**
     * consumed by get_string
     * @const string TOTAL_HIDDEN
     */
    const TOTAL_HIDDEN                           = 'summarytotalhidden';

    /**
     * consumed by get_string
     * @const string TOTAL_LOCKED
     */
    const TOTAL_LOCKED                           = 'summarytotallocked';

    /**
     * consumed by get_string
     * @const string TOTAL_UNGRADED
     */
    const TOTAL_UNGRADED                         = 'summarytotalungraded';

    /**
     * consumed by get_string
     * @const string TOTAL_NO_GRADE
     */
    const TOTAL_NO_GRADE                         = 'summarytotalnograde';

    /**
     * consumed by get_string
     * @const string OVERALL_HEADING
     */
    const OVERALL_HEADING                        = 'summaryoverallheading';

    /**
     * consumed by get_string
     * @const string OVERALL_MOODLE
     */
    const OVERALL_MOODLE                         = 'summaryoverallmoodle';

    /**
     * consumed by get_string
     * @const string OVERALL_SUBMITTED
     */
    const OVERALL_SUBMITTED                      = 'summaryoverallsubmitted';

    /**
     * consumed by get_string
     * @const string OVERALL_AVAILABLE
     */
    const OVERALL_AVAILABLE                      = 'summaryoverallavailable';

    /**
     * consumed by get_string
     * @const string LAST_ACCESS
     */
    const LAST_ACCESS                            = 'summarylastaccess';

    /**
     * consumed by get_string
     * @const string OVERALL_DUMMY_USER_LOCKED
     */
    const OVERALL_DUMMY_USER_LOCKED              = 'summaryoveralldummyuserlocked';

    /**
     * consumed by get_string
     * @const string USAGE_HEADING
     */
    const USAGE_HEADING                          = 'summaryusageheading';

    /**
     * consumed by get_string
     * @const string HOURS_LOGGED
     */
    const HOURS_LOGGED                           = 'summaryhourslogged';

    /**
     * consumed by get_string
     * @const string WEEKS_LEFT
     */
    const WEEKS_LEFT                             = 'summaryweeksleft';

    /**
     * consumed by get_string
     * @const string NOT_APPLICABLE
     */
    const NOT_APPLICABLE                         = 'summarynotapplicable';
    
    /**
     * consumed by get_string
     * @const string NOT_APPLICABLE
     */
    const DAYS_AGO                               = 'summarydaysago';

    /**
     * id of the user who is the subject of the subreport
     * @var int $userid
     */
    protected $userid;

    /**
     * id of the course being reported on
     * @var int $courseid
     */
    protected $courseid;

    /**
     * Moodle context object
     * @var object $context
     */
    protected $context;

    /**
     * object representation of gradeable course items
     * @var object $gtree
     */
    protected $gtree;

    /**
     * whether or not $USER can view hidden content
     * @var bool $canviewhidden
     */
    protected $canviewhidden;

    /**
     * collection of grade item activities
     * @var array $activities
     */
    protected $activities = array();

    /**
     * count of grade items in course (not category or course)
     * @var int $totalgradeitems
     */
    protected $totalgradeitems = 0;

    /**
     * count of locked activities in course
     * @var int $totallockedgradeitems
     */
    protected $totallockedgradeitems = 0;

    /**
     * count of hidden activities in course
     * @var int $totallockedgradeitems
     */
    protected $totalhiddengradeitems = 0;

    /**
     * count of available complete activities in course
     * @var int $totalcomplete
     */
    protected $totalcomplete = 0;

    /**
     * count of available incomplete activities in course
     * @var int $totalincomplete
     */
    protected $totalincomplete = 0;

    /**
     * count of available grade items for which there is no user grade
     * @var int $totalnograde
     */
    protected $totalnograde = 0;

    /**
     * count of items with no grade which have an ungraded submission
     * @var int $totalnograde
     */
    protected $totalungraded = 0;

    /**
     * overall grade based only on submitted assignments
     * @var float $overallsubmitted
     */
    protected $overallsubmitted;

    /**
     * overall grade based on all available assignments
     * @var float $overallavailable
     */
    protected $overallavailable;

    /**
     * overall grade as calculated by Moodle
     * @var float $overallmoodle
     */
    protected $overallmoodle;

    /**
     * number of full days since last access
     * @var int $lastaccessdays
     */
    protected $lastaccessdays;

    /**
     * number of weeks before end of enrollment
     * @var int $weeksleft
     */
    protected $weeksleft;

    /**
     * number of separate hours counted in logs
     * @var int $hourslogged
     */
    protected $hourslogged;

    /**
     * Constructor. Sets local copies of user preferences
     * @param int $userid
     * @param object $course
     * @param object $context
     * @param object $params
     * @param string $format The format to output the report in
     */
    public function __construct($userid, $courseid, $context, $course, $params) {
        global $DB, $CFG;

        $this->userid = $userid;
        $this->courseid = $courseid;
        $this->course = $course;
        $this->context = $context;
        $this->params = $params;

        $this->canviewhidden = has_capability('moodle/grade:viewhidden', get_context_instance(CONTEXT_COURSE, $this->courseid));
        //grade tree - no fillers, category grade item is not last child, no outcomes
        $this->gtree         = new grade_tree($this->courseid, false, false, null, false);
        $this->initialize_activities();
        $this->initialize_overall_grades();
        $this->initialize_attendance();
    }

    /**
     * Builds $this->activities
     */
    protected function initialize_activities(){
        $this->initialize_activities_recursive($this->gtree->top_element);
    }

    /**
     * Uses grade items and grade grades to gather data on gradeable activities completed by a user
     * @param object $element
     */
    protected function initialize_activities_recursive(&$element){
        global $DB, $CFG;

        $type = $element['type'];
        $grade_object = $element['object'];
        $eid = $grade_object->id;

        if ($type == 'item') {//interested in this
            $this->activities[$eid] = array();
            $this->activities[$eid]['item'] =  $grade_object;
            $this->activities[$eid]['id']   =  $eid;
            //grade items are examined
            $this->totalgradeitems++;

            if ($grade_object->is_hidden()) {
                $this->totalhiddengradeitems++;
                $this->activities[$eid]['hidden'] =  true;
            } else {
                $this->activities[$eid]['hidden'] =  false;
            }

            if ($grade_object->is_locked($this->userid)) {
                $this->totallockedgradeitems++;
                $this->activities[$eid]['hidden'] =  true;
            } else {
                $this->activities[$eid]['hidden'] =  false;
            }

            if ($grade_grade = grade_grade::fetch(array('itemid'=>$grade_object->id,'userid'=>$this->userid))) {
                //check for pass or fail
                if ($grade_grade->is_passed($grade_object)) {
                    $this->totalcomplete++;
                } else {
                    $this->totalincomplete++;
                }
                $this->activities[$eid]['grade'] =  $grade_grade;
            } else {
                $this->activities[$eid]['grade'] =  false;
                $this->totalnograde++;

                //check if ungraded
                $itemtype = $grade_object->itemtype;
                $itemmodule = $grade_object->itemmodule;
                $iteminstance = $grade_object->iteminstance;
                if ($itemtype=='mod' && $iteminstance && $itemmodule=='assignment') {
                    $params = array($this->userid, $iteminstance);
                    $sql = "SELECT id FROM {assignment_submissions} " .
                           "WHERE  timemodified > timemarked " .
                           "  AND  userid = ? AND assignment = ?";
                    if ($waitingforgrade = $DB->get_record_sql($sql, $params)) {
                        $this->totalungraded++;
                    }
                }
            }
        }

        if (isset($element['children'])) {
            foreach ($element['children'] as $key=>$child) {
                $this->initialize_activities_recursive($element['children'][$key]);
            }
        }
    }

    /**
     * Uses the PRG dummy user to calculate final grades based on submissions and what is available
     * Appropriate properties are initialized in $this
     * @return bool whether or not dummy user was in use
     */
    protected function initialize_overall_grades() {

        $classname = 'progress_report_generator';
        //get the id of the PRG dummy user
        if ($dummyid = $classname::get_dummy_user_id($this->courseid)){
            $dummygrades = array();

            //dummy should now be a clone of the user for this course
            $course_item = grade_item::fetch_course_item($this->courseid);
            
            $course_grade = new grade_grade(array('itemid'=>$course_item->id, 'userid'=>$this->userid));
            $grademoodle = $course_grade->finalgrade;
            
            //initialize the dummy user's grades to clone of $userid's grades
            foreach ($this->activities as $activity) {
                //does dummy user have grade?
                if ($dummygrade = grade_grade::fetch(array('itemid'=>$activity['id'], 'userid'=>$dummyid))) {
                    //does user being cloned have grade
                    if ($activity['grade']) {
                        //change the value of the dummy grade to the cloned user's grade
                        $activity['item']->update_final_grade($dummyid, $activity['grade']->finalgrade, 'gradereport');
                        //store object for later use    
                        $dummygrades[$activity['id']] = grade_grade::fetch(array('itemid'=>$activity['id'], 'userid'=>$dummyid));
                    } else {
                        //user being cloned does not have this grade, so delete the dummy's instance of it
                        $dummygrade->delete('gradereport');
                    }
                } else { 
                    //dummy has no grade, does the user being cloned have one?
                    if ($activity['grade']) {
                        //create the grade for the dummy
                        $activity['item']->update_final_grade($dummyid, $activity['grade']->finalgrade, 'gradereport');
                        //store object for later use    
                        $dummygrades[$activity['id']] = grade_grade::fetch(array('itemid'=>$activity['id'], 'userid'=>$dummyid));
                    }
                }
            }
            
            //regrade the dummy's course grade and store the value
            if (!$errors = grade_regrade_final_grades($this->courseid, $dummyid, $course_item)) {
                print_r($errors);
            }
            $course_grade = new grade_grade(array('itemid'=>$course_item->id, 'userid'=>$dummyid));
            //course grade based on everything submitted by user being cloned
            $gradesubmitted = $course_grade->finalgrade;
            
            //change dummygrade value to MAX for each submitted/existing grade item
            foreach ($dummygrades as $dummygrade) {
                if($this->activities[$dummygrade->itemid]['item']->update_final_grade($dummyid, $this->activities[$dummygrade->itemid]['item']->grademax, 'gradereport')){
                }
            }
            
            //regrade the dummy user to get MAX based on submitted
            if (!$errors = grade_regrade_final_grades($this->courseid, $dummyid, $course_item)) {
                print_r($errors);
            }
            
            if (!$course_grade = grade_grade::fetch(array('itemid'=>$course_item->id, 'userid'=>$dummyid))) {
                $course_grade = new grade_grade(array('itemid'=>$course_item->id, 'userid'=>$dummyid));
            }
            $gradesubmittedmax = $course_grade->finalgrade;
            
            //redo activities to same as cloned user plus assign minimum value to non-existent grades for available grade items
            foreach ($this->activities as $activity) {
                //if the user being cloned has a grade
                if ($activity['grade']) {
                    //add a grade if the activity is available... there should be a way of checking if the locked activity was ever unlocked
                    if ($activity['item']->update_final_grade($dummyid, $activity['grade']->finalgrade, 'gradereport')) {
                        $dummygrades[$activity['id']] = grade_grade::fetch(array('itemid'=>$activity['id'], 'userid'=>$dummyid));
                    }
                } else {
                    //the user has not attempted this available assignment, give it the minimum grade
                    if (!$activity['item']->hidden and !$activity['item']->locked) {
                        if ($activity['item']->update_final_grade($dummyid, $activity['item']->grademin, 'gradereport')) {
                            //save the dummy grade
                            $dummygrades[$activity['id']] = grade_grade::fetch(array('itemid'=>$activity['id'], 'userid'=>$dummyid));
                        }
                    }
                }
            }
            
            //get grade base on cloned user's grades plus minimum grade in available where item is not graded
            if (!$errors = grade_regrade_final_grades($this->courseid, $dummyid, $course_item)) {
                print_r($errors);
            }
            
            if (!$course_grade = grade_grade::fetch(array('itemid'=>$course_item->id, 'userid'=>$dummyid))) {
                $course_grade = new grade_grade(array('itemid'=>$course_item->id, 'userid'=>$dummyid));
            }
            $gradeavailable = $course_grade->finalgrade;
            
            //regrade all available to max value to get the course grade based on maximum performance in available assignments
            foreach ($dummygrades as $dummygrade) {
                if ($this->activities[$dummygrade->itemid]['item']->update_final_grade($dummyid, $this->activities[$dummygrade->itemid]['item']->grademax, 'gradereport')) {
                }
            }
            if (!$errors = grade_regrade_final_grades($this->courseid, $dummyid, $course_item)) {
                print_r($errors);
            }
            
            if (!$course_grade = grade_grade::fetch(array('itemid'=>$course_item->id, 'userid'=>$dummyid))) {
                $course_grade = new grade_grade(array('itemid'=>$course_item->id, 'userid'=>$dummyid));
            }
            $gradeavailablemax = $course_grade->finalgrade;
            //echo 'grade moodle: ' . $grademoodle . '; ' ;
            //echo 'grade available: ' . $gradeavailable . '; ' ;
            //echo 'grade available max: ' . $gradeavailablemax . '; ' ;
            //echo 'grade submitted: ' . $gradesubmitted . '; ' ;
            //echo 'grade submitted max: ' . $gradesubmittedmax . '; ' ;
            $this->overallmoodle    = number_format($grademoodle, 2);
            $this->overallavailable = ($gradeavailablemax == 0) ? 0 : number_format($gradeavailable / $gradeavailablemax * 100, 2);
            $this->overallsubmitted = ($gradesubmittedmax == 0) ? 0 : number_format($gradesubmitted / $gradesubmittedmax * 100, 2);
            $classname::delete_dummy_user($dummyid);
            return true;
        } else {
            return false;
        }
    }

   /**
     * Initializes values for last course visit, enrollment end, and number of hours visited
     * @return void
     */
    protected function initialize_attendance() {
        global $DB, $CFG;

        $now = time();

        //get last access in days ago
        $params = array($this->userid, $this->courseid);
        $sql = "SELECT timeaccess FROM {user_lastaccess} " .
               "WHERE  userid = ? AND courseid = ? ";
        $this->lastaccessdays = null;
        if ($lastaccess = $DB->get_record_sql($sql, $params)) {
            $this->lastaccessdays = floor(($now - $lastaccess->timeaccess) / DAYSECS);
        }

        //get discrete visits grouped by hour increments
        $params = array($this->userid, $this->courseid);
        $sql = "SELECT id, time FROM {log} " .
               "WHERE  userid = ? AND course = ? " .
               "ORDER BY time ASC";
        $this->hourslogged = 0;
        if ($visits = $DB->get_records_sql($sql, $params)) {
            //iterate timestamps to estimate how many discrete hours the user has spent.
            $offset = 0;
            foreach($visits as $visit) {
                $difference = $visit->time - $offset;
                if ($difference > HOURSECS) {
                    $offset = $visit->time;
                    $this->hourslogged++;
                }
            }
        }

        //get weeks left from end of enrollment and now
        $params = array($this->userid, $this->courseid);
        $sql = "SELECT MAX(timeend) as maxend " .
               "FROM {user_enrolments} ue JOIN {enrol} e ON ue.enrolid = e.id " .
               "WHERE  ue.userid = ? AND e.courseid = ? " .
               "GROUP BY userid";
        $this->weeksleft = null;
        if ($weeksleft = $DB->get_record_sql($sql, $params)) {
            if ($weeksleft->maxend > 0) {
                //the duration is not indefinite
                $this->weeksleft = $weeksleft->maxend - $now;
                if ($this->weeksleft < 0 ) {
                    $this->weeksleft = 0;
                } else {
                    $this->weeksleft = ceil($this->weeksleft / WEEKSECS);
                }
            }
        }
    }

    /**
     * Create an array representation of subreport based on activities
     * @return array content representation of subreport
     */
    public function get_subreport_content() {

        $strings = array();
        $strings['totalgradeitemheading'] = get_string(self::TOTAL_GRADE_ITEM_HEADING, 'gradereport_progress');
        $strings['totalgradeitems']       = get_string(self::TOTAL_GRADE_ITEMS, 'gradereport_progress');
        $strings['totalcomplete']         = get_string(self::TOTAL_COMPLETE, 'gradereport_progress');
        $strings['totalincomplete']       = get_string(self::TOTAL_INCOMPLETE, 'gradereport_progress');
        $strings['totalhidden']           = get_string(self::TOTAL_HIDDEN, 'gradereport_progress');
        $strings['totallocked']           = get_string(self::TOTAL_LOCKED, 'gradereport_progress');
        $strings['totalungraded']         = get_string(self::TOTAL_UNGRADED, 'gradereport_progress');
        $strings['totalnograde']          = get_string(self::TOTAL_NO_GRADE, 'gradereport_progress');
        $strings['overallheading']        = get_string(self::OVERALL_HEADING, 'gradereport_progress');
        $strings['overallmoodle']         = get_string(self::OVERALL_MOODLE, 'gradereport_progress');
        $strings['overallsubmitted']      = get_string(self::OVERALL_SUBMITTED, 'gradereport_progress');
        $strings['overallavailable']      = get_string(self::OVERALL_AVAILABLE, 'gradereport_progress');
        $strings['overalldummylocked']    = get_string(self::OVERALL_DUMMY_USER_LOCKED, 'gradereport_progress');
        $strings['usageheading']          = get_string(self::USAGE_HEADING, 'gradereport_progress');
        $strings['lastaccess']            = get_string(self::LAST_ACCESS, 'gradereport_progress');
        $strings['hourslogged']           = get_string(self::HOURS_LOGGED, 'gradereport_progress');
        $strings['weeksleft']             = get_string(self::WEEKS_LEFT, 'gradereport_progress');
        $strings['notapplicable']         = get_string(self::NOT_APPLICABLE, 'gradereport_progress');
        $strings['daysago']               = get_string(self::DAYS_AGO, 'gradereport_progress');
        
        $settings = array();
        $settings['report_grade_progress_sum_show_total'] = self::get_report_setting($this->courseid, 'report_grade_progress_sum_show_total', 1);
        $settings['report_grade_progress_sum_show_completed'] = self::get_report_setting($this->courseid, 'report_grade_progress_sum_show_completed', 1);
        $settings['report_grade_progress_sum_show_incomplete'] = self::get_report_setting($this->courseid, 'report_grade_progress_sum_show_incomplete', 1);
        $settings['report_grade_progress_sum_show_grade_submitted'] = self::get_report_setting($this->courseid, 'report_grade_progress_sum_show_grade_submitted', 1);
        $settings['report_grade_progress_sum_show_grade_available'] = self::get_report_setting($this->courseid, 'report_grade_progress_sum_show_grade_available', 1);
        $settings['report_grade_progress_sum_show_last_login'] = self::get_report_setting($this->courseid, 'report_grade_progress_sum_show_last_login', 1);
        $settings['report_grade_progress_sum_show_total_logins'] = self::get_report_setting($this->courseid, 'report_grade_progress_sum_show_total_logins', 1);
        $settings['report_grade_progress_sum_show_weeks_left'] = self::get_report_setting($this->courseid, 'report_grade_progress_sum_show_weeks_left', 1);

        $prg = "progress_report_generator";

        //box
        $item = $prg::build_content_container_node($prg::HTML_DIV, array('id' => 'progress-subreport-' . self::SHORT_NAME));
        
        $i  = 0;
        ///title
        $item['children'][$i] = $prg::build_content_container_node($prg::HTML_HEADING_3);
        $text = get_string(self::TITLE_STRING, 'gradereport_progress');
        $item['children'][$i]['children'][0] = $prg::build_content_text_node($text);
        $i++;
        ///h4 - grade item totals
        $item['children'][$i] = $prg::build_content_container_node($prg::HTML_HEADING_4);
        $text = $strings['totalgradeitemheading'];
        $item['children'][$i]['children'][0] = $prg::build_content_text_node($text);
        $i++;
        //dl
        $item['children'][$i] = $prg::build_content_container_node($prg::HTML_DEFINITION_LIST);

        if ($settings['report_grade_progress_sum_show_total']){
            //dt - total
            $ii = 0;
            $text = $strings['totalgradeitems'];
            $item['children'][$i]['children'][$ii] = $prg::build_content_container_node($prg::HTML_DEFINITION_TERM);
            $item['children'][$i]['children'][$ii]['children'][0] = $prg::build_content_text_node($text);
            $ii++;
            //dd
            $text = $this->totalgradeitems;
            $item['children'][$i]['children'][$ii] = $prg::build_content_container_node($prg::HTML_DEFINITION_DEFINITION);
            $item['children'][$i]['children'][$ii]['children'][0] = $prg::build_content_text_node($text);
            $ii++;


            //dt - locked
            $text = $strings['totallocked'];
            $item['children'][$i]['children'][$ii] = $prg::build_content_container_node($prg::HTML_DEFINITION_TERM);
            $item['children'][$i]['children'][$ii]['children'][0] = $prg::build_content_text_node($text);
            $ii++;
            //dd
            $text = $this->totallockedgradeitems;
            $item['children'][$i]['children'][$ii] = $prg::build_content_container_node($prg::HTML_DEFINITION_DEFINITION);
            $item['children'][$i]['children'][$ii]['children'][0] = $prg::build_content_text_node($text);
            $ii++;
        }

        if ($settings['report_grade_progress_sum_show_completed']){
            //dt - complete
            $text = $strings['totalcomplete'];
            $item['children'][$i]['children'][$ii] = $prg::build_content_container_node($prg::HTML_DEFINITION_TERM);
            $item['children'][$i]['children'][$ii]['children'][0] = $prg::build_content_text_node($text);
            $ii++;
            //dd
            $text = $this->totalcomplete;
            $item['children'][$i]['children'][$ii] = $prg::build_content_container_node($prg::HTML_DEFINITION_DEFINITION);
            $item['children'][$i]['children'][$ii]['children'][0] = $prg::build_content_text_node($text);
            $ii++;
        }

        if ($settings['report_grade_progress_sum_show_incomplete']) {
            //dt - incomplete
            $text = $strings['totalincomplete'];
            $item['children'][$i]['children'][$ii] = $prg::build_content_container_node($prg::HTML_DEFINITION_TERM);
            $item['children'][$i]['children'][$ii]['children'][0] = $prg::build_content_text_node($text);
            $ii++;
            //dd
            $text = $this->totalincomplete;
            $item['children'][$i]['children'][$ii] = $prg::build_content_container_node($prg::HTML_DEFINITION_DEFINITION);
            $item['children'][$i]['children'][$ii]['children'][0] = $prg::build_content_text_node($text);
            $ii++;

            //dt - no grade
            $text = $strings['totalnograde'];
            $item['children'][$i]['children'][$ii] = $prg::build_content_container_node($prg::HTML_DEFINITION_TERM);
            $item['children'][$i]['children'][$ii]['children'][0] = $prg::build_content_text_node($text);
            $ii++;
            //dd
            $text = $this->totalnograde;
            $item['children'][$i]['children'][$ii] = $prg::build_content_container_node($prg::HTML_DEFINITION_DEFINITION);
            $item['children'][$i]['children'][$ii]['children'][0] = $prg::build_content_text_node($text);
            $ii++;

            //dt - ungraded
            $text = $strings['totalungraded'];
            $item['children'][$i]['children'][$ii] = $prg::build_content_container_node($prg::HTML_DEFINITION_TERM);
            $item['children'][$i]['children'][$ii]['children'][0] = $prg::build_content_text_node($text);
            $ii++;
            //dd
            $text = $this->totalungraded;
            $item['children'][$i]['children'][$ii] = $prg::build_content_container_node($prg::HTML_DEFINITION_DEFINITION);
            $item['children'][$i]['children'][$ii]['children'][0] = $prg::build_content_text_node($text);
            $i++;
        }

       if($settings['report_grade_progress_sum_show_grade_submitted'] || $settings['report_grade_progress_sum_show_grade_available']){
           ///h4 - overall grades
           $item['children'][$i] = $prg::build_content_container_node($prg::HTML_HEADING_4);
           $text = $strings['overallheading'];
           $item['children'][$i]['children'][0] = $prg::build_content_text_node($text);
           $i++;
           //dl
           $item['children'][$i] = $prg::build_content_container_node($prg::HTML_DEFINITION_LIST);
           $ii = 0;
           /* this seems to be the same as submitted and gets confusing.
           //dt - overall grade from moodle
           $text = $strings['overallmoodle'];
           $item['children'][$i]['children'][$ii] = $prg::build_content_container_node($prg::HTML_DEFINITION_TERM);
           $item['children'][$i]['children'][$ii]['children'][0] = $prg::build_content_text_node($text);
           $ii++;
           //dd
           $text = $this->overallmoodle . '%';
           $item['children'][$i]['children'][$ii] = $prg::build_content_container_node($prg::HTML_DEFINITION_DEFINITION);
           $item['children'][$i]['children'][$ii]['children'][0] = $prg::build_content_text_node($text);
           $ii++;
           */
           if($settings['report_grade_progress_sum_show_grade_submitted']){
               //dt - overall grade from submitted

               $attributes = ($this->overallsubmitted < 50) ? array('class' => 'incomplete') : array();
               $text = $strings['overallsubmitted'];
               $item['children'][$i]['children'][$ii] = $prg::build_content_container_node($prg::HTML_DEFINITION_TERM, $attributes);
               $item['children'][$i]['children'][$ii]['children'][0] = $prg::build_content_text_node($text);
               $ii++;
               //dd
               $text = $this->overallsubmitted . '%';
               $item['children'][$i]['children'][$ii] = $prg::build_content_container_node($prg::HTML_DEFINITION_DEFINITION, $attributes);
               $item['children'][$i]['children'][$ii]['children'][0] = $prg::build_content_text_node($text);
               $ii++;
               $attributes = array();
           }

           if ($settings['report_grade_progress_sum_show_grade_available']) {
               //dt - overall grade from available
               if ($this->overallavailable < 50) {
                   $attributes = array('class' => 'incomplete');
               }
               $text = $strings['overallavailable'];
               $item['children'][$i]['children'][$ii] = $prg::build_content_container_node($prg::HTML_DEFINITION_TERM, $attributes);
               $item['children'][$i]['children'][$ii]['children'][0] = $prg::build_content_text_node($text);
               $ii++;
               //dd
               $text = $this->overallavailable . '%';
               $item['children'][$i]['children'][$ii] = $prg::build_content_container_node($prg::HTML_DEFINITION_DEFINITION, $attributes);
               $item['children'][$i]['children'][$ii]['children'][0] = $prg::build_content_text_node($text);
               $i++;
               $attributes = array();
           }
       }

        if($settings['report_grade_progress_sum_show_last_login'] || $settings['report_grade_progress_sum_show_total_logins'] || $settings['report_grade_progress_sum_show_weeks_left']){
            //h4 - attendance stats
            $item['children'][$i] = $prg::build_content_container_node($prg::HTML_HEADING_4);
            $text = $strings['usageheading'];
            $item['children'][$i]['children'][0] = $prg::build_content_text_node($text);
            $i++;
            //dl
            $item['children'][$i] = $prg::build_content_container_node($prg::HTML_DEFINITION_LIST);
            $ii =0;

            if ($settings['report_grade_progress_sum_show_last_login']) {
                //dt - last access
                $text = $strings['lastaccess'];
                $item['children'][$i]['children'][$ii] = $prg::build_content_container_node($prg::HTML_DEFINITION_TERM);
                $item['children'][$i]['children'][$ii]['children'][0] = $prg::build_content_text_node($text);
                $ii++;
                //dd
                $text = $strings['notapplicable'];
                if (!is_null($this->lastaccessdays)) {
                    $text = $this->lastaccessdays . $strings['daysago'] ;
                }
                $item['children'][$i]['children'][$ii] = $prg::build_content_container_node($prg::HTML_DEFINITION_DEFINITION);
                $item['children'][$i]['children'][$ii]['children'][0] = $prg::build_content_text_node($text);
                $ii++;
            }

            if ($settings['report_grade_progress_sum_show_total_logins']) {
                //dt - hours logged
                $text = $strings['hourslogged'] ;
                $item['children'][$i]['children'][$ii] = $prg::build_content_container_node($prg::HTML_DEFINITION_TERM);
                $item['children'][$i]['children'][$ii]['children'][0] = $prg::build_content_text_node($text);
                $ii++;
                //dd
                $text = $this->hourslogged;
                $item['children'][$i]['children'][$ii] = $prg::build_content_container_node($prg::HTML_DEFINITION_DEFINITION);
                $item['children'][$i]['children'][$ii]['children'][0] = $prg::build_content_text_node($text);
                $ii++;
            }

            if ($settings['report_grade_progress_sum_show_weeks_left']) {
                //dt - weeks left
                $text = $strings['weeksleft'] ;
                $item['children'][$i]['children'][$ii] = $prg::build_content_container_node($prg::HTML_DEFINITION_TERM);
                $item['children'][$i]['children'][$ii]['children'][0] = $prg::build_content_text_node($text);
                $ii++;
                //dd
                $text = $strings['notapplicable'];
                if (!is_null($this->weeksleft)) {
                    $text = $this->weeksleft;
                }
                $item['children'][$i]['children'][$ii] = $prg::build_content_container_node($prg::HTML_DEFINITION_DEFINITION);
                $item['children'][$i]['children'][$ii]['children'][0] = $prg::build_content_text_node($text);
            }
        }
        return $item;

    }

    /**
     * Gets the name string of this subreport for use in the progress report generator ui.
     * @return string
     */
    static public function get_name_string() {
        return get_string(self::NAME_CODE_STRING, 'gradereport_progress');
    }

    /**
     * Determines the weight of this subreport
     * @return int weight
     */
    static public function get_weight($courseid) {
        global $CFG;
        
        $defaultweight = progress_report_generator::DEFAULT_SUBREPORT_WEIGHT;
        $classweight = self::SETTING_WEIGHT;
        $weightless = progress_report_generator::WEIGHTLESS;
        
        $defaultvalue = isset($CFG->$classweight)? $CFG->$classweight : $weightless;
        return grade_get_setting($courseid, $classweight, $defaultvalue);
    }

    /**
     * Retrieve a report setting from CFG, course settings or return default value
     * @return int setting value
     */
    static public function get_report_setting($courseid, $settingname, $settingdefault) {
        global $CFG;

        $defaultvalue = isset($CFG->$settingname)? $CFG->$settingname : $settingdefault;
        return grade_get_setting($courseid, $settingname, $defaultvalue);
    }

    /**
     * Returns an array of parameters that this subreport consumes
     * @param $weight the weight of the report is used to test if it is not at the default value
     * @return array parameters consumed by subreport
     */
    static public function get_params($weight) {
        $params = array();
        return $params;
    }

    /**
     * returns the availability of this subreport
     * @param int $courseid
     * @return bool
     */
    static public function available($courseid) {
        global $CFG;
        //rename setting because scope operator throws syntax error when used to get variable object property
        $classenabled = self::SETTING_ENABLED;
        //if the subplugin has not been installed, allow this to be false
        $enabled = isset($CFG->$classenabled)? $CFG->$classenabled : false;
        return grade_get_setting($courseid, $classenabled, $enabled); 
    }
}

