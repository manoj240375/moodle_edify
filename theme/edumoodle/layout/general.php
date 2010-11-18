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
      
      <?php if ($hasnavbar) { ?>
        <div class="navbar clearfix">
          <div class="breadcrumb"><?php echo $OUTPUT->navbar(); ?></div>
          <div class="navbutton"> <?php echo $PAGE->button; ?></div>
        </div>
      <?php } ?>
  
<?php } ?>
      
    <div id="page-content">
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

  echo $OUTPUT->standard_footer_html();
  
} ?>

</div>     
     

<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>