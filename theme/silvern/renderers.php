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
 * The render implementation for the silvern theme.
 *
 * @copyright 2010 Darryl Pogue
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since     Moodle 2.0
 */
class theme_silvern_core_renderer extends core_renderer {

    /**
     * Get the DOCTYPE declaration that should be used with this page.
     * Designed to be called in theme layout.php files.
     *
     * @return string the DOCTYPE declaration (and any XML prologue) that
     *  should be used.
     */
    public function doctype() {
        global $CFG;

        $doctype = '<!DOCTYPE html>' . "\n";
        $this->contenttype = 'text/html; charset=UTF-8';

        return $doctype;
    }
    
    /**
     * The standard tags (meta tags, links to stylesheets and JavaScript,
     * etc.) that should be included in the <head> tag. Designed to be
     * called in theme layout.php files.
     *
     * @return string HTML fragment.
     */
    public function standard_head_html() {
        global $CFG, $SESSION;
        $output = '';
        $output .= '<meta charset="utf-8">' . "\n";
        $output .= '<meta name="keywords" content="moodle, ' . $this->page->title . '" />' . "\n";
        if (!$this->page->cacheable) {
            $output .= '<meta http-equiv="pragma" content="no-cache" />' . "\n";
            $output .= '<meta http-equiv="expires" content="0" />' . "\n";
        }
        // This is only set by the {@link redirect()} method
        $output .= $this->metarefreshtag;

        // Check if a periodic refresh delay has been set and make sure we arn't
        // already meta refreshing
        if ($this->metarefreshtag=='' && $this->page->periodicrefreshdelay!==null) {
            $output .= '<meta http-equiv="refresh" content="'.$this->page->periodicrefreshdelay.';url='.$this->page->url->out().'" />';
        }

        $output .= '<title>'.$this->page->title.'</title>'."\n";
        
        $output .= '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js"></script>'."\n";
        $output .= '<script type="text/javascript">WebFont.load({ google: { families: [ \'droid sans:n,b\', \'droid serif:n,i,b,bi\' ]}});</script>'."\n";

        // HTML5 shim for IE
        $output .= '<!--[if lt IE 9]>';
        $output .= '<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>';
        $output .= '<![endif]-->'."\n";

        //$this->page->requires->js('lib/javascript-static.js')->in_head(); // contains deprecated stuff too, do not add extre file for that for perf reasons!
        //$this->page->requires->js_function_call('setTimeout', array('fix_column_widths()', 20));

         $focus = $this->page->focuscontrol;
        if (!empty($focus)) {
            if (preg_match("#forms\['([a-zA-Z0-9]+)'\].elements\['([a-zA-Z0-9]+)'\]#", $focus, $matches)) {
                // This is a horrifically bad way to handle focus but it is passed in
                // through messy formslib::moodleform
                $this->page->requires->js_function_call('old_onload_focus', array($matches[1], $matches[2]));
            } else if (strpos($focus, '.')!==false) {
                // Old style of focus, bad way to do it
                debugging('This code is using the old style focus event, Please update this code to focus on an element id or the moodleform focus method.', DEBUG_DEVELOPER);
                $this->page->requires->js_function_call('old_onload_focus', explode('.', $focus, 2));
            } else {
                // Focus element with given id
                $this->page->requires->js_function_call('focuscontrol', array($focus));
            }
        }

        // Get the theme stylesheet - this has to be always first CSS, this loads also styles.css from all plugins;
        // any other custom CSS can not be overridden via themes and is highly discouraged
        $urls = $this->page->theme->css_urls($this->page);
        foreach ($urls as $url) {
            $this->page->requires->css_theme($url);
        }

        // Get the theme javascript head and footer
        $jsurl = $this->page->theme->javascript_url(true);
        $this->page->requires->js($jsurl, true);
        $jsurl = $this->page->theme->javascript_url(false);
        $this->page->requires->js($jsurl);

        // Perform a browser environment check for the flash version.  Should only run once per login session.
        if (isloggedin() && !empty($CFG->excludeoldflashclients) && empty($SESSION->flashversion)) {
            $this->page->requires->js('/lib/swfobject/swfobject.js');
            $this->page->requires->js_init_call('M.core_flashdetect.init');
        }

        // Get any HTML from the page_requirements_manager.
        $output .= $this->page->requires->get_head_code($this->page, $this);

        // List alternate versions.
        foreach ($this->page->alternateversions as $type => $alt) {
            $output .= html_writer::empty_tag('link', array('rel' => 'alternate',
                    'type' => $type, 'title' => $alt->title, 'href' => $alt->url));
        }

        return $output;
    }
    
