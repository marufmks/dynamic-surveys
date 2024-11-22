<div class="wrap ds-admin-wrap">
    <h1>Dynamic Surveys</h1>
    
    <h2>Create New Survey</h2>
    <form id="ds-create-survey">
        <div class="ds-form-group">
            <label for="title">Survey Title</label>
            <input type="text" id="title" name="title" required>
        </div>
        
        <div class="ds-form-group">
            <label for="question">Question</label>
            <input type="text" id="question" name="question" required>
        </div>
        
        <div class="ds-form-group">
            <label>Options</label>
            <div id="ds-options">
                <div class="option-row">
                    <input type="text" name="options[]" required>
                    <button type="button" class="ds-remove-option">Remove</button>
                </div>
                <div class="option-row">
                    <input type="text" name="options[]" required>
                    <button type="button" class="ds-remove-option">Remove</button>
                </div>
            </div>
            <button type="button" id="ds-add-option" class="button">Add Option</button>
        </div>
        
        <button type="submit" class="button button-primary">Create Survey</button>
    </form>
    
    <h2>Existing Surveys</h2>
    <table class="ds-survey-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Question</th>
                <th>Status</th>
                <th>Shortcode</th>
                <th>Actions</th>
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
                <td><code title="Click to copy shortcode">[dynamic_survey id="<?php echo esc_attr($survey->id); ?>"]</code></td>
                <td>
                    <button class="button ds-delete-survey" data-id="<?php echo esc_attr($survey->id); ?>">Delete</button>
                    <button class="button ds-toggle-status" data-id="<?php echo esc_attr($survey->id); ?>">
                        <?php echo $survey->status === 'open' ? 'Close' : 'Open'; ?>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div> 