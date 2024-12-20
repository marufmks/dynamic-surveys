<?php
/**
 * @link              https://github.com/marufmks
 * @since             1.0.0
 * @package           Dynamic Surveys
 *
 * @wordpress-plugin
 * Plugin Name:       Dynamic Surveys
 * Description:       Create and manage interactive surveys and polls with customizable options. Display survey results in real-time using beautiful charts. Export survey data to CSV format.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:       7.4
 * Author:            Maruf Khan
 * Author URI:        https://github.com/marufmks/
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       dynamic-surveys
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('DYNAMIC_SURVEYS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('DYNAMIC_SURVEYS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DYNAMIC_SURVEYS_PLUGIN_VERSION', '1.0.0');

// Load plugin text domain
load_plugin_textdomain('dynamic-surveys', false, DYNAMIC_SURVEYS_PLUGIN_PATH . '/languages');

// Activation and deactivation
function dynamic_surveys_activate() {
	require_once DYNAMIC_SURVEYS_PLUGIN_PATH . 'includes/class-dynamic-surveys-activator.php';
	Dynamic_Surveys_Activator::activate();
	
}

function dynamic_surveys_deactivate() {
	require_once DYNAMIC_SURVEYS_PLUGIN_PATH . 'includes/class-dynamic-surveys-deactivator.php';
	Dynamic_Surveys_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'dynamic_surveys_activate' );
register_deactivation_hook( __FILE__, 'dynamic_surveys_deactivate' );

// Load required files
require_once DYNAMIC_SURVEYS_PLUGIN_PATH . 'includes/admin/class-dynamic-surveys-manager.php';
require_once DYNAMIC_SURVEYS_PLUGIN_PATH . 'includes/admin/class-dynamic-surveys-admin-menu.php';
require_once DYNAMIC_SURVEYS_PLUGIN_PATH . 'includes/admin/class-dynamic-surveys-admin-ajax-handlers.php';
require_once DYNAMIC_SURVEYS_PLUGIN_PATH . 'includes/frontend/class-dynamic-surveys-frontend.php';
require_once DYNAMIC_SURVEYS_PLUGIN_PATH . 'includes/frontend/class-dynamic-surveys-frontend-ajax-handlers.php';



