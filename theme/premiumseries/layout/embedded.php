<?php echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <title><?php echo $PAGE->title ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
</head>
<body id="<?php echo $PAGE->bodyid ?>" class="<?php echo $PAGE->bodyclasses ?>">
<?php echo $OUTPUT->standard_top_of_body_html() ?>

<div id="page">



    <div id="content" class="clearfix">
        <?php echo core_renderer::MAIN_CONTENT_TOKEN ?>
    </div>

</div>
<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>