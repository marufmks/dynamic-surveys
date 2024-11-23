jQuery(document).ready(function($) {
    // Configure Toastr
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": true,
        "timeOut": "3000"
    };

    // Helper function to escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Handle vote submission
    $('.ds-vote-form').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const surveyId = $form.data('survey-id');
        const selectedOption = $form.find('input[name="survey_option"]:checked').val();
        
        if (!selectedOption) {
            toastr.warning(wp.i18n.__('Please select an option to vote', 'dynamic-surveys'));
            return;
        }
        
        const $submitButton = $form.find('button[type="submit"]');
        $submitButton.prop('disabled', true);
        
        $.ajax({
            url: dsFrontend.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ds_submit_vote',
                nonce: dsFrontend.nonce,
                survey_id: surveyId,
                option: selectedOption
            },
            success: function(response) {
                if (response.success) {
                    // Create results display
                    const $results = $('<div class="ds-survey-results"></div>');
                    $results.append('<h3>' + escapeHtml($form.siblings('h3').text()) + '</h3>');
                    $results.append('<canvas id="ds-results-chart-' + escapeHtml(surveyId) + '"></canvas>');
                    
                    // Replace form with results
                    $form.parent().replaceWith($results);
                    
                    // Initialize chart
                    const ctx = document.getElementById('ds-results-chart-' + surveyId).getContext('2d');
                    new Chart(ctx, response.data.results);
                    
                    toastr.success(response.data.message || wp.i18n.__('Vote submitted successfully!', 'dynamic-surveys'));
                } else {
                    toastr.error(response.data.message || wp.i18n.__('Error submitting vote', 'dynamic-surveys'));
                    $submitButton.prop('disabled', false);
                }
            },
            error: function() {
                toastr.error(wp.i18n.__('Error submitting vote. Please try again.', 'dynamic-surveys'));
                $submitButton.prop('disabled', false);
            }
        });
    });

    // Initialize existing results charts
    $('.ds-survey-results canvas').each(function() {
        const $canvas = $(this);
        const results = $canvas.data('results');
        
        if (results) {
            const ctx = this.getContext('2d');
            new Chart(ctx, results);
            toastr.info(wp.i18n.__('Survey results loaded', 'dynamic-surveys'));
        }
    });
}); 