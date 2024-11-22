jQuery(document).ready(function($) {
    // Configure Toastr
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": true,
        "timeOut": "3000"
    };

    // Use a single event delegation for all dynamic elements
    const $adminWrap = $('.ds-admin-wrap');

    // Add new option - single row
    $adminWrap.on('click', '#ds-add-option', function(e) {
        e.preventDefault();
        const newOption = `
            <div class="option-row">
                <input type="text" name="options[]" required>
                <button type="button" class="ds-remove-option">Remove</button>
            </div>
        `;
        $('#ds-options').append(newOption);
        toastr.success('New option added');
    });

    // Remove option using event delegation
    $adminWrap.on('click', '.ds-remove-option', function(e) {
        e.preventDefault();
        const totalOptions = $('#ds-options .option-row').length;
        if (totalOptions > 2) {
            $(this).closest('.option-row').remove();
            toastr.info('Option removed');
        } else {
            toastr.warning('A survey must have at least two options.');
        }
    });

    // Handle survey creation
    const $createSurveyForm = $('#ds-create-survey');
    let isSubmitting = false;

    // Remove any existing submit handlers first
    $createSurveyForm.off('submit');

    // Add the submit handler
    $createSurveyForm.on('submit', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        
        if (isSubmitting) {
            console.log('Form is already submitting');
            return false;
        }
        
        // Validate if all options are filled
        let emptyOptions = false;
        $('#ds-options input[type="text"]').each(function() {
            if (!$(this).val().trim()) {
                emptyOptions = true;
                return false;
            }
        });

        if (emptyOptions) {
            toastr.error('Please fill in all option fields');
            return false;
        }
        
        isSubmitting = true;
        console.log('Starting form submission');
        
        const $submitButton = $(this).find('button[type="submit"]');
        $submitButton.prop('disabled', true);
        
        const formData = new FormData(this);
        formData.append('action', 'ds_create_survey');
        formData.append('nonce', dsAdmin.nonce);
        
        $.ajax({
            url: dsAdmin.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Form submission response:', response);
                if (response.success) {
                    $createSurveyForm[0].reset();
                    
                    $('#ds-options').html(`
                        <div class="option-row">
                            <input type="text" name="options[]" required>
                            <button type="button" class="ds-remove-option">Remove</button>
                        </div>
                        <div class="option-row">
                            <input type="text" name="options[]" required>
                            <button type="button" class="ds-remove-option">Remove</button>
                        </div>
                    `);
                    
                    const survey = response.data.survey;
                    const newRow = `
                        <tr>
                            <td>${survey.title}</td>
                            <td>${survey.question}</td>
                            <td>${survey.status}</td>
                            <td><code title="Click to copy shortcode">[dynamic_survey id="${survey.id}"]</code></td>
                            <td>
                                <button class="button ds-delete-survey" data-id="${survey.id}">Delete</button>
                                <button class="button ds-toggle-status" data-id="${survey.id}">Close</button>
                            </td>
                        </tr>
                    `;
                    $('.ds-survey-table tbody').prepend(newRow);
                    
                    toastr.success(response.data.message || 'Survey created successfully!');
                } else {
                    toastr.error(response.data.message || 'Error creating survey');
                }
            },
            error: function() {
                toastr.error('Error creating survey. Please try again.');
            },
            complete: function() {
                console.log('Form submission complete');
                setTimeout(() => {
                    isSubmitting = false;
                    $submitButton.prop('disabled', false);
                }, 1000);
            }
        });

        return false;
    });

    // Handle survey deletion with event delegation
    $adminWrap.on('click', '.ds-delete-survey', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $button = $(this);
        if ($button.data('processing')) return;
        
        if (!confirm('Are you sure you want to delete this survey?')) {
            return;
        }
        
        $button.data('processing', true);
        const surveyId = $button.data('id');
        const $row = $button.closest('tr');
        
        $button.prop('disabled', true);
        
        $.ajax({
            url: dsAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ds_delete_survey',
                nonce: dsAdmin.nonce,
                survey_id: surveyId
            },
            success: function(response) {
                if (response.success) {
                    $row.fadeOut(400, function() {
                        $(this).remove();
                    });
                    toastr.success('Survey deleted successfully');
                } else {
                    toastr.error(response.data.message || 'Failed to delete survey');
                }
            },
            error: function() {
                toastr.error('Error deleting survey. Please try again.');
            },
            complete: function() {
                $button.data('processing', false);
                $button.prop('disabled', false);
            }
        });
    });

    // Handle survey status toggle with event delegation
    $adminWrap.on('click', '.ds-toggle-status', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $button = $(this);
        if ($button.data('processing')) return;
        
        $button.data('processing', true);
        const surveyId = $button.data('id');
        const currentStatus = $button.text().toLowerCase();
        
        $button.prop('disabled', true);
        
        $.ajax({
            url: dsAdmin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ds_toggle_survey_status',
                nonce: dsAdmin.nonce,
                survey_id: surveyId,
                current_status: currentStatus === 'close' ? 'open' : 'closed'
            },
            success: function(response) {
                if (response.success) {
                    $button.text(response.data.new_status === 'open' ? 'Close' : 'Open');
                    $button.closest('tr').find('td:nth-child(3)').text(response.data.new_status);
                    toastr.success('Survey status updated successfully');
                } else {
                    toastr.error(response.data.message || 'Failed to update survey status');
                }
            },
            error: function() {
                toastr.error('Error updating survey status. Please try again.');
            },
            complete: function() {
                $button.data('processing', false);
                $button.prop('disabled', false);
            }
        });
    });

    // Handle shortcode copying
    $(document).on('click', '.ds-survey-table code', function(e) {
        e.preventDefault();
        
        const shortcode = $(this).text();
        
        // Create temporary textarea
        const $temp = $("<textarea>");
        $("body").append($temp);
        $temp.val(shortcode).select();
        
        try {
            // Copy text
            document.execCommand("copy");
            toastr.success('Shortcode copied to clipboard!');
        } catch (err) {
            toastr.error('Failed to copy shortcode');
            console.error('Failed to copy:', err);
        }
        
        // Remove temporary textarea
        $temp.remove();
    });

    // Add tooltip on shortcode hover
    $(document).on('mouseenter', '.ds-survey-table code', function() {
        $(this).attr('title', 'Click to copy shortcode');
    });
}); 