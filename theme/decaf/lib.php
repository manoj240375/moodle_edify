<?php

class decaf_topsettings_renderer extends plugin_renderer_base {

    public function settings_tree(settings_navigation $navigation) {
        global $CFG;
        $content = html_writer::start_tag('a', array('href' => "$CFG->wwwroot", 'id' =>'home'));
        $content .= html_writer::empty_tag('img', array('alt' => '', 'src' =>$this->pix_url('home_icon', 'theme')));
        $content .= html_writer::end_tag('a');
        $content .= $this->navigation_node($navigation, array('class' => 'dropdown  dropdown-horizontal'));
        if (has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM))) {
            $content .= $this->search_form(new moodle_url("$CFG->wwwroot/$CFG->admin/search.php"), optional_param('query', '', PARAM_RAW));
        }
        $content .= html_writer::empty_tag('br', array('clear' => 'all'));
        $content = html_writer::nonempty_tag('div', $content, array('id' => 'awesomebar'));
        return $content;
    }

    protected function navigation_node(navigation_node $node, $attrs=array()) {
        $items = $node->children;

        // exit if empty, we don't want an empty ul element
        if ($items->count() == 0) {
            return '';
        }

        // array of nested li elements
        $lis = array();
        foreach ($items as $item) {
            if (!$item->display) {
                continue;
            }

            $isbranch = ($item->children->count() > 0 || $item->nodetype == navigation_node::NODETYPE_BRANCH);
            $hasicon = (!$isbranch && $item->icon instanceof renderable);

            if ($isbranch) {
                $item->hideicon = true;
            }
            $content = $this->output->render($item);

            $content .= $this->navigation_node($item);

            $content = html_writer::tag('li', $content);
            $lis[] = $content;
        }

        if (count($lis)) {

            return html_writer::tag('ul', implode("\n", $lis), $attrs);
        } else {
            return '';
        }
    }

    public function search_form(moodle_url $formtarget, $searchvalue) {
        global $CFG;

        if (empty($searchvalue)) {
            $searchvalue = 'Search Settings..';
        }

        $content = html_writer::start_tag('form', array('class' => 'topadminsearchform', 'method' => 'get', 'action' => $formtarget));
        $content .= html_writer::start_tag('div', array('class' => 'search-box'));
        $content .= html_writer::tag('label', s(get_string('searchinsettings', 'admin')), array('for' => 'adminsearchquery', 'class' => 'accesshide'));
        $content .= html_writer::empty_tag('input', array('id' => 'topadminsearchquery', 'type' => 'text', 'name' => 'query', 'value' => s($searchvalue),
                    'onfocus' => "if(this.value == 'Search Settings..') {this.value = '';}",
                    'onblur' => "if (this.value == '') {this.value = 'Search Settings..';}"));
        //$content .= html_writer::empty_tag('input', array('class'=>'search-go','type'=>'submit', 'value'=>''));
        $content .= html_writer::end_tag('div');
        $content .= html_writer::end_tag('form');

        return $content;
    }

}

/**
 * decaf_filtered_blocks_for_region() override blocks_for_region()
 *  in moodlelib.php. Returns a string
 * values ready for use.
 *
 * @return string
 */
function decaf_filtered_blocks_for_region($region) {
    global $PAGE, $OUTPUT;
    $blockcontents = $PAGE->blocks->get_content_for_region($region, $OUTPUT);

    $output = '';
    foreach ($blockcontents as $bc) {
        if ($bc instanceof block_contents) {
            if ($bc->attributes['class'] != 'block_settings  block') {
                $output .= $OUTPUT->block($bc, $region);
            }
        } else if ($bc instanceof block_move_target) {
            $output .= $OUTPUT->block_move_target($bc);
        } else {
            throw new coding_exception('Unexpected type of thing (' . get_class($bc) . ') found in list of block contents.');
        }
    }
    return $output;
}

/**
 * get_performance_output() override get_peformance_info()
 *  in moodlelib.php. Returns a string
 * values ready for use.
 *
 * @return string
 */
function decaf_performance_output($param) {
	
    $html = '<div class="performanceinfo"><ul>';
	if (isset($param['realtime'])) $html .= '<li><a class="red" href="#"><var>'.$param['realtime'].' secs</var><span>Load Time</span></a></li>';
	if (isset($param['memory_total'])) $html .= '<li><a class="orange" href="#"><var>'.display_size($param['memory_total']).'</var><span>Memory Used</span></a></li>';
    if (isset($param['includecount'])) $html .= '<li><a class="blue" href="#"><var>'.$param['includecount'].' Files </var><span>Included</span></a></li>';
    if (isset($param['dbqueries'])) $html .= '<li><a class="purple" href="#"><var>'.$param['dbqueries'].' </var><span>DB Read/Write</span></a></li>';
    $html .= '</ul></div>';

    return $html;
}

function decaf_process_css($css, $theme) {

    if (!empty($theme->settings->backgroundcolor)) {
        $backgroundcolor = $theme->settings->backgroundcolor;
    } else {
        $backgroundcolor = null;
    }
    $css = decaf_set_backgroundcolor($css, $backgroundcolor);

    if (!empty($theme->settings->customcss)) {
        $customcss = $theme->settings->customcss;
    } else {
        $customcss = null;
    }
    $css = decaf_set_customcss($css, $customcss);

    return $css;
}

/**
 * Sets the background colour variable in CSS
 *
 * @param string $css
 * @param mixed $backgroundcolor
 * @return string
 */
function decaf_set_backgroundcolor($css, $backgroundcolor) {
    $tag = '[[setting:backgroundcolor]]';
    $replacement = $backgroundcolor;
    if (is_null($replacement)) {
        $replacement = '#EEEEEE';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

/**
 * Sets the custom css variable in CSS
 *
 * @param string $css
 * @param mixed $customcss
 * @return string
 */
function decaf_set_customcss($css, $customcss) {
    $tag = '[[setting:customcss]]';
    $replacement = $customcss;
    if (is_null($replacement)) {
        $replacement = '';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}