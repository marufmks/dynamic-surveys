<div class="wrap ds-admin-wrap">
    <h1><?php esc_html_e('Dynamic Surveys', 'dynamic-surveys'); ?></h1>
    
    <h2><?php esc_html_e('Create New Survey', 'dynamic-surveys'); ?></h2>
    <form id="ds-create-survey">
        <div class="ds-form-group">
            <label for="title"><?php esc_html_e('Survey Title', 'dynamic-surveys'); ?></label>
            <input type="text" id="title" name="title" required>
        </div>
        
        <div class="ds-form-group">
            <label for="question"><?php esc_html_e('Question', 'dynamic-surveys'); ?></label>
            <input type="text" id="question" name="question" required>
        </div>
        
        <div class="ds-form-group">
            <label><?php esc_html_e('Options', 'dynamic-surveys'); ?></label>
            <div id="ds-options">
                <div class="option-row">
                    <input type="text" name="options[]" required>
                    <button type="button" class="ds-remove-option"><?php esc_html_e('Remove', 'dynamic-surveys'); ?></button>
                </div>
                <div class="option-row">
                    <input type="text" name="options[]" required>
                    <button type="button" class="ds-remove-option"><?php esc_html_e('Remove', 'dynamic-surveys'); ?></button>
                </div>
            </div>
            <button type="button" id="ds-add-option" class="button"><?php esc_html_e('Add Option', 'dynamic-surveys'); ?></button>
        </div>
        
        <button type="submit" class="button button-primary"><?php esc_html_e('Create Survey', 'dynamic-surveys'); ?></button>
    </form>
    
    <h2><?php esc_html_e('Existing Surveys', 'dynamic-surveys'); ?></h2>
    <table class="ds-survey-table">
        <thead>
            <tr>
                <th><?php esc_html_e('Title', 'dynamic-surveys'); ?></th>
                <th><?php esc_html_e('Question', 'dynamic-surveys'); ?></th>
                <th><?php esc_html_e('Status', 'dynamic-surveys'); ?></th>
                <th><?php esc_html_e('Shortcode', 'dynamic-surveys'); ?></th>
                <th><?php esc_html_e('Actions', 'dynamic-surveys'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $surveys = DS_Survey_Manager::get_all_surveys();
            foreach ($surveys as $survey) :
            ?>
            <tr>
                <td><?php echo esc_html($survey->title); ?></td>
                <td><?php echo esc_html($survey->question); ?></td>
                <td><?php echo esc_html($survey->status); ?></td>
                <td><code title="<?php esc_attr_e('Click to copy shortcode', 'dynamic-surveys'); ?>">[dynamic_survey id="<?php echo esc_attr($survey->id); ?>"]</code></td>
                <td>
                    <button class="button ds-delete-survey" data-id="<?php echo esc_attr($survey->id); ?>">
                        <?php esc_html_e('Delete', 'dynamic-surveys'); ?>
                    </button>
                    <button class="button ds-toggle-status" data-id="<?php echo esc_attr($survey->id); ?>">
                        <?php echo $survey->status === 'open' ? esc_html__('Close', 'dynamic-surveys') : esc_html__('Open', 'dynamic-surveys'); ?>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div> 