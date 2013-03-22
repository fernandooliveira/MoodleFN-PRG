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
 * Class definition of subplugin
 *
 * @package    gradebook
 * @subpackage progress
 * @copyright  2011 MoodleFN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir.'/messagelib.php');
require_once($CFG->libdir.'/accesslib.php');
require_once($CFG->libdir.'/moodlelib.php');

/**
 * Email Formatter
 * @package gradebook
 * @subpackage progress
 */
class progress_report_format_email{
    /**
     * consumed by get_string
     * @const string HEADING
     */  
    const HEADING                               = 'emailreportheading';
    
    /**
     * consumed by get_string
     * @const string SHORT_NAME
     */  
    const SHORT_NAME                            = 'email';

    /**
     * consumed by get_string
     * @const string CONFIGURATION_FORM_HEADING
     */  
    const CONFIGURATION_FORM_HEADING            = 'emailconfigformheading';

    /**
     * consumed by get_string
     * @const string EMAIL_SUBJECT
     */  
    const EMAIL_SUBJECT                         = 'emailsubjectline';    

    /**
     * consumed by get_string
     * @const string STATUS_NOT_SENT
     */  
    const STATUS_NOT_SENT                       = 'emailstatusnotsent';    
    
    /**
     * consumed by get_string
     * @const string STATUS_SENT
     */  
    const STATUS_SENT                           = 'emailstatussent';    
    
    /**
     * consumed by get_string
     * @const string STATUS_HEADING
     */  
    const STATUS_HEADING                        = 'emailstatusheading';    
    
    /**
     * consumed by get_string
     * @const string RECIPIENTS_USER
     */  
    const RECIPIENTS_USER                       = 'emailrecipientsuser';

    /**
     * consumed by get_string
     * @const string RECIPIENTS_MENTOR
     */  
    const RECIPIENTS_MENTOR                     = 'emailrecipientsmentor';

    /**
     * consumed by get_string
     * @const string RECIPIENTS_TEACHER
     */  
    const RECIPIENTS_TEACHER                    = 'emailrecipientsteacher';

    /**
     * consumed by get_string
     * @const string FROM_LEGEND
     */  
    const FROM_LEGEND                           = 'emailfromlegend';

    /**
     * consumed by get_string
     * @const string FROM_LABEL
     */  
    const FROM_LABEL                            = 'emailfromlabel';

    /**
     * consumed by get_string
     * @const string ADDITIONAL_MESSAGE_LEGEND
     */  
    const ADDITIONAL_MESSAGE_LEGEND             = 'emailadditionalmessagelegend';

    /**
     * consumed by get_string
     * @const string ADDITIONAL_MESSAGE_TITLE
     */  
    const ADDITIONAL_MESSAGE_TITLE              = 'emailadditionalmessagetitle';
    
    /**
     * consumed by get_string
     * @const string EMAIL_SUBMIT_BUTTON
     */  
    const EMAIL_SUBMIT_BUTTON                   = 'emailsubmitbutton';
    
    /**
     * consumed by get_string
     * @const string NAME_CODE_STRING
     */  
    const NAME_CODE_STRING = 'email';
        
    /**
     * consumed by grade_get_setting
     * @const string SETTING_ENABLED
     */  
    const SETTING_ENABLED                = 'report_grade_progress_email_enabled';
    
    /**
     * renderer object used by formatter to create formatted outputs
     * override renderer in your theme to alter formatter outputs
     * @var object $renderer
     */  
    public $renderer;
    
    /**
     * associative array of parameters used to build url query strings
     * @var array $params
     */  
    public $params;

    /**
     * array of recipients
     * @var array $params
     */  
    public $usersto;
    
    /**
     * email body as plain text
     * @var string $fullmessage
     */  
    public $fullmessage;

    /**
     * email body as HTML
     * @var string $fullmessagehtml
     */  
    public $fullmessagehtml;    
    
    
    /**
     * Constructor. Initializes the progress report generator values for courseid, course, context, selected user id, and format
     * @param array $params an associative array of parameters used to build url query strings
     * @return void
     */
    public function __construct($params) {
        global $PAGE;
        
        $this->params = $params;
        
    }

