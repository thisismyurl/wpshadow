/**
 * WPShadow Admin Scripts
 * Common JavaScript functionality for all admin pages
 * Consolidates inline scripts from various PHP modules
 * 
 * @package WPShadow
 */

(function($) {
    'use strict';

    /**
     * WPShadow Admin Module
     */
    const WPShadowAdmin = {
        
        /**
         * Initialize admin functionality
         */
        init: function() {
            this.initModals();
            this.initFormHandlers();
            this.initAjaxHandlers();
            this.initToggles();
            this.initToolLinks();
        },

        /**
         * Modal Management
         */
        initModals: function() {
            const self = this;

            // Open modal handler
            $(document).on('click', '[data-modal-trigger]', function(e) {
                e.preventDefault();
                const modalId = $(this).data('modal-trigger');
                self.openModal(modalId);
            });

            // Close modal button
            $(document).on('click', '.wps-modal-close', function(e) {
                e.preventDefault();
                const modal = $(this).closest('.wps-modal');
                self.closeModal(modal);
            });

            // Close modal on background click
            $(document).on('click', '.wps-modal', function(e) {
                if ($(e.target).hasClass('wps-modal')) {
                    self.closeModal($(this));
                }
            });

            // Close modal on Escape key
            $(document).on('keydown', function(e) {
                if (e.keyCode === 27) {
                    self.closeModal($('.wps-modal.active'));
                }
            });
        },

        /**
         * Open a modal by ID
         */
        openModal: function(modalId) {
            const modal = $('#' + modalId);
            if (modal.length) {
                modal.addClass('active');
                $('body').css('overflow', 'hidden');
            }
        },

        /**
         * Close a modal
         */
        closeModal: function(modal) {
            if (modal && modal.length) {
                modal.removeClass('active');
                $('body').css('overflow', '');
            }
        },

        /**
         * Form Handlers
         */
        initFormHandlers: function() {
            const self = this;

            // Generic form submit with AJAX
            $(document).on('submit', '[data-ajax-form]', function(e) {
                e.preventDefault();
                self.handleFormSubmit($(this));
            });

            // Inline edit handlers
            $(document).on('click', '[data-edit-field]', function(e) {
                e.preventDefault();
                self.enableFieldEdit($(this));
            });
        },

        /**
         * Handle form submission with AJAX
         */
        handleFormSubmit: function(form) {
            const self = this;
            const formData = new FormData(form[0]);
            const button = form.find('button[type="submit"]');
            const originalText = button.text();

            // Show loading state
            button.prop('disabled', true).text(wpshadowAdmin.i18n.saving || 'Saving...');

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        self.showNotice('success', response.message || wpshadowAdmin.i18n.saved);
                        if (response.redirect) {
                            setTimeout(() => {
                                window.location.href = response.redirect;
                            }, 1000);
                        }
                    } else {
                        self.showNotice('error', response.message || wpshadowAdmin.i18n.error);
                    }
                },
                error: function(xhr, status, error) {
                    self.showNotice('error', wpshadowAdmin.i18n.error);
                    console.error('AJAX error:', error);
                },
                complete: function() {
                    button.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Enable inline field editing
         */
        enableFieldEdit: function(trigger) {
            const fieldName = trigger.data('edit-field');
            const currentValue = trigger.data('value') || trigger.text();
            const input = $('<input type="text" value="' + currentValue + '" class="wps-inline-edit" />');
            
            trigger.replaceWith(input);
            input.focus().select();

            const saveEdit = (newValue) => {
                if (newValue && newValue !== currentValue) {
                    this.sendFieldUpdate(fieldName, newValue);
                } else {
                    input.replaceWith(trigger);
                }
            };

            input.on('blur', function() {
                saveEdit($(this).val());
            }).on('keydown', function(e) {
                if (e.keyCode === 13) { // Enter
                    saveEdit($(this).val());
                } else if (e.keyCode === 27) { // Escape
                    input.replaceWith(trigger);
                }
            });
        },

        /**
         * Send field update via AJAX
         */
        sendFieldUpdate: function(fieldName, newValue) {
            const self = this;
            
            $.ajax({
                url: wpshadowAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wps_update_field',
                    field: fieldName,
                    value: newValue,
                    nonce: wpshadowAdmin.nonce
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        self.showNotice('success', response.message);
                    } else {
                        self.showNotice('error', response.message);
                    }
                },
                error: function() {
                    self.showNotice('error', wpshadowAdmin.i18n.error);
                }
            });
        },

        /**
         * AJAX Handlers
         */
        initAjaxHandlers: function() {
            const self = this;

            // Generic AJAX action buttons
            $(document).on('click', '[data-action]', function(e) {
                e.preventDefault();
                const action = $(this).data('action');
                const confirm = $(this).data('confirm');

                if (confirm && !window.confirm(confirm)) {
                    return;
                }

                self.performAction(action, $(this));
            });
        },

        /**
         * Perform AJAX action
         */
        performAction: function(action, button) {
            const self = this;
            const originalText = button.text();

            button.prop('disabled', true);

            $.ajax({
                url: wpshadowAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: action,
                    nonce: wpshadowAdmin.nonce,
                    id: button.data('id') || ''
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        self.showNotice('success', response.message);
                        if (response.redirect) {
                            setTimeout(() => {
                                window.location.href = response.redirect;
                            }, 1000);
                        } else if (button.data('refresh')) {
                            location.reload();
                        }
                    } else {
                        self.showNotice('error', response.message);
                    }
                },
                error: function() {
                    self.showNotice('error', wpshadowAdmin.i18n.error);
                },
                complete: function() {
                    button.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Toggle Handlers
         */
        initToggles: function() {
            const self = this;

            // Toggle switch handler
            $(document).on('change', '[data-toggle-action]', function() {
                const action = $(this).data('toggle-action');
                const enabled = $(this).is(':checked');
                const toggleSwitch = $(this).closest('.wps-status-badge');

                self.performToggle(action, enabled, toggleSwitch);
            });
        },

        /**
         * Handle toggle switch state
         */
        performToggle: function(action, enabled, toggleElement) {
            const self = this;

            $.ajax({
                url: wpshadowAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: action,
                    enabled: enabled ? 1 : 0,
                    nonce: wpshadowAdmin.nonce
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        self.showNotice('success', response.message);
                    } else {
                        // Revert toggle on error
                        toggleElement.find('input[type="checkbox"]').prop('checked', !enabled);
                        self.showNotice('error', response.message);
                    }
                },
                error: function() {
                    // Revert toggle on error
                    toggleElement.find('input[type="checkbox"]').prop('checked', !enabled);
                    self.showNotice('error', wpshadowAdmin.i18n.error);
                }
            });
        },

        /**
         * Tool/Help Link Handlers
         */
        initToolLinks: function() {
            $(document).on('click', '.wps-tool-link', function(e) {
                const href = $(this).attr('href');
                if (href === '#') {
                    e.preventDefault();
                }
            });
        },

        /**
         * Notification Management
         */
        showNotice: function(type, message) {
            const noticeClass = 'notice notice-' + type;
            const notice = $('<div class="' + noticeClass + ' is-dismissible"><p>' + message + '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>');

            $('.wp-header-end').before(notice);

            // Auto-dismiss success notices after 3 seconds
            if (type === 'success') {
                setTimeout(() => {
                    notice.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 3000);
            }

            // Dismiss button handler
            notice.on('click', '.notice-dismiss', function() {
                notice.fadeOut(300, function() {
                    $(this).remove();
                });
            });
        },

        /**
         * Spinner utilities
         */
        showSpinner: function(container) {
            const spinner = $('<div class="wps-spinner dashicons dashicons-update"></div>');
            $(container).append(spinner);
            return spinner;
        },

        removeSpinner: function(spinner) {
            $(spinner).remove();
        },

        /**
         * Helper: Format date
         */
        formatDate: function(date) {
            if (typeof date === 'string') {
                date = new Date(date);
            }
            return date.toLocaleDateString(wpshadowAdmin.locale || 'en-US');
        },

        /**
         * Helper: Format number
         */
        formatNumber: function(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        WPShadowAdmin.init();
        // Make available globally for other scripts
        window.WPShadowAdmin = WPShadowAdmin;
    });

})(jQuery);
