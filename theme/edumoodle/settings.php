<?php
/**
 * Settings for the edumoodle theme
   http://docs.moodle.org/en/Development:Themes_2.0_adding_a_settings_page
 */
 
// Create our admin page
$temp = new admin_settingpage('theme_edumoodle', get_string('configtitle','theme_edumoodle'));

// Logo file setting
$name = 'theme_edumoodle/logo';
$title = get_string('logo','theme_edumoodle');
$description = get_string('logodesc', 'theme_edumoodle');
$default = '/edumoodle/pix/logo.png';
$setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_URL);
$temp->add($setting);

// Slider image 1
$name = 'theme_edumoodle/slider1';
$title = get_string('slider1','theme_edumoodle');
$default = '/edumoodle/pix/slider1.png';
$description = get_string('slider1desc', 'theme_edumoodle');
$setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_URL);
$temp->add($setting);

// Slider 1 text
$name = 'theme_edumoodle/slider1text';
$title = get_string('slider1text','theme_edumoodle');
$description = get_string('slider1textdesc', 'theme_edumoodle');
$setting = new admin_setting_confightmleditor($name, $title, $description, '');
$temp->add($setting);

// Slider image 2
$name = 'theme_edumoodle/slider2';
$title = get_string('slider2','theme_edumoodle');
$default = '/edumoodle/pix/slider2.png';
$description = get_string('slider2desc', 'theme_edumoodle');
$setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_URL);
$temp->add($setting);

// Slider 2 text
$name = 'theme_edumoodle/slider2text';
$title = get_string('slider2text','theme_edumoodle');
$description = get_string('slider2textdesc', 'theme_edumoodle');
$setting = new admin_setting_confightmleditor($name, $title, $description, '');
$temp->add($setting);

// Slider image 3
$name = 'theme_edumoodle/slider3';
$title = get_string('slider3','theme_edumoodle');
$default = '/edumoodle/pix/slider3.png';
$description = get_string('slider3desc', 'theme_edumoodle');
$setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_URL);
$temp->add($setting);

// Slider 3 text
$name = 'theme_edumoodle/slider3text';
$title = get_string('slider3text','theme_edumoodle');
$description = get_string('slider3textdesc', 'theme_edumoodle');
$setting = new admin_setting_confightmleditor($name, $title, $description, '');
$temp->add($setting);

  
// Background colour setting
$name = 'theme_edumoodle/backgroundcolor';
$title = get_string('backgroundcolor','theme_edumoodle');
$description = get_string('backgroundcolordesc', 'theme_edumoodle');
$default = '#4EACDB';
$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, null);
$temp->add($setting);

// Header Border colour setting
$name = 'theme_edumoodle/bordercolor';
$title = get_string('bordercolor','theme_edumoodle');
$description = get_string('bordercolordesc', 'theme_edumoodle');
$default = '#F57110';
$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, null);
$temp->add($setting);

// Block Border colour setting
$name = 'theme_edumoodle/blockbordercolor';
$title = get_string('blockbordercolor','theme_edumoodle');
$description = get_string('blockbordercolordesc', 'theme_edumoodle');
$default = '#4FADDC';
$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, null);
$temp->add($setting);

// Link colour setting
$name = 'theme_edumoodle/linkcolor';
$title = get_string('linkcolor','theme_edumoodle');
$description = get_string('linkcolordesc', 'theme_edumoodle');
$default = '#F57110';
$setting = new admin_setting_configcolourpicker($name, $title, $description, $default, null);
$temp->add($setting);

// Footnote
$name = 'theme_edumoodle/footnote';
$title = get_string('footnote','theme_edumoodle');
$description = get_string('footnotedesc', 'theme_edumoodle');
$setting = new admin_setting_confightmleditor($name, $title, $description, '');
$temp->add($setting);

// Custom CSS file
$name = 'theme_edumoodle/customcss';
$title = get_string('customcss','theme_edumoodle');
$description = get_string('customcssdesc', 'theme_edumoodle');
$setting = new admin_setting_configtextarea($name, $title, $description, '');
$temp->add($setting);
 
// Add our page to the structure of the admin tree
$ADMIN->add('themes', $temp);
?>