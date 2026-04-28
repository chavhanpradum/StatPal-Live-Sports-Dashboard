<?php
/**
 * Plugin Name: StatPal Sports Integration
 * Description: Integrates StatPal Sports API for live scores, odds and results.
 * Version: 1.0.0
 * Author: cmsMinds
 */


 if (!defined('ABSPATH')) exit;

define('STATPAL_API_KEY', '');
define('STATPAL_ODDS_API_KEY', '');
 define('STATPAL_PATH', plugin_dir_path(__FILE__));
 define('STATPAL_URL', plugin_dir_url(__FILE__));
 
 require_once STATPAL_PATH . 'includes/class-statpal-api.php';
 require_once STATPAL_PATH . 'includes/class-render.php';
 require_once STATPAL_PATH . 'includes/class-ajax.php';
 require_once STATPAL_PATH . 'includes/class-statpal-settings.php';
 
 add_action('wp_enqueue_scripts', function(){
    $style_version = file_exists(STATPAL_PATH . 'assets/style.css') ? filemtime(STATPAL_PATH . 'assets/style.css') : null;
    $script_version = file_exists(STATPAL_PATH . 'assets/statpal.js') ? filemtime(STATPAL_PATH . 'assets/statpal.js') : null;

    wp_enqueue_style('statpal-style', STATPAL_URL . 'assets/style.css', [], $style_version);
    wp_enqueue_script('statpal-script', STATPAL_URL . 'assets/statpal.js', [], $script_version, true);
    wp_localize_script('statpal-script', 'StatPalAjax', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('statpal_nonce')
    ]);
 });
 
 new StatPal_Render();
 new StatPal_Ajax();