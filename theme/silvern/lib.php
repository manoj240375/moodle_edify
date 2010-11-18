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
 * General purpose functions for the silvern theme.
 *
 * @copyright 2010 Darryl Pogue
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since     Moodle 2.0
 */

function silvern_process_css($css, $theme) {
    if (!empty($theme->settings->welcomecolour)) {
        $welcomecol = $theme->settings->welcomecolour;
    } else {
        $welcomecol = null;
    }
    $css = silvern_set_welcomecolour($css, $welcomecol);

    if (!empty($theme->settings->logourl)) {
        $logourl = $theme->settings->logourl;
    } else {
        $logourl = $theme->pix_url('moodle_logo', 'theme');
    }
    $css = silvern_set_logourl($css, $logourl);

    return $css;
}

/**
 * Sets the background colour of the welcome message and home tab in CSS.
 *
 * @param string $css
 * @param mixed $colour
 * @return string
 */
function silvern_set_welcomecolour($css, $colour) {
    $tag = '[[setting:welcomecolour]]';
    $tag_grad = '[[setting:welcomecolourfade]]';

    if (is_null($colour)) {
        $rgb = Hex2RGB('#800000');
    } else {
        $rgb = Hex2RGB($colour);
    }
    $replacement = 'rgb('.$rgb[0].','.$rgb[1].','.$rgb[2].')';
    $css = str_replace($tag, $replacement, $css);
    $replacement = 'rgba('.$rgb[0].','.$rgb[1].','.$rgb[2].',0.4)';
    $css = str_replace($tag_grad, $replacement, $css);
    return $css;
}

/**
 * Sets the logo URL.
 *
 * @param string $css
 * @param mixed $url
 * @return string
 */
function silvern_set_logourl($css, $url) {
    $tag = '[[setting:logourl]]';

    $css = str_replace($tag, $url, $css);
    return $css;
}


/**
 * Converts a hex colour to an array of RGB values.
 *
 * @params string $colour
 * @returns array
 *
 * @copyright 2006 Jonas John
 * http://www.jonasjohn.de/snippets/php/hex2rgb.htm
 */
function Hex2RGB($colour) {
    $colour = str_replace('#', '', $colour);
    if (strlen($colour) != 6){ return array(0,0,0); }
    $rgb = array();
    for ($x=0;$x<3;$x++){
        $rgb[$x] = hexdec(substr($colour,(2*$x),2));
    }
    return $rgb;
}
