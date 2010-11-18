<?php

$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hassidepre = $PAGE->blocks->region_has_content('side-pre', $OUTPUT);
$hassidepost = $PAGE->blocks->region_has_content('side-post', $OUTPUT);

$bodyclasses = array();
if ($hassidepre && !$hassidepost) {
    $bodyclasses[] = 'side-pre-only';
} else if ($hassidepost && !$hassidepre) {
    $bodyclasses[] = 'side-post-only';
} else if (!$hassidepost && !$hassidepre) {
    $bodyclasses[] = 'content-only';
}
//Site Logo
if (!empty($PAGE->theme->settings->logo)) {
    $logourl = $PAGE->theme->settings->logo;
} else {
    $logourl = $OUTPUT->pix_url('logo', 'theme');
}
//Slider images
//1
if (!empty($PAGE->theme->settings->slider1)) {
    $slider1 = $PAGE->theme->settings->slider1;
} else {
    $slider1 = $OUTPUT->pix_url('slider1', 'theme');
}
//2
if (!empty($PAGE->theme->settings->slider2)) {
    $slider2 = $PAGE->theme->settings->slider2;
} else {
    $slider2 = $OUTPUT->pix_url('slider2', 'theme');
}
//3
if (!empty($PAGE->theme->settings->slider3)) {
    $slider3 = $PAGE->theme->settings->slider3;
} else {
    $slider3 = $OUTPUT->pix_url('slider3', 'theme');
}
//Slider Text
//1
if (!empty($PAGE->theme->settings->slider1text)) {
    $slider1text = $PAGE->theme->settings->slider1text;
} else {
    $slider1text = '<h3>Art Competition</h3>
					<h4>Year 11 Summer Art Competition</h4>
					<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla interdum mollis lacus quis molestie. Integer a libero quis urna elementum malesuada. Vestibulum pellentesque mollis est et lobortis. Ut vel tortor vitae odio lacinia elementum. Fusce sed leo volutpat nibh rhoncus malesuada.</p>';
}
//2
if (!empty($PAGE->theme->settings->slider2text)) {
    $slider2text = $PAGE->theme->settings->slider2text;
} else {
    $slider2text = '<h3>Computer Lab</h3>
					<h4>T3 is being refurbished this summer</h4>
					<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla interdum mollis lacus quis molestie. Integer a libero quis urna elementum malesuada. Vestibulum pellentesque mollis est et lobortis. Ut vel tortor vitae odio lacinia elementum. Fusce sed leo volutpat nibh rhoncus malesuada.</p>';
}
//3
if (!empty($PAGE->theme->settings->slider3text)) {
    $slider3text = $PAGE->theme->settings->slider3text;
} else {
    $slider3text = '<h3>Sports Day</h3>
					<h4>Sports day results</h4>
					<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla interdum mollis lacus quis molestie. Integer a libero quis urna elementum malesuada. Vestibulum pellentesque mollis est et lobortis. Ut vel tortor vitae odio lacinia elementum. Fusce sed leo volutpat nibh rhoncus malesuada.</p>';
}
//Footnote
if (!empty($PAGE->theme->settings->footnote)) {
    $footnote = $PAGE->theme->settings->footnote;
} else {
    $footnote = '<h3>Footer</h3>
					<p>Images, Text & Links can be placed here to appear on the front page and all other pages.</p>';
}


echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
  <title><?php echo $PAGE->title; ?></title>
  <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
  <?php echo $OUTPUT->standard_head_html() ?>

</head>
 
<body id="<?php echo $PAGE->bodyid ?>" class="<?php echo $PAGE->bodyclasses.' '.join(' ', $bodyclasses) ?>">

<?php echo $OUTPUT->standard_top_of_body_html() ?>

<?php if ($hasheading || $hasnavbar) { ?>

<div id="page-wrapper">
  <div id="page" class="clearfix">
    
    <div id="page-header" class="clearfix">
      <div id="page-header-left"> &nbsp; </div>
      <?php if ($PAGE->heading) { ?>
        <img class="sitelogo" src="<?php echo $logourl;?>" alt="Site Logo" />
        <h1 class="headermain"><?php echo $PAGE->heading ?></h1>
        <div class="headermenu">
          <?php echo $OUTPUT->login_info();
          if (!empty($PAGE->layout_options['langmenu'])) {
            echo $OUTPUT->lang_menu();
          }
          echo $PAGE->headingmenu; ?>
        </div>
      <?php } ?>
     <div id="page-header-right"> &nbsp; </div>
    </div>

<?php } ?>
      
    <div id="page-content">

        <div id="slider">
			<ul>
			<li>
				<img src="<?php echo $slider1;?>" />
				<div class="content">
					<?php echo $slider1text; ?>
				</div>
				<div class="clear"></div>
			</li>
			<li>
				<img src="<?php echo $slider2;?>" />
				<div class="content">
					<?php echo $slider2text; ?>
				</div>
				<div class="clear"></div>
			</li>
			<li>
				<img src="<?php echo $slider3;?>" />
				<div class="content">
					<?php echo $slider3text; ?>
				</div>
				<div class="clear"></div>
			</li>

			</ul>
        </div>

        <div id="region-main-box">
            <div id="region-post-box">
            
                <div id="region-main-wrap">
                    <div id="region-main">
                        <div class="region-content">
                            <?php echo core_renderer::MAIN_CONTENT_TOKEN ?>
                        </div>
                    </div>
                </div>
                
                <?php if ($hassidepre) { ?>
                <div id="region-pre">
                    <div class="region-content">
                        <?php echo $OUTPUT->blocks_for_region('side-pre') ?>
                    </div>
                </div>
                <?php } ?>
                
                <?php if ($hassidepost) { ?>
                <div id="region-post">
                    <div class="region-content">
                        <?php echo $OUTPUT->blocks_for_region('side-post') ?>
                    </div>
                </div>
                <?php } ?>
                
            </div>
        </div>
    </div>
    
<?php if ($hasfooter) { ?>
  
    <div id="page-footer" class="clearfix">
     <div id="page-footer-left">
       &nbsp;
     </div>
     <div class="footnote">
       <?php echo $footnote; ?>
     </div>
        <p class="helplink">&nbsp;<!--<?php echo page_doc_link(get_string('moodledocslink')) ?>--></p>
        <?php echo $OUTPUT->login_info(); ?>
     <div id="page-footer-right">
       &nbsp;
     </div>
    </div>

<?php }

if ($hasheading || $hasnavbar) { ?>
  
  </div> <!-- END #page -->
</div> <!-- END #page-wrapper -->

<?php } ?>

<div id="page-footer-bottom">

<?php if ($hasfooter) {
  echo $OUTPUT->home_link();
  echo $OUTPUT->standard_footer_html();
} ?>

</div>     

<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>