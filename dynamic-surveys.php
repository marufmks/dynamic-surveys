<?php
/**
 * @link              https://https://github.com/marufmks
 * @since             1.0.0
 * @package           Dynamic_Surveys
 *
 * @wordpress-plugin
 * Plugin Name:       Dynamic Surveys
 * Plugin URI:        https://github.com/marufmks/dynamic-surveys
 * Description:       A WordPress plugin that allows administrators to create simple surveys and dynamically display aggregated results to users.
 * Version:           1.0.0
 * Author:            Maruf Khan
 * Author URI:        https://https://github.com/marufmks/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       dynamic-surveys
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('DS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('DS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DS_VERSION', '1.0.0');

//load plugin text domain
load_plugin_textdomain('dynamic-surveys', false, DS_PLUGIN_PATH . '/languages');

// Activation hook
register_activation_hook(__FILE__, 'ds_activate_plugin');

function ds_activate_plugin() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Create surveys table
    $sql_surveys = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ds_surveys (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        question text NOT NULL,
        options longtext NOT NULL,
        status varchar(20) DEFAULT 'open',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    // Create votes table
    $sql_votes = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ds_votes (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        survey_id mediumint(9) NOT NULL,
        user_id bigint(20) NOT NULL,
        option_id varchar(32) NOT NULL,
        ip_address varchar(45),
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY survey_user (survey_id, user_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_surveys);
    dbDelta($sql_votes);
}

// Load required files
require_once DS_PLUGIN_PATH . 'includes/admin/admin-menu.php';
require_once DS_PLUGIN_PATH . 'includes/admin/survey-manager.php';
require_once DS_PLUGIN_PATH . 'includes/frontend/shortcodes.php';
require_once DS_PLUGIN_PATH . 'includes/frontend/ajax-handlers.php';
require_once DS_PLUGIN_PATH . 'includes/admin/ajax-handlers.php';


// Enqueue scripts and styles

add_action('wp_enqueue_scripts', 'ds_frontend_scripts');

function ds_frontend_scripts() {
    wp_enqueue_script('wp-i18n');
    wp_enqueue_script('wp-escape-html');
    
    wp_enqueue_style('toastr-css', DS_PLUGIN_URL.'/assets/css/toastr.min.css');
    wp_enqueue_style('ds-frontend-style', DS_PLUGIN_URL . 'assets/css/frontend.css', array(), DS_VERSION);
    
    wp_enqueue_script('chart-js', DS_PLUGIN_URL . 'assets/js/chart.js', array(), DS_VERSION, true);
    wp_enqueue_script('toastr-js', DS_PLUGIN_URL.'/assets/js/toastr.min.js', ['jquery'], DS_VERSION, true); 
    wp_enqueue_script('ds-frontend-script', DS_PLUGIN_URL . 'assets/js/frontend.js', 
        array('jquery', 'chart-js', 'toastr-js', 'wp-i18n', 'wp-escape-html'), 
        DS_VERSION, 
        true
    );
    
    wp_set_script_translations('ds-frontend-script', 'dynamic-surveys');
    
    wp_localize_script('ds-frontend-script', 'dsFrontend', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ds-frontend-nonce')
    ));
} 