    /**
     * Builds string representation of report html and plaintext
     * @param array $contents data representation of report content
     * @return true
     */    
    public function format_content($contents) {
        global $PAGE;
        
        $this->renderer = $PAGE->get_renderer('gradereport_progress', 'emailhtml');
        $this->fullmessagehtml = "";
        foreach ($contents as $content) {
          $this->fullmessagehtml .= $this->format_content_recursive($content);
        }
        
        $this->renderer = $PAGE->get_renderer('gradereport_progress', 'emailplain');
        $this->fullmessage = "";
        foreach ($contents as $content) {
          $this->fullmessage .= $this->format_content_recursive($content);
        }
        return true;
    }

    /**
     * Builds string representation of report html
     * @param array $contents data representation of report content
     * @return string
     */
    protected function format_content_recursive($content) {
        //no changes needed?
        $classname = 'progress_report_generator';
        $formatted = "";
        
        switch ($content['type']) {
            case $classname::NODE_AUTONOMOUS :
                $formatted .= $this->renderer->write_node_self_closed($content['html'], $content['attributes']);
                break;
                
            case $classname::NODE_CONTAINER :
                if ($content['html'] == $classname::HTML_FORM) {
                    //do not print user interfaces
                    break;
                }
                $formatted .= $this->renderer->write_open_node($content['html'], $content['attributes']);
                if (isset($content['children'])) {
                    foreach ($content['children'] as $child) {
                        $formatted .= $this->format_content_recursive($child);
                    }
                }
                $formatted .= $this->renderer->write_close_node($content['html']);
                break;
                
            case $classname::NODE_TEXT :
                $formatted .= $content['text'];
                break;
        }
        
        return $formatted;
    }
    
    /**
     * Outputs report content
     * @param string $formatted string representation of report content
     * @param array $user data representation of user subject of report
     * @return string
     */
    public function render_content($formatted, $user) {
        global $USER;
        
        //populate recipients
        $recipients = array();
        $users = self::get_users_to($this->params['id'], $this->params['userid']);

        //check selected user to recipients
        $uidstring = 'tou' . $users['user']->id;
        if ($domail = optional_param($uidstring, 0 , PARAM_BOOL)) {
            //ensure each recipient is mailed only once
            $recipients[$users['user']->id] = $users['user'];
        }
        
        //add selected mentors to recipients
        if ($users['mentors']) {
            foreach ($users['mentors'] as $user) {
                $uidstring = 'tom' . $user->id;
                if ($checked = optional_param($uidstring, 0 , PARAM_BOOL)) {
                    //ensure each recipient is mailed only once
                    $recipients[$user->id] = $user;
                }
            }
        }
        
        //add selected teachers to recipients
        if ($users['teachers']) {
            foreach ($users['teachers'] as $user) {
                $uidstring = 'tot' . $user->id;
                if ($checked = optional_param($uidstring, 0 , PARAM_BOOL)) {
                    //ensure each recipient is mailed only once
                    $recipients[$user->id] = $user;
                }
            }
        }
        
        //get from
        $fromstring = self::SHORT_NAME . 'from';
        if ($checked = optional_param($fromstring, 0 , PARAM_BOOL)) {
            $contact = $USER;
        } else {
            $contact = get_admin();
        }
    
        //prepend addional message to html and plaintext
        $messageid = self::SHORT_NAME . 'message';
        
        $additionalhtml = optional_param($messageid, '', PARAM_CLEANHTML);        
        $this->fullmessagehtml = $additionalhtml . '<br/>' . $this->fullmessagehtml;
        
        $additionaltext = optional_param($messageid, '', PARAM_ALPHANUM);
        $this->fullmessage = $additionaltext. '\n' . $this->fullmessage;
        
        //set subject
        $subject = get_string(self::EMAIL_SUBJECT, 'gradereport_progress');
            
        //iterate recipients and email messages
        //collect result of message send
        $sent = array();
        foreach ($recipients as $id => $recipient) {
            if (email_to_user($recipient, $contact, $subject, $this->fullmessage, $this->fullmessagehtml)) {
                $sent[$id] = true;
            } else {
                $sent[$id] = false;
            }
        }
                
        $status = $this->get_content_status($sent, $recipients);
        
        //instantiating formatter to format assembled content and render result.
        $formatter = new progress_report_format_base($this->params);
        $contents = array();
        $contents[] = $status;
        $formattedcontent = $formatter->format_content($contents);
        
        $formatter->render_content($formattedcontent, $this->params['userid']);
            
    }


    
    /**
     * returns whether or not the format is considered configured based on supplied parameters
     * @param array $params
     * @return bool
     */ 
    static public function requires_configuration($params) {
        //check if at least course and userid are configured
        if (!isset($params['id']) or !isset($params['userid'])) {
            return true;
        }
        //check if usersto fields exist
        $users = self::get_users_to($params['id'], $params['userid']);

        //check if at least one recipient
        if ($users) {
            $atleastone = false;
            //check if user is checked and valid
            if($param = optional_param('tou'.$users['user']->id, false, PARAM_BOOL)) {
                $atleastone = true;
            }
            
            if ($users['mentors'] and !$atleastone) {
                foreach ($users['mentors'] as $user) {
                    if($param = optional_param('tom'.$user->id, false, PARAM_BOOL)) {
                        $atleastone = true;
                        break;
                    }
                }    
            }
            
            if ($users['teachers'] and !$atleastone) {
                foreach ($users['teachers'] as $user) {
                    if($param = optional_param('tot'.$user->id, false, PARAM_BOOL)) {
                        $atleastone = true;
                        break;
                    }
                }    
            }
            return !$atleastone;
        } else {
            //there are no users, the form should be unconfigurable if the submission is legitimate
            return true;
        }
        
    }    

    
    /**
     * Array representation of content tracking status of emails sent.
     * @param array $sent tracking whether each recipient was emailed or not
     * @param array $recipients user objects for email recipients
     * @return string
     */
    public function get_content_status($sent, $recipients) {
        $prg = 'progress_report_generator';
        
        //box
        $item = $prg::build_content_container_node($prg::HTML_DIV, array('id' => 'progress-email-status'));
        
        //heading                           
        $item['children'][0] = $prg::build_content_container_node($prg::HTML_HEADING_3, array());
        $text = get_string(self::STATUS_HEADING, 'gradereport_progress');
        $item['children'][0]['children'][0] = $prg::build_content_text_node($text);

        $stringsent    = get_string(self::STATUS_NOT_SENT, 'gradereport_progress');
        $stringnotsent = get_string(self::STATUS_SENT, 'gradereport_progress');
        //dl
        $item['children'][1] = $prg::build_content_container_node($prg::HTML_DEFINITION_LIST, array());
        $i = 0;
        
        foreach ($recipients as $id => $recipient) {
            //dt - username
            $text = fullname($recipient);
            $item['children'][1]['children'][$i] = $prg::build_content_container_node($prg::HTML_DEFINITION_TERM, array());
            $item['children'][1]['children'][$i]['children'][0] = $prg::build_content_text_node($text);    
            $i++;
            //dd - sent status
            if ($sent[$id]) {
                $text = $stringsent;
            } else {
                $text = $stringnotsent;
            }
            $item['children'][1]['children'][$i] = $prg::build_content_container_node($prg::HTML_DEFINITION_DEFINITION, array());
            $item['children'][1]['children'][$i]['children'][0] = $prg::build_content_text_node($text);
            $i++;
        }

        return $item;
    }
    
