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


// Activation and deactivation
function activate_dynamic_surveys() {
	require_once DS_PLUGIN_PATH . 'includes/class-ds-activator.php';
	Ds_Activator::activate();
}

function deactivate_dynamic_surveys() {
	require_once DS_PLUGIN_PATH . 'includes/class-ds-deactivator.php';
	Ds_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_dynamic_surveys' );
register_deactivation_hook( __FILE__, 'deactivate_dynamic_surveys' );


// Load required files
require_once DS_PLUGIN_PATH . 'includes/admin/class-ds-survey-manager.php';
require_once DS_PLUGIN_PATH . 'includes/admin/ds-admin-menu.php';
require_once DS_PLUGIN_PATH . 'includes/admin/ds-admin-ajax-handlers.php';
require_once DS_PLUGIN_PATH . 'includes/frontend/ds-frontend.php';
require_once DS_PLUGIN_PATH . 'includes/frontend/ds-frontend-ajax-handlers.php';



