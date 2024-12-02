    <?php
    if (! defined('ABSPATH')) {
        exit;
    }

    if (! class_exists('Ds_Admin_Ajax_Handlers')) {

        class Ds_Admin_Ajax_Handlers
        {

            public function __construct()
            {
                add_action('wp_ajax_ds_create_survey', array($this, 'ds_admin_create_survey_handler'));
                add_action('wp_ajax_ds_delete_survey', array($this, 'ds_admin_delete_survey_handler'));
                add_action('wp_ajax_ds_toggle_survey_status', array($this, 'ds_admin_toggle_survey_status_handler'));
                add_action('wp_ajax_ds_export_survey', array($this, 'ds_admin_export_survey_handler'));
            }

            public function ds_admin_create_survey_handler()
            {
                if (! isset($_POST['nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ds_admin_nonce')) {
                    wp_send_json_error(array('message' => esc_html__('Security check failed', 'dynamic-surveys')));
                }


                // Check user capabilities
                if (! current_user_can('manage_options')) {
                    wp_send_json_error(array('message' => esc_html__('Insufficient permissions', 'dynamic-surveys')));
                }

                // Validate required fields
                if (
                    ! isset($_POST['title'], $_POST['question'], $_POST['options']) ||
                    empty($_POST['title']) || empty($_POST['question']) || empty($_POST['options'])
                ) {
                    wp_send_json_error(array('message' => esc_html__('Please fill in all required fields', 'dynamic-surveys')));
                }

                // Sanitize input
                $title    = sanitize_text_field(wp_unslash($_POST['title']));
                $question = sanitize_text_field(wp_unslash($_POST['question']));
                $options  = array_map('sanitize_text_field', wp_unslash($_POST['options']));

                global $wpdb;
                $table_name = $wpdb->prefix . 'ds_surveys';

                 $result = $wpdb->insert(
                    $table_name,
                    array(
                        'title'      => $title,
                        'question'   => $question,
                        'options'    => wp_json_encode($options),
                        'status'     => 'open',
                        'created_at' => current_time('mysql'),
                    ),
                    array('%s', '%s', '%s', '%s', '%s')
                );

                if (false === $result) {
                    wp_send_json_error(array('message' => esc_html__('Failed to create survey', 'dynamic-surveys')));
                }

                wp_send_json_success(array(
                    'message' => esc_html__('Survey created successfully!', 'dynamic-surveys'),
                    'survey'  => array(
                        'id'         => $wpdb->insert_id,
                        'title'      => $title,
                        'question'   => $question,
                        'options'    => $options,
                        'status'     => 'open',
                        'created_at' => current_time('mysql'),
                    ),
                ));
            }


            public function ds_admin_delete_survey_handler()
            {
                // Verify nonce
                if (! isset($_POST['nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ds_admin_nonce')) {
                    wp_send_json_error(array('message' => esc_html__('Security check failed', 'dynamic-surveys')));
                }


                // Check user capabilities
                if (! current_user_can('manage_options')) {
                    wp_send_json_error(array('message' => esc_html__('Insufficient permissions', 'dynamic-surveys')));
                }

                if (! isset($_POST['survey_id'])) {
                    wp_send_json_error(array('message' => esc_html__('Survey ID is required', 'dynamic-surveys')));
                }

                $survey_id = intval(wp_unslash($_POST['survey_id']));

                global $wpdb;

                // Delete survey
                $result = $wpdb->delete(
                    $wpdb->prefix . 'ds_surveys',
                    array('id' => $survey_id),
                    array('%d')
                );

                if (false === $result) {
                    wp_send_json_error(array('message' => esc_html__('Failed to delete survey', 'dynamic-surveys')));
                }

                // Also delete related votes
                $wpdb->delete(
                    $wpdb->prefix . 'ds_votes',
                    array('survey_id' => $survey_id),
                    array('%d')
                );

                wp_send_json_success(array('message' => esc_html__('Survey deleted successfully', 'dynamic-surveys')));
            }

            public function ds_admin_toggle_survey_status_handler()
            {
                // Verify nonce
                if (! isset($_POST['nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ds_admin_nonce')) {
                    wp_send_json_error(array('message' => esc_html__('Security check failed', 'dynamic-surveys')));
                }


                // Check user capabilities
                if (! current_user_can('manage_options')) {
                    wp_send_json_error(array('message' => esc_html__('Insufficient permissions', 'dynamic-surveys')));
                }

                if (! isset($_POST['survey_id'], $_POST['current_status'])) {
                    wp_send_json_error(array('message' => esc_html__('Required fields missing', 'dynamic-surveys')));
                }

                $survey_id      = intval(wp_unslash($_POST['survey_id']));
                $current_status = sanitize_text_field(wp_unslash($_POST['current_status']));
                $new_status     = ('open' === $current_status) ? 'closed' : 'open';

                global $wpdb;

                $result = $wpdb->update(
                    $wpdb->prefix . 'ds_surveys',
                    array('status' => $new_status),
                    array('id' => $survey_id),
                    array('%s'),
                    array('%d')
                );

                if (false === $result) {
                    wp_send_json_error(array('message' => esc_html__('Failed to update survey status', 'dynamic-surveys')));
                }

                wp_send_json_success(array(
                    'message'    => esc_html__('Survey status updated successfully', 'dynamic-surveys'),
                    'new_status' => $new_status,
                ));
            }

            public function ds_admin_export_survey_handler()
            {
                // Verify nonce
                if (! isset($_POST['nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ds_admin_nonce')) {
                    wp_send_json_error(array('message' => esc_html__('Security check failed', 'dynamic-surveys')));
                }


                // Check user capabilities
                if (! current_user_can('manage_options')) {
                    wp_die(esc_html__('You do not have sufficient permissions', 'dynamic-surveys'));
                }

                if (! isset($_GET['survey_id'])) {
                    wp_die(esc_html__('Invalid survey ID', 'dynamic-surveys'));
                }

                $survey_id = intval(wp_unslash($_GET['survey_id']));

                global $wpdb;

                $survey = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}ds_surveys WHERE id = %d",
                    $survey_id
                ));

                if (! $survey) {
                    wp_die(esc_html__('Survey not found', 'dynamic-surveys'));
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

                // Initialize WP_Filesystem
                global $wp_filesystem;
                if (! function_exists('WP_Filesystem')) {
                    require_once ABSPATH . 'wp-admin/includes/file.php';
                }
                WP_Filesystem();

                // Use WP_Filesystem for output
                $temp_file = wp_tempnam();
                if (! $temp_file) {
                    wp_die(esc_html__('Unable to create a temporary file.', 'dynamic-surveys'));
                }

                $output = '';
                // Add UTF-8 BOM for proper Excel encoding
                $output .= chr(0xEF) . chr(0xBB) . chr(0xBF);

                // CSV Headers
                $output .= implode(',', array(
                    esc_html__('Question', 'dynamic-surveys'),
                    esc_html__('Option Selected', 'dynamic-surveys'),
                    esc_html__('User Email', 'dynamic-surveys'),
                    esc_html__('Display Name', 'dynamic-surveys'),
                    esc_html__('Created At', 'dynamic-surveys'),
                )) . "\n";

                // CSV Data Rows
                foreach ($results as $row) {
                    $output .= implode(',', array(
                        $survey->question,
                        $row->option_selected,
                        $row->user_email,
                        $row->display_name,
                        $row->created_at,
                    )) . "\n";
                }

                // Save file content
                $wp_filesystem->put_contents($temp_file, $output);

                // Output the file using WP_Filesystem
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename=survey-' . esc_attr($survey_id) . '-results.csv');

                $file_contents = $wp_filesystem->get_contents($temp_file);

                // Escape the file contents for safe output
                if ($file_contents !== false) {
                    echo esc_textarea($file_contents); // Escaping multi-line plain text content for output
                }

                // Delete the temporary file
                wp_delete_file($temp_file);

                exit;
            }
        }
    }

    new Ds_Admin_Ajax_Handlers();
