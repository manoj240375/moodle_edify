<?php

$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hassidepre = $PAGE->blocks->region_has_content('side-pre', $OUTPUT);
$hassidepost = $PAGE->blocks->region_has_content('side-post', $OUTPUT);

$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));

$bodyclasses = array();
if ($hassidepre && !$hassidepost) {
    $bodyclasses[] = 'side-pre-only';
} else if ($hassidepost && !$hassidepre) {
    $bodyclasses[] = 'side-post-only';
} else if ($hassidepost && $hassidepre) {
    $bodyclasses[] = 'both-pre-post';
} else if (!$hassidepost && !$hassidepre) {
    $bodyclasses[] = 'content-only';
}

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <title><?php echo $PAGE->title ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
    <?php echo $OUTPUT->standard_head_html();	?>
    
    <!-- randomiser for the backgrounds -->
    <?php 
	
	$totalImages = 10;
	$randomFirst = rand(1,$totalImages);
	$image = "landscapebg" . $randomFirst;
	
	echo "<style type='text/css'>";
	echo "	html,body { background:#333333 url(" . $OUTPUT->pix_url($image, 'theme') . ") fixed top left no-repeat;}";
	echo "</style>"
	
	?>

</head>
<body id="<?php echo $PAGE->bodyid ?>" class="<?php echo $PAGE->bodyclasses.' '.join(' ', $bodyclasses) ?>">

<?php echo $OUTPUT->standard_top_of_body_html() ?>

<div id="topbars"></div>
<?php if ($hascustommenu) { ?>
<div id="custommenu"><?php echo $custommenu; ?></div>
<?php } ?>

<div id="page">

<?php if ($hasheading || $hasnavbar) { ?>
    <div id="page-header">
        <?php if ($hasheading) { ?>
        <h1 class="headermain"><?php echo $PAGE->heading ?></h1>
        <div class="headermenu"><?php
    		echo $OUTPUT->login_info();
	        if (!empty($PAGE->layout_options['langmenu'])) {
                echo $OUTPUT->lang_menu();
            }
	        echo $PAGE->headingmenu		
			?>
			<?php if ($hasnavbar) { ?>
                <?php echo $PAGE->button; ?>
            <?php } ?>
        </div><?php } ?>
        <div class="breadcrumb"><?php echo $OUTPUT->navbar(); ?></div>
    </div>
<?php } ?>
<!-- END OF HEADER -->

    <div id="page-content"> 	<!-- START OF PAGE CONTENT -->

		<?php if ($hassidepost) { ?>
            <div id="region-post" class="block-region" >
                <div class="region-content">
                    <?php echo $OUTPUT->blocks_for_region('side-post'); ?>
                </div>
            </div>
        <?php } ?>    

		<?php if ($hassidepre) { ?>
            <div id="region-pre" class="block-region" >
                <div class="region-content">
                    <?php echo $OUTPUT->blocks_for_region('side-pre'); ?>
                </div>
            </div>
        <?php } ?>    
        

        <div id="region-main">
	
            <div id="contentCol" class="region-content">
                <?php echo core_renderer::MAIN_CONTENT_TOKEN ?>
            </div>
    
        </div>

		<!-- START OF FOOTER -->
		<?php if ($hasfooter) { ?>
        <div id="page-footer" class="clearfix">
            <?php
            echo $OUTPUT->login_info();
            echo $OUTPUT->standard_footer_html();
            ?>
            <p><img id="institutionalLogo" src="<?php echo $OUTPUT->pix_url('logo', 'theme') ?>" alt="Institution's logo" /></p>
        </div>
    <?php } ?>
	
    </div> 					<!-- END OF PAGE CONTENT -->

</div>



<?php echo $OUTPUT->standard_end_of_body_html() ?>



</body>
</html>