    /**
     * returns a form that can be used to configure the format.
     * @param array $params
     * @return array representation of content
     */
    static public function get_content_configuration_form($params) {
        
        $prg = 'progress_report_generator';
        
        //box
        $item = $prg::build_content_container_node($prg::HTML_DIV, array('id' => 'progress-email-ui'));
        
        //heading                           
        $item['children'][0] = $prg::build_content_container_node($prg::HTML_HEADING_3);
        $text = get_string(self::CONFIGURATION_FORM_HEADING, 'gradereport_progress');
        $item['children'][0]['children'][0] = $prg::build_content_text_node($text);
        
        ///form start
        $url = new moodle_url('/grade/report/progress/index.php', $params);
        $item['children'][1] = $prg::build_content_container_node($prg::HTML_FORM, array('id' => 'progress-report-form-email', 'action' => $url, 'method' => 'post'));
        
        //get $users to make checkboxes for
        $users = self::get_users_to($params['id'], $params['userid']);
        
        //fieldset - recipients - user
        $item['children'][1]['children'][0] = $prg::build_content_container_node($prg::HTML_FIELDSET);
        $i = 0;
        
        //legend
        $item['children'][1]['children'][0]['children'][$i] =  $prg::build_content_container_node($prg::HTML_LEGEND);
        $text = get_string(self::RECIPIENTS_USER, 'gradereport_progress');
        $item['children'][1]['children'][0]['children'][$i]['children'][0] = $prg::build_content_text_node($text);    
        $i++;
        
        //checkbox - user
        $uidstring = 'tou' . $users['user']->id;
        $attributes = array('id' => $uidstring, 
                            'type' => 'checkbox', 
                            'name' => $uidstring, 
                            'value' => '1');
        if ($checked = optional_param($uidstring, 0 , PARAM_BOOL)) {
            $attributes['checked'] = 'checked';
        }
        
        $item['children'][1]['children'][0]['children'][$i] = $prg::build_content_autonomous_node($prg::HTML_INPUT, $attributes); 
        
        $i++;
        //label for checkbox - user
        $text = fullname($users['user']);        
        $attributes = array('for' => 'tou' . $uidstring);
        $item['children'][1]['children'][0]['children'][$i] = $prg::build_content_container_node($prg::HTML_LABEL, $attributes);
        $item['children'][1]['children'][0]['children'][$i]['children'][0] = $prg::build_content_text_node($text);    
        
        //fieldset - mentors
        $item['children'][1]['children'][1] = $prg::build_content_container_node($prg::HTML_FIELDSET);
        $i = 0;
        
        //legend
        $item['children'][1]['children'][1]['children'][$i] =  $prg::build_content_container_node($prg::HTML_LEGEND);
        $text = get_string(self::RECIPIENTS_MENTOR, 'gradereport_progress');
        $item['children'][1]['children'][1]['children'][$i]['children'][0] = $prg::build_content_text_node($text);    
        $i++;
        
        //iterate mentors
        if ($users['mentors']) {
            //ul
            $item['children'][1]['children'][1]['children'][$i] =  $prg::build_content_container_node($prg::HTML_LIST_UNORDERED);
            $j = 0;
            foreach ($users['mentors'] as $user) {
                //li
                $item['children'][1]['children'][1]['children'][$i]['children'][$j] =  $prg::build_content_container_node($prg::HTML_LIST_ITEM);
                
                //checkbox
                $uidstring = 'tom' . $user->id;
                $attributes = array('id' => $uidstring, 
                                    'type' => 'checkbox', 
                                    'name' => $uidstring, 
                                    'value' => '1');
                if ($checked = optional_param($uidstring, 0 , PARAM_BOOL)) {
                    $attributes['checked'] = 'checked';
                }
                $item['children'][1]['children'][1]['children'][$i]['children'][$j]['children'][0] = $prg::build_content_autonomous_node($prg::HTML_INTPUT, $attributes);
                
                //label
                $text = fullname($user);        
                $attributes = array('for' => $uidstring);
                $item['children'][1]['children'][0]['children'][$i]['children'][$j]['children'][1] = $prg::build_content_container_node($prg::HTML_LABEL, $attributes);
                $item['children'][1]['children'][0]['children'][$i]['children'][$j]['children'][1]['children'][0] = $prg::build_content_text_node($text);                    
                
                $j++;
            }
            $i++;
        }

        //fieldset - teachers
        $item['children'][1]['children'][2] = $prg::build_content_container_node($prg::HTML_FIELDSET);
        $i = 0;
        
        //legend
        $item['children'][1]['children'][2]['children'][$i] =  $prg::build_content_container_node($prg::HTML_LEGEND);
        $text = get_string(self::RECIPIENTS_TEACHER, 'gradereport_progress');
        $item['children'][1]['children'][2]['children'][$i]['children'][0] = $prg::build_content_text_node($text);    
        $i++;
        
        //iterate teachers
        if ($users['teachers']) {
            //ul
            $item['children'][1]['children'][2]['children'][$i] =  $prg::build_content_container_node($prg::HTML_LIST_UNORDERED);
            $j = 0;
            foreach ($users['teachers'] as $user) {
                //li
                $item['children'][1]['children'][2]['children'][$i]['children'][$j] =  $prg::build_content_container_node($prg::HTML_LIST_ITEM);
                
                //checkbox
                $uidstring = 'tot' . $user->id;
                $attributes = array('id' => $uidstring, 
                                    'type' => 'checkbox', 
                                    'name' => $uidstring, 
                                    'value' => '1');
                if ($checked = optional_param($uidstring, 0 , PARAM_BOOL)) {
                    $attributes['checked'] = 'checked';
                }                                    
                $item['children'][1]['children'][2]['children'][$i]['children'][$j]['children'][0] = $prg::build_content_autonomous_node($prg::HTML_INTPUT, $attributes);
                
                //label
                $text = fullname($user);        
                $attributes = array('for' => $uidstring);
                $item['children'][1]['children'][2]['children'][$i]['children'][$j]['children'][1] = $prg::build_content_container_node($prg::HTML_LABEL, $attributes);
                $item['children'][1]['children'][2]['children'][$i]['children'][$j]['children'][1]['children'][0] = $prg::build_content_text_node($text);                    
                
                $j++;
            }
            $i++;        
        }
        
        //fieldset - from choice
        $item['children'][1]['children'][3] = $prg::build_content_container_node($prg::HTML_FIELDSET);
        
        //legend
        $item['children'][1]['children'][3]['children'][0] =  $prg::build_content_container_node($prg::HTML_LEGEND);
        $text = get_string(self::FROM_LEGEND, 'gradereport_progress');
        $item['children'][1]['children'][3]['children'][0]['children'][0] = $prg::build_content_text_node($text);    
        
        //checkbox - from
        $fromstring = self::SHORT_NAME . 'from';
        $attributes = array('id' => $fromstring, 
                            'type' => 'checkbox', 
                            'name' => $fromstring, 
                            'value' => '1');
        if ($checked = optional_param($fromstring, 0 , PARAM_BOOL)) {
            $attributes['checked'] = 'checked';
        }
        
        $item['children'][1]['children'][3]['children'][1] = $prg::build_content_autonomous_node($prg::HTML_INPUT, $attributes); 
        
        //label for checkbox - from
        $text = get_string(self::FROM_LABEL, 'gradereport_progress');      
        $attributes = array('for' => $fromstring);
        $item['children'][1]['children'][3]['children'][2] = $prg::build_content_container_node($prg::HTML_LABEL, $attributes);
        $item['children'][1]['children'][3]['children'][2]['children'][0] = $prg::build_content_text_node($text);          
        
        //fieldset - additional message
        $item['children'][1]['children'][4] = $prg::build_content_container_node($prg::HTML_FIELDSET);
        
        //legend
        $item['children'][1]['children'][4]['children'][0] =  $prg::build_content_container_node($prg::HTML_LEGEND);
        $text = get_string(self::ADDITIONAL_MESSAGE_LEGEND, 'gradereport_progress');
        $item['children'][1]['children'][4]['children'][0]['children'][0] = $prg::build_content_text_node($text);    
        
        //textarea
        $messageid = self::SHORT_NAME . 'message';
        $title = get_string(self::ADDITIONAL_MESSAGE_TITLE, 'gradereport_progress');
        $value = optional_param($messageid, '' , PARAM_CLEANHTML);
        
        $attributes = array('id' => $messageid,
                            'name' => $messageid, 
                            'title' => $title);
        $item['children'][1]['children'][4]['children'][1] = $prg::build_content_container_node($prg::HTML_TEXT_BOX, $attributes);
        $item['children'][1]['children'][4]['children'][1]['children'][0] = $prg::build_content_text_node($value);  
        
        ///submit button
        $item['children'][1]['children'][5] = $prg::build_content_autonomous_node($prg::HTML_INPUT, 
                                                                      array('id'    => 'progress-select-submit-email',
                                                                            'type'  => 'submit', 
                                                                            'name'  => 'report-select-submit-email',
                                                                            'value' => get_string(self::EMAIL_SUBMIT_BUTTON, 'gradereport_progress')
                                                                            ));
        return $item;

    }
    
