<?php
if (!defined('ABSPATH')) {
    exit;
}
if(!class_exists('Dynamic_Surveys_Admin_Menu')) {   
    class Dynamic_Surveys_Admin_Menu {
        public function __construct() {
            add_action('admin_menu', array($this, 'add_admin_menu'));
            
        }
        function add_admin_menu() {
            $hook = add_submenu_page(
                'tools.php',
                'Dynamic Surveys',
                'Dynamic Surveys',
                'manage_options',
                'dynamic-surveys',
                array($this, 'ds_admin_page')
            );
            
            add_action("admin_print_scripts-{$hook}", array($this, 'ds_enqueue_admin_scripts'));
        }
    
        function ds_admin_page() {
            include DS_PLUGIN_PATH . 'templates/admin/ds-admin-display.php';
        }
        
        function ds_enqueue_admin_scripts() {
            // Enqueue WordPress scripts
            wp_enqueue_script('wp-i18n');
            wp_enqueue_script('wp-escape-html');
            
            wp_enqueue_style('toastr-admin-css', DS_PLUGIN_URL.'/assets/css/toastr.min.css');
            wp_enqueue_style('ds-admin-css', DS_PLUGIN_URL.'/assets/css/ds-admin.css', array(), DS_VERSION);
            
            wp_enqueue_script('jquery');
            wp_enqueue_script('toastr-admin-js', DS_PLUGIN_URL.'/assets/js/toastr.min.js', ['jquery'], DS_VERSION, true); 
            wp_enqueue_script('ds-admin-js', DS_PLUGIN_URL.'/assets/js/ds-admin.js', ['jquery', 'toastr-admin-js', 'wp-i18n', 'wp-escape-html','wp-util'], DS_VERSION, true);
            
            wp_set_script_translations('ds-admin-js', 'dynamic-surveys');
            
            wp_localize_script('ds-admin-js', 'dsAdmin', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ds_admin_nonce')
            ]);
        }
    }
}

new Dynamic_Surveys_Admin_Menu();






