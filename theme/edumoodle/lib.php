<?php

function edumoodle_process_css($css, $theme) {
 
    if (!empty($theme->settings->backgroundcolor)) {
        $backgroundcolor = $theme->settings->backgroundcolor;
    } else {
        $backgroundcolor = null;
    }
    $css = edumoodle_set_backgroundcolor($css, $backgroundcolor);

    if (!empty($theme->settings->linkcolor)) {
        $linkcolor = $theme->settings->linkcolor;
    } else {
        $linkcolor = null;
    }
    $css = edumoodle_set_linkcolor($css, $linkcolor);

    if (!empty($theme->settings->bordercolor)) {
        $bordercolor = $theme->settings->bordercolor;
    } else {
        $bordercolor = null;
    }
    $css = edumoodle_set_bordercolor($css, $bordercolor);
 
    if (!empty($theme->settings->blockbordercolor)) {
        $blockbordercolor = $theme->settings->blockbordercolor;
    } else {
        $blockbordercolor = null;
    }
    $css = edumoodle_set_blockbordercolor($css, $blockbordercolor);

    if (!empty($theme->settings->customcss)) {
        $customcss = $theme->settings->customcss;
    } else {
        $customcss = null;
    }
    $css = edumoodle_set_customcss($css, $customcss);
 
    return $css;
}

/**
 * Sets the background colour variable in CSS
 *
 * @param string $css
 * @param mixed $backgroundcolor
 * @return string
 */
function edumoodle_set_backgroundcolor($css, $backgroundcolor) {
    $tag = '[[setting:backgroundcolor]]';
    $replacement = $backgroundcolor;
    if (is_null($replacement)) {
        $replacement = '#4EACDB';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

/**
 * Sets the text link colour variable in CSS
 *
 * @param string $css
 * @param mixed $backgroundcolor
 * @return string
 */
function edumoodle_set_linkcolor($css, $linkcolor) {
    $tag = '[[setting:linkcolor]]';
    $replacement = $linkcolor;
    if (is_null($replacement)) {
        $replacement = '#F57110';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

/**
 * Sets the Header Border colour variable in CSS
 *
 * @param string $css
 * @param mixed $bordercolor
 * @return string
 */
function edumoodle_set_bordercolor($css, $bordercolor) {
    $tag = '[[setting:bordercolor]]';
    $replacement = $bordercolor;
    if (is_null($replacement)) {
        $replacement = '#F57110';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

/**
 * Sets the Header Border colour variable in CSS
 *
 * @param string $css
 * @param mixed $bordercolor
 * @return string
 */
function edumoodle_set_blockbordercolor($css, $blockbordercolor) {
    $tag = '[[setting:blockbordercolor]]';
    $replacement = $blockbordercolor;
    if (is_null($replacement)) {
        $replacement = '#4FADDC';
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
function edumoodle_set_customcss($css, $customcss) {
    $tag = '[[setting:customcss]]';
    $replacement = $customcss;
    if (is_null($replacement)) {
        $replacement = '';
    }
    $css = str_replace($tag, $replacement, $css);
    return $css;
}

?>