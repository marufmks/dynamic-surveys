<?php
if (!defined('ABSPATH')) {
    exit;
}

// Add AJAX handlers for admin actions
add_action('wp_ajax_ds_create_survey', 'ds_admin_create_survey_handler');
add_action('wp_ajax_ds_delete_survey', 'ds_admin_delete_survey_handler');
add_action('wp_ajax_ds_toggle_survey_status', 'ds_admin_toggle_survey_status_handler');

function ds_admin_create_survey_handler() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ds_admin_nonce')) {
        wp_send_json_error(['message' => 'Security check failed']);
    }

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Insufficient permissions']);
    }

    // Validate required fields
    if (empty($_POST['title']) || empty($_POST['question']) || empty($_POST['options'])) {
        wp_send_json_error(['message' => 'Please fill in all required fields']);
    }

    // Sanitize input
    $title = sanitize_text_field($_POST['title']);
    $question = sanitize_text_field($_POST['question']);
    $options = array_map('sanitize_text_field', $_POST['options']);

    // Create survey
    $survey_data = [
        'title' => $title,
        'question' => $question,
        'options' => $options,
        'status' => 'open',
        'created_at' => current_time('mysql')
    ];

    global $wpdb;
    $table_name = $wpdb->prefix . 'ds_surveys';
    
    // Insert survey
    $result = $wpdb->insert(
        $table_name,
        [
            'title' => $survey_data['title'],
            'question' => $survey_data['question'],
            'options' => json_encode($survey_data['options']),
            'status' => $survey_data['status'],
            'created_at' => $survey_data['created_at']
        ],
        ['%s', '%s', '%s', '%s', '%s']
    );

    if ($result === false) {
        wp_send_json_error(['message' => 'Failed to create survey']);
    }

    $survey_data['id'] = $wpdb->insert_id;
    
    wp_send_json_success([
        'message' => 'Survey created successfully!',
        'survey' => $survey_data
    ]);
}

function ds_admin_delete_survey_handler() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ds_admin_nonce')) {
        wp_send_json_error(['message' => 'Security check failed']);
    }

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Insufficient permissions']);
    }

    $survey_id = intval($_POST['survey_id']);
    
    global $wpdb;
    $result = $wpdb->delete(
        $wpdb->prefix . 'ds_surveys',
        ['id' => $survey_id],
        ['%d']
    );

    if ($result === false) {
        wp_send_json_error(['message' => 'Failed to delete survey']);
    }

    // Also delete related votes
    $wpdb->delete(
        $wpdb->prefix . 'ds_votes',
        ['survey_id' => $survey_id],
        ['%d']
    );

    wp_send_json_success(['message' => 'Survey deleted successfully']);
}

function ds_admin_toggle_survey_status_handler() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ds_admin_nonce')) {
        wp_send_json_error(['message' => 'Security check failed']);
    }

    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Insufficient permissions']);
    }

    $survey_id = intval($_POST['survey_id']);
    $current_status = sanitize_text_field($_POST['current_status']);
    $new_status = $current_status === 'open' ? 'closed' : 'open';
    
    global $wpdb;
    $result = $wpdb->update(
        $wpdb->prefix . 'ds_surveys',
        ['status' => $new_status],
        ['id' => $survey_id],
        ['%s'],
        ['%d']
    );

    if ($result === false) {
        wp_send_json_error(['message' => 'Failed to update survey status']);
    }

    wp_send_json_success([
        'message' => 'Survey status updated successfully',
        'new_status' => $new_status
    ]);
} 