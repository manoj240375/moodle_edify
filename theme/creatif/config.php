<?php

/*

Theme Name: 	créatif
Theme Version:	1
Designed by:	Rolley
				Work blog:  rolleys.wordpress.com
				Personal blog:  rolleys.blogspot.com
				
Theme Purpose:

	I think students just like anyone else want to use a nice looking system for their learning, 
	so this theme attempts to add more creative visuals to the moodle UI - something a little 
	more refined than the usual	LMS look. 
	
	Of course this theme has some shortfalls, it hasn't met the bar fully but it is just a first attempt.
	It was quite rushed, I found out about a theme-comp a bit late and didn't have time to look at using 
	renderers to assist with my theme-design.
		  
	I think the big thing to improve next version of this theme, along with renderers and more effective 
	css will be to allow teachers to add their own imagery for the backgrounds - pictures that are course 
	specific for them. It would use these instead of the pre-packaged ones I've got in the pix directory.
	
	I'd also like to make it work 100% drag-and-drop with jQuery's theme roller.. So in other words, you 
	simply design your jQuery theme roller and drop it in to the moodle theme directory - and the moodle
	ui takes on the jQuery css and pix.
		
Credits - and changes:

	Icons: http://famfamfam.com/lab/icons/silk/ - by Mark James, awesome icons available under the Creative Commons Attribution license.

	jQuery's theme roller for some of the images and themeroller css for those.
	
	Three of the images I've put in as backgrounds are from FlickR, photos under the creative commons license.
	They are as follows:
	
		M31, the andromeda galaxy
		http://www.flickr.com/photos/astroporn/3918373246/
	
		A peaceful river
		http://www.flickr.com/photos/chrisschoenbohm/4646875436/
	
		An isolated storm
		http://www.flickr.com/photos/chrisschoenbohm/4659700300/

	The rest of the backgrounds are from my own photos and a couple of my wife's. 

	Want to add your own images or replace the included ones? Easy, I've included:
	- A layered PSD (photoshop CS5) file with guides and layer masks, drop your own
	  photos in and duplicate one of the layer masks and you can save your own.
	
	Don't forget to adjust the variable that indicates the total number of images though,
	this can be found in the head of the standard.php and login.php files: $totalImages
	
Recommendation:

	The theme works best in Safari and Firefox, I did all my dev in Safari. I tested in IE7 and IE8 as well, 
	and as usual there was a significant difference in visuals. I've attmpted to fix all the issues I could find, 
	but there could be further issues that I haven't been able to identify - if so, sorry!
	
Insitution's logo:

	You'll see an image in the pix directory called "logo.png", simply replace it with your organisation's own logo.
	If you want	it to blend in to the background without 24bit transparency cut the image from a #333333 background.

Devices:

	Tested and OK on:
		- iPad
		- iPhone - I did consider doing iPhone specific css but really, I don't think it's much needed. 
		  I could do it for a future version though if there's a demand..

*/

$THEME->name = 'creatif';
$THEME->parents = array('base', 'canvas');
$THEME->sheets = array(
	'creatif',
	'blocksAndDock',
	'iefixes',
	'custommenus',
	'courseviews',
	'calendars',
	'tabs',
	'mods_quiz'	
);

$THEME->custompix = true;

$THEME->layouts = array(
    'base' => array(
        'file' => 'standard.php',
        'regions' => array(),
    ),
    'standard' => array(
        'file' => 'standard.php',
        'regions' => array('side-post'),
        'defaultregion' => 'side-post',
    ),
    'course' => array(
        'file' => 'standard.php',
        'regions' => array('side-post'),
        'defaultregion' => 'side-post',
    ),
    'coursecategory' => array(
        'file' => 'standard.php',
        'regions' => array('side-post'),
        'defaultregion' => 'side-post',
    ),
    'incourse' => array(
        'file' => 'standard.php',
        'regions' => array('side-post'),
        'defaultregion' => 'side-post',
    ),
    'frontpage' => array(
        'file' => 'standard.php',
        'regions' => array('side-post','side-pre'),
        'defaultregion' => 'side-post',
    ),
    'mydashboard' => array(
        'file' => 'standard.php',
        'regions' => array('side-post','side-pre'),
        'defaultregion' => 'side-post',
        'options' => array('langmenu'=>true),
    ),
    'mypublic' => array(
        'file' => 'standard.php',
        'regions' => array('side-post'),
        'defaultregion' => 'side-post',
    ),
    'login' => array(
        'file' => 'standard.php',
        'regions' => array(),
        'options' => array('langmenu'=>true),
    ),
    'popup' => array(
        'file' => 'standard.php',
        'regions' => array(),
        'options' => array('nofooter'=>true),
    ),
    'frametop' => array(
        'file' => 'standard.php',
        'regions' => array(),
        'options' => array('nofooter'=>true),
    ),
    'maintenance' => array(
        'file' => 'standard.php',
        'regions' => array(),
        'options' => array('noblocks'=>true, 'nofooter'=>true, 'nonavbar'=>true, 'nocustommenu'=>true),
    ),
    'admin' => array(
        'file' => 'standard.php',
        'regions' => array('side-post'),
        'defaultregion' => 'side-post',
    )
);

$THEME->enable_dock = true;
$THEME->javascripts_footer = array();
