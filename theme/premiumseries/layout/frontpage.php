<?php

$hassidepre = $PAGE->blocks->region_has_content('side-pre', $OUTPUT);
$hassidepost = $PAGE->blocks->region_has_content('side-post', $OUTPUT);
$showsidepre = $hassidepre && !$PAGE->blocks->region_completely_docked('side-pre', $OUTPUT);
$showsidepost = $hassidepost && !$PAGE->blocks->region_completely_docked('side-post', $OUTPUT);

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
    <meta name="description" content="<?php echo strip_tags(format_text($SITE->summary, FORMAT_HTML)) ?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
</head>
<body id="<?php echo $PAGE->bodyid ?>" class="<?php echo $PAGE->bodyclasses.' '.join(' ', $bodyclasses) ?>">
<?php echo $OUTPUT->standard_top_of_body_html() ?>

<div id="page">

    <div id="page-header" class="clearfix">

		<div id="logo">
			<h1><a href="#"><span>Premium</span>Series</a></h1>
			<p>From an Original Design By Free CSS Templates</p>
			<p class="socioweb-icon">
				<a href="#" title="#"><img src="<?php echo $OUTPUT->pix_url('images/facebook', 'theme') ?>" height="30" width="30" alt="Facebook" title="Facebook" /></a>
				<a href="#" title="#"><img src="<?php echo $OUTPUT->pix_url('images/twitter', 'theme') ?>" height="30" width="30" alt="Twitter" title="Twitter" /></a>
				<a href="#" title="#"><img src="<?php echo $OUTPUT->pix_url('images/flickr', 'theme') ?>" height="30" width="30" alt="Flickr" title="Flickr" /></a>
            </p>
		</div>
		<div id="top-menu-outer">
		<div id="top-menu">
			<ul class="top-main">
				<li class="current_page_item"><a href="<?php echo $CFG->wwwroot ?>">Homepage</a></li>
				<li><?php include("loginout.php")?></li>
			</ul>
			<ul id="rss-feed">
				<li><a href="<?php echo $CFG->wwwroot ?>/blocks/rss_client/managefeeds.php">RSS Feeds</a></li>
			</ul>
		 </div>
		 </div>

   </div>
<!-- END OF HEADER -->

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
    <div id="page-footer">
		<p class="copyright">&copy;&nbsp;&nbsp;2010 All Rights Reserved &nbsp;&bull;&nbsp;Original Design by <a href="http://www.freecsstemplates.org/">Free CSS Templates</a>.</p>
		<p class="link"><a href="http://moodle.org/mod/data/view.php?id=6552">GPL Themes for Moodle 2.0</a>&nbsp;&#8226;&nbsp;<a href="http://www.gnu.org/licenses/gpl.html">Terms of Use</a></p>

    </div>
    <div class="clearfix"></div>
</div>
<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>