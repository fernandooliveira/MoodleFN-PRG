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
 * File containing class definitions for subreports.
 *
 * @package    gradebook
 * @subpackage progress
 * @copyright  2011 MoodleFN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . '/datalib.php');
require_once($CFG->dirroot . '/grade/report/lib.php');
require_once($CFG->dirroot . '/grade/lib.php');
require_once($CFG->dirroot . '/grade/report/lib.php');

defined('MOODLE_INTERNAL') || die;

/**
 * Summary subreport
 * @package gradebook
 * @subpackage progress
 */
class progress_report_subreport_category{
    /**
     * consumed by get_string
     * @const string NAME_CODE_STRING
     */
    const NAME_CODE_STRING                      = 'category';
    /**
     * consumed by get_string
     * @const string TITLE_STRING
     */
    const TITLE_STRING                          = 'categorytitle';

    /**
     * consumed by grade_get_setting
     * @const string SETTING_ENABLED
     */
    const SETTING_ENABLED                       = 'grade_report_progress_cat_enabled';
    /**
     * consumed by grade_get_setting
     * @const string SETTING_WEIGHT
     */
    const SETTING_WEIGHT                        = 'grade_report_progress_cat_weight';
    /**
     * consumed by grade_get_setting
     * @const string SETTING_WEIGHT
     */
    const SETTING_DEPTH                         = 'grade_report_progress_cat_depth';

    /**
     * short name for subreport used for tagging URL/POST/GET parameters
     * @const string SHORT_NAME
     */
    const SHORT_NAME                             = 'cat';

    /**
     * consumed by get_string
     * @const string TOTAL_GRADE_ITEMS
     */
    const TOTAL_GRADE_ITEMS                      = 'categorytotalgradeitems';

    /**
     * consumed by get_string
     * @const string TOTAL_COMPLETE
     */
    const TOTAL_COMPLETE                         = 'categorytotalcomplete';

    /**
     * consumed by get_string
     * @const string OVERALL_MOODLE
     */
    const OVERALL_MOODLE                         = 'categoryoverallmoodle';

    /**
     * consumed by get_string
     * @const string OVERALL_SUBMITTED
     */
    const OVERALL_SUBMITTED                      = 'categoryoverallsubmitted';

    /**
     * consumed by get_string
     * @const string OVERALL_AVAILABLE
     */
    const OVERALL_AVAILABLE                      = 'categoryoverallavailable';

    /**
     * consumed by get_string
     * @const string NOT_AUTHORIZED
     */
    const NOT_AUTHORIZED                         = 'categorynotauthorized';

    /**
     * consumed by get_string
     * @const string NOT_ENROLLED
     */
    const NOT_ENROLLED                           = 'categorynotenrolled';

    /**
     * id of the user who is the subject of the subreport
     * @var int $userid
     */
    protected $userid;

    /**
     * id of the coure from which the category report has been called
     * @var int $courseid
     */
    protected $courseid;

    /**
     * course object from which the category report has been called
     * @var  $course
     */
    protected $course;

    /**
     * Moodle context object from which the category report has been called
     * @var object $context
     */
    protected $context;

    /**
     * whether or not $USER can view hidden content
     * @var bool $canviewhidden
     */
    protected $canviewhidden;

    /**
     * maximum category depth.  defaults to 0, meaning no sibling categories are drilled into
     * @var int $maxdepth
     */
    protected $maxdepth = 0;

    /**
     * array representation of category structure
     * @var array $categories
     */
    protected $categories = array();

    /**
     * id of the course being processed (course id iterator)
     * @var in $courseiditerator
     */
    protected $courseiditerator = array();

    /**
     * array representation of course grades and items
     * @var array $courses
     */
    protected $courses = array();

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
        //$this->params = $params;