    /**
     * Return the blackbar for the top of the page.
     * @return string HTML fragment.
     */
    public function blackbar_info() {
        global $USER, $CFG, $SITE, $DB;

        if (during_initial_install()) {
            return '';
        }
        
        if (empty($user) and !empty($USER->id)) {
		    $user = $USER;
		}

		if (empty($course)) {
		    $course = $SITE;
		}
		
		$output = array();
		//$output[1] = $this->output_tag('a', array('href' => '#', 'onclick' => 'increase()', 'title' => 'Increase text size'), "+");

		$loginurl = get_login_url();

		if (empty($course->id)) {
		    // $course->id is not defined during installation
		    return '';
		} else if (!empty($user->id)) {
	        $context = get_context_instance(CONTEXT_COURSE, $course->id);

		    $fullname = fullname($user, true);

		    if (isset($user->username) && $user->username == 'guest') {
		        $output[] = html_writer::tag('a', get_string('login'), array('href' => $loginurl));
		    } else {
		    	$output[0] = html_writer::tag('b', $fullname, null);
		    	$output[] = html_writer::tag('a', get_string('profile'), array('href' => $CFG->wwwroot.'/user/view.php?id='.$user->id.'&amp;course='.$course->id));
		    	$output[] = html_writer::tag('a', get_string('logout'), array('href' => $CFG->wwwroot.'/login/logout.php?sesskey='.sesskey()));
		    }
		} else {
			$output[] = html_writer::tag('a', get_string('login'), array('href' => $loginurl));
		}
		
		ksort($output);

		return implode(' | ', $output);
    }

    
    /**
     * Prints a nice side block with an optional header.
     *
     * The content is described
     * by a {@link block_contents} object.
     *
     * @param block_contents $bc HTML for the content
     * @param string $region the region the block is appearing in.
     * @return string the HTML to be output.
     */
    function block($bc, $region) {
        $bc = clone($bc); // Avoid messing up the object passed in.
        if (empty($bc->blockinstanceid) || !strip_tags($bc->title)) {
            $bc->collapsible = block_contents::NOT_HIDEABLE;
        }
        if ($bc->collapsible == block_contents::HIDDEN) {
            $bc->add_class('hidden');
        }
        if (!empty($bc->controls)) {
            $bc->add_class('block_with_controls');
        }

        $output = '';
        $skipdest = '';
        
        if ($bc->collapsible != block_contents::NOT_HIDEABLE) {
            $bc->attributes['class'] .= ' collapsible';
        }
        
        $output .= html_writer::start_tag('aside', $bc->attributes);

        $controlshtml = $this->block_controls($bc->controls);

        $title = '';
        if ($bc->title) {
            $title = html_writer::tag('h1', $bc->title, null);
        }

        if ($title || $controlshtml) {
            $output .= html_writer::tag('header', $title.$controlshtml, null);
        }

        $output .= html_writer::start_tag('div', null);
        $output .= $bc->content;
        $output .= html_writer::end_tag('div');

        if ($bc->footer) {
            $output .= html_writer::tag('footer', $bc->footer, null);
        }
        $output .= html_writer::end_tag('aside');

        /*if ($bc->annotation) {
            $output .= html_writer::tag('div', array('class' => 'blockannotation'), $bc->annotation);
        }
        $output .= $skipdest;*/

        $this->init_block_title_js($bc);
        return $output;
    }


    /**
     * Calls the JS require function to hide a block.
     * @param block_contents $bc A block_contents object
     * @return void
     */
    protected function init_block_title_js($bc) {
        if ($bc->collapsible != block_contents::NOT_HIDEABLE) {
            $userpref = 'block' . $bc->blockinstanceid . 'hidden';
            user_preference_allow_ajax_update($userpref, PARAM_BOOL);

            $module = array('name'=>'block_controls', 'fullpath'=>'/theme/silvern/javascript/blocks.js', 'requires'=>array('node'));
            $this->page->requires->js_init_call('M.block_controls.init', array(array('blockid' => $bc->blockinstanceid, 'userpref' => $userpref)), false, $module);
            /*$this->page->requires->yui2_lib('dom');
            $this->page->requires->yui2_lib('event');
            $plaintitle = strip_tags($bc->title);
            $this->page->requires->js_function_call('new block_title_controls', array($bc->id, $userpref,
                    get_string('hideblocka', 'access', $plaintitle), get_string('showblocka', 'access', $plaintitle),
                    $this->pix_url('t/switch_minus')->out(false, array(), false), $this->pix_url('t/switch_plus')->out(false, array(), false)));*/
        }
    }

    /**
     * Output all the blocks in a particular region.
     * @param string $region the name of a region on this page.
     * @return string the HTML to be output.
     */
    public function blocks_for_region($region) {
        $blockcontents = $this->page->blocks->get_content_for_region($region, $this);

        $output = '';
        foreach ($blockcontents as $bc) {
            if ($bc instanceof block_contents) {
                $output .= $this->block($bc, $region);
            } else if ($bc instanceof block_move_target) {
                $output .= $this->block_move_target($bc);
            } else {
                throw new coding_exception('Unexpected type of thing (' . get_class($bc) . ') found in list of block contents.');
            }
        }
        return $output;
    }
}
