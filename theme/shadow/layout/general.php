<?php

$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hassidepre = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-pre', $OUTPUT));
$hassidepost = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-post', $OUTPUT));

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

if (!empty($PAGE->theme->settings->logo)) {
    $logourl = $PAGE->theme->settings->logo;
} else {
    $logourl = $OUTPUT->pix_url('logo', 'theme');
}

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <title><?php echo $PAGE->title ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
</head>

<body id="<?php echo $PAGE->bodyid ?>" class="<?php echo $PAGE->bodyclasses.' '.join(' ', $bodyclasses) ?>">
<?php echo $OUTPUT->standard_top_of_body_html();

echo '<div id="page">';
if ($hasheading || $hasnavbar) { ?>
    <div id="header" class="clearfix">
        <div class="headermain">
            <div class="headermainsx">&nbsp;</div>
            <div class="headermainbg">
                <img src="<?php echo $logourl ?>" title="Custom logo here" alt="Custom logo here" />
                <?php
                echo $OUTPUT->login_info();
                echo $OUTPUT->lang_menu();
                echo $PAGE->headingmenu;
                ?>
            </div>
            <div class="headermaindx">&nbsp;</div>
        </div>
    </div>
    <?php

    if ($hascustommenu) {
        echo '<div id="custommenu" class="menubar">'.$custommenu.'</div>';
    }

    //Accessibility: breadcrumb trail/navbar now a DIV, not a table.
    if ($hasnavbar) {
        echo '<div class="navbar clearfix">';
        echo '    <div class="breadcrumb">'.$OUTPUT->navbar().'</div>';
        echo '    <div class="navbutton">'.$PAGE->button.'</div>';
        echo '</div>';
    }
} ?>

<!-- END OF HEADER -->

    <div id="page-content" class="shrinker">
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
    <div id="footer">
        <?php
        echo '	<div class="footersx">&nbsp;</div>';
        echo '	<div class="footerbg">';
        echo $OUTPUT->login_info();
        //echo $OUTPUT->home_link();
        echo $OUTPUT->standard_footer_html();
        echo '	</div>';
        echo '	<div class="footerdx">&nbsp;</div>';

        echo '<p class="helplink">'.page_doc_link(get_string('moodledocslink')).'</p>';

        echo '<div class="moodlelogo">';
        echo '<img src="'.$OUTPUT->pix_url('moodlelogo20', 'theme').'" title="Moodle logo" alt="Moodle logo" />';
        echo '</div>';
        ?>
    </div>
    <?php } ?>
</div> <!-- closes <div id="page"> -->

<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>