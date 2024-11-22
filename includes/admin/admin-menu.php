<?php
if (!defined('ABSPATH')) {
    exit;
}

// Hook the menu function to admin_menu
add_action('admin_menu', 'ds_add_admin_menu');

function ds_add_admin_menu() {
    $hook = add_submenu_page(
        'tools.php',
        'Dynamic Surveys',
        'Dynamic Surveys',
        'manage_options',
        'dynamic-surveys',
        'ds_admin_page'
    );
    
    // Load scripts only on our plugin page
    add_action("admin_print_scripts-{$hook}", 'ds_enqueue_admin_scripts');
}

function ds_admin_page() {
    include DS_PLUGIN_PATH . 'templates/admin/main.php';
}

function ds_enqueue_admin_scripts() {
    // Enqueue styles with correct paths
    wp_enqueue_style('ds-admin-css', DS_PLUGIN_URL.'/assets/css/admin.css');
    wp_enqueue_style('toastr-admin-css', DS_PLUGIN_URL.'/assets/css/toastr.min.css');
    
    // Enqueue scripts with correct paths
    wp_enqueue_script('jquery');
    wp_enqueue_script('toastr-admin-js', DS_PLUGIN_URL.'/assets/js/toastr.min.js', ['jquery'], DS_VERSION, true); 
    wp_enqueue_script('ds-admin-js', DS_PLUGIN_URL.'/assets/js/admin.js' , ['jquery', 'toastr-admin-js'], DS_VERSION, true);
    
    wp_localize_script('ds-admin-js', 'dsAdmin', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ds_admin_nonce')
    ]);
}

