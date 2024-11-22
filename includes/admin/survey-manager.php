<?php
if (!defined('ABSPATH')) {
    exit;
}

class DS_Survey_Manager {
    public static function create_survey($title, $question, $options) {
        global $wpdb;
        
        $data = array(
            'title' => sanitize_text_field($title),
            'question' => sanitize_text_field($question),
            'options' => json_encode(array_map('sanitize_text_field', $options)),
            'status' => 'open'
        );
        
        $wpdb->insert("{$wpdb->prefix}ds_surveys", $data);
        return $wpdb->insert_id;
    }
    
    public static function get_survey($id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ds_surveys WHERE id = %d",
            $id
        ));
    }
    
    public static function get_all_surveys() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ds_surveys ORDER BY created_at DESC");
    }
    
    public static function delete_survey($id) {
        global $wpdb;
        $wpdb->delete("{$wpdb->prefix}ds_surveys", array('id' => $id));
        $wpdb->delete("{$wpdb->prefix}ds_votes", array('survey_id' => $id));
    }
    
    public static function update_survey_status($id, $status) {
        global $wpdb;
        return $wpdb->update(
            "{$wpdb->prefix}ds_surveys",
            array('status' => $status),
            array('id' => $id)
        );
    }
    
    public static function has_user_voted($survey_id, $user_id) {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}ds_votes WHERE survey_id = %d AND user_id = %d",
            $survey_id,
            $user_id
        )) > 0;
    }
} 