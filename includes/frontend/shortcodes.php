<?php
if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('dynamic_survey', 'ds_survey_shortcode');

function ds_survey_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => 0
    ), $atts);
    
    if (!$atts['id']) {
        return esc_html__('Invalid survey ID', 'dynamic-surveys');
    }
    
    $survey = DS_Survey_Manager::get_survey($atts['id']);
    if (!$survey) {
        return esc_html__('Survey not found', 'dynamic-surveys');
    }
    
    if ($survey->status !== 'open') {
        return esc_html__('This survey is currently closed', 'dynamic-surveys');
    }
    
    $user_id = get_current_user_id();
    if (!$user_id) {
        return esc_html__('Please log in to participate in the survey', 'dynamic-surveys');
    }
    
    $has_voted = DS_Survey_Manager::has_user_voted($survey->id, $user_id);
    
    // Get survey results if user has voted
    $results = null;
    if ($has_voted) {
        $results = ds_get_survey_results($survey->id);
    }
    
    ob_start();
    include DS_PLUGIN_PATH . 'templates/frontend/survey.php';
    return ob_get_clean();
} 