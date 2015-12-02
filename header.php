<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
    <head>
	<meta charset="<?php bloginfo('charset'); ?>">

	<link href="//www.google-analytics.com" rel="dns-prefetch">
        <link href="<?php echo get_template_directory_uri(); ?>/i/icon/favicon.ico" rel="shortcut icon">

	<?php
	if(ENV === 'dev') {
	    echo "<link href='" . get_template_directory_uri() . "/css/local-fonts.css' rel='stylesheet' type='text/css' />" . PHP_EOL;
	    
	} else {
	    echo "<link href='//cloud.webtype.com/css/6169560d-2171-4fcb-8562-53068fcd8629.css' rel='stylesheet' type='text/css' />" . PHP_EOL;
	}
	?>


	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="<?php bloginfo('description'); ?>">
	<script src='<?= get_template_directory_uri() ?>/js/lib/modernizr-2.7.1.min.js'></script>
	<meta name="format-detection" content="telephone=no" />

	<?php wp_head(); ?>


	<script>
	</script>
    </head>
    <body <?php body_class(); ?>>