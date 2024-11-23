<?php
if (!defined('ABSPATH')) {
    exit;
}

if(!class_exists('Ds_frontend')) {  
    class Ds_frontend
    {
        public function __construct()
        {
            add_action('wp_enqueue_scripts', array($this, 'frontend_scripts'));
            add_shortcode('dynamic_survey', array($this, 'ds_survey_shortcode'));
        }
    
    
        function frontend_scripts()
        {
            wp_enqueue_script('wp-i18n');
            wp_enqueue_script('wp-escape-html');
    
            wp_enqueue_style('toastr-css', DS_PLUGIN_URL . '/assets/css/toastr.min.css');
            wp_enqueue_style('ds-frontend-style', DS_PLUGIN_URL . 'assets/css/ds-frontend.css', array(), DS_VERSION);
    
            wp_enqueue_script('chart-js', DS_PLUGIN_URL . 'assets/js/chart.js', array(), DS_VERSION, true);
            wp_enqueue_script('toastr-js', DS_PLUGIN_URL . '/assets/js/toastr.min.js', ['jquery'], DS_VERSION, true);
            wp_enqueue_script(
                'ds-frontend-script',
                DS_PLUGIN_URL . 'assets/js/ds-frontend.js',
                array('jquery', 'chart-js', 'toastr-js', 'wp-i18n', 'wp-escape-html'),
                DS_VERSION,
                true
            );
    
            wp_set_script_translations('ds-frontend-script', 'dynamic-surveys');
    
            wp_localize_script('ds-frontend-script', 'dsFrontend', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ds-frontend-nonce')
            ));
        }
    
    
    
        function ds_survey_shortcode($atts)
        {
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
                return sprintf(
                    '<div class="ds-message">%s</div>',
                    esc_html__('This survey is currently closed', 'dynamic-surveys')
                );
            }
    
            $user_id = get_current_user_id();
            if (!$user_id) {
                $login_url = wp_login_url(get_permalink());
                return sprintf(
                    '<div class="ds-message">%s<br><a href="%s" class="ds-login-link">%s</a></div>',
                    esc_html__('Please log in to participate in the survey', 'dynamic-surveys'),
                    esc_url($login_url),
                    esc_html__('Click here to login', 'dynamic-surveys')
                );
            }
    
            $has_voted = DS_Survey_Manager::has_user_voted($survey->id, $user_id);
    
            $results = null;
            if ($has_voted) {
                global $wpdb;
                $options = json_decode($survey->options, true);
                $votes_count = array();
    
                foreach ($options as $index => $option) {
                    $count = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM {$wpdb->prefix}ds_votes 
                    WHERE survey_id = %d AND option_id = %s",
                        $survey->id,
                        $index
                    ));
                    $votes_count[$index] = (int) $count;
                }
    
                $results = array(
                    'type' => 'pie',
                    'data' => array(
                        'labels' => $options,
                        'datasets' => array(
                            array(
                                'data' => array_values($votes_count),
                                'backgroundColor' => array(
                                    '#FF6384',
                                    '#36A2EB',
                                    '#FFCE56',
                                    '#4BC0C0',
                                    '#9966FF',
                                    '#FF9F40'
                                )
                            )
                        )
                    ),
                    'options' => array(
                        'responsive' => true
                    )
                );
            }
    
            ob_start();
            include DS_PLUGIN_PATH . 'templates/frontend/ds-frontend-display.php';
            return ob_get_clean();
        }
    }
}

new Ds_frontend();