    /**
     * returns a form that can be used to configure the format.
     * @param int $courseid 
     * @param int $userid      
     * @return array representation of users or false if empty
     */    
    public static function get_users_to($courseid, $userid) {
        global $CFG, $DB;
        
        //get course
        if (!$course = $DB->get_record('course', array('id' => $courseid))) {
            print_error('nocourseid');
        }
        
        //get contexts where permission is applicable
        $coursecontext   = get_context_instance(CONTEXT_COURSE, $courseid);
        
        $users = array();
        
        //get user's record
        $usersql  = "SELECT u.* FROM {user} u WHERE u.id = ?";
        $params[] = $userid;
        $result = $DB->get_recordset_sql($usersql, $params);
        //could be a problem on one record return
        foreach ($result as $user) {
            $users['user'] = $user;
            $users['ids'][$user->id] = $user->id;
        }
        
        $users['mentors'] = array();
        //get user's mentors.
        $usercontext   = get_context_instance(CONTEXT_USER, $userid);
        $params[] = $usercontext->id;
        $mentorsql = "SELECT DISTINCT u.* ". 
                     "FROM {user} u JOIN {role_assignments} ra ON u.id = ra.userid ".
                     "WHERE ra.contextid = ? ". 
                     "ORDER BY u.firstname ASC";
        
        if ($results = $DB->get_recordset_sql($mentorsql, $params)) {
            foreach ($results as $mentor) {
                $users['mentors'][] = $mentor;
                $users['ids'][$mentor->id] = $mentor->id;
            }
        } else {
            $users['mentors'] = false;
        }
        
        //get user's teachers
        $users['teachers'] = array();
        require_once($CFG->libdir.'/accesslib.php');
        if ($course->groupmode == SEPARATEGROUPS) {
            //get user's groups
            require_once($CFG->libdir.'grouplib.php');
            if ($groups = groups_get_all_groups($courseid, $userid)) {
                $groupids = array();
                foreach ($groups as $group) {
                    $groupids[] = $group->id;
                }
            } else {
                $groupsids = '';
            }            
            
            //teachers that can see all groups
            $context    = $coursecontext;
            $capability = array('gradereport/progress:viewall', 'moodle/site:accessallgroups');
            $fields     = 'u.*';
            $sort       = 'u.firstname ASC';
            $limitfrom  = '';
            $limitnum   = '';
            $groups     = '';
            if ($teachersall = get_users_by_capability($context, $capability, $fields, $sort, $limitfrom, $limitnum, $groups)) {
                foreach ($teachersall as $teacherall) {
                    if (!in_array($teacherall->id, $users['ids'])) {
                    //avoid adding users twice
                        $users['teachers'][] = $teacherall;
                        $users['ids'][$teacherall->id] = $teacherall->id;
                    }
                }
            } 
            
            //teachers restricted to their group
            $context    = $coursecontext;
            $capability = array('gradereport/progress:viewall');
            $fields     = 'u.*';
            $sort       = 'u.firstname ASC';
            $limitfrom  = '';
            $limitnum   = '';
            $groups     = $groupsids;
            if ($teachersgroup = get_users_by_capability($context, $capability, $fields, $sort, $limitfrom, $limitnum, $groups)) {
                foreach ($teachersgroup as $teachergroup) {
                    //avoid adding users twice
                    if (!in_array($teachergroup->id, $users['ids'])) {
                        $users['teachers'][] = $teachergroup;
                        $users['ids'][$teachergroup->id] = $teachergroup->id;
                    }
                }
            }
            
        } else {
            $context    = $coursecontext;
            $capability = array('gradereport/progress:viewall');
            $fields     = 'u.*';
            $sort       = 'u.firstname ASC';
            $limitfrom  = '';
            $limitnum   = '';
            $groups     = '';
            $teachers = get_users_by_capability($context, $capability, $fields, $sort, $limitfrom, $limitnum, $groups);
        }
        
        if (empty($users['teachers'])) {
            $users['teachers'] = false;
        }
        
        if (!empty($users['ids'])) {
            return $users;
        } else {
            return false;
        }
    }
    
        
    /**
     * Gets the name string of this subreport for use in the progress report generator ui.
     * @return string 
     */
    static public function get_name_string() {
        return get_string(self::NAME_CODE_STRING, 'gradereport_progress');
    }
   
    
    /**
     * Returns an array of parameters that this format consumes
     * @return array parameters consumed by format
     */
    static public function get_params() {
        $params = array();
        //all params are POST, they do not need to be part of urls
        return $params;
    }
       
    /**
     * returns the availability of this format
     * @param int $courseid
     * @return bool
     */ 
    static public function available($courseid) {
        
        global $CFG, $DB, $USER;
        
        //rename setting because scope operator throws syntax error when used to get variable object property
        $classenabled = self::SETTING_ENABLED;
        //if the subplugin has not been installed, allow this to be false
        $enabled = isset($CFG->$classenabled) ? $CFG->$classenabled : false;
        $available = grade_get_setting($courseid, $classenabled, $enabled);
        
        if (!$available) {
            return false;
        }
        
        //check if user is student
        $coursecontext = get_context_instance(CONTEXT_COURSE, $courseid);
        if (has_capability('gradereport/progress:viewall', $coursecontext)) {
            $cansend = true;
        } else {
            $studentscansend = 'report_grade_progress_email_enabled_students';
            $cfgsend = isset($CFG->$studentscansend) ? $CFG->$studentscansend : false;
            $cansend = grade_get_setting($courseid, $classenabled, $cfgsend);
        }
        
        return $cansend;
        
    }
}