<?php

/**
 * Settings for the demystified theme
 */

// Create our admin page
$temp = new admin_settingpage('theme_decaf', get_string('configtitle','theme_decaf'));

// Background colour setting
$name = 'theme_decaf/backgroundcolor';
$title = get_string('backgroundcolor','theme_decaf');
$description = get_string('backgroundcolordesc', 'theme_decaf');
$default = '#EEE';
$previewconfig = array('selector'=>'html', 'style'=>'backgroundColor');
$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
$temp->add($setting);

// Foot note setting
$name = 'theme_decaf/footnote';
$title = get_string('footnote','theme_decaf');
$description = get_string('footnotedesc', 'theme_decaf');
$setting = new admin_setting_confightmleditor($name, $title, $description, '');
$temp->add($setting);

// Custom CSS file
$name = 'theme_decaf/customcss';
$title = get_string('customcss','theme_decaf');
$description = get_string('customcssdesc', 'theme_decaf');
$setting = new admin_setting_configtextarea($name, $title, $description, '');
$temp->add($setting);

// Add our page to the structure of the admin tree
$ADMIN->add('themes', $temp);