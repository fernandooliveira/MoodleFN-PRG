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
 * Custom renderers for the progress grade report
 *
 * @package    gradebook
 * @subpackage progress
 * @copyright  2011 MoodleFN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Custom renderer for the progress grade report HTML formatter
 *
 * To get an instance of this use the following code:
 * $renderer = $PAGE->get_renderer('gradereport_progress');
 */
class gradereport_progress_base_renderer extends plugin_renderer_base {
    
    public function write_node_self_closed($tag, $attributes){
        return html_writer::empty_tag($tag, $attributes);
    }

    public function write_open_node($tag, $attributes){
        return html_writer::start_tag($tag, $attributes);
    }

    public function write_close_node($tag){
        return html_writer::end_tag($tag);
    }
}

/**
 * Custom renderer for html email strings
 *
 * To get an instance of this use the following code:
 * $renderer = $PAGE->get_renderer('gradereport_progress');
 */
class gradereport_progress_emailhtml_renderer extends plugin_renderer_base {
    
    public function write_node_self_closed($tag, $attributes){
        return html_writer::empty_tag($tag, $attributes);
    }

    public function write_open_node($tag, $attributes){
        return html_writer::start_tag($tag, $attributes);
    }

    public function write_close_node($tag){
        return html_writer::end_tag($tag);
    }
}

/**
 * Custom renderer for plaintext email strings
 *
 * To get an instance of this use the following code:
 * $renderer = $PAGE->get_renderer('gradereport_progress');
 */
class gradereport_progress_emailplain_renderer extends plugin_renderer_base {
    
    public function write_node_self_closed($tag, $attributes){
        //plain text does not allow for self closed elements
        return '';
    }
    
    public function write_open_node($tag, $attributes){
        switch($tag) {
            case 'legend' :
            case 'h3' :
            case 'h4' :
            case 'dt' :
            case 'dl' :
            case 'li' :
            case 'table' :
                return '\n';
                break;
            default :
                break;
        }
        return '';
    }
    
    public function write_close_node($tag){
        
        switch($tag) {
            case 'legend' :
            case 'h3' :
            case 'h4' :
            case 'dd' :
            case 'dl' :
            case 'li' :
            case 'table' :
            case 'p' :
            case 'tr' :
                return '\n';
                break;
            case 'td' :
            case 'th' :
                return ' | |';
                break;
            default :
                break;
        }
        return '';
    }
}

/**
 * Custom renderer for the progress grade report email formatter
 *
 * To get an instance of this use the following code:
 * $renderer = $PAGE->get_renderer('gradereport_progress_email');
 */
class gradereport_progress_email_renderer extends plugin_renderer_base {
//write html (possibly removing hyperlinks)
//will need html string in formatter AND a plain text string...
//having the recursive formatters check the previous sibling as a param, may help formatting.
}

/**
 * Custom renderer for the progress grade report pdf formatter
 *
 * To get an instance of this use the following code:
 * $renderer = $PAGE->get_renderer('gradereport_progress_pdf');
 */
class gradereport_progress_pdf_renderer extends plugin_renderer_base {
    const STYLE_H1                     = 'text-align:center';
    const STYLE_H2                     = 'text-align:center';
    const STYLE_H3                     = 'text-align:center';
    const STYLE_H4                     = 'text-align:left';    
    const STYLE_TABLE                  = 'text-align:left';
    const BORDER_TABLE                 = '1';
    const CELL_SPACING_TABLE           = '6';
    const CELL_PADDING_TABLE           = '4';
    const STYLE_TABLE_HEADER           = 'font-size:large';
    const STYLE_TABLE_CELL             = 'text-align:left';
    const STYLE_DEFINITION_LIST        = 'text-align:left';
    const STYLE_DEFINITION_TERM        = 'text-align:left';
    const STYLE_DEFINITION_DEFINITION  = 'text-align:left';
    const STYLE_HYPERLINK              = 'text-align:left';
    const STYLE_HIGHLIGHT              = 'text-align:left';
    const STYLE_SOMETHING              = '';

    public function scrub_attributes($attributes) {
        $scrubbed = array();
        
        foreach ($attributes as $key => $value) {
            switch($key) {
                case 'href':
                case 'alt' :
                case 'width' :
                case 'height' :
                case 'border' :
                case 'src' :
                    $scrubbed[$key] = $value;
                    break;
            }
        }        
        
        return $scrubbed;
    }
    
    //scrub class attribute
    public function write_node_self_closed($tag, $attributes){
        //get scrubbed attributes
        $scrubbed = $this->scrub_attributes($attributes);
        
        //return only tags appropriate to TCPDF
        switch($tag) {
            case 'img' :
                //$scrubbed['style'] = self::STYLE_SOMETHING;
                break;
        }    
        
        
        if (isset($scrubbed['style'])) {
            return html_writer::empty_tag($tag, $scrubbed);
            //return html_writer::empty_tag($tag, array());
        } else {
            return '';
        }
    }

