<?php
if (!defined('ABSPATH')) {
    exit;
}

// Add AJAX handlers for frontend actions
add_action('wp_ajax_ds_submit_vote', 'ds_handle_vote_submission');
add_action('wp_ajax_nopriv_ds_submit_vote', 'ds_handle_vote_submission');

function ds_handle_vote_submission() {
    check_ajax_referer('ds-frontend-nonce', 'nonce');
    
    $survey_id = intval($_POST['survey_id']);
    $option_id = sanitize_text_field($_POST['option']);
    $user_id = get_current_user_id();
    
    if (!$user_id || DS_Survey_Manager::has_user_voted($survey_id, $user_id)) {
        wp_send_json_error(['message' => 'You have already voted on this survey']);
    }
    
    global $wpdb;
    $result = $wpdb->insert(
        "{$wpdb->prefix}ds_votes",
        array(
            'survey_id' => $survey_id,
            'user_id' => $user_id,
            'option_id' => $option_id,
            'ip_address' => $_SERVER['REMOTE_ADDR']
        )
    );

    if ($result === false) {
        wp_send_json_error(['message' => 'Failed to submit vote']);
    }
    
    $results = ds_get_survey_results($survey_id);
    wp_send_json_success([
        'message' => 'Vote submitted successfully!',
        'results' => $results
    ]);
}

function ds_get_survey_results($survey_id) {
    global $wpdb;
    
    $survey = DS_Survey_Manager::get_survey($survey_id);
    $options = json_decode($survey->options);
    
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT option_id, COUNT(*) as count 
        FROM {$wpdb->prefix}ds_votes 
        WHERE survey_id = %d 
        GROUP BY option_id",
        $survey_id
    ));
    
    $data = array(
        'type' => 'pie',
        'data' => array(
            'labels' => $options,
            'datasets' => array(
                array(
                    'data' => array_fill(0, count($options), 0),
                    'backgroundColor' => array(
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF'
                    )
                )
            )
        ),
        'options' => array(
            'responsive' => true
        )
    );
    
    foreach ($results as $result) {
        $data['data']['datasets'][0]['data'][$result->option_id] = intval($result->count);
    }
    
    return $data;
} 