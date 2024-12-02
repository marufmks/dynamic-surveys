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

    // Use a single event delegation for all dynamic elements
    const $adminWrap = $('.ds-admin-wrap');

    // Add new option - single row
    $adminWrap.on('click', '#ds-add-option', function(e) {
        e.preventDefault();
        const newOption = `
            <div class="option-row">
                <input type="text" name="options[]" required>
                <button type="button" class="ds-remove-option">${wp.i18n.__('Remove', 'dynamic-surveys')}</button>
            </div>
        `;
        $('#ds-options').append(newOption);
        toastr.success(wp.i18n.__('New option added', 'dynamic-surveys'));
    });

    // Remove option using event delegation
    $adminWrap.on('click', '.ds-remove-option', function(e) {
        e.preventDefault();
        const totalOptions = $('#ds-options .option-row').length;
        if (totalOptions > 2) {
            $(this).closest('.option-row').remove();
            toastr.info(wp.i18n.__('Option removed', 'dynamic-surveys'));
        } else {
            toastr.warning(wp.i18n.__('A survey must have at least two options.', 'dynamic-surveys'));
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
            toastr.error(wp.i18n.__('Please fill in all option fields', 'dynamic-surveys'));
            return false;
        }
        
        isSubmitting = true;
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
                if (response.success) {
                    $createSurveyForm[0].reset();
                    
                    $('#ds-options').html(`
                        <div class="option-row">
                            <input type="text" name="options[]" required>
                            <button type="button" class="ds-remove-option">${wp.i18n.__('Remove', 'dynamic-surveys')}</button>
                        </div>
                        <div class="option-row">
                            <input type="text" name="options[]" required>
                            <button type="button" class="ds-remove-option">${wp.i18n.__('Remove', 'dynamic-surveys')}</button>
                        </div>
                    `);
                    
                    const survey = response.data.survey;
                    const newRow = `
                        <tr>
                            <td>${escapeHtml(survey.title)}</td>
                            <td>${escapeHtml(survey.question)}</td>
                            <td>${escapeHtml(survey.status)}</td>
                            <td><code title="${wp.i18n.__('Click to copy shortcode', 'dynamic-surveys')}">[dynamic_survey id="${escapeHtml(survey.id)}"]</code></td>
                            <td>
                                <button class="button ds-delete-survey" data-id="${escapeHtml(survey.id)}">
                                    ${wp.i18n.__('Delete', 'dynamic-surveys')}
                                </button>
                                <button class="button ds-toggle-status" data-id="${escapeHtml(survey.id)}">
                                    ${wp.i18n.__('Close', 'dynamic-surveys')}
                                </button>
                            </td>
                        </tr>
                    `;
                    $('.ds-survey-table tbody').prepend(newRow);
                    
                    toastr.success(response.data.message || wp.i18n.__('Survey created successfully!', 'dynamic-surveys'));
                } else {
                    toastr.error(response.data.message || wp.i18n.__('Error creating survey', 'dynamic-surveys'));
                }
            },
            error: function() {
                toastr.error(wp.i18n.__('Error creating survey. Please try again.', 'dynamic-surveys'));
            },
            complete: function() {
                setTimeout(() => {
                    isSubmitting = false;
                    $submitButton.prop('disabled', false);
                }, 1000);
            }
        });

        return false;
    });

    // Handle survey status toggle
    $(document).on('click', '.ds-toggle-status', function(e) {
        e.preventDefault();
        
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
                    // Update button text
                    const newStatus = response.data.new_status;
                    $button.text(newStatus === 'open' ? wp.i18n.__('Close', 'dynamic-surveys') : wp.i18n.__('Open', 'dynamic-surveys'));
                    
                    // Update status cell
                    $button.closest('tr').find('td:nth-child(3)').text(newStatus);
                    
                    toastr.success(wp.i18n.__('Survey status updated successfully', 'dynamic-surveys'));
                } else {
                    toastr.error(response.data.message || wp.i18n.__('Failed to update survey status', 'dynamic-surveys'));
                }
            },
            error: function() {
                toastr.error(wp.i18n.__('Error updating survey status. Please try again.', 'dynamic-surveys'));
            },
            complete: function() {
                $button.data('processing', false);
                $button.prop('disabled', false);
            }
        });
    });

    // Handle shortcode copying
    $(document).on('click', '.ds-survey-table code', function() {
        const shortcode = $(this).text();
        
        // Create temporary textarea
        const $temp = $("<textarea>");
        $("body").append($temp);
        $temp.val(shortcode).select();
        
        try {
            // Execute copy command
            document.execCommand("copy");
            toastr.success(wp.i18n.__('Shortcode copied to clipboard!', 'dynamic-surveys'));
        } catch (err) {
            toastr.error(wp.i18n.__('Failed to copy shortcode', 'dynamic-surveys'));
            console.error('Copy failed:', err);
        }
        
        // Remove temporary textarea
        $temp.remove();
    });

    // Handle survey deletion
    $(document).on('click', '.ds-delete-survey', function(e) {
        e.preventDefault();
        
        if (!confirm(wp.i18n.__('Are you sure you want to delete this survey?', 'dynamic-surveys'))) {
            return;
        }
        
        const $button = $(this);
        if ($button.data('processing')) return;
        
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
                    toastr.success(wp.i18n.__('Survey deleted successfully', 'dynamic-surveys'));
                } else {
                    toastr.error(response.data.message || wp.i18n.__('Failed to delete survey', 'dynamic-surveys'));
                }
            },
            error: function() {
                toastr.error(wp.i18n.__('Error deleting survey. Please try again.', 'dynamic-surveys'));
            },
            complete: function() {
                $button.data('processing', false);
                $button.prop('disabled', false);
            }
        });
    });

    $('.ds-export-csv').on('click', function(e) {
        e.preventDefault();
        const surveyId = $(this).data('survey-id');
        const url = dsAdmin.ajaxUrl + '?action=ds_export_survey&survey_id=' + surveyId + '&nonce=' + dsAdmin.nonce;
        window.location.href = url;
    });
}); 