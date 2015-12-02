<?php

/*
 *  © Chris How, Primesolid 2015
 *  All rights reserved.
 */

class MCG_Theme {

    public function __construct() {
	$this->cleanup_wordpress_junk();
	$this->setup_theme_supports();
	$this->add_scripts_and_styles();
	$this->move_scripts_to_page_bottom();
	$this->cache_bust_scripts_and_styles();
	$this->disable_categories_and_tags();
    }

    protected function disable_categories_and_tags() {
	add_action('init', function () {
	    register_taxonomy('category', array());
	});
	unregister_widget('WP_Widget_Categories');


	add_action('init', function () {
	    register_taxonomy('post_tag', array());
	});
    }

    protected function cleanup_wordpress_junk() {
	$this->disable_comments();
	$this->disable_emojis();
	$this->hide_dashboard_clutter();
    }

    protected function setup_theme_supports() {
	/*
	 * Let WordPress manage the document title.
	 * By adding theme support, we declare that this theme does not use a
	 * hard-coded <title> tag in the document head, and expect WordPress to
	 * provide it for us.
	 */
	add_theme_support('title-tag');

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
	 */
	add_theme_support('post-thumbnails');

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support('html5', array(
//	    'search-form',
//	    'comment-form',
//	    'comment-list',
//	    'gallery',
	    'caption',
	));
    }

    protected function disable_comments() {
	// Hide 'Comments' admin menu item
	add_action('admin_menu', function() {
	    remove_menu_page('edit-comments.php');   //Comments
	    remove_submenu_page('options-general.php', 'options-discussion.php'); // Settings -> Discussion
	});

	// Disable support for comments and trackbacks in post types
	add_action('admin_init', function() {
	    $post_types = get_post_types();
	    foreach ($post_types as $post_type) {
		if (post_type_supports($post_type, 'comments')) {
		    remove_post_type_support($post_type, 'comments');
		    remove_post_type_support($post_type, 'trackbacks');
		}
	    }
	});

	$df_disable_comments_status = function () {
	    return false;
	};

	add_filter('comments_open', $df_disable_comments_status, 20, 2);
	add_filter('pings_open', $df_disable_comments_status, 20, 2);

	// Hide existing comments
	$df_disable_comments_hide_existing_comments = function($comments) {
	    $comments = array();
	    return $comments;
	};

	add_filter('comments_array', $df_disable_comments_hide_existing_comments, 10, 2);

	// Redirect any user trying to access comments page
	add_action('admin_init', function() {
	    global $pagenow;
	    if ($pagenow === 'edit-comments.php' || $pagenow === 'options-discussion.php') {
		wp_redirect(admin_url());
		exit;
	    }
	});

	// Remove comments metabox from dashboard
	add_action('admin_init', function() {
	    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
	});

	// Remove comments links from admin bar
	add_action('admin_bar_menu', function($wp_admin_bar) {
	    $wp_admin_bar->remove_node('comments');
	}, 999);
    }

    protected function disable_emojis() {
	add_action('init', function() {
	    // all actions related to emojis
	    remove_action('admin_print_styles', 'print_emoji_styles');
	    remove_action('wp_head', 'print_emoji_detection_script', 7);
	    remove_action('admin_print_scripts', 'print_emoji_detection_script');
	    remove_action('wp_print_styles', 'print_emoji_styles');
	    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
	    remove_filter('the_content_feed', 'wp_staticize_emoji');
	    remove_filter('comment_text_rss', 'wp_staticize_emoji');

	    // filter to remove TinyMCE emojis
	    add_filter('tiny_mce_plugins', function ( $plugins ) {
		if (is_array($plugins)) {
		    return array_diff($plugins, array('wpemoji'));
		} else {
		    return array();
		}
	    });
	});
    }

    protected function hide_dashboard_clutter() {
	add_action('wp_dashboard_setup', function () {
	    global $wp_meta_boxes;

	    // Remove the quickpress widget
	    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);

	    // Remove the incoming links widget
	    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);

	    // Remove the welcome widget
	    remove_action('welcome_panel', 'wp_welcome_panel');
	});
    }

    function move_scripts_to_page_bottom() {
	remove_action('wp_head', 'wp_print_scripts');
	remove_action('wp_head', 'wp_print_head_scripts', 9);
	remove_action('wp_head', 'wp_enqueue_scripts', 1);
	add_action('wp_footer', 'wp_print_scripts', 5);
	add_action('wp_footer', 'wp_enqueue_scripts', 5);
	add_action('wp_footer', 'wp_print_head_scripts', 5);
    }

    protected function cache_bust_scripts_and_styles() {
	$ds_filename_based_cache_busting = function ( $src ) {

	    // Don't touch admin scripts
	    if (is_admin()) {
		return $src;
	    }

	    return preg_replace(
		    '/\.(js|css)\?ver=(.+)$/', '.$2.$1', $src
	    );
	};

	add_filter('script_loader_src', $ds_filename_based_cache_busting);
	add_filter('style_loader_src', $ds_filename_based_cache_busting);
    }

    protected function add_scripts_and_styles() {
	add_action('init', function() {
	    if (!is_admin()) {
		switch (ENV) {
		    case 'dev':
			wp_enqueue_style('mcg-normalise', get_template_directory_uri() . '/css/normalise.css');
			wp_enqueue_style('mcg-style', get_template_directory_uri() . '/css/style.css', NULL, filemtime(get_stylesheet_directory() . '/css/style.css'));
			wp_enqueue_style('mcg-style-tablet', get_template_directory_uri() . '/css/tablet.css', NULL, filemtime(get_stylesheet_directory() . '/css/tablet.css'));
			wp_enqueue_style('mcg-style-phone', get_template_directory_uri() . '/css/phone.css', NULL, filemtime(get_stylesheet_directory() . '/css/phone.css'));

			wp_register_script(
				'mcg-plugins', get_template_directory_uri() . '/js/plugins.js', array('jquery'), filemtime(get_stylesheet_directory() . '/js/plugins.js'), true
			);
			wp_register_script(
				'mcg-script', get_template_directory_uri() . '/js/scripts.js', array('mcg-plugins'), filemtime(get_stylesheet_directory() . '/js/scripts.js'), true
			);
			wp_enqueue_script('mcg-script');
			break;

		    default:
			wp_enqueue_style('mcg-all', get_template_directory_uri() . '/css/all.min.css', NULL, filemtime(get_stylesheet_directory() . '/css/all.min.css'));
			wp_register_script(
				'mcg-scripts-all', get_template_directory_uri() . '/js/all.min.js', array('jquery'), filemtime(get_stylesheet_directory() . '/js/all.min.js'), true
			);
			wp_enqueue_script('mcg-scripts-all');
			break;
		}
	    } else {
		wp_enqueue_style('mcg-admin', get_template_directory_uri() . '/css/admin.css', NULL, filemtime(get_stylesheet_directory() . '/css/admin.css'));
		wp_register_script(
			'mcg-admin', get_template_directory_uri() . '/js/admin.js', array('jquery'), filemtime(get_stylesheet_directory() . '/js/admin.js'), true
		);
		wp_enqueue_script('mcg-admin');
	    }
	});

    }

}
