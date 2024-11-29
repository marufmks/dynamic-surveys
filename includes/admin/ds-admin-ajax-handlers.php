<?php
if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('Ds_Admin_Ajax_Handlers')) {
    class Ds_Admin_Ajax_Handlers
    {
        public function __construct()
        {
            add_action('wp_ajax_ds_create_survey', array($this, 'ds_admin_create_survey_handler'));
            add_action('wp_ajax_ds_delete_survey', array($this, 'ds_admin_delete_survey_handler'));
            add_action('wp_ajax_ds_toggle_survey_status', array($this, 'ds_admin_toggle_survey_status_handler'));
            add_action('wp_ajax_ds_export_survey', array($this, 'ds_admin_export_survey_handler'));
        }
        function ds_admin_create_survey_handler()
        {
            // Verify nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ds_admin_nonce')) {
                wp_send_json_error(['message' => esc_html_e('Security check failed', 'dynamic-surveys')]);
            }
        
            // Check user capabilities
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => esc_html_e('Insufficient permissions', 'dynamic-surveys')]);
            }
        
            // Validate required fields
            if (empty($_POST['title']) || empty($_POST['question']) || empty($_POST['options'])) {
                wp_send_json_error(['message' => esc_html_e('Please fill in all required fields', 'dynamic-surveys')]);
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
                    'options' => wp_json_encode($survey_data['options']),
                    'status' => $survey_data['status'],
                    'created_at' => $survey_data['created_at']
                ],
                ['%s', '%s', '%s', '%s', '%s']
            );
        
            if ($result === false) {
                wp_send_json_error(['message' => esc_html_e('Failed to create survey', 'dynamic-surveys')]);
            }
        
            $survey_data['id'] = $wpdb->insert_id;
        
            wp_send_json_success([
                'message' => esc_html_e('Survey created successfully!', 'dynamic-surveys'),
                'survey' => $survey_data
            ]);
        }
        
        function ds_admin_delete_survey_handler()
        {
            // Verify nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ds_admin_nonce')) {
                wp_send_json_error(['message' => esc_html_e('Security check failed', 'dynamic-surveys')]);
            }
        
            // Check user capabilities
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => esc_html_e('Insufficient permissions', 'dynamic-surveys')]);
            }
        
            $survey_id = intval($_POST['survey_id']);
        
            global $wpdb;
            $result = $wpdb->delete(
                $wpdb->prefix . 'ds_surveys',
                ['id' => $survey_id],
                ['%d']
            );
        
            if ($result === false) {
                wp_send_json_error(['message' => esc_html_e('Failed to delete survey', 'dynamic-surveys')]);
            }
        
            // Also delete related votes
            $wpdb->delete(
                $wpdb->prefix . 'ds_votes',
                ['survey_id' => $survey_id],
                ['%d']
            );
        
            wp_send_json_success(['message' => esc_html_e('Survey deleted successfully', 'dynamic-surveys')]);
        }
        
        function ds_admin_toggle_survey_status_handler()
        {
            // Verify nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ds_admin_nonce')) {
                wp_send_json_error(['message' => esc_html_e('Security check failed', 'dynamic-surveys')]);
            }
        
            // Check user capabilities
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => esc_html_e('Insufficient permissions', 'dynamic-surveys')]);
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
                wp_send_json_error(['message' => esc_html_e('Failed to update survey status', 'dynamic-surveys')]);
            }
        
            wp_send_json_success([
                'message' => esc_html_e('Survey status updated successfully', 'dynamic-surveys'),
                'new_status' => $new_status
            ]);
        }
        
        function ds_admin_export_survey_handler()
        {
            // Check nonce first
            if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'ds_admin_nonce')) {
                wp_die(esc_html_e('Security check failed', 'dynamic-surveys'));
            }
        
            // Check permissions
            if (!current_user_can('manage_options')) {
                wp_die(esc_html_e('You do not have sufficient permissions', 'dynamic-surveys'));
            }
        
            $survey_id = isset($_GET['survey_id']) ? intval($_GET['survey_id']) : 0;
            if (!$survey_id) {
                wp_die(esc_html_e('Invalid survey ID', 'dynamic-surveys'));
            }
        
            global $wpdb;
        
            // Get survey details
            $survey = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}ds_surveys WHERE id = %d",
                $survey_id
            ));
        
            if (!$survey) {
                wp_die(esc_html_e('Survey not found', 'dynamic-surveys'));
            }
        
            // Get votes with user information
            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT v.*, u.user_email, u.display_name 
                 FROM {$wpdb->prefix}ds_votes v 
                 LEFT JOIN {$wpdb->users} u ON v.user_id = u.ID 
                 WHERE v.survey_id = %d 
                 ORDER BY v.created_at",
                $survey_id
            ));
        
            // Set headers for CSV download
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=survey-' . $survey_id . '-results.csv');
            header('Pragma: no-cache');
            header('Expires: 0');
        
            // Create CSV file
            $output = fopen('php://output', 'w');
        
            // Add UTF-8 BOM for proper Excel encoding
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        
            // CSV Headers
            fputcsv($output, array(
                esc_html_e('Question', 'dynamic-surveys'),
                esc_html_e('Option Selected', 'dynamic-surveys'),
                esc_html_e('User Email', 'dynamic-surveys'),
                esc_html_e('User Name', 'dynamic-surveys'),
                esc_html_e('IP Address', 'dynamic-surveys'),
                esc_html_e('Date', 'dynamic-surveys')
            ));
        
            $options = json_decode($survey->options, true);
        
            // Add data rows
            foreach ($results as $row) {
                fputcsv($output, array(
                    $survey->question,
                    isset($options[$row->option_id]) ? $options[$row->option_id] : '',
                    $row->user_email ?: esc_html_e('Anonymous', 'dynamic-surveys'),
                    $row->display_name ?: esc_html_e('Anonymous', 'dynamic-surveys'),
                    $row->ip_address,
                    $row->created_at
                ));
            }
        
            fclose($output);
            exit();
        }
    }
}

new Ds_Admin_Ajax_Handlers();