    public function write_open_node($tag, $attributes){
        //get scrubbed attributes
        $scrubbed = $this->scrub_attributes($attributes);
        
        //check if highlighted
        if (isset($attributes['class'])){
            if ($attributes['class'] == 'highlight' || $attributes['class'] == 'incomplete') {
                $scrubbed['style'] = self::STYLE_HIGHLIGHT;
            }
        } else {
            //return only tags appropriate to TCPDF
            switch($tag) {
                case 'h1' :
                    $scrubbed['style'] = self::STYLE_H1;
                    break;
                case 'h2' :
                    $scrubbed['style'] = self::STYLE_H2;
                    break;
                case 'h3' :
                    $scrubbed['style'] = self::STYLE_H3;
                    break;
                case 'h4' :
                    $scrubbed['style'] = self::STYLE_H4;
                    break;
                case 'dl' :
                    $scrubbed['style'] = self::STYLE_DEFINITION_LIST;
                    break;
                case 'dt' :
                    $scrubbed['style'] = self::STYLE_DEFINITION_TERM;
                    break;
                case 'dd' :
                    $scrubbed['style'] = self::STYLE_DEFINITION_DEFINITION;
                    break;
                case 'table' :
                    $scrubbed['style'] = self::STYLE_TABLE;
                    $scrubbed['border']      = self::BORDER_TABLE;
                    $scrubbed['cellspacing'] = self::CELL_SPACING_TABLE;
                    $scrubbed['cellpadding'] = self::CELL_PADDING_TABLE;
                    break;
                case 'td' :
                    $scrubbed['style'] = self::STYLE_TABLE_CELL;
                    break;
                case 'tr' :
                    $scrubbed['style'] = self::STYLE_TABLE_HEADER;
                    break;
                case 'a' :
                    $scrubbed['style'] = self::STYLE_HYPERLINK;
                    break;
            }    
        }
        
        if (isset($scrubbed['style'])) {
            return html_writer::start_tag($tag, $scrubbed);
            //return html_writer::start_tag($tag, array());
        } else {
            return '';
        }
    }

    public function write_close_node($tag){
        switch($tag) {
            case 'table' :
            case 'h1' :
            case 'h2' :
            case 'h3' :
            case 'h4' :
            case 'dl' :
            case 'dt' :
            case 'dd' :
            case 'td' :
            case 'tr' :
            case 'a' :
                return html_writer::end_tag($tag);
                break;
            default :
                return '';
                break;
        }    
    }
}


/**
 * Custom renderer for the progress grade report print formatter
 *
 * To get an instance of this use the following code:
 * $renderer = $PAGE->get_renderer('gradereport_progress_print');
 */
class gradereport_progress_print_renderer extends gradereport_progress_base_renderer {
    
    /**
     * consumed by get_string
     * @const string PRINT_BUTTON
     */  
    const PRINT_BUTTON                           = 'printbutton';
    
    public function write_header($courseid, $heading) {
        global $CFG, $DB;
        
        if (!$course = $DB->get_record('course', array('id' => $courseid))) {
            print_error('nocourseid');
        }
        
        $output  = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
        $output .= html_writer::start_tag('html', array('xmlns' => 'http://www.w3.org/1999/xhtml', 'dir' => 'ltr', 'lang' => 'en', 'xml:lang' => 'en'));
        $output .= html_writer::start_tag('head');
        $output .= html_writer::empty_tag('meta', array('http-equiv' => 'Content-Type', 'content' => 'text/html; charset=utf-8'));
        $output .= html_writer::empty_tag('link', array('type' => 'hidden', 'rel' => 'text/css', 'href' => $CFG->wwwroot . '/grade/report/progress/style.css'));
        $output .= html_writer::tag('title', $heading);
        $output .= html_writer::end_tag('head');
        $output .= html_writer::start_tag('body', array('id' => 'progress-print'));
        $output .= html_writer::start_tag('div');
        $output .= html_writer::start_tag('form');
        $output .= html_writer::empty_tag('input', array('id' => 'print-button', 'type' => 'submit', 'value' => get_string(self::PRINT_BUTTON, 'gradereport_progress'), 'onClick' => 'window.print()'));
        $output .= html_writer::end_tag('form');
        $output .= html_writer::end_tag('div');
        $output .= html_writer::start_tag('div', array('id' => 'print-generator'));
        $output .= html_writer::tag('h1', $heading);
        $output .= html_writer::tag('h2', $course->fullname);
 
        return $output;
    }

    public function write_footer() {
        $output  = html_writer::end_tag('div');
        $output .= html_writer::end_tag('body');
        $output .= html_writer::end_tag('html');    
  
        return $output;
    }
}