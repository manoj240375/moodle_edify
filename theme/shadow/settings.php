<?php

/**
 * Settings for the shadow theme
 */

// Create our admin page
$temp = new admin_settingpage('theme_shadow', get_string('configtitle','theme_shadow'));

// Background colour setting
$name = 'theme_shadow/backgroundcolor';
$title = get_string('backgroundcolor','theme_shadow');
$description = get_string('backgroundcolordesc', 'theme_shadow');
$default = '#FFFFFF';
$previewconfig = array('selector'=>'.block .content', 'style'=>'backgroundColor');
$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
$temp->add($setting);

// Logo file setting
$name = 'theme_shadow/logo';
$title = get_string('logo','theme_shadow');
$description = get_string('logodesc', 'theme_shadow');
$setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
$temp->add($setting);

// Block region width
$name = 'theme_shadow/regionwidth';
$title = get_string('regionwidth','theme_shadow');
$description = get_string('regionwidthdesc', 'theme_shadow');
$default = 200;
$choices = array(150=>'150px', 170=>'170px', 200=>'200px', 240=>'240px', 290=>'290px', 350=>'350px', 420=>'420px');
$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
$temp->add($setting);

// Custom CSS file
$name = 'theme_shadow/customcss';
$title = get_string('customcss','theme_shadow');
$description = get_string('customcssdesc', 'theme_shadow');
$setting = new admin_setting_configtextarea($name, $title, $description, '');
$temp->add($setting);

// Add our page to the structure of the admin tree
$ADMIN->add('themes', $temp);