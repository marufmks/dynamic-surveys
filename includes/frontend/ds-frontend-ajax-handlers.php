<?php
if (!defined('ABSPATH')) {
    exit;
}

if(!class_exists('Ds_frontend_ajax_handlers')) {  
    class Ds_frontend_ajax_handlers {
        public function __construct() {
    
            add_action('wp_ajax_ds_submit_vote', array($this, 'ds_handle_vote_submission'));
            add_action('wp_ajax_nopriv_ds_submit_vote', array($this, 'ds_handle_vote_submission'));
        }
        function ds_get_client_ip() {
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                return $_SERVER['HTTP_CLIENT_IP'];
            }
            
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $forwarded_ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                return trim($forwarded_ips[0]);
            }
            
            // If on localhost, return a dummy IP for testing
            if ($_SERVER['REMOTE_ADDR'] == '::1' || $_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
                return '192.168.1.' . rand(2, 254); // Generate a random local IP for testing
            }
            
            return $_SERVER['REMOTE_ADDR'];
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
        
        function ds_handle_vote_submission() {
            check_ajax_referer('ds-frontend-nonce', 'nonce');
            
            $survey_id = intval($_POST['survey_id']);
            $option_id = sanitize_text_field($_POST['option']);
            $user_id = get_current_user_id();
            $ip_address =$this->ds_get_client_ip();
            
            // Check for existing votes
            global $wpdb;
            $existing_vote = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}ds_votes 
                 WHERE survey_id = %d 
                 AND (user_id = %d OR (user_id = 0 AND ip_address = %s))",
                $survey_id,
                $user_id,
                $ip_address
            ));
            
            if ($existing_vote) {
                wp_send_json_error(['message' => __('You have already voted on this survey', 'dynamic-surveys')]);
            }
            
            // Insert vote
            $result = $wpdb->insert(
                "{$wpdb->prefix}ds_votes",
                array(
                    'survey_id' => $survey_id,
                    'user_id' => $user_id,
                    'option_id' => $option_id,
                    'ip_address' => $ip_address
                ),
                array('%d', '%d', '%s', '%s')
            );
        
            if ($result === false) {
                wp_send_json_error(['message' => __('Failed to submit vote', 'dynamic-surveys')]);
            }
            
            $results =$this->ds_get_survey_results($survey_id);
            wp_send_json_success([
                'message' => __('Vote submitted successfully!', 'dynamic-surveys'),
                'results' => $results
            ]);
        }
        
        
    }
}

new Ds_frontend_ajax_handlers();

