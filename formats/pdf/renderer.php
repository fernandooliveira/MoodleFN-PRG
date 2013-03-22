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
 * Class definition of renderer for subplugin
 *
 * @package    gradebook
 * @subpackage progress
 * @copyright  2011 MoodleFN
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Custom renderer for the progress grade report pdf formatter
 *
 * To get an instance of this use the following code:
 * $renderer = $PAGE->get_renderer('gradereport_progress_pdf');
 */
/*
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
*/

class gradereport_progress_pdf_renderer extends plugin_renderer_base {

    public function write_html_document_start($title = ''){
        $html = '<!DOCTYPE html>
                 <html>
                 <head>
	             <title>'.$title.'</title>';
        return $html;
    }

    public function write_html_add_css(){
        global $CFG;
        
        $style  = '<style>';
        
        //get basic PDF styles
        $style .= file_get_contents($CFG->dirroot.'/grade/report/progress/formats/pdf/styles.css');
        
        //get all report PDF-specific styles
        require_once($CFG->dirroot . '/grade/report/progress/lib.engine.php');
        $prg_subplugin_manager_instance = progress_report_subplugin_manager::get_instance();

        if ($files = $prg_subplugin_manager_instance->get_report_css_files_relative('pdf.css')) {
            foreach ($files as $file) {
                $style .= file_get_contents($CFG->dirroot.'/'.$file);
            }
        }        
        
        $style .= '</style>';
        
        return $style;
    }

    public function write_html_document_end_head(){
        return '</head>
                <body>';
    }

    public function add_html_page_break(){
        return '<pagebreak>';
    }

    public function write_html_document_end(){
        return '</body>
                </html>';
    }
    
    public function write_html_document_front_page($heading){
        return '<h1>'.$heading.'</h1>
                <pagebreak>';
    }
    
    public function write_node_self_closed($tag, $attributes){ //problem with dl
        return html_writer::empty_tag($tag, $attributes);
    }

    public function write_open_node($tag, $attributes){
        //if a tag is dl, tr or td it needs to be handled such that:
        //dl = table
        //dt starts a row and table cell
        //dd ends a table cell and row

        switch($tag) {
            case 'dl' :
                if (isset($attributes['class'])) {
                    $attributes['class'] .= ' dl';
                } else {
                    $attributes['class'] = ' dl';
                }
                return html_writer::start_tag('table', $attributes);
                break;
            case 'dt' :
                if (isset($attributes['class'])) {
                    $attributes['class'] .= ' dt';
                } else {
                    $attributes['class'] = ' dt';
                }
                return html_writer::start_tag('tr').html_writer::start_tag('td', $attributes);
                break;
            case 'dd' :
                if (isset($attributes['class'])) {
                    $attributes['class'] .= ' dd';
                } else {
                    $attributes['class'] = 'dd';
                }
                return html_writer::start_tag('td', $attributes);
                break;
            case 'ul' :
                if (isset($attributes['class'])) {
                    $attributes['class'] .= ' ul';
                } else {
                    $attributes['class'] = 'ul';
                }
                return html_writer::start_tag('div', $attributes);
                break;
            case 'li' :
                if (isset($attributes['class'])) {
                    $attributes['class'] .= ' li';
                } else {
                    $attributes['class'] = 'li';
                }
                return html_writer::start_tag('div', $attributes);
                break;                
            default :
                return html_writer::start_tag($tag, $attributes);
                break;
        }        
        
        return html_writer::start_tag($tag, $attributes);
    }

    public function write_close_node($tag){
        switch($tag) {
            case 'dl' :
                return html_writer::end_tag('table');
                break;
            case 'dt' :
                return html_writer::end_tag('td');
                break;
            case 'dd' :
                return html_writer::end_tag('td').html_writer::end_tag('tr');
                break;
            case 'ul' :
                return html_writer::end_tag('div');
                break;
            case 'li' :
                return html_writer::end_tag('div');
                break;
            default :
                return html_writer::end_tag($tag);
                break;
        }     
        return html_writer::end_tag($tag);
    }
}