        $this->canviewhidden = has_capability('moodle/grade:viewhidden', get_context_instance(CONTEXT_COURSE, $this->courseid));
        $this->maxdepth = grade_get_setting($courseid, self::SETTING_DEPTH, false, false);
        $this->initialize_categories();
        $this->initialize_courses();
        $this->initialize_overall_grades();
    }

    /**
     * Setup for recursive building of $this->categories
     */
    protected function initialize_categories() {
        $category = new stdClass();
        $category->id = $this->course->category;
        $this->initialize_categories_recursive(0, $category);
    }

    /**
     * Builds $this->categories elements
     * @param int $depth current depth of categories
     * @param object $category current category
     */
    protected function initialize_categories_recursive($depth, $category) {
        $this->categories[] = $category->id;
        if ($depth < $this->maxdepth) {
            $children = get_categories($category->id);
            foreach ($children as $child) {
                initialize_categories_recursive($depth +1, $child->id);
            }
        }
    }

    /**
     * Builds $this->courses
     */
    protected function initialize_courses(){
        //get all courses $user is enrolled in
        global $USER;

        $classname = 'progress_report_generator';

        $usercoursesraw = enrol_get_users_courses($this->userid, false, null,'c.sortorder ASC');
        $usercourses    = array();
        foreach ($usercoursesraw as $courseraw) {
            $usercourses[] = $courseraw->id;
        }

        //get all courses in category(ies)
        foreach ($this->categories as $category) {
            $rs = get_courses($category, "c.sortorder ASC", "c.id, c.fullname");
            foreach ($rs as $course) {
                $this->courses[$course->id] = array();
                $this->courses[$course->id]['string'] = $course->fullname;
                $this->courses[$course->id]['id']     = $course->id;
                if (in_array($course->id, $usercourses)) {
                    $this->courses[$course->id]['enrolled'] = true;
                } else {
                    $this->courses[$course->id]['enrolled'] = false;
                }
                //check access - expensive
                $this->courses[$course->id]['access'] = $classname::validate_selected_user($course->id, $USER->id, $this->userid);

                if ($this->courses[$course->id]['access'] && $this->courses[$course->id]['enrolled']) {
                    //grade tree - no fillers, category grade item is not last child, no outcomes
                    $this->courses[$course->id]['gtree'] = new grade_tree($course->id, false, false, null, false);;
                    $this->courses[$course->id]['activities'] = array();
                    $this->courses[$course->id]['totalcomplete'] = 0;
                    $this->courses[$course->id]['totalactivities'] = 0;

                    //initialize activities for this course
                    //$this->courseiditerator = $course->id;
                    $this->initialize_activities_recursive($this->courses[$course->id]['gtree']->top_element, $course->id);
                }
            }
        }
        //go through each course now that activities are done and initialize overall grades.
        //see the scratch pages for tweaks needed.
    }

    /**
     * Uses grade items and grade grades to gather data on gradeable activities completed by a user
     * @param object $element
     */
    protected function initialize_activities_recursive(&$element, $courseid){
        global $DB, $CFG;

        $type = $element['type'];
        $grade_object = $element['object'];
        $eid = $grade_object->id;

        if ($type == 'item') {//interested in this
            $this->courses[$courseid]['activities'][$eid] = array();
            $this->courses[$courseid]['activities'][$eid]['item'] =  $grade_object;
            $this->courses[$courseid]['activities'][$eid]['id']   =  $eid;
            //grade items are examined
            $this->courses[$courseid]['totalactivities']++;

            if ($grade_grade = grade_grade::fetch(array('itemid'=>$grade_object->id,'userid'=>$this->userid))) {
                //check for pass or fail
                if ($grade_grade->is_passed($grade_object)) {
                    $this->courses[$courseid]['totalcomplete']++;
                }
                $this->courses[$courseid]['activities'][$eid]['grade'] =  $grade_grade;
            } else {
                $this->courses[$courseid]['activities'][$eid]['grade'] =  false;
            }
        }

        if (isset($element['children'])) {
            foreach ($element['children'] as $key=>$child) {
                $this->initialize_activities_recursive($element['children'][$key], $courseid);
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
        $errors  = array();

        foreach ($this->courses as $course) {
            //get the id of the PRG dummy user
            if ($dummyid = $classname::get_dummy_user_id($course['id'])){
                $dummygrades = array();

                //dummy should now be a clone of the user for this course
                $course_item = grade_item::fetch_course_item($course['id']);
                
                $course_grade = new grade_grade(array('itemid'=>$course_item->id, 'userid'=>$this->userid));
                $grademoodle = $course_grade->finalgrade;

                //initialize the dummy user's grades to clone of $userid's grades
                foreach ($course['activities'] as $activity) {
                    //does dummy user have grade?
                    if ($dummygrade = grade_grade::fetch(array('itemid'=>$activity['id'], 'userid'=>$dummyid))) {
                        //does user being cloned have grade
                        if ($activity['grade']) {
                            //change the value of the dummy grade to the cloned user's grade
                            if($activity['item']->update_final_grade($dummyid, $activity['grade']->finalgrade, 'gradereport')) {
                                //store object for later use
                                $dummygrades[$activity['id']] = grade_grade::fetch(array('itemid'=>$activity['id'], 'userid'=>$dummyid));
                            }
                        } else {
                            //there is no user grade, so there should be no dummy grade
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
                //regrade the dummy and store the value
                if (!$courseerrors = grade_regrade_final_grades($course['id'], $dummyid, $course_item)) {
                    print_r($courseerrors);
                }
                //dummy should now be a clone of the user for this course
                $course_grade = new grade_grade(array('itemid'=>$course_item->id, 'userid'=>$dummyid));
                $gradesubmitted = $course_grade->finalgrade;

                //change dummygrade value to MAX
                foreach ($dummygrades as $dummygrade) {
                    if ($course['activities'][$dummygrade->itemid]['item']->update_final_grade($dummyid, $course['activities'][$dummygrade->itemid]['item']->grademax, 'gradereport')) {
                    }
                }
                
                //regrade the dummy user to get MAX based on submitted
                if (!$courseerrors = grade_regrade_final_grades($course['id'], $dummyid, $course_item)) {
                    print_r($courseerrors);
                }
                
                if (!$course_grade = grade_grade::fetch(array('itemid'=>$course_item->id, 'userid'=>$dummyid))) {
                    $course_grade = new grade_grade(array('itemid'=>$course_item->id, 'userid'=>$dummyid));
                }
                $gradesubmittedmax = $course_grade->finalgrade;

                //redo activities to same as cloned user plus assign minimum value to non-existant grade items
                foreach ($course['activities'] as $activity) {
                    //if the user being cloned has a grade
                    if ($activity['grade']) {
                        //add a grade if the activity is available... there should be a way of checking if the locked activity was ever unlocked
                        if ($activity['item']->update_final_grade($dummyid, $activity['grade']->finalgrade, 'gradereport')) {
                            $dummygrades[$activity['id']] = grade_grade::fetch(array('itemid'=>$activity['id'], 'userid'=>$dummyid));
                        }
                    } else {
                        //user has not attempted assignment, if the assignment can be attempted (is available) assign a minimum grade
                        if (!$activity['item']->hidden and !$activity['item']->locked) {
                            if ($activity['item']->update_final_grade($dummyid, $activity['item']->grademin, 'gradereport')) {
                                $dummygrades[$activity['id']] = grade_grade::fetch(array('itemid'=>$activity['id'], 'userid'=>$dummyid));
                            }
                        }
                    }
                }
                if (!$courseerrors = grade_regrade_final_grades($course['id'], $dummyid, $course_item)) {
                    print_r($courseerrors);
                }
                
                if (!$course_grade = grade_grade::fetch(array('itemid'=>$course_item->id, 'userid'=>$dummyid))) {
                    $course_grade = new grade_grade(array('itemid'=>$course_item->id, 'userid'=>$dummyid));
                }
                $gradeavailable = $course_grade->finalgrade;

                foreach ($dummygrades as $dummygrade) {
                    if ($course['activities'][$dummygrade->itemid]['item']->update_final_grade($dummyid, $course['activities'][$dummygrade->itemid]['item']->grademax, 'gradereport')) {
                    }
                }
                if (!$courseerrors = grade_regrade_final_grades($course['id'], $dummyid, $course_item)) {
                    print_r($courseerrors);
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
                $this->courses[$course['id']]['overallmoodle']    = number_format($grademoodle, 2);
                $this->courses[$course['id']]['overallavailable'] = ($gradeavailablemax == 0) ? 0 : number_format($gradeavailable / $gradeavailablemax * 100, 2);
                $this->courses[$course['id']]['overallsubmitted'] = ($gradesubmittedmax == 0) ? 0 : number_format($gradesubmitted / $gradesubmittedmax * 100, 2);
                $classname::delete_dummy_user($dummyid);
                $errors[$course['id']] = $courseerrors;

            } else {
                $errors[$course['id']] =  true;
            }
        }
        return $errors;
    }

    /**
     * Create an array representation of subreport based on activities
     * @return array content representation of subreport
     */
    public function get_subreport_content() {

        //preload strings instead of calling get_string from the loop.
        $strings = array();
        $strings['totalgradeitems']       = get_string(self::TOTAL_GRADE_ITEMS, 'gradereport_progress');
        $strings['totalcomplete']         = get_string(self::TOTAL_COMPLETE, 'gradereport_progress');
        $strings['overallmoodle']         = get_string(self::OVERALL_MOODLE, 'gradereport_progress');
        $strings['overallsubmitted']      = get_string(self::OVERALL_SUBMITTED, 'gradereport_progress');
        $strings['overallavailable']      = get_string(self::OVERALL_AVAILABLE, 'gradereport_progress');
        $strings['notauthorized']         = get_string(self::NOT_AUTHORIZED, 'gradereport_progress');
        $strings['notenrolled']           = get_string(self::NOT_ENROLLED, 'gradereport_progress');

        $prg = "progress_report_generator";

        //box
        $item = $prg::build_content_container_node($prg::HTML_DIV, array('id' => 'progress-subreport-' . self::SHORT_NAME));

        ///title
        $item['children'][0] = $prg::build_content_container_node($prg::HTML_HEADING_3);
        $text = get_string(self::TITLE_STRING, 'gradereport_progress');
        $item['children'][0]['children'][0] = $prg::build_content_text_node($text);

        //ul
        $item['children'][1] = $prg::build_content_container_node($prg::HTML_LIST_UNORDERED);
        $i = 0;
        foreach ($this->courses as $course) {
            $j = 0;
            //li
            $item['children'][1]['children'][$i] = $prg::build_content_container_node($prg::HTML_LIST_ITEM);

            //heading 4
            $item['children'][1]['children'][$i]['children'][$j] = $prg::build_content_container_node($prg::HTML_HEADING_4);
            $item['children'][1]['children'][$i]['children'][$j]['children'][0] = $prg::build_content_text_node($course['string']);
            $j++;
            
            if ($course['access']) {
                if ($course['enrolled']) {
                    //$userid is enrolled and $USER can see the userid in this context.
                    //dl
                    $item['children'][1]['children'][$i]['children'][$j] = $prg::build_content_container_node($prg::HTML_DEFINITION_LIST);
                    $k = 0;
                    //dt number of activities
                    $attributes = array();
                    $text = $strings['totalgradeitems'];
                    $item['children'][1]['children'][$i]['children'][$j]['children'][$k] = $prg::build_content_container_node($prg::HTML_DEFINITION_TERM, $attributes);
                    $item['children'][1]['children'][$i]['children'][$j]['children'][$k]['children'][0] = $prg::build_content_text_node($text);
                    $k++;
                    //dd
                    $text = $course['totalactivities'];
                    $item['children'][1]['children'][$i]['children'][$j]['children'][$k] = $prg::build_content_container_node($prg::HTML_DEFINITION_DEFINITION, $attributes);
                    $item['children'][1]['children'][$i]['children'][$j]['children'][$k]['children'][0] = $prg::build_content_text_node($text);
                    $k++;

                    //dt number of completed activities
                    $text = $strings['totalcomplete'];
                    $item['children'][1]['children'][$i]['children'][$j]['children'][$k] = $prg::build_content_container_node($prg::HTML_DEFINITION_TERM);
                    $item['children'][1]['children'][$i]['children'][$j]['children'][$k]['children'][0] = $prg::build_content_text_node($text);
                    $k++;
                    //dd
                    if ($course['totalactivities'] != 0) {
                        $percentage = $course['totalcomplete'] / $course['totalactivities'] * 100;
                        $percentage = '(' . (string)number_format($percentage,2) . " %)";
                    } else {
                        $percentage = '(0)';
                    }
                    $text = (string)$course['totalcomplete'] . ' ' . $percentage ;
                    $item['children'][1]['children'][$i]['children'][$j]['children'][$k] = $prg::build_content_container_node($prg::HTML_DEFINITION_DEFINITION, $attributes);
                    $item['children'][1]['children'][$i]['children'][$j]['children'][$k]['children'][0] = $prg::build_content_text_node($text);
                    $k++;

                    //dt overall moodle grade
                    //$attributes = ($section[''] > 0) ? array() : array('class' => 'highlight');
                    $text = $strings['overallmoodle'];
                    $item['children'][1]['children'][$i]['children'][$j]['children'][$k] = $prg::build_content_container_node($prg::HTML_DEFINITION_TERM, $attributes);
                    $item['children'][1]['children'][$i]['children'][$j]['children'][$k]['children'][0] = $prg::build_content_text_node($text);
                    $k++;
                    //dd
                    $text = $course['overallmoodle'] . '%';
                    $item['children'][1]['children'][$i]['children'][$j]['children'][$k] = $prg::build_content_container_node($prg::HTML_DEFINITION_DEFINITION, $attributes);
                    $item['children'][1]['children'][$i]['children'][$j]['children'][$k]['children'][0] = $prg::build_content_text_node($text);
                    $k++;

                    //dt overall submitted grade
                    //$attributes = ($section['notattempted'] > 0) ? array() : array('class' => 'highlight');
                    $text = $strings['overallsubmitted'];
                    $item['children'][1]['children'][$i]['children'][$j]['children'][$k] = $prg::build_content_container_node($prg::HTML_DEFINITION_TERM, $attributes);
                    $item['children'][1]['children'][$i]['children'][$j]['children'][$k]['children'][0] = $prg::build_content_text_node($text);
                    $k++;
                    //dd
                    if ($course['overallsubmitted'] < 50) {
                        $attributes = array('class' => 'incomplete');
                    }
                    $text = $course['overallsubmitted'] . '%';
                    $item['children'][1]['children'][$i]['children'][$j]['children'][$k] = $prg::build_content_container_node($prg::HTML_DEFINITION_DEFINITION, $attributes);
                    $item['children'][1]['children'][$i]['children'][$j]['children'][$k]['children'][0] = $prg::build_content_text_node($text);
                    $k++;
                    $attributes = array();

                    //dt overall available activities
                    //$attributes = array();
                    $text = $strings['overallavailable'];
                    $item['children'][1]['children'][$i]['children'][$j]['children'][$k] = $prg::build_content_container_node($prg::HTML_DEFINITION_TERM, $attributes);
                    $item['children'][1]['children'][$i]['children'][$j]['children'][$k]['children'][0] = $prg::build_content_text_node($text);
                    $k++;
                    //dd
                    if ($course['overallavailable'] < 50) {
                        $attributes = array('class' => 'incomplete');
                    }
                    $text = $course['overallavailable'] . '%';
                    $item['children'][1]['children'][$i]['children'][$j]['children'][$k] = $prg::build_content_container_node($prg::HTML_DEFINITION_DEFINITION, $attributes);
                    $item['children'][1]['children'][$i]['children'][$j]['children'][$k]['children'][0] = $prg::build_content_text_node($text);
                    $attributes = array();

                } else {
                    //$USER is authorized but $userid is not enrolled
                    $text = $strings['notenrolled'];
                    $item['children'][1]['children'][$i]['children'][1] = $prg::build_content_container_node($prg::HTML_PARAGRAPH);
                    $item['children'][1]['children'][$i]['children'][1]['children'][0] = $prg::build_content_text_node($text);
                }
            } else {
                //$USER is not authorized to see users in this course
                $text = $strings['notauthorized'];
                $item['children'][1]['children'][$i]['children'][1] = $prg::build_content_container_node($prg::HTML_PARAGRAPH);
                $item['children'][1]['children'][$i]['children'][1]['children'][0] = $prg::build_content_text_node($text);
            }
            $i++;

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
        //$default = grade_get_setting($courseid, self::SETTING_WEIGHT, progress_report_generator::DEFAULT_SUBREPORT_WEIGHT, false);
        global $CFG;
        $weightless = progress_report_generator::WEIGHTLESS;
        $classweight = self::SETTING_WEIGHT;
        $default = (isset($CFG->classweight) ? $CFG->classweight : $weightless);
        return optional_param(self::SHORT_NAME.'weight', $default, PARAM_INT);
    }

    /**
     * Returns an array of parameters that this subreport consumes
     * @param $weight the weight of the report is used to test if it is not at the default value
     * @return array parameters consumed by subreport
     */
    static public function get_params($weight) {
        $params = array();
        $params[self::SHORT_NAME.'weight'] = $weight;
        return $params;
    }

    /**
     * returns the availability of this subreport
     * @param int $courseid
     * @return bool
     */
    static public function available($courseid) {
        //setting default value. default to off.
        //return grade_get_setting($courseid, self::SETTING_ENABLED, 0);
        global $CFG;
        $classenabled = self::SETTING_ENABLED;
        return (isset($CFG->$classenabled)? $CFG->$classenabled : false);
    }
}

