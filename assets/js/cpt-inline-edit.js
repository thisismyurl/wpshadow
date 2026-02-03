/**
 * CPT Inline Editing
 *
 * Handles inline editing of custom fields in quick edit.
 *
 * @package    WPShadow
 * @subpackage Assets
 * @since      1.6034.1200
 */

(function($) {
    'use strict';

    /**
     * Initialize inline editing
     */
    function initInlineEdit() {
        // Populate quick edit fields when opened
        $(document).on('click', '.editinline', function() {
            const postId = $(this).closest('tr').attr('id').replace('post-', '');
            populateQuickEditFields(postId);
        });

        // Save inline edits
        $('#the-list').on('click', '.save .button', handleInlineSave);
    }

    /**
     * Populate quick edit fields with current values
     */
    function populateQuickEditFields(postId) {
        const $row = $('#post-' + postId);
        const $editRow = $('#edit-' + postId);

        if (!$editRow.length) {
            return;
        }

        // Get post type
        const postType = $row.find('.post-state').data('post-type') || 
                        $('#posts-filter input[name="post_type"]').val() || 
                        'post';

        // Populate fields based on post type
        switch (postType) {
            case 'testimonial':
                populateTestimonialFields($row, $editRow);
                break;
            case 'team_member':
                populateTeamFields($row, $editRow);
                break;
            case 'wps_event':
                populateEventFields($row, $editRow);
                break;
            case 'service':
                populateServiceFields($row, $editRow);
                break;
        }
    }

    /**
     * Populate testimonial-specific fields
     */
    function populateTestimonialFields($row, $editRow) {
        const rating = $row.find('.column-rating').data('rating') || '';
        $editRow.find('select[name="wpshadow_rating"]').val(rating);
    }

    /**
     * Populate team member-specific fields
     */
    function populateTeamFields($row, $editRow) {
        const jobTitle = $row.find('.column-job-title').text().trim();
        $editRow.find('input[name="wpshadow_job_title"]').val(jobTitle);
    }

    /**
     * Populate event-specific fields
     */
    function populateEventFields($row, $editRow) {
        const startDate = $row.find('.column-start-date').data('timestamp') || '';
        if (startDate) {
            const date = new Date(parseInt(startDate) * 1000);
            const dateStr = date.toISOString().slice(0, 16); // Format: YYYY-MM-DDTHH:mm
            $editRow.find('input[name="wpshadow_start_date"]').val(dateStr);
        }
    }

    /**
     * Populate service-specific fields
     */
    function populateServiceFields($row, $editRow) {
        const price = $row.find('.column-price').data('price') || '';
        $editRow.find('input[name="wpshadow_price"]').val(price);
    }

    /**
     * Handle inline save
     */
    function handleInlineSave() {
        const $editRow = $(this).closest('tr');
        const postId = $editRow.attr('id').replace('edit-', '');
        
        // Get post type
        const postType = $('#posts-filter input[name="post_type"]').val() || 'post';

        // Get custom field values
        const customFields = getCustomFieldValues($editRow, postType);

        if (Object.keys(customFields).length === 0) {
            return; // No custom fields to save
        }

        // Add custom fields to the inline-save data
        $.each(customFields, function(key, value) {
            $('<input>')
                .attr('type', 'hidden')
                .attr('name', key)
                .val(value)
                .appendTo($editRow.find('.inline-edit-col'));
        });

        // WordPress will handle the save via its inline-edit mechanism
    }

    /**
     * Get custom field values from edit row
     */
    function getCustomFieldValues($editRow, postType) {
        const fields = {};

        switch (postType) {
            case 'testimonial':
                const rating = $editRow.find('select[name="wpshadow_rating"]').val();
                if (rating) {
                    fields.wpshadow_rating = rating;
                }
                break;

            case 'team_member':
                const jobTitle = $editRow.find('input[name="wpshadow_job_title"]').val();
                if (jobTitle) {
                    fields.wpshadow_job_title = jobTitle;
                }
                break;

            case 'wps_event':
                const startDate = $editRow.find('input[name="wpshadow_start_date"]').val();
                if (startDate) {
                    fields.wpshadow_start_date = startDate;
                }
                break;

            case 'service':
                const price = $editRow.find('input[name="wpshadow_price"]').val();
                if (price) {
                    fields.wpshadow_price = price;
                }
                break;
        }

        return fields;
    }

    // Initialize when DOM is ready
    $(document).ready(function() {
        // Wait for WordPress inline edit to initialize
        setTimeout(function() {
            initInlineEdit();
        }, 500);
    });

})(jQuery);
