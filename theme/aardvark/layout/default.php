<?php

$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hassidepre = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-pre', $OUTPUT));
$hassidepost = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-post', $OUTPUT));
$haslogininfo = (empty($PAGE->layout_options['nologininfo']));

$showsidepre = ($hassidepre && !$PAGE->blocks->region_completely_docked('side-pre', $OUTPUT));
$showsidepost = ($hassidepost && !$PAGE->blocks->region_completely_docked('side-post', $OUTPUT));

$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));

$bodyclasses = array();
if ($showsidepre && !$showsidepost) {
    $bodyclasses[] = 'side-pre-only';
} else if ($showsidepost && !$showsidepre) {
    $bodyclasses[] = 'side-post-only';
} else if (!$showsidepost && !$showsidepre) {
    $bodyclasses[] = 'content-only';
}
if ($hascustommenu) {
    $bodyclasses[] = 'has_custom_menu';
}

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <title><?php echo $PAGE->title ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
</head>
<body id="<?php echo $PAGE->bodyid ?>" class="<?php echo $PAGE->bodyclasses.' '.join(' ', $bodyclasses) ?>">
<?php echo $OUTPUT->standard_top_of_body_html() ?>
<div id="page">
<?php if ($hasheading || $hasnavbar) { ?>
    <div id="page-header">
        <?php if ($hasheading) { ?>

        	<div id="logo">

			        <div class="headermenu">
			        <?php echo $OUTPUT->lang_menu();?>
			        <?php echo $PAGE->headingmenu ?>
					</div>
			</div>
		<?php } ?>
	</div>
	<!-- END OF HEADER -->
		<div id="aardvark-menu">

			<ul>
				<li><a href="<?php echo $CFG->wwwroot ?>" title="Home"><img src="<?php echo $CFG->httpswwwroot.'/theme/'.current_theme() ?>/pix/pix_core/menu/home_icon.png"  alt="Icon" title="Icon" /></a></li>
				<li><?php include('loginout.php');?></li>
           </ul>
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
                  <div id="region-main-pad">
                    <div id="region-main">
                      <div class="region-content">
                            <?php echo core_renderer::MAIN_CONTENT_TOKEN ?>
                      </div>
                    </div>
                  </div>
                </div>

                <?php if ($hassidepre) { ?>
                <div id="region-pre" class="block-region">
                   <div class="region-content">
                        <?php echo $OUTPUT->blocks_for_region('side-pre') ?>
                   </div>
                </div>
                <?php } ?>

                <?php if ($hassidepost) { ?>
                <div id="region-post" class="block-region">
                   <div class="region-content">
                        <?php echo $OUTPUT->blocks_for_region('side-post') ?>
                   </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>

<!-- START OF FOOTER -->
    <?php if ($hasfooter) { ?>
    <div id="page-footer" class="clearfix">

		<p class="helplink"><?php echo page_doc_link(get_string('moodledocslink')) ?></p>

			<div class="socioweb-icons">
				<ul>
					<li><a href="#" title="#"><img src="<?php echo $OUTPUT->pix_url('pix_core/header/facebook', 'theme') ?>" height="30" width="30" alt="Facebook" title="Facebook" /></a></li>
					<li><a href="#" title="#"><img src="<?php echo $OUTPUT->pix_url('pix_core/header/twitter', 'theme') ?>" height="30" width="30" alt="Twitter" title="Twitter" /></a></li>
					<li><a href="#" title="#"><img src="<?php echo $OUTPUT->pix_url('pix_core/header/flickr', 'theme') ?>" height="30" width="30" alt="Flickr" title="Flickr" /></a></li>
				</ul>
			</div>


		<p><?php echo $OUTPUT->login_info();?></p>

    </div>
    <?php } ?>
    <div class="clearfix"></div>
</div>